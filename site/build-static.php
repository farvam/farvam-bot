<?php
/* نسخه ثابت HTML سایت (برای پیش‌نمایش یا هاست بدون PHP):  php build-static.php <out-dir> */
define('STATIC_BUILD', true);
$out = rtrim($argv[1] ?? (__DIR__ . '/static'), '/');
@mkdir($out, 0775, true);
function render(string $file, array $get = []): string { $_GET = $get; ob_start(); (function () use ($file) { require $file; })(); return ob_get_clean(); }
require_once __DIR__ . '/lib.php';
file_put_contents("$out/index.html", render(__DIR__ . '/index.php'));
file_put_contents("$out/blog.html", render(__DIR__ . '/blog/index.php'));
foreach (articles() as $a) file_put_contents("$out/blog-{$a['slug']}.html", render(__DIR__ . '/blog/article.php', ['slug' => $a['slug']]));
echo "built ", 2 + count(articles()), " pages into $out\n";
