<?php
/* پنل مدیریت محتوای سایت فَروَم */
require_once dirname(__DIR__) . '/lib.php';

session_name('farvam_admin');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict', 'secure' => !empty($_SERVER['HTTPS']), 'path' => '/']);
session_start();
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('X-Robots-Tag: noindex, nofollow');

function admin_hash(): string { $a = load_json('admin'); return $a['hash'] ?? ADMIN_PASSWORD_HASH; }
function csrf(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function check_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(400); exit('درخواست نامعتبر است. صفحه را دوباره باز کنید.'); } }
function flash(?string $m = null) { if ($m !== null) { $_SESSION['flash'] = $m; return; } $f = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']); return $f; }
function go(string $q = ''): void { header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . ($q ? '?' . $q : '')); exit; }
function writable_ok(): bool { return is_writable(DATA_DIR); }

/* ---------- login throttle ---------- */
function too_many(): bool {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'x'; $d = load_json('login-attempts');
    $recent = array_filter($d[$ip] ?? [], fn($t) => $t > time() - 900);
    return count($recent) >= 5;
}
function note_fail(): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'x'; $d = load_json('login-attempts');
    $d[$ip] = array_values(array_filter($d[$ip] ?? [], fn($t) => $t > time() - 900)); $d[$ip][] = time();
    save_json('login-attempts', $d);
}

$act = $_POST['act'] ?? $_GET['act'] ?? '';
if ($act === 'logout') { session_destroy(); go(); }
if ($act === 'login') {
    check_csrf();
    if (too_many()) { flash('ورودهای ناموفق زیاد بود. ۱۵ دقیقه بعد دوباره امتحان کنید.'); go(); }
    if (password_verify((string)($_POST['password'] ?? ''), admin_hash())) { session_regenerate_id(true); $_SESSION['ok'] = 1; go(); }
    note_fail(); usleep(600000); flash('رمز اشتباه است.'); go();
}
$authed = !empty($_SESSION['ok']);

/* ---------- actions ---------- */
function merge_post($orig, $post) {
    if (!is_array($orig)) {
        if (is_bool($orig)) return $post === '1';
        return is_string($post) ? str_replace("\r\n", "\n", $post) : $orig;
    }
    if (!is_array($post)) return $orig;
    $isList = array_is_list($orig);
    $out = $isList ? [] : $orig;
    foreach ($post as $k => $v) {
        $tmpl = $orig[$k] ?? ($isList && $orig ? $orig[array_key_last($orig)] : '');
        $out[$k] = merge_post($tmpl, $v);
    }
    if ($isList) { $out = array_values($out); }
    return $out;
}
$tab = $_GET['tab'] ?? 'leads';
if ($authed && $act === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="farvam-leads-' . date('Y-m-d') . '.csv"');
    $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF");
    fputcsv($o, ['زمان', 'نام', 'موبایل', 'صنف', 'شهر', 'آشنایی از', 'کانال تبلیغ', 'دغدغه']);
    foreach (load_json('leads') as $l) fputcsv($o, [$l['time'], $l['name'], $l['phone'], $l['role'], $l['city'], $l['source'], $l['utm'], $l['note']]);
    exit;
}
if ($authed && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (!writable_ok()) { flash('پوشه data قابل نوشتن نیست. دسترسی آن را روی هاست ۷۵۵ یا ۷۷۵ بگذارید.'); go("tab=$tab"); }
    switch ($act) {
        case 'save_content':
            $c = load_json('content');
            $new = merge_post($c, $_POST['f'] ?? []);
            // arrays that were fully emptied in the form
            foreach ((array)($_POST['empty'] ?? []) as $path) { $ref = &$new; foreach (explode('.', $path) as $k) { if (!isset($ref[$k])) { unset($ref); continue 2; } $ref = &$ref[$k]; } $ref = []; unset($ref); }
            flash(save_json('content', $new) ? 'تغییرات ذخیره شد.' : 'ذخیره نشد.'); go('tab=content');
        case 'save_json':
            $which = in_array($_POST['which'] ?? '', ['content', 'engine'], true) ? $_POST['which'] : 'content';
            $d = json_decode((string)($_POST['json'] ?? ''), true);
            if (!is_array($d)) { flash('JSON نامعتبر است؛ چیزی ذخیره نشد. خطا: ' . json_last_error_msg()); go("tab=$tab"); }
            flash(save_json($which, $d) ? 'ذخیره شد.' : 'ذخیره نشد.'); go("tab=$tab");
        case 'save_article':
            $all = load_json('articles'); $orig = $_POST['orig_slug'] ?? '';
            $slug = trim(strtolower(preg_replace('/[^a-z0-9\-]+/i', '-', trim((string)$_POST['slug']))), '-');
            if ($slug === '') { flash('نامک (slug) لازم است؛ فقط حروف انگلیسی کوچک، عدد و خط تیره.'); go("tab=articles&edit=" . urlencode($orig ?: 'new')); }
            $faq = [];
            foreach (preg_split('/\R/', (string)($_POST['faq'] ?? '')) as $line) { if (strpos($line, '|') !== false) { [$q, $a] = array_map('trim', explode('|', $line, 2)); if ($q && $a) $faq[] = ['q' => $q, 'a' => $a]; } }
            $item = ['slug' => $slug, 'title' => trim($_POST['title']), 'seo_title' => trim($_POST['seo_title']), 'description' => trim($_POST['description']),
                     'excerpt' => trim($_POST['excerpt']), 'keywords' => trim($_POST['keywords']), 'tag' => trim($_POST['tag']),
                     'date' => $_POST['date'] ?: date('Y-m-d'), 'updated' => date('Y-m-d'), 'published' => !empty($_POST['published']),
                     'body' => str_replace("\r\n", "\n", (string)$_POST['body']), 'faq' => $faq];
            if ($item['seo_title'] === '') unset($item['seo_title']);
            $found = false;
            foreach ($all as $i => $a) if ($a['slug'] === ($orig ?: $slug)) { $all[$i] = $item; $found = true; }
            if (!$found) { foreach ($all as $a) if ($a['slug'] === $slug) { flash('این نامک قبلاً استفاده شده.'); go('tab=articles&edit=new'); } $all[] = $item; }
            flash(save_json('articles', $all) ? 'مقاله ذخیره شد.' : 'ذخیره نشد.'); go('tab=articles&edit=' . urlencode($slug));
        case 'delete_article':
            $all = array_values(array_filter(load_json('articles'), fn($a) => $a['slug'] !== ($_POST['slug'] ?? '')));
            flash(save_json('articles', $all) ? 'مقاله حذف شد.' : 'حذف نشد.'); go('tab=articles');
        case 'upload':
            $slot = preg_replace('/[^a-z0-9\-]/', '', (string)($_POST['slot'] ?? ''));
            $f = $_FILES['file'] ?? null;
            if (!$slot || !$f || $f['error'] !== UPLOAD_ERR_OK) { flash('فایلی انتخاب نشده بود.'); go('tab=images'); }
            if ($f['size'] > 5 * 1024 * 1024) { flash('حجم تصویر باید کمتر از ۵ مگابایت باشد.'); go('tab=images'); }
            $info = @getimagesize($f['tmp_name']);
            $types = [IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_WEBP => 'imagecreatefromwebp'];
            if (!$info || !isset($types[$info[2]])) { flash('فقط تصویر JPG، PNG یا WEBP.'); go('tab=images'); }
            $dest = dirname(__DIR__) . '/images/' . $slot . '.webp';
            if ($info[2] === IMAGETYPE_WEBP) { $ok = move_uploaded_file($f['tmp_name'], $dest); }
            elseif (function_exists('imagewebp')) { $im = $types[$info[2]]($f['tmp_name']); imagepalettetotruecolor($im); imagealphablending($im, true); imagesavealpha($im, true); $ok = imagewebp($im, $dest, 86); imagedestroy($im); }
            else { flash('هاست شما تبدیل به WEBP را پشتیبانی نمی‌کند؛ لطفاً تصویر را با فرمت WEBP بارگذاری کنید.'); go('tab=images'); }
            flash($ok ? 'تصویر جایگزین شد. اگر عکس شامل اطلاعات مشتری است، قبل از بارگذاری آن را محو کنید.' : 'بارگذاری نشد.'); go('tab=images');
        case 'font':
            $dir = dirname(__DIR__) . '/assets/fonts/';
            if (!empty($_POST['remove'])) { foreach (glob($dir . 'custom-display.*') as $f) @unlink($f); flash('فونت اختصاصی حذف شد؛ تیترها با نستعلیق پیش‌فرض نمایش داده می‌شوند.'); go('tab=font'); }
            $f = $_FILES['file'] ?? null;
            if (!$f || $f['error'] !== UPLOAD_ERR_OK) { flash('فایلی انتخاب نشده بود.'); go('tab=font'); }
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['woff2', 'woff', 'ttf', 'otf'], true) || $f['size'] > 4 * 1024 * 1024) { flash('فقط فایل فونت WOFF2، WOFF، TTF یا OTF تا ۴ مگابایت.'); go('tab=font'); }
            $head = file_get_contents($f['tmp_name'], false, null, 0, 4);
            if (!in_array($head, ["wOF2", "wOFF", "\x00\x01\x00\x00", "OTTO", "true"], true)) { flash('این فایل فونت معتبر نیست.'); go('tab=font'); }
            foreach (glob($dir . 'custom-display.*') as $old) @unlink($old);
            flash(move_uploaded_file($f['tmp_name'], $dir . 'custom-display.' . $ext) ? 'فونت تیترها جایگزین شد. سایت را با Ctrl+F5 تازه کنید.' : 'بارگذاری نشد؛ پوشه assets/fonts باید قابل نوشتن باشد.'); go('tab=font');
        case 'password':
            if (!password_verify((string)$_POST['current'], admin_hash())) { flash('رمز فعلی اشتباه است.'); go('tab=security'); }
            $n = (string)$_POST['new'];
            if (mb_strlen($n) < 10 || $n !== (string)$_POST['again']) { flash('رمز جدید باید حداقل ۱۰ کاراکتر باشد و دو بار یکسان وارد شود.'); go('tab=security'); }
            flash(save_json('admin', ['hash' => password_hash($n, PASSWORD_DEFAULT)]) ? 'رمز عوض شد.' : 'ذخیره نشد.'); go('tab=security');
    }
}

/* ---------- field labels ---------- */
$L = ['seo' => 'سئو و متا', 'site_name' => 'نام سایت', 'title' => 'عنوان', 'description' => 'توضیحات', 'keywords' => 'کلمات کلیدی', 'og_image' => 'تصویر پیش‌نمایش لینک',
 'contact' => 'تماس و لینک دکمه‌ها', 'phone_display' => 'شماره تلفن (نمایشی)', 'phone_tel' => 'شماره برای تماس (با +98)', 'whatsapp' => 'شماره واتساپ (فقط رقم با 98)', 'address' => 'آدرس', 'city' => 'شهر',
 'site_label' => 'متن لینک سایت', 'panel_url' => 'لینک ورود به پنل', 'whatsapp_message' => 'پیام آماده واتساپ',
 'social' => 'شبکه‌های اجتماعی (فقط آیدی)', 'instagram' => 'اینستاگرام', 'telegram' => 'تلگرام', 'bale' => 'بله', 'eitaa' => 'ایتا',
 'offer' => 'پیشنهاد ویژه (ماه رایگان)', 'enabled' => 'نمایش داده شود', 'badge' => 'برچسب', 'text_html' => 'متن', 'points' => 'بندها', 'cta' => 'متن دکمه', 'lead_title' => 'عنوان پیام درخواست',
 'nav' => 'منوی بالا', 'items' => 'موارد', 'panel_label' => 'متن لینک پنل', 'hero' => 'بخش اول صفحه', 'eyebrow' => 'برچسب بالای تیتر', 'title_html' => 'تیتر اصلی', 'for_prefix' => 'پیش‌متن صنف چرخان', 'rotator' => 'صنف‌های چرخان',
 'lead_html' => 'متن زیر تیتر', 'cta1' => 'دکمه اول', 'cta2' => 'دکمه دوم', 'chips' => 'برچسب صنف‌ها', 'shot_alt' => 'متن جایگزین تصویر',
 'leaks' => 'نشتی‌های سود', 'kicker' => 'برچسب بخش', 'intro' => 'مقدمه', 'note_html' => 'جمله پایانی', 't' => 'عنوان', 'd' => 'توضیح',
 'calc' => 'ماشین‌حساب', 'note' => 'یادداشت', 'pillars' => 'چهار ستون', 'img' => 'نام تصویر', 'img_alt' => 'متن جایگزین تصویر', 'ticks_html' => 'بندها',
 'strips' => 'نوارهای دعوت به اقدام', 'sub' => 'زیرعنوان', 'panels' => 'گالری پنل‌ها', 'tabs' => 'برچسب تب‌ها', 'flow' => 'جریان اطلاعات', 'replaces_label' => 'برچسب «به‌جای»', 'replaces' => 'ابزارهای جایگزین‌شده',
 'compare' => 'جدول مقایسه', 'cols' => 'ستون‌ها', 'short' => 'نام کوتاه ستون‌ها (موبایل)', 'rows' => 'ردیف‌ها', 'roles' => 'صنف‌ها', 'tab' => 'برچسب تب', 'role' => 'عنوان صنف', 'list' => 'بندها',
 'pitch' => 'موتور توضیحات (متن بخش)', 'security' => 'امنیت', 'layers' => 'لایه‌ها', 'lv' => 'شماره لایه', 'proof' => 'نکته‌ها', 'b' => 'عنوان', 's' => 'توضیح',
 'faq' => 'پرسش‌های رایج', 'q' => 'پرسش', 'a' => 'پاسخ', 'steps' => 'مراحل شروع', 'blog' => 'بخش مقالات', 'all' => 'دکمه همه مقاله‌ها', 'page_title' => 'عنوان صفحه مقالات', 'page_description' => 'توضیح صفحه مقالات', 'cta_title' => 'عنوان کادر پیشنهاد', 'cta_text' => 'متن کادر پیشنهاد',
 'demo' => 'فرم شروع', 'lead' => 'متن', 'submit' => 'متن دکمه فرم', 'sources' => 'گزینه‌های «از کجا آشنا شدید»', 'footer' => 'پاورقی', 'text' => 'متن', 'gallery' => 'تصاویر گالری', 'acc' => 'حسابداری', 'ops' => 'عملیاتی', 'mgmt' => 'مدیریتی', 'labels' => 'نام تصاویر', 'google_verify' => 'کد تأیید Google Search Console', 'analytics_head' => 'کد آمار بازدید (Google Analytics یا Clarity)', 'testimonials' => 'نظر مشتری‌ها', 'name' => 'نام', 'shop' => 'مغازه و شهر'];
$SKIP = ['_help'];
function lab($k) { global $L; return $L[$k] ?? (is_int($k) ? 'مورد ' . fa_digits($k + 1) : $k); }
function field($name, $path, $key, $val) {
    if (is_bool($val)) {
        echo '<label class="chk"><input type="hidden" name="' . h($name) . '" value="0"><input type="checkbox" name="' . h($name) . '" value="1"' . ($val ? ' checked' : '') . '> ' . h(lab($key)) . '</label>';
        return;
    }
    if (is_array($val)) {
        $isList = array_is_list($val);
        echo '<fieldset class="grp' . ($isList ? ' list' : '') . '" data-path="' . h($path) . '"><legend>' . h(lab($key)) . '</legend>';
        foreach ($val as $k => $v) {
            echo $isList ? '<div class="arr-item">' : '';
            field($name . '[' . $k . ']', $path . '.' . $k, $k, $v);
            echo $isList ? '<button type="button" class="rm" title="حذف این مورد">×</button></div>' : '';
        }
        if ($isList) echo '<button type="button" class="add">+ افزودن مورد</button>';
        echo '</fieldset>';
        return;
    }
    $long = mb_strlen((string)$val) > 70 || str_ends_with((string)$key, '_html') || in_array($key, ['description', 'intro', 'a', 'd', 'text', 'lead', 'note', 'analytics_head'], true);
    echo '<label class="fld"><span>' . h(lab($key)) . '</span>';
    echo $long ? '<textarea name="' . h($name) . '" rows="' . max(2, min(8, (int)ceil(mb_strlen((string)$val) / 80))) . '">' . h($val) . '</textarea>'
               : '<input name="' . h($name) . '" value="' . h($val) . '"' . (preg_match('/url|tel|whatsapp|telegram|instagram|bale|eitaa|img|og_image/', (string)$key) ? ' dir="ltr"' : '') . '>';
    echo '</label>';
}
$slots = array_keys(load_json('content')['labels'] ?? []);
?><!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>پنل مدیریت فَروَم</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700;800&display=swap">
<style>
:root{--bg:#F4F6FB;--card:#fff;--text:#0E1424;--muted:#5d677e;--line:#dde2ec;--gold:#A8740C;--gold2:#C8901A;--teal:#0A9A85;--rose:#D23A5C}
@media (prefers-color-scheme:dark){:root{--bg:#070B14;--card:#0F1524;--text:#EEF2F8;--muted:#98A3B6;--line:#243049;--gold:#FFC84A;--gold2:#FFD66E;--teal:#2EF2D0;--rose:#FF6B8B;color-scheme:dark}}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font:15px/1.8 Vazirmatn,Tahoma,sans-serif}
.wrap{max-width:1100px;margin:0 auto;padding:18px}
header{display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;padding:14px 18px;background:var(--card);border-bottom:1px solid var(--line);position:sticky;top:0;z-index:5}
header b{font-size:1.1rem}header nav{display:flex;flex-wrap:wrap;gap:6px}
header nav a{padding:6px 12px;border-radius:999px;text-decoration:none;color:var(--muted);border:1px solid var(--line)}
header nav a.on{color:var(--text);border-color:var(--teal);box-shadow:0 0 0 2px color-mix(in srgb,var(--teal) 20%,transparent)}
.card{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:20px;margin-bottom:16px}
.flash{background:color-mix(in srgb,var(--teal) 12%,var(--card));border:1px solid var(--teal);padding:10px 14px;border-radius:12px;margin-bottom:14px}
.warn{background:color-mix(in srgb,var(--rose) 10%,var(--card));border-color:var(--rose)}
input,textarea,select{width:100%;font:inherit;color:var(--text);background:var(--bg);border:1px solid var(--line);border-radius:10px;padding:8px 10px}
textarea{resize:vertical}textarea.code{font:13px/1.6 ui-monospace,Consolas,monospace;direction:ltr;text-align:left;min-height:60vh}
label.fld{display:grid;gap:4px;margin-bottom:10px}label.fld span{font-weight:700;font-size:.9rem;color:var(--muted)}
label.chk{display:flex;gap:8px;align-items:center;margin-bottom:10px;font-weight:700}label.chk input{width:auto}
fieldset.grp{border:1px solid var(--line);border-radius:14px;padding:12px 14px;margin:0 0 12px}
fieldset.grp>legend{font-weight:800;padding:0 8px;color:var(--gold)}
details.sec{background:var(--card);border:1px solid var(--line);border-radius:16px;margin-bottom:10px}
details.sec>summary{cursor:pointer;padding:14px 18px;font-weight:800}details.sec>div{padding:0 18px 12px}
.arr-item{position:relative;padding-left:34px}
.rm{position:absolute;left:0;top:26px;width:28px;height:28px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--rose);cursor:pointer}
.add{border:1px dashed var(--teal);background:none;color:var(--teal);border-radius:10px;padding:6px 12px;cursor:pointer;font:inherit}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:999px;border:0;font:inherit;font-weight:800;cursor:pointer;text-decoration:none}
.btn-gold{background:linear-gradient(180deg,var(--gold2),var(--gold));color:#1a1405}.btn-ghost{background:none;border:1px solid var(--line);color:var(--text)}.btn-red{background:none;border:1px solid var(--rose);color:var(--rose)}
.bar{position:sticky;bottom:0;background:var(--card);border-top:1px solid var(--line);padding:12px 18px;display:flex;gap:10px;justify-content:flex-start;border-radius:16px 16px 0 0}
table{width:100%;border-collapse:collapse}td,th{padding:8px;border-bottom:1px solid var(--line);text-align:right;vertical-align:top}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px}@media(max-width:700px){.grid2{grid-template-columns:1fr}}
.slots{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:12px}
.slot{border:1px solid var(--line);border-radius:14px;padding:10px;display:grid;gap:8px}.slot img{width:100%;aspect-ratio:16/10;object-fit:cover;border-radius:10px;background:var(--bg)}
.muted{color:var(--muted);font-size:.9rem}code{direction:ltr;unicode-bidi:embed}
.login{max-width:380px;margin:12vh auto}
</style>
</head>
<body>
<?php if (!$authed): ?>
<div class="login card">
  <h2 style="margin-top:0">ورود به پنل مدیریت فَروَم</h2>
  <?php if ($m = flash()): ?><div class="flash warn"><?= h($m) ?></div><?php endif; ?>
  <form method="post"><input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="act" value="login">
    <label class="fld"><span>رمز عبور</span><input type="password" name="password" autofocus required autocomplete="current-password"></label>
    <button class="btn btn-gold">ورود</button>
  </form>
</div>
<?php exit; endif; ?>
<header>
  <b>پنل مدیریت فَروَم</b>
  <nav>
    <?php foreach (['leads' => 'درخواست‌ها', 'content' => 'متن‌ها و لینک‌ها', 'articles' => 'مقاله‌ها', 'images' => 'تصاویر', 'font' => 'فونت تیترها', 'engine' => 'موتور توضیحات', 'advanced' => 'ویرایش پیشرفته', 'security' => 'امنیت', 'help' => 'راهنما'] as $k => $v): ?>
    <a href="?tab=<?= $k ?>" class="<?= $tab === $k ? 'on' : '' ?>"><?= $v ?></a>
    <?php endforeach; ?>
    <a href="<?= h(url_home()) ?>" target="_blank">مشاهده سایت ↗</a>
    <a href="?act=logout">خروج</a>
  </nav>
</header>
<div class="wrap">
<?php if (!writable_ok()): ?><div class="flash warn">پوشه <code>data</code> قابل نوشتن نیست؛ تا دسترسی آن درست نشود، تغییرات ذخیره نمی‌شوند.</div><?php endif; ?>
<?php if ($m = flash()): ?><div class="flash"><?= h($m) ?></div><?php endif; ?>

<?php if ($tab === 'leads'): $leads = load_json('leads'); ?>
<div class="card">
  <p>درخواست‌هایی که از فرم «ماه رایگان» سایت ثبت شده‌اند. تعداد: <b><?= fa_digits(count($leads)) ?></b> · <a href="?act=csv">دریافت فایل اکسل (CSV)</a></p>
  <div style="overflow-x:auto"><table><thead><tr><th>زمان</th><th>نام</th><th>موبایل</th><th>صنف</th><th>شهر</th><th>آشنایی از</th><th>کانال</th><th>دغدغه</th></tr></thead><tbody>
  <?php foreach (array_slice($leads, 0, 300) as $l): ?><tr><td><?= h(jalali_label($l['time'])) ?> <?= h(substr($l['time'], 11, 5)) ?></td><td><?= h($l['name']) ?></td><td dir="ltr"><a href="tel:<?= h($l['phone']) ?>"><?= h($l['phone']) ?></a></td><td><?= h($l['role']) ?></td><td><?= h($l['city']) ?></td><td><?= h($l['source']) ?></td><td dir="ltr"><?= h($l['utm']) ?></td><td><?= h($l['note']) ?></td></tr><?php endforeach; ?>
  <?php if (!$leads): ?><tr><td colspan="8" class="muted">هنوز درخواستی ثبت نشده است.</td></tr><?php endif; ?>
  </tbody></table></div>
</div>

<?php elseif ($tab === 'content'): $c = load_json('content'); ?>
<p class="muted">هر بخش را باز کنید، متن یا لینک را عوض کنید و «ذخیره» را بزنید. در متن‌هایی که عنوانشان «تیتر اصلی»، «متن» یا «بندها» است، می‌توانید از <code>&lt;b&gt;</code> برای پررنگ و <code>&lt;br&gt;</code> برای شکستن خط استفاده کنید.</p>
<form method="post" id="cform"><input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="act" value="save_content">
<?php foreach ($c as $k => $v): if (in_array($k, $SKIP, true)) continue; ?>
  <details class="sec"<?= in_array($k, ['contact', 'offer'], true) ? ' open' : '' ?>><summary><?= h(lab($k)) ?></summary><div><?php field("f[$k]", $k, $k, $v); ?></div></details>
<?php endforeach; ?>
  <div class="bar"><button class="btn btn-gold">ذخیره تغییرات</button><a class="btn btn-ghost" href="<?= h(url_home()) ?>" target="_blank">پیش‌نمایش سایت</a></div>
</form>
<script>
document.addEventListener('click',function(e){
  if(e.target.classList.contains('rm')){var it=e.target.closest('.arr-item'),fs=it.parentElement;it.remove();renum(fs);}
  if(e.target.classList.contains('add')){var fs=e.target.parentElement,items=fs.querySelectorAll(':scope>.arr-item');
    if(!items.length){alert('برای افزودن، از «ویرایش پیشرفته» استفاده کنید.');return}
    var c=items[items.length-1].cloneNode(true);c.querySelectorAll('input:not([type=hidden]):not([type=checkbox]),textarea').forEach(function(x){x.value=''});fs.insertBefore(c,e.target);renum(fs);}
});
function renum(fs){var path=fs.dataset.path.split('.'),base='f['+path.join('][')+']';
  fs.querySelectorAll(':scope>.arr-item').forEach(function(it,i){it.querySelectorAll('[name]').forEach(function(x){x.name=x.name.replace(new RegExp('^'+base.replace(/[\[\]]/g,'\\$&')+'\\[\\d+\\]'),base+'['+i+']')})});
  var mark=document.querySelector('input[name="empty[]"][value="'+fs.dataset.path+'"]');
  if(!fs.querySelector(':scope>.arr-item')){if(!mark){mark=document.createElement('input');mark.type='hidden';mark.name='empty[]';mark.value=fs.dataset.path;document.getElementById('cform').appendChild(mark)}}else if(mark)mark.remove();
}
</script>

<?php elseif ($tab === 'articles'):
    $all = load_json('articles'); $edit = $_GET['edit'] ?? '';
    if ($edit !== ''):
        $a = ['slug' => '', 'title' => '', 'description' => '', 'excerpt' => '', 'keywords' => '', 'tag' => 'راهنما', 'date' => date('Y-m-d'), 'published' => true, 'body' => "<p>مقدمه…</p>\n<h2>عنوان بخش اول</h2>\n<p>…</p>", 'faq' => []];
        foreach ($all as $x) if ($x['slug'] === $edit) $a = $x;
        $faqTxt = implode("\n", array_map(fn($f) => $f['q'] . ' | ' . $f['a'], $a['faq'] ?? []));
?>
<form method="post" class="card"><input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="act" value="save_article"><input type="hidden" name="orig_slug" value="<?= h($edit === 'new' ? '' : $a['slug']) ?>">
  <div class="grid2">
    <label class="fld"><span>عنوان مقاله (H1)</span><input name="title" value="<?= h($a['title']) ?>" required></label>
    <label class="fld"><span>نامک آدرس (انگلیسی، مثلاً gold-accounting)</span><input name="slug" value="<?= h($a['slug']) ?>" dir="ltr" required pattern="[a-z0-9\-]+"></label>
    <label class="fld"><span>عنوان سئو (اختیاری، حداکثر ۶۰ کاراکتر)</span><input name="seo_title" value="<?= h($a['seo_title'] ?? '') ?>" maxlength="70"></label>
    <label class="fld"><span>برچسب</span><input name="tag" value="<?= h($a['tag'] ?? '') ?>"></label>
  </div>
  <label class="fld"><span>توضیحات متا (۱۲۰ تا ۱۶۰ کاراکتر؛ همان متنی که گوگل زیر عنوان نشان می‌دهد)</span><textarea name="description" rows="2" maxlength="200"><?= h($a['description']) ?></textarea></label>
  <label class="fld"><span>خلاصه کارت مقاله</span><textarea name="excerpt" rows="2"><?= h($a['excerpt'] ?? '') ?></textarea></label>
  <div class="grid2">
    <label class="fld"><span>کلمات کلیدی (با ویرگول)</span><input name="keywords" value="<?= h($a['keywords'] ?? '') ?>"></label>
    <label class="fld"><span>تاریخ انتشار</span><input type="date" name="date" value="<?= h($a['date']) ?>"></label>
  </div>
  <label class="chk"><input type="checkbox" name="published" value="1"<?= !empty($a['published']) ? ' checked' : '' ?>> منتشر شود</label>
  <label class="fld"><span>متن مقاله (HTML). تیترهای بخش‌ها را با &lt;h2&gt; بنویسید تا فهرست مطالب خودکار ساخته شود. برای لینک به مقاله دیگر: <code>{{a:slug}}</code></span><textarea name="body" class="code" style="min-height:50vh"><?= h($a['body']) ?></textarea></label>
  <label class="fld"><span>پرسش‌های رایج مقاله (هر خط: پرسش | پاسخ)</span><textarea name="faq" rows="5"><?= h($faqTxt) ?></textarea></label>
  <div class="bar"><button class="btn btn-gold">ذخیره مقاله</button><a class="btn btn-ghost" href="?tab=articles">بازگشت</a><?php if ($edit !== 'new'): ?><a class="btn btn-ghost" href="<?= h(url_article($a['slug'])) ?>" target="_blank">مشاهده ↗</a><?php endif; ?></div>
</form>
<?php if ($edit !== 'new'): ?>
<form method="post" onsubmit="return confirm('این مقاله حذف شود؟')"><input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="act" value="delete_article"><input type="hidden" name="slug" value="<?= h($a['slug']) ?>"><button class="btn btn-red">حذف این مقاله</button></form>
<?php endif; ?>
<?php else: ?>
<div class="card">
  <p><a class="btn btn-gold" href="?tab=articles&edit=new">+ مقاله جدید</a></p>
  <table><thead><tr><th>عنوان</th><th>نامک</th><th>تاریخ</th><th>وضعیت</th><th></th></tr></thead><tbody>
  <?php foreach ($all as $a): ?><tr><td><?= h($a['title']) ?></td><td dir="ltr"><?= h($a['slug']) ?></td><td><?= jalali_label($a['date']) ?></td><td><?= !empty($a['published']) ? 'منتشرشده' : 'پیش‌نویس' ?></td><td><a href="?tab=articles&edit=<?= urlencode($a['slug']) ?>">ویرایش</a></td></tr><?php endforeach; ?>
  </tbody></table>
</div>
<?php endif; ?>

<?php elseif ($tab === 'images'): ?>
<p class="muted">برای هر جای تصویر، عکس جدید (JPG، PNG یا WEBP تا ۵ مگابایت) بارگذاری کنید. جایی که تصویر ندارد در سایت پنهان می‌ماند. قبل از بارگذاری، اسم و شماره مشتری‌ها را محو کنید.</p>
<div class="slots">
<?php foreach ($slots as $s): $p = dirname(__DIR__) . "/images/$s.webp"; ?>
  <form class="slot card" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="act" value="upload"><input type="hidden" name="slot" value="<?= h($s) ?>">
    <b><?= h(c("labels.$s", $s)) ?></b>
    <?php if (is_file($p)): ?><img src="<?= h(base() . "images/$s.webp?" . filemtime($p)) ?>" alt=""><?php else: ?><div class="muted">هنوز تصویری ندارد</div><?php endif; ?>
    <input type="file" name="file" accept="image/png,image/jpeg,image/webp" required>
    <button class="btn btn-ghost">بارگذاری</button>
  </form>
<?php endforeach; ?>
</div>

<?php elseif ($tab === 'engine' || $tab === 'advanced'): $which = $tab === 'engine' ? 'engine' : 'content'; ?>
<div class="card">
<?php if ($which === 'engine'): ?>
  <p>موتور توضیحات، متن معرفی را از کنار هم گذاشتن جمله‌های هر صنف با قالب‌های هر لحن می‌سازد. در بخش <code>roles</code> جمله‌های هر صنف و در بخش <code>tones</code> قالب‌های هر لحن را عوض یا اضافه کنید. در قالب‌ها از <code>{pain}</code>، <code>{agitate}</code>، <code>{after}</code>، <code>{feature}</code>، <code>{benefit}</code>، <code>{mechanism}</code>، <code>{role}</code>، <code>{roles}</code>، <code>{brand}</code> و <code>{offer}</code> استفاده کنید.</p>
<?php else: ?>
  <p>ویرایش مستقیم همه محتوای صفحه اصلی. برای افزودن یا حذف بخش، صنف یا ستون از اینجا استفاده کنید. قبل از هر ذخیره، یک نسخه پشتیبان خودکار در <code>data/backup-content.json</code> ساخته می‌شود.</p>
<?php endif; ?>
  <form method="post"><input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="act" value="save_json"><input type="hidden" name="which" value="<?= $which ?>">
    <textarea name="json" class="code" spellcheck="false"><?= h(json_encode(load_json($which), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></textarea>
    <div class="bar"><button class="btn btn-gold">ذخیره</button></div>
  </form>
</div>

<?php elseif ($tab === 'font'): $cf = glob(dirname(__DIR__) . '/assets/fonts/custom-display.*'); ?>
<div class="card" style="max-width:640px">
  <h3 style="margin-top:0">فونت تیترها</h3>
  <p>تیترهای سایت الان با <b><?= $cf ? 'فونت اختصاصی شما (' . h(basename($cf[0])) . ')' : 'نستعلیق مدرن (Noto Nastaliq)' ?></b> نمایش داده می‌شوند.</p>
  <p class="muted">برای شکسته نستعلیق یا نستعلیقی که حروف کشیده (ـــ) را پشتیبانی می‌کند، فایل فونت را اینجا بارگذاری کنید. فقط فونتی را بارگذاری کنید که مجوز استفاده در وب‌سایت را دارید. بعد از بارگذاری، برای کشیدن حروف در تیترها از «ـ» (Shift+J در صفحه‌کلید فارسی) استفاده کنید؛ مثلاً «طلـــا».</p>
  <form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="act" value="font">
    <label class="fld"><span>فایل فونت (WOFF2، WOFF، TTF یا OTF)</span><input type="file" name="file" accept=".woff2,.woff,.ttf,.otf" required></label>
    <button class="btn btn-gold">بارگذاری و استفاده</button>
  </form>
<?php if ($cf): ?>
  <form method="post" style="margin-top:12px"><input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="act" value="font"><input type="hidden" name="remove" value="1"><button class="btn btn-red">برگشت به نستعلیق پیش‌فرض</button></form>
<?php endif; ?>
</div>

<?php elseif ($tab === 'security'): ?>
<form method="post" class="card" style="max-width:460px"><input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="act" value="password">
  <h3 style="margin-top:0">تغییر رمز پنل</h3>
  <label class="fld"><span>رمز فعلی</span><input type="password" name="current" required autocomplete="current-password"></label>
  <label class="fld"><span>رمز جدید (حداقل ۱۰ کاراکتر)</span><input type="password" name="new" required minlength="10" autocomplete="new-password"></label>
  <label class="fld"><span>تکرار رمز جدید</span><input type="password" name="again" required minlength="10" autocomplete="new-password"></label>
  <button class="btn btn-gold">ذخیره رمز</button>
</form>

<?php else: ?>
<div class="card">
  <h3 style="margin-top:0">راهنمای سریع</h3>
  <ul>
    <li><b>عوض کردن شماره، واتساپ یا لینک دکمه‌ها:</b> «متن‌ها و لینک‌ها» ← «تماس و لینک دکمه‌ها».</li>
    <li><b>روشن و خاموش کردن ماه رایگان:</b> «متن‌ها و لینک‌ها» ← «پیشنهاد ویژه» ← تیک «نمایش داده شود».</li>
    <li><b>آیدی کانال‌ها:</b> «شبکه‌های اجتماعی»؛ فقط آیدی بدون @. آیکون‌ها خودکار پایین سایت ظاهر می‌شوند.</li>
    <li><b>مقاله جدید:</b> «مقاله‌ها» ← «مقاله جدید». نامک را انگلیسی بنویسید. مقاله خودکار به نقشه سایت اضافه می‌شود.</li>
    <li><b>عنوان و توضیحات گوگل:</b> «سئو و متا». عنوان کمتر از ۶۰ و توضیحات بین ۱۲۰ تا ۱۶۰ کاراکتر باشد.</li>
    <li><b>نقشه سایت برای گوگل:</b> <code dir="ltr"><?= h(abs_url('sitemap.xml')) ?></code> را در Google Search Console ثبت کنید.</li>
  </ul>
</div>
<?php endif; ?>
</div>
</body>
</html>
