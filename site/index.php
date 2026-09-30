<?php
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/partials/layout.php';
require_once __DIR__ . '/partials/sections.php';

$posts = articles();
$faq = (array)c('faq.items');
$ld = [
    org_ld(),
    ['@type' => 'WebSite', '@id' => abs_url('#site'), 'url' => abs_url(), 'name' => c('seo.site_name'), 'inLanguage' => 'fa-IR', 'publisher' => ['@id' => abs_url('#org')]],
    ['@type' => 'SoftwareApplication', 'name' => c('seo.site_name'), 'alternateName' => 'Farvam',
     'applicationCategory' => 'BusinessApplication', 'applicationSubCategory' => 'Accounting', 'operatingSystem' => 'Web, Android, iOS',
     'url' => abs_url(), 'image' => abs_url(c('seo.og_image')), 'description' => c('seo.description'), 'inLanguage' => 'fa-IR',
     'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'IRR', 'description' => c('offer.title'), 'availability' => 'https://schema.org/InStock'],
     'publisher' => ['@id' => abs_url('#org')]],
    ['@type' => 'FAQPage', 'mainEntity' => array_map(fn($f) => ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]], $faq)],
];
page_start(['title' => c('seo.title'), 'description' => c('seo.description'), 'keywords' => c('seo.keywords'), 'canonical' => abs_url(), 'ld' => $ld]);
$b = base();
?>
<main id="top">
<!-- HERO -->
<section class="hero" style="padding-block:0">
  <div class="facets" aria-hidden="true"><i style="--s:34px;--d:7s;top:12%;left:8%"></i><i style="--s:18px;--d:9s;top:62%;left:4%"></i><i style="--s:26px;--d:8s;top:78%;left:46%"></i><i style="--s:14px;--d:6s;top:20%;left:52%"></i><i style="--s:22px;--d:10s;top:8%;right:6%"></i></div>
  <div class="logo3d-stage rise" role="img" aria-label="لوگوی فَروَم">
    <div class="logo3d-tilt" id="logo3d"><div class="logo3d">
<?php for ($i = 12; $i >= 1; $i--): ?>
      <img class="ly" src="<?= $b ?>images/logo-bronze.webp" alt="" width="560" height="425" style="--i:<?= $i ?>" decoding="async">
<?php endfor; ?>
      <img class="ly front" src="<?= $b ?>images/logo-metal.webp" alt="" width="560" height="425" fetchpriority="high">
    </div></div>
    <div class="logo3d-floor"></div>
  </div>
  <div class="wrap">
    <div>
      <span class="eyebrow rise"><?= t('hero.eyebrow') ?></span>
<?php $tv = array_values(array_filter((array)c('hero.title_variants_html'))); if (!$tv) $tv = [c('hero.title_html')]; ?>
      <h1 class="rise d2" id="h1ab" data-variants="<?= h(json_encode(array_map('safe_html', $tv), JSON_UNESCAPED_UNICODE)) ?>"><?= safe_html($tv[0]) ?></h1>
      <p class="for-line rise d2"><?= t('hero.for_prefix') ?> <b id="rot"><?= h(c('hero.rotator')[0] ?? '') ?></b></p>
      <p class="lead rise d3"><?= x('hero.lead_html') ?></p>
      <div class="hero-cta">
        <a class="btn btn-gold" href="#demo"><?= t('hero.cta1') ?></a>
        <a class="btn btn-glass" href="<?= h(url_present()) ?>">▶ <?= t('presentation.cta') ?></a>
<?php if (videos_for('') || videos_for()): ?>        <button class="btn btn-glass" type="button" id="vopen">🎬 <?= t('hero.video_cta') ?></button>
<?php endif; ?>
      </div>
<?php if (c('offer.enabled')): ?>
      <div class="offer"><span class="pill"><?= t('offer.badge') ?></span><span><?= x('offer.text_html') ?></span></div>
<?php endif; ?>
      <ul class="chips"><?php foreach ((array)c('hero.chips') as $ch): ?><li><?= h($ch) ?></li><?php endforeach; ?></ul>
    </div>
    <div class="hero-visual stage rise d3">
      <figure class="shot tilt empty" data-img="acc-accounts" data-label="<?= t('labels.acc-accounts') ?>">
        <div class="bar"><i></i><i></i><i></i></div>
        <img alt="<?= t('hero.shot_alt') ?>" fetchpriority="high">
        <div class="ph"></div>
      </figure>
      <div class="voucher glass" aria-label="نمونه سند تصویری ترازو">
        <div class="lcd">12.450 <small>g · 750‰</small></div>
        <ol>
          <li><span>ثبت کیفی</span><span class="ok">✓</span></li>
          <li><span>تأیید صندوق‌دار</span><span class="ok">✓</span></li>
          <li><span>تأیید حسابدار</span><span class="wait">در انتظار</span></li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- OFFER -->
<?php if (c('offer.enabled')): ?>
<section id="offer" style="padding-block:0 30px">
  <div class="wrap">
    <div class="offer-hero glass">
      <div class="offer-num" aria-hidden="true"><span>۱</span><small>ماه</small></div>
      <div class="offer-body">
        <div class="kicker"><?= t('offer.badge') ?></div>
        <h2><?= t('offer.title') ?></h2>
        <p><?= x('offer.text_html') ?></p>
        <ul class="ticks"><?php foreach ((array)c('offer.points') as $p): ?><li><?= h($p) ?></li><?php endforeach; ?></ul>
        <?php deadline_badge(); ?>
      </div>
      <div class="acts"><a class="btn btn-gold" href="#demo"><?= t('offer.cta') ?></a><a class="btn btn-glass" href="<?= h(wa(c('contact.whatsapp_message'))) ?>" target="_blank" rel="noopener">واتساپ</a></div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php sec_testimonials(); ?>

<?php sec_videos(''); ?>

<!-- LEAKS -->
<section>
  <div class="wrap">
<?php sec_head('leaks'); ?>
    <div class="leaks">
<?php foreach ((array)c('leaks.items') as $i => $l): ?>
      <div class="leak glass"><span class="neon r"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?= $icons[$i % 5] ?></svg></span><h3><?= h($l['t']) ?></h3><p><?= h($l['d']) ?></p></div>
<?php endforeach; ?>
    </div>
    <p class="leaks-note"><?= x('leaks.note_html') ?></p>
<?php calc_box(); ?>
  </div>
</section>

<!-- PILLARS -->
<section id="pillars" style="padding-top:0">
  <div class="wrap">
<?php sec_head('pillars', false); ?>
    <div class="pillars">
<?php foreach ((array)c('pillars.items') as $i => $p): [$ic, $svg] = $pillarIcons[$i % 4]; ?>
      <article class="pillar glass">
        <div class="pillar-text">
          <div class="pillar-top"><span class="neon <?= $ic ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?= $svg ?></svg></span><h3><?= h($p['title']) ?></h3></div>
          <p><?= h($p['intro']) ?></p>
          <ul class="ticks"><?php foreach ((array)$p['ticks_html'] as $tk): ?><li><?= safe_html($tk) ?></li><?php endforeach; ?></ul>
        </div>
        <?php shot($p['img'], $p['img_alt'], $ic); ?>
      </article>
<?php endforeach; ?>
    </div>
<?php strip_cta(0); ?>
  </div>
</section>

<?php sec_presentation(); ?>

<!-- PANELS GALLERY -->
<section id="panels" style="padding-top:0">
  <div class="wrap">
<?php sec_head('panels'); ?>
    <div class="gal-tabs" role="tablist" aria-label="انتخاب پنل">
<?php $first = true; foreach ((array)c('panels.tabs') as $k => $label): ?>
      <button class="tab" role="tab" id="g-<?= h($k) ?>" aria-controls="gal-<?= h($k) ?>" aria-selected="<?= $first ? 'true' : 'false' ?>"><?= h($label) ?></button>
<?php $first = false; endforeach; ?>
    </div>
<?php $first = true; foreach ((array)c('gallery') as $k => $items): ?>
    <div class="gallery" id="gal-<?= h($k) ?>" role="tabpanel" aria-labelledby="g-<?= h($k) ?>"<?= $first ? '' : ' hidden' ?>>
<?php foreach ($items as [$img, $alt, $cap]) shot($img, $alt, $k === 'ops' ? 't' : '', $cap); ?>
    </div>
<?php $first = false; endforeach; ?>
  </div>
</section>

<!-- ROLES -->
<section id="roles" style="padding-top:0">
  <div class="wrap">
<?php sec_head('roles', false); $roles = (array)c('roles.items'); $firstK = array_key_first($roles); $r0 = $roles[$firstK] ?? []; ?>
    <div class="tabs" role="tablist" aria-label="انتخاب صنف">
<?php foreach ($roles as $k => $r): ?>
      <button class="tab" role="tab" id="t-<?= h($k) ?>" aria-controls="p-role" aria-selected="<?= $k === $firstK ? 'true' : 'false' ?>" data-k="<?= h($k) ?>"><?= h($r['tab'] ?? $k) ?></button>
<?php endforeach; ?>
    </div>
    <div class="panel glass" id="p-role" role="tabpanel" aria-labelledby="t-<?= h($firstK) ?>">
      <div>
        <div class="role" data-f="role"><?= h($r0['role'] ?? '') ?></div>
        <h3 data-f="title"><?= h($r0['title'] ?? '') ?></h3>
        <p class="intro" data-f="intro"><?= h($r0['intro'] ?? '') ?></p>
        <ul class="ticks" data-f="list"><?php foreach (($r0['list'] ?? []) as $li): ?><li><b><?= h($li[0]) ?></b> <?= h($li[1]) ?></li><?php endforeach; ?></ul>
      </div>
      <figure class="shot empty" id="role-shot" data-img="<?= h($r0['img'] ?? '') ?>" data-label=""><img alt="" loading="lazy"><div class="ph"></div></figure>
    </div>
<?php sec_segments_links(); strip_cta(1); ?>
  </div>
</section>

<!-- COMPARE -->
<section id="compare" style="padding-top:0">
  <div class="wrap">
<?php sec_head('compare', false); $cols = (array)c('compare.cols'); $short = (array)c('compare.short'); ?>
    <div class="cmp-wrap glass">
      <table class="cmp">
        <thead><tr><?php foreach ($cols as $i => $col): ?><th scope="col"<?= $i === 3 ? ' class="us"' : '' ?>><?= h($col) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
<?php foreach ((array)c('compare.rows') as $r): ?>
<tr><th scope="row"><?= h($r[0]) ?></th><td class="no" data-l="<?= h($short[0] ?? '') ?>"><?= h($r[1]) ?></td><td class="no" data-l="<?= h($short[1] ?? '') ?>"><?= h($r[2]) ?></td><td class="us"><?= h($r[3]) ?></td></tr>
<?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php sec_pitch(); ?>

<?php sec_pricing(); ?>

<!-- FLOW -->
<section style="padding-top:0">
  <div class="wrap">
<?php sec_head('flow'); ?>
    <ol class="flow">
<?php foreach ((array)c('flow.items') as $f): ?>      <li class="glass"><h3><?= h($f['t']) ?></h3><p><?= h($f['d']) ?></p></li>
<?php endforeach; ?>
    </ol>
    <div class="replaces"><span><?= t('flow.replaces_label') ?></span><?php foreach ((array)c('flow.replaces') as $r): ?><s><?= h($r) ?></s><?php endforeach; ?></div>
  </div>
</section>

<!-- SECURITY -->
<section id="security" style="padding-top:0">
  <div class="wrap">
<?php sec_head('security'); ?>
    <ul class="layers">
<?php foreach ((array)c('security.layers') as $i => $l): [$ic, $svg] = $layerIcons[$i % 3]; ?>
      <li class="glass"><span class="neon <?= $ic ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?= $svg ?></svg></span><div class="lv"><?= h($l['lv']) ?></div><h3><?= h($l['t']) ?></h3><p><?= h($l['d']) ?></p></li>
<?php endforeach; ?>
    </ul>
    <div class="proof">
<?php foreach ((array)c('security.proof') as $p): ?>      <div><b><?= h($p['b']) ?></b><span><?= h($p['s']) ?></span></div>
<?php endforeach; ?>
    </div>
  </div>
</section>

<?php sec_about(); ?>

<?php sec_follow(); ?>

<!-- BLOG -->
<?php if ($posts): ?>
<section id="blog" style="padding-top:0">
  <div class="wrap">
<?php sec_head('blog'); ?>
    <div class="posts">
<?php foreach (array_slice($posts, 0, 6) as $p): ?>
      <a class="post glass" href="<?= h(url_article($p['slug'])) ?>">
        <span class="post-tag"><?= h($p['tag'] ?? 'راهنما') ?></span>
        <h3><?= h($p['title']) ?></h3>
        <p><?= h($p['excerpt'] ?? $p['description']) ?></p>
        <span class="post-meta"><?= fa_digits(reading_minutes($p['body'])) ?> دقیقه مطالعه</span>
      </a>
<?php endforeach; ?>
    </div>
    <p style="margin-top:22px"><a class="btn btn-glass" href="<?= h(url_blog()) ?>"><?= t('blog.all') ?></a></p>
  </div>
</section>
<?php endif; ?>

<?php sec_faq($faq); ?>

<!-- STEPS -->
<section id="start" style="padding-top:0">
  <div class="wrap">
<?php sec_head('steps', false); ?>
    <ol class="steps">
<?php foreach ((array)c('steps.items') as $s): ?>      <li class="glass"><h3><?= h($s['t']) ?></h3><p><?= h($s['d']) ?></p></li>
<?php endforeach; ?>
    </ol>
  </div>
</section>

<?php sec_referral(); ?>

<?php sec_demo(); ?>

</main>
<?php video_modal(); ?>
<?php page_end(true);
