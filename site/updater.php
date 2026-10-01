<?php
/*
 * One-click update of the site code from GitHub (admin → «به‌روزرسانی»).
 * Replaces code only. Never touches the database, uploaded images, videos, files or fonts
 * (only new shipped media that is missing is added). The previous code is kept for rollback.
 * In Docker the applied code is also kept in the database volume, so it survives a container
 * restart or recreate (deploy/docker/entrypoint.sh re-applies it).
 */
require_once __DIR__ . '/lib.php';

const UPD_REPO   = 'farvam/farvam-bot';
const UPD_BRANCH = 'claude/blissful-mendel-p3sz7h';
const UPD_MEDIA  = ['images', 'files', 'assets/fonts'];         // owner's uploads: add missing only
const UPD_SKIP   = ['build-static.php', 'config.php', '.gitignore', 'README.md'];

function upd_dir(): string { return (DB_FILE !== '' ? dirname(DB_FILE) : DATA_DIR) . '/update'; }
function upd_info(): array { return load_json('update'); }

function upd_http(string $url): string {
    $c = curl_init($url);
    curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 5,
        CURLOPT_USERAGENT => 'panel-market-updater', CURLOPT_CONNECTTIMEOUT => 20, CURLOPT_TIMEOUT => 240,
        CURLOPT_HTTPHEADER => ['Accept: application/vnd.github+json']]);
    $body = curl_exec($c); $code = curl_getinfo($c, CURLINFO_HTTP_CODE); $err = curl_error($c); curl_close($c);
    if ($body === false || $code !== 200) throw new RuntimeException('اتصال به گیت‌هاب برقرار نشد (' . ($err ?: "HTTP $code") . ').');
    return $body;
}

/* latest version on GitHub: [sha, date, message] */
function upd_latest(): array {
    $d = json_decode(upd_http('https://api.github.com/repos/' . UPD_REPO . '/commits/' . UPD_BRANCH), true);
    if (empty($d['sha'])) throw new RuntimeException('پاسخ گیت‌هاب نامعتبر بود.');
    return ['sha' => $d['sha'], 'date' => $d['commit']['committer']['date'] ?? '', 'message' => strtok((string)($d['commit']['message'] ?? ''), "\n")];
}

function upd_rm(string $p): void {
    if (is_link($p) || is_file($p)) { @unlink($p); return; }
    if (!is_dir($p)) return;
    foreach (scandir($p) as $f) if ($f !== '.' && $f !== '..') upd_rm("$p/$f");
    @rmdir($p);
}

/* relative paths of all files under $root */
function upd_files(string $root): array {
    $out = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) if ($f->isFile()) $out[] = substr($f->getPathname(), strlen($root) + 1);
    sort($out);
    return $out;
}
function upd_is_media(string $rel): bool {
    foreach (UPD_MEDIA as $m) if (str_starts_with($rel, "$m/")) return true;
    return false;
}

/* copy code from $src into the live site; returns [changed code files, added media files] */
function upd_install_tree(string $src, string $dst): array {
    $code = 0; $media = 0;
    foreach (upd_files($src) as $rel) {
        if (in_array($rel, UPD_SKIP, true) || basename($rel) === '.applied' || str_ends_with($rel, '.upd')) continue;
        $to = "$dst/$rel";
        $isData = str_starts_with($rel, 'data/');
        if (upd_is_media($rel) || ($isData && !db())) { if (file_exists($to)) continue; $media++; }    // never overwrite owner's files
        elseif (is_file($to) && sha1_file($to) === sha1_file("$src/$rel")) continue;
        else $code++;
        if (!is_dir(dirname($to)) && !mkdir(dirname($to), 0755, true)) throw new RuntimeException("پوشه $rel ساخته نشد.");
        if (!copy("$src/$rel", "$to.upd") || !rename("$to.upd", $to)) throw new RuntimeException("فایل $rel نوشته نشد (دسترسی نوشتن).");
    }
    return [$code, $media];
}

/* snapshot of the live code (no media, no live data) into $to */
function upd_snapshot_code(string $site, string $to): void {
    upd_rm($to); mkdir($to, 0755, true);
    foreach (upd_files($site) as $rel) {
        if (upd_is_media($rel) || in_array($rel, UPD_SKIP, true) || str_starts_with($rel, 'data/') || str_ends_with($rel, '.upd')) continue;
        @mkdir(dirname("$to/$rel"), 0755, true); copy("$site/$rel", "$to/$rel");
    }
    foreach (glob("$site/data/*.json") as $f) if (db()) { @mkdir("$to/data", 0755, true); copy($f, "$to/data/" . basename($f)); }
}

/* PHP syntax check of the new code, when the php binary is available */
function upd_lint(string $src): void {
    $php = is_executable('/usr/local/bin/php') ? '/usr/local/bin/php' : '';
    if ($php === '' || !function_exists('exec')) return;
    foreach (upd_files($src) as $rel) {
        if (!str_ends_with($rel, '.php')) continue;
        $out = []; $rc = 0; exec(escapeshellarg($php) . ' -l ' . escapeshellarg("$src/$rel") . ' 2>&1', $out, $rc);
        if ($rc !== 0) throw new RuntimeException("نسخه جدید خطا دارد ($rel)؛ چیزی تغییر نکرد.");
    }
}

function upd_lock() {
    $d = upd_dir(); if (!is_dir($d)) mkdir($d, 0755, true);
    $l = fopen("$d/lock", 'c');
    if (!$l || !flock($l, LOCK_EX | LOCK_NB)) throw new RuntimeException('یک به‌روزرسانی دیگر در حال انجام است.');
    return $l;
}

/* keep the applied code where the Docker entrypoint re-applies it after a container recreate */
function upd_persist(string $src): void {
    if (DB_FILE === '') return;
    $cur = upd_dir() . '/current';
    upd_snapshot_code($src, $cur);
    file_put_contents("$cur/.applied", (string)time());
}

function upd_apply(): string {
    @set_time_limit(600);
    $lock = upd_lock(); $d = upd_dir(); $site = __DIR__;
    $latest = upd_latest(); $info = upd_info();
    if (($info['sha'] ?? '') === $latest['sha']) return 'سایت همین الان آخرین نسخه را دارد.';
    $tmp = "$d/tmp"; upd_rm($tmp); mkdir($tmp, 0755, true);
    try {
        file_put_contents("$tmp/u.tar.gz", upd_http('https://api.github.com/repos/' . UPD_REPO . '/tarball/' . $latest['sha']));
        (new PharData("$tmp/u.tar.gz"))->extractTo("$tmp/x", null, true);
        $src = (glob("$tmp/x/*/site", GLOB_ONLYDIR) ?: [''])[0];
        foreach (['index.php', 'lib.php', 'admin/index.php', 'updater.php', 'partials/layout.php'] as $must)
            if ($src === '' || !is_file("$src/$must")) throw new RuntimeException('بسته دریافتی کامل نیست؛ چیزی تغییر نکرد.');
        upd_lint($src);
        upd_snapshot_code($site, "$d/prev");                       // for «بازگشت به نسخه قبل»
        [$code, $media] = upd_install_tree($src, $site);
        upd_persist($src);
        if (function_exists('opcache_reset')) opcache_reset();
        save_json('update', ['sha' => $latest['sha'], 'date' => $latest['date'], 'message' => $latest['message'], 'at' => date('c'),
                             'prev' => ['sha' => $info['sha'] ?? '', 'date' => $info['date'] ?? '']]);
        return 'به‌روزرسانی انجام شد: ' . fa_digits($code) . ' فایل برنامه عوض شد' . ($media ? '، ' . fa_digits($media) . ' فایل تصویر/فایل جدید اضافه شد' : '') . '. اطلاعات، عکس‌ها و ویدیوهای شما دست نخورد.';
    } finally { upd_rm($tmp); flock($lock, LOCK_UN); }
}

function upd_can_rollback(): bool { return is_file(upd_dir() . '/prev/index.php'); }

function upd_rollback(): string {
    $lock = upd_lock(); $prev = upd_dir() . '/prev';
    try {
        if (!upd_can_rollback()) throw new RuntimeException('نسخه قبلی برای برگرداندن وجود ندارد.');
        $keep = upd_dir() . '/prev-apply'; upd_rm($keep); rename($prev, $keep);
        upd_snapshot_code(__DIR__, $prev);                          // so the rollback itself can be undone
        upd_install_tree($keep, __DIR__);
        upd_persist($keep); upd_rm($keep);
        if (function_exists('opcache_reset')) opcache_reset();
        $info = upd_info();
        save_json('update', ['sha' => $info['prev']['sha'] ?? '', 'date' => $info['prev']['date'] ?? '', 'message' => 'بازگشت به نسخه قبل', 'at' => date('c'),
                             'prev' => ['sha' => $info['sha'] ?? '', 'date' => $info['date'] ?? '']]);
        return 'نسخه قبلی برگردانده شد. اطلاعات، عکس‌ها و ویدیوهای شما دست نخورد.';
    } finally { flock($lock, LOCK_UN); }
}
