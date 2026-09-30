<?php
/* صفحه فرود مخصوص هر صنف: /for/vitrin , /for/bonak , ... */
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/partials/layout.php';
require_once __DIR__ . '/partials/sections.php';

$roles = (array)c('roles.items');
$k = $GLOBALS['SEGMENT'] ?? preg_replace('/[^a-z]/', '', (string)($_GET['role'] ?? ''));
if (!isset($roles[$k])) { http_response_code(404); header('Location: ' . url_home()); return; }
$r = $roles[$k];
$E = load_json('engine');
$er = $E['roles'][$k] ?? [];
$canon = abs_url('for/' . $k);
$title = $r['role'] . ': ' . $r['title'];
$desc = $r['intro'] . ' ' . c('offer.title') . '.';
$faq = array_merge(
    [['q' => 'فَروَم برای ' . ($er['plural'] ?? $r['tab']) . ' چه کاری انجام می‌دهد؟', 'a' => $r['intro']]],
    array_slice((array)c('faq.items'), 0, 5)
);
$ld = [org_ld(), breadcrumb_ld([['خانه', abs_url()], [$r['tab'], $canon]]),
    ['@type' => 'WebPage', 'name' => $title, 'description' => $desc, 'url' => $canon, 'inLanguage' => 'fa-IR', 'about' => ['@type' => 'SoftwareApplication', 'name' => c('seo.site_name'), 'applicationCategory' => 'BusinessApplication']],
    ['@type' => 'FAQPage', 'mainEntity' => array_map(fn($f) => ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]], $faq)]];
page_start(['title' => 'نرم‌افزار ' . $r['tab'] . ' طلا | ' . $r['title'] . ' ' . c('segments.title_suffix'), 'description' => mb_substr($desc, 0, 170), 'canonical' => $canon, 'ld' => $ld,
    'keywords' => 'نرم افزار ' . $r['tab'] . ', حسابداری ' . $r['tab'] . ' طلا, ' . c('seo.keywords')]);
$b = base();
$pains = $er['pain'] ?? []; $ag = $er['agitate'] ?? []; $after = $er['after'] ?? []; $feat = $er['feature'] ?? []; $mech = $er['mechanism'] ?? [];
?>
<main id="top">
<section class="seg-hero">
  <div class="wrap">
    <?php breadcrumbs([['خانه', url_home()], [$r['tab'], '']]); ?>
    <div class="seg-grid">
      <div>
        <span class="eyebrow"><?= h($r['role']) ?></span>
        <h1><?= h($r['title']) ?></h1>
        <p class="lead"><?= h($r['intro']) ?></p>
        <div class="hero-cta">
          <a class="btn btn-gold" href="#demo"><?= t('segments.cta') ?> <?= h($er['plural'] ?? $r['tab']) ?></a>
          <a class="btn btn-glass" href="<?= h(url_present()) ?>">▶ <?= t('presentation.cta') ?></a>
        </div>
<?php if (c('offer.enabled')): ?>        <div class="offer"><span class="pill"><?= t('offer.badge') ?></span><span><?= x('offer.text_html') ?></span></div>
<?php endif; ?>
      </div>
      <figure class="shot tilt empty" data-img="<?= h($r['img']) ?>" data-label="<?= h(c('labels.' . $r['img'], '')) ?>"><div class="bar"><i></i><i></i><i></i></div><img alt="<?= h(c('labels.' . $r['img'], '') . ' در فَروَم') ?>"><div class="ph"></div></figure>
    </div>
  </div>
</section>

<?php if ($pains): ?>
<section style="padding-top:0">
  <div class="wrap">
    <div class="sec-head"><div class="kicker">دردهای رایج <?= h($er['plural'] ?? '') ?></div><h2>این‌ها برایتان آشناست؟</h2></div>
    <div class="leaks seg-pains">
<?php foreach ($pains as $i => $p): ?>
      <div class="leak glass"><span class="neon r"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v5M12 16.5v.5"/><circle cx="12" cy="12" r="9"/></svg></span><h3><?= h($p) ?></h3><p><?= h($ag[$i] ?? '') ?></p></div>
<?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section style="padding-top:0">
  <div class="wrap">
    <div class="sec-head"><div class="kicker">با فَروَم</div><h2>بعد از فَروَم، روزهای کاری‌تان این شکلی است</h2></div>
    <div class="ba">
<?php foreach ($after as $i => $a): ?>
      <div class="ba-row glass"><div><span class="ba-l">قبل</span><p><?= h($pains[$i] ?? '') ?></p></div><div class="ba-arrow" aria-hidden="true">←</div><div><span class="ba-l ok">بعد</span><p><b><?= h($a) ?></b></p></div></div>
<?php endforeach; ?>
    </div>
    <ul class="ticks seg-list">
<?php foreach ($r['list'] as $li): ?>      <li><b><?= h($li[0]) ?></b> <?= h($li[1]) ?></li>
<?php endforeach; ?>
    </ul>
    <div class="cta-strip glass"><p>همه این‌ها را یک ماه رایگان روی کار خودتان امتحان کنید<small><?= h(implode(' · ', array_slice($feat, 0, 2))) ?></small></p><div class="acts"><a class="btn btn-gold" href="#demo"><?= t('offer.cta') ?></a><a class="btn btn-glass" href="<?= h(tel()) ?>" dir="ltr"><?= t('contact.phone_display') ?></a></div></div>
  </div>
</section>

<?php if ($mech): ?>
<section style="padding-top:0">
  <div class="wrap">
    <div class="sec-head"><div class="kicker">چطور کار می‌کند؟</div><h2>سازوکار، نه ادعا</h2></div>
    <ol class="steps"><?php foreach ($mech as $i => $m): ?><li class="glass"><h3><?= h($feat[$i] ?? '') ?></h3><p><?= h($m) ?></p></li><?php endforeach; ?></ol>
  </div>
</section>
<?php endif; ?>

<?php sec_videos($k); sec_testimonials(); ?>
<section style="padding-top:0"><div class="wrap"><?php calc_box(); ?></div></section>
<?php sec_presentation(); sec_follow(); sec_faq($faq); ?>
<section style="padding-top:0"><div class="wrap"><?php sec_segments_links(); ?><p style="margin-top:14px"><a class="btn btn-glass" href="<?= h(url_home('#pillars')) ?>"><?= t('segments.more') ?></a></p></div></section>
<?php sec_referral(); sec_demo($r['tab']); ?>
</main>
<?php page_end(true);
