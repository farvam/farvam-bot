<?php
/* Shared page shell: <head> with full SEO, header, footer, mobile bar and scripts. */

function page_start(array $m): void {
    $title = $m['title'] ?? c('seo.title');
    $desc  = $m['description'] ?? c('seo.description');
    $canon = $m['canonical'] ?? abs_url();
    $img   = abs_url($m['image'] ?? c('seo.og_image'));
    $type  = $m['type'] ?? 'website';
    $b = base();
    ?><!doctype html>
<html lang="fa-IR" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= h($title) ?></title>
<meta name="description" content="<?= h($desc) ?>">
<?php if (!empty($m['keywords'])): ?><meta name="keywords" content="<?= h($m['keywords']) ?>">
<?php endif; ?>
<meta name="robots" content="<?= h($m['robots'] ?? 'index, follow, max-image-preview:large, max-snippet:-1') ?>">
<link rel="canonical" href="<?= h($canon) ?>">
<link rel="alternate" hreflang="fa-IR" href="<?= h($canon) ?>">
<meta name="author" content="<?= t('seo.site_name') ?>">
<meta name="geo.region" content="IR-10"><meta name="geo.placename" content="<?= t('contact.city') ?>">
<meta property="og:type" content="<?= h($type) ?>">
<meta property="og:locale" content="fa_IR">
<meta property="og:site_name" content="<?= t('seo.site_name') ?>">
<meta property="og:title" content="<?= h($m['og_title'] ?? $title) ?>">
<meta property="og:description" content="<?= h($desc) ?>">
<meta property="og:url" content="<?= h($canon) ?>">
<meta property="og:image" content="<?= h($img) ?>">
<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= h($m['og_title'] ?? $title) ?>">
<?php if ($type === 'article'): ?>
<meta property="article:published_time" content="<?= h($m['published'] ?? '') ?>">
<meta property="article:modified_time" content="<?= h($m['modified'] ?? $m['published'] ?? '') ?>">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= h($m['og_title'] ?? $title) ?>">
<meta name="twitter:description" content="<?= h($desc) ?>">
<meta name="twitter:image" content="<?= h($img) ?>">
<meta name="color-scheme" content="light dark">
<meta name="theme-color" content="#F4F6FB" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#060910" media="(prefers-color-scheme: dark)">
<link rel="icon" href="<?= $b ?>images/logo-mark-deep.png" media="(prefers-color-scheme: light)">
<link rel="icon" href="<?= $b ?>images/logo-mark-gold.png" media="(prefers-color-scheme: dark)">
<link rel="apple-touch-icon" href="<?= $b ?>images/logo-coin.png">
<link rel="preload" href="<?= $b ?>assets/fonts/vazirmatn-400.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= $b ?>assets/fonts/nastaliq-bold.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= $b ?>assets/style.css?v=4">
<?php $cf = glob(__DIR__ . '/../assets/fonts/custom-display.*'); if ($cf): $ext = pathinfo($cf[0], PATHINFO_EXTENSION); $fmt = ['woff2' => 'woff2', 'woff' => 'woff', 'ttf' => 'truetype', 'otf' => 'opentype'][$ext] ?? 'truetype'; ?>
<style>@font-face{font-family:"Farvam Custom";src:url(<?= $b ?>assets/fonts/custom-display.<?= h($ext) ?>?<?= filemtime($cf[0]) ?>) format("<?= $fmt ?>");font-display:swap}:root{--display:"Farvam Custom","Farvam Nastaliq","Vazirmatn",Tahoma,serif}</style>
<?php endif; ?>
<?php foreach (($m['ld'] ?? []) as $ld) echo json_ld(['@context' => 'https://schema.org'] + $ld), "\n"; ?>
<?php if (c('seo.google_verify')): ?><meta name="google-site-verification" content="<?= t('seo.google_verify') ?>">
<?php endif; ?>
<?php if (!is_static()) echo c('seo.analytics_head'), "\n"; /* کد آمار (Google Analytics، Clarity و…) از پنل مدیریت */ ?>
</head>
<body>
<div class="page" dir="rtl" lang="fa">
<canvas id="galaxy" aria-hidden="true"></canvas>
<?php if (c('offer.enabled')): ?>
<a class="topbar" href="<?= h(url_home('#demo')) ?>"><span class="pill"><?= t('offer.badge') ?></span> <?= t('offer.title') ?> <span aria-hidden="true">←</span></a>
<?php endif; ?>
<header class="nav">
  <div class="wrap">
    <a class="brand" href="<?= h(url_home()) ?>" aria-label="<?= t('seo.site_name') ?>">
      <img src="<?= $b ?>images/logo-mark-deep.png" alt="" width="56" height="36" class="brand-mark lg-light"><img src="<?= $b ?>images/logo-mark-gold.png" alt="" width="56" height="36" class="brand-mark lg-dark">
      <?= t('seo.site_name') ?>
    </a>
    <ul>
<?php foreach ((array)c('nav.items') as $it): ?>
      <li><a href="<?= h(nav_href($it[0])) ?>"><?= h($it[1]) ?></a></li>
<?php endforeach; ?>
      <li><a href="<?= h(c('contact.panel_url')) ?>" target="_blank" rel="noopener"><?= t('nav.panel_label') ?></a></li>
    </ul>
    <div class="nav-side">
      <button class="theme-btn" id="theme-btn" type="button" aria-label="تغییر حالت روشن و تیره" title="حالت نمایش: خودکار"><svg id="theme-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"></svg></button>
      <a class="nav-tel num" href="<?= h(tel()) ?>" dir="ltr"><?= t('contact.phone_display') ?></a>
      <a class="btn btn-gold" href="<?= h(url_home('#demo')) ?>"><?= t('nav.cta') ?></a>
    </div>
  </div>
</header>
<?php
}

function breadcrumbs(array $items): void {
    echo '<nav class="crumbs" aria-label="مسیر صفحه"><ol>';
    foreach ($items as $i => [$label, $href]) {
        echo '<li>' . ($href && $i < count($items) - 1 ? '<a href="' . h($href) . '">' . h($label) . '</a>' : '<span aria-current="page">' . h($label) . '</span>') . '</li>';
    }
    echo '</ol></nav>';
}
function breadcrumb_ld(array $items): array {
    $list = [];
    foreach ($items as $i => [$label, $abs]) $list[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $label, 'item' => $abs];
    return ['@type' => 'BreadcrumbList', 'itemListElement' => $list];
}

function offer_box(string $title = '', string $text = ''): void { ?>
<aside class="offer-box glass">
  <div class="ob-logo" aria-hidden="true"><img class="lg-light" src="<?= base() ?>images/logo-deep.png" alt="" width="66" height="50"><img class="lg-dark" src="<?= base() ?>images/logo-gold.png" alt="" width="66" height="50"></div>
  <div>
    <b><?= h($title ?: c('offer.title')) ?></b>
    <p><?= $text !== '' ? h($text) : x('offer.text_html') ?></p>
  </div>
  <div class="acts"><a class="btn btn-gold" href="<?= h(url_home('#demo')) ?>"><?= t('offer.cta') ?></a><a class="btn btn-glass" href="<?= h(tel()) ?>" dir="ltr"><?= t('contact.phone_display') ?></a></div>
</aside>
<?php }

function page_end(bool $withEngine = false): void {
    $b = base();
    $data = [
        'base' => $b, 'whatsapp' => c('contact.whatsapp'), 'social' => c('social'),
        'roles' => c('roles.items'), 'labels' => c('labels'), 'rotator' => c('hero.rotator'),
        'leadTitle' => c('offer.lead_title'), 'leadUrl' => is_static() ? '' : BASE_PATH . 'lead.php',
    ];
    if ($withEngine) { $e = load_json('engine'); $e['offer'] = $e['offer'] ?? c('offer.badge'); $data['engine'] = $e; }
    ?>
<nav class="mbar glass" aria-label="تماس سریع">
  <a href="<?= h(tel()) ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg>تماس</a>
  <a href="<?= h(wa(c('contact.whatsapp_message'))) ?>" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20l1.3-4A8 8 0 1 1 8 18.7z"/></svg>واتساپ</a>
  <a class="btn-gold" href="<?= h(url_home('#demo')) ?>"><?= t('offer.badge') ?></a>
</nav>
<footer>
  <div class="wrap foot-grid">
    <div class="foot-col">
      <span class="foot-brand"><img class="lg-light" src="<?= $b ?>images/logo-deep.png" alt="لوگوی فَروَم" width="61" height="46"><img class="lg-dark" src="<?= $b ?>images/logo-gold.png" alt="لوگوی فَروَم" width="61" height="46"><?= t('footer.text') ?></span>
      <span class="socials" id="socials"></span>
    </div>
    <div class="foot-col">
      <b>دسترسی سریع</b>
      <a href="<?= h(url_home('#pillars')) ?>">امکانات فَروَم</a>
      <a href="<?= h(url_home('#pitch')) ?>">فَروَم به زبان شما</a>
      <a href="<?= h(url_blog()) ?>">مقالات</a>
      <a href="<?= h(url_home('#demo')) ?>"><?= t('offer.cta') ?></a>
    </div>
    <div class="foot-col">
      <b>تماس</b>
      <a href="<?= h(tel()) ?>" dir="ltr" class="num"><?= t('contact.phone_display') ?></a>
      <span><?= t('contact.address') ?></span>
      <a href="<?= h(c('contact.panel_url')) ?>" target="_blank" rel="noopener" dir="ltr"><?= t('contact.site_label') ?></a>
    </div>
  </div>
</footer>
</div>
<script>window.FARVAM=<?= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>;</script>
<script src="<?= $b ?>assets/app.js?v=4" defer></script>
</body>
</html>
<?php
}
