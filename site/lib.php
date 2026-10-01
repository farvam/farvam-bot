<?php
require_once __DIR__ . '/config.php';
if (!defined('DB_FILE')) define('DB_FILE', (string)getenv('FARVAM_DB'));

/* ---------- data ----------
 * With DB_FILE set (Docker), everything is stored in SQLite: table `store` holds each dataset
 * (content, articles, leads, ...) and `store_history` keeps previous versions of edited ones.
 * The shipped data/*.json files are only the defaults until the first save.
 * Without DB_FILE (shared hosting) the JSON files in data/ are used directly. */
const HISTORY_SETS = ['content', 'articles', 'presentation', 'engine', 'admin'];
const HISTORY_KEEP = 30;
function db(): ?PDO {
    static $pdo = false;
    if ($pdo !== false) return $pdo;
    $pdo = null;
    if (DB_FILE === '' || !class_exists('PDO') || !in_array('sqlite', PDO::getAvailableDrivers(), true)) return null;
    try {
        $pdo = new PDO('sqlite:' . DB_FILE, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec('PRAGMA busy_timeout = 5000');
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('CREATE TABLE IF NOT EXISTS store (name TEXT PRIMARY KEY, data TEXT NOT NULL, updated_at TEXT NOT NULL)');
        $pdo->exec('CREATE TABLE IF NOT EXISTS store_history (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, data TEXT NOT NULL, saved_at TEXT NOT NULL)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS store_history_name ON store_history (name, id)');
    } catch (Throwable $e) {
        error_log('farvam: database unavailable, using JSON files: ' . $e->getMessage());
        $pdo = null;
    }
    return $pdo;
}
function load_json(string $name, $default = []) {
    if ($pdo = db()) {
        $q = $pdo->prepare('SELECT data FROM store WHERE name = ?'); $q->execute([$name]);
        $row = $q->fetchColumn();
        if ($row !== false) { $d = json_decode((string)$row, true); return is_array($d) ? $d : $default; }
    }
    $f = DATA_DIR . '/' . $name . '.json';
    if (!is_file($f)) return $default;
    $d = json_decode((string)file_get_contents($f), true);
    return is_array($d) ? $d : $default;
}
function save_json(string $name, $data): bool {
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) return false;
    if ($pdo = db()) {
        try {
            $pdo->beginTransaction();
            if (in_array($name, HISTORY_SETS, true)) {
                $prev = json_encode(load_json($name, null), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if ($prev !== 'null') {
                    $pdo->prepare('INSERT INTO store_history (name, data, saved_at) VALUES (?, ?, ?)')->execute([$name, $prev, date('c')]);
                    $pdo->prepare('DELETE FROM store_history WHERE name = ? AND id NOT IN (SELECT id FROM store_history WHERE name = ? ORDER BY id DESC LIMIT ' . HISTORY_KEEP . ')')->execute([$name, $name]);
                }
            }
            $pdo->prepare('INSERT INTO store (name, data, updated_at) VALUES (?, ?, ?) ON CONFLICT(name) DO UPDATE SET data = excluded.data, updated_at = excluded.updated_at')
                ->execute([$name, $json, date('c')]);
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('farvam: save failed: ' . $e->getMessage());
            return false;
        }
    }
    $f = DATA_DIR . '/' . $name . '.json';
    if (is_file($f)) @copy($f, DATA_DIR . '/backup-' . $name . '.json');
    $tmp = $f . '.tmp';
    if (file_put_contents($tmp, $json, LOCK_EX) === false) return false;
    return rename($tmp, $f);
}
/* previous saved versions of a dataset, newest first: [[id, saved_at], ...] */
function data_history(string $name): array {
    if (!($pdo = db())) return [];
    $q = $pdo->prepare('SELECT id, saved_at FROM store_history WHERE name = ? ORDER BY id DESC'); $q->execute([$name]);
    return $q->fetchAll(PDO::FETCH_NUM);
}
function data_restore(string $name, int $id): bool {
    if (!($pdo = db())) return false;
    $q = $pdo->prepare('SELECT data FROM store_history WHERE name = ? AND id = ?'); $q->execute([$name, $id]);
    $d = json_decode((string)$q->fetchColumn(), true);
    return is_array($d) && save_json($name, $d);
}
function content(): array { static $c = null; if ($c === null) $c = load_json('content'); return $c; }
function articles(bool $onlyPublished = true): array {
    $all = load_json('articles');
    if ($onlyPublished) $all = array_values(array_filter($all, fn($a) => !empty($a['published'])));
    usort($all, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));
    return $all;
}
function article_by_slug(string $slug): ?array {
    foreach (articles() as $a) if (($a['slug'] ?? '') === $slug) return $a;
    return null;
}

/* ---------- output helpers ---------- */
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function c(string $path, $default = '') {
    $v = content();
    foreach (explode('.', $path) as $k) { if (!is_array($v) || !array_key_exists($k, $v)) return $default; $v = $v[$k]; }
    return $v;
}
/* escaped text */
function t(string $path): string { return h(c($path)); }
/* limited HTML for *_html fields (admin-authored) */
function x(string $path): string { return safe_html((string)c($path)); }
function safe_html(string $s): string {
    $s = strip_tags($s, '<b><strong><em><i><br><a><span><small><u><mark><ul><ol><li><p><h2><h3><h4><table><thead><tbody><tr><th><td><blockquote><code><figure><figcaption><img><hr>');
    $s = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $s);
    $s = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2/i', '$1="#"', $s);
    return $s;
}

/* ---------- urls ---------- */
function is_static(): bool { return defined('STATIC_BUILD') && STATIC_BUILD; }
function base(): string { return is_static() ? '' : BASE_PATH; }
function url_home(string $hash = ''): string { return (is_static() ? 'index.html' : BASE_PATH) . $hash; }
function url_blog(): string { return is_static() ? 'blog.html' : (PRETTY_URLS ? BASE_PATH . 'blog/' : BASE_PATH . 'blog/index.php'); }
function url_article(string $slug): string {
    if (is_static()) return 'blog-' . $slug . '.html';
    return PRETTY_URLS ? BASE_PATH . 'blog/' . rawurlencode($slug) : BASE_PATH . 'blog/article.php?slug=' . rawurlencode($slug);
}
function abs_url(string $path = ''): string { return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/'); }
function canonical_article(string $slug): string { return abs_url('blog/' . rawurlencode($slug)); }
function tel(): string { return 'tel:' . c('contact.phone_tel'); }
function wa(string $msg = ''): string { return 'https://wa.me/' . c('contact.whatsapp') . ($msg !== '' ? '?text=' . rawurlencode($msg) : ''); }
/* nav item: '#x' anchors go to home, 'blog' goes to blog */
function nav_href(string $k): string {
    if ($k === 'blog') return url_blog();
    if ($k !== '' && $k[0] === '#') return url_home($k);
    return $k;
}
/* {{a:slug}} inside article bodies becomes a link to that article */
function expand_links(string $html): string {
    return preg_replace_callback('/\{\{a:([a-z0-9\-]+)\}\}/', fn($m) => h(url_article($m[1])), $html);
}

/* ---------- text utilities ---------- */
function fa_digits($s): string { return strtr((string)$s, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']); }
function reading_minutes(string $html): int { $w = count(preg_split('/\s+/u', trim(strip_tags($html)))); return max(1, (int)round($w / 200)); }
function jalali_label(string $iso): string {
    // Gregorian → Jalali (for display only)
    [$gy, $gm, $gd] = array_map('intval', explode('-', substr($iso, 0, 10)));
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100) + intdiv($gy2 + 399, 400) + $gd + $g_d_m[$gm - 1];
    $jy = -1595 + (33 * intdiv($days, 12053)); $days %= 12053;
    $jy += 4 * intdiv($days, 1461); $days %= 1461;
    if ($days > 365) { $jy += intdiv($days - 1, 365); $days = ($days - 1) % 365; }
    $jm = ($days < 186) ? 1 + intdiv($days, 31) : 7 + intdiv($days - 186, 30);
    $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));
    $months = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
    return fa_digits($jd) . ' ' . $months[$jm - 1] . ' ' . fa_digits($jy);
}
/* add ids to h2/h3 for a table of contents */
function toc_and_body(string $html): array {
    $toc = []; $i = 0;
    $body = preg_replace_callback('/<h2>(.*?)<\/h2>/u', function ($m) use (&$toc, &$i) {
        $i++; $id = 's' . $i; $toc[] = ['id' => $id, 't' => strip_tags($m[1])];
        return '<h2 id="' . $id . '">' . $m[1] . '</h2>';
    }, $html);
    return [$toc, $body];
}
function json_ld($data): string {
    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}
function org_ld(): array {
    return [
        '@type' => ['Organization', 'LocalBusiness'], '@id' => abs_url('#org'),
        'name' => c('seo.site_name'), 'alternateName' => 'Farvam', 'url' => abs_url(),
        'logo' => abs_url('images/logo-gold.png'), 'image' => abs_url(c('seo.og_image')),
        'telephone' => c('contact.phone_tel'),
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => c('contact.address'), 'addressLocality' => c('contact.city'), 'addressCountry' => 'IR'],
        'sameAs' => array_values(array_filter([
            c('social.instagram') ? 'https://instagram.com/' . c('social.instagram') : '',
            c('social.telegram') ? 'https://t.me/' . c('social.telegram') : '',
        ])),
    ];
}

/* ---------- v5: extra urls & media ---------- */
function url_present(): string { return is_static() ? 'present.html' : BASE_PATH . 'present.php'; }
function url_segment(string $k): string {
    if (is_static()) return 'for-' . $k . '.html';
    return PRETTY_URLS ? BASE_PATH . 'for/' . rawurlencode($k) : BASE_PATH . 'for.php?role=' . rawurlencode($k);
}
function file_url(string $path): string { return preg_match('#^https?://#', $path) ? $path : base() . ltrim($path, '/'); }
/* Aparat / YouTube / direct video file -> embeddable HTML (lazy) */
function video_embed(string $url, string $title = ''): string {
    $url = trim($url); if ($url === '') return '';
    if (preg_match('#aparat\.com/(?:v|video/video/embed/videohash)/([A-Za-z0-9]+)#', $url, $m))
        return '<iframe src="https://www.aparat.com/video/video/embed/videohash/' . h($m[1]) . '/vt/frame" title="' . h($title) . '" allowfullscreen loading="lazy"></iframe>';
    if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/|youtube\.com/shorts/)([A-Za-z0-9_\-]{6,})#', $url, $m))
        return '<iframe src="https://www.youtube-nocookie.com/embed/' . h($m[1]) . '" title="' . h($title) . '" allow="accelerometer; encrypted-media; picture-in-picture" allowfullscreen loading="lazy"></iframe>';
    if (preg_match('#\.(mp4|webm|m4v)(\?.*)?$#i', $url))
        return '<video src="' . h(file_url($url)) . '" controls preload="none" playsinline></video>';
    return '';
}
function videos_for(?string $role = null): array {
    return array_values(array_filter((array)c('videos.items'), fn($v) => trim($v['url'] ?? '') !== '' && ($role === null || ($v['role'] ?? '') === $role || ($role === '' && ($v['role'] ?? '') === ''))));
}
/* cache-busting version for assets/<file> (changes whenever the file is updated) */
function asset_v(string $file): string { return (string)(@filemtime(__DIR__ . '/assets/' . $file) ?: 1); }
