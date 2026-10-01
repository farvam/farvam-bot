<?php
/*
 * تنظیمات اصلی سایت فَروَم
 *
 * SITE_URL  : آدرس کامل سایت بدون / آخر (برای سئو، نقشه سایت و پیش‌نمایش لینک‌ها)
 * BASE_PATH : مسیر پوشه سایت روی هاست، با / اول و آخر. اگر سایت در ریشه دامنه است: '/'
 * PRETTY_URLS : اگر هاست Apache یا LiteSpeed است true بماند (فایل .htaccess آدرس‌های تمیز می‌سازد).
 *               اگر مقاله‌ها باز نشدند، false کنید.
 * ADMIN_PASSWORD_HASH : رمز اولیه پنل مدیریت. بعد از اولین ورود، از بخش «امنیت» رمز را عوض کنید.
 */
define('SITE_URL', 'https://farvamcertification.ir/farvam');
define('BASE_PATH', '/farvam/');
define('PRETTY_URLS', true);
define('ADMIN_PASSWORD_HASH', '$2y$12$Px3vg6GmuDpe1QcXQecyC.JrTZyByhp3Vx52BMhIHFbYm.FNz1yZ6');
define('DATA_DIR', __DIR__ . '/data');
// SQLite database file (set by Docker). Empty = store data as JSON files in data/ (shared hosting).
define('DB_FILE', (string)getenv('FARVAM_DB'));
date_default_timezone_set('Asia/Tehran');
