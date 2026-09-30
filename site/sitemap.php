<?php
require_once __DIR__ . '/lib.php';
header('Content-Type: application/xml; charset=utf-8');
$latest = articles()[0]['updated'] ?? articles()[0]['date'] ?? date('Y-m-d');
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">', "\n";
$u = function (string $loc, string $mod, string $freq, string $pri, string $img = '') {
    echo "  <url><loc>", h($loc), "</loc><lastmod>", h(substr($mod, 0, 10)), "</lastmod><changefreq>$freq</changefreq><priority>$pri</priority>";
    if ($img) echo "<image:image><image:loc>", h($img), "</image:loc></image:image>";
    echo "</url>\n";
};
$u(abs_url(), $latest, 'weekly', '1.0', abs_url(c('seo.og_image')));
$u(abs_url('blog/'), $latest, 'weekly', '0.8');
$u(abs_url('present.php'), $latest, 'monthly', '0.7');
foreach (array_keys((array)c('roles.items')) as $k) $u(abs_url('for/' . $k), $latest, 'monthly', '0.9');
foreach (articles() as $a) $u(canonical_article($a['slug']), $a['updated'] ?? $a['date'], 'monthly', '0.7', abs_url($a['image'] ?? c('seo.og_image')));
echo '</urlset>', "\n";
