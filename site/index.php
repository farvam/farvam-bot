<?php
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/partials/layout.php';

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
function sec_head(string $k, bool $intro = true): void { ?>
    <div class="sec-head">
      <div class="kicker"><?= t("$k.kicker") ?></div>
      <h2><?= t("$k.title") ?></h2>
<?php if ($intro && c("$k.intro")): ?>      <p><?= t("$k.intro") ?></p>
<?php endif; ?>
    </div>
<?php }
function strip_cta(int $i): void { $s = c('strips')[$i] ?? null; if (!$s) return; ?>
    <div class="cta-strip glass"><p><?= h($s['title']) ?><small><?= h($s['sub']) ?></small></p><div class="acts"><a class="btn btn-gold" href="#demo"><?= t('offer.cta') ?></a><a class="btn btn-glass" href="<?= h(tel()) ?>" dir="ltr"><?= t('contact.phone_display') ?></a></div></div>
<?php }
function shot(string $img, string $alt, string $cls = '', string $cap = ''): void { ?>
<figure class="shot <?= $cls ?> empty" data-img="<?= h($img) ?>" data-label="<?= h(c("labels.$img", $img)) ?>"><img alt="<?= h($alt) ?>" loading="lazy" decoding="async"><div class="ph"></div><?php if ($cap): ?><figcaption class="cap"><?= h($cap) ?></figcaption><?php endif; ?></figure>
<?php }
$icons = [
 '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h4"/>',
 '<path d="M12 3v18M5 7h14M3 14l2-7 2 7a2 2 0 0 1-4 0ZM17 14l2-7 2 7a2 2 0 0 1-4 0Z"/>',
 '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
 '<circle cx="12" cy="12" r="8"/><path d="M12 8v4l2.5 2.5"/>',
 '<path d="M14 4l6 6-9 9H5v-6z"/><path d="M12 6l6 6"/>'];
$pillarIcons = [
 ['', '<path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/>'],
 ['t', '<rect x="3" y="6" width="18" height="14" rx="2"/><circle cx="12" cy="13" r="3.5"/><path d="M8 6l1.5-2h5L16 6"/>'],
 ['', '<path d="M3 17l5-5 4 4 8-8"/><path d="M15 8h5v5"/>'],
 ['t', '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c.5-3.5 3-5.5 6-5.5s5.5 2 6 5.5"/><path d="M16 4.5a3 3 0 0 1 0 6M18 14.5c1.8.7 2.8 2.6 3 5.5"/>']];
$layerIcons = [
 ['', '<rect x="4" y="4" width="16" height="16" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M12 9V7M12 17v-2M9 12H7M17 12h-2"/>'],
 ['t', '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>'],
 ['', '<path d="M12 3l8 3v6c0 4.5-3.5 8-8 9-4.5-1-8-4.5-8-9V6z"/><path d="M8.5 12l2.5 2.5 4.5-5"/>']];
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
      <h1 class="rise d2"><?= x('hero.title_html') ?></h1>
      <p class="for-line rise d2"><?= t('hero.for_prefix') ?> <b id="rot"><?= h(c('hero.rotator')[0] ?? '') ?></b></p>
      <p class="lead rise d3"><?= x('hero.lead_html') ?></p>
      <div class="hero-cta">
        <a class="btn btn-gold" href="#demo"><?= t('hero.cta1') ?></a>
        <a class="btn btn-glass" href="#panels"><?= t('hero.cta2') ?></a>
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

<?php if (c('offer.enabled')): ?>
<!-- OFFER -->
<section id="offer" style="padding-block:0 30px">
  <div class="wrap">
    <div class="offer-hero glass">
      <div class="offer-num" aria-hidden="true"><span>۱</span><small>ماه</small></div>
      <div class="offer-body">
        <div class="kicker"><?= t('offer.badge') ?></div>
        <h2><?= t('offer.title') ?></h2>
        <p><?= x('offer.text_html') ?></p>
        <ul class="ticks"><?php foreach ((array)c('offer.points') as $p): ?><li><?= h($p) ?></li><?php endforeach; ?></ul>
      </div>
      <div class="acts"><a class="btn btn-gold" href="#demo"><?= t('offer.cta') ?></a><a class="btn btn-glass" href="<?= h(wa(c('contact.whatsapp_message'))) ?>" target="_blank" rel="noopener">واتساپ</a></div>
    </div>
  </div>
</section>
<?php endif; ?>

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
    <div class="calc glass" id="calc" style="margin-top:34px">
      <div>
        <div class="kicker"><?= t('calc.kicker') ?></div>
        <h3 style="font-size:1.45rem"><?= t('calc.title') ?></h3>
        <p style="color:var(--muted);margin-top:10px"><?= t('calc.intro') ?></p>
        <div class="fields" style="margin-top:18px">
          <div class="form-row">
            <label for="c-sout">مغایرت متوسط هر روز (سوت)<input id="c-sout" type="number" inputmode="decimal" min="0" value="50"></label>
            <label for="c-days">روز کاری در ماه<input id="c-days" type="number" inputmode="numeric" min="1" max="31" value="26"></label>
          </div>
          <label for="c-price">قیمت هر گرم طلای ۱۸ عیار امروز (تومان)<input id="c-price" type="number" inputmode="numeric" min="0" placeholder="قیمت امروز را وارد کنید"></label>
        </div>
      </div>
      <div class="calc-out" aria-live="polite">
        <div class="big">سالانه <b id="c-year-g">۰</b> گرم</div>
        <div class="row"><span>در ماه</span><b id="c-month-g">۰ گرم</b></div>
        <div class="row"><span>ارزش سالانه به قیمت امروز</span><b id="c-year-t">قیمت را وارد کنید</b></div>
        <p class="calc-note"><?= t('calc.note') ?></p>
        <a class="btn btn-gold" href="#demo" style="justify-self:start"><?= t('calc.cta') ?></a>
      </div>
    </div>
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
<?php strip_cta(1); ?>
  </div>
</section>

<!-- PITCH ENGINE -->
<section id="pitch" style="padding-top:0">
  <div class="wrap">
<?php sec_head('pitch'); ?>
    <div class="pitch glass">
      <div class="pitch-ctrl">
        <div><span class="lbl">صنف</span><div class="tabs sm" id="p-roles"></div></div>
        <div><span class="lbl">لحن</span><div class="tabs sm" id="p-tones"></div></div>
        <div><span class="lbl">فرمول فروش</span><div class="tabs sm" id="p-forms"></div></div>
      </div>
      <div class="pitch-card">
        <div class="pitch-label" id="p-label"></div>
        <div class="pitch-out" id="p-out" aria-live="polite"></div>
        <div class="acts">
          <button type="button" class="btn btn-glass" id="p-again">یک نسخه دیگر بنویس</button>
          <button type="button" class="btn btn-glass" id="p-copy">کپی متن</button>
          <a class="btn btn-gold" id="p-wa" href="#" target="_blank" rel="noopener">فرستادن در واتساپ</a>
        </div>
      </div>
    </div>
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

<?php if (c('testimonials.enabled') && c('testimonials.items')): ?>
<!-- TESTIMONIALS -->
<section id="reviews" style="padding-top:0">
  <div class="wrap">
<?php sec_head('testimonials', false); ?>
    <div class="reviews">
<?php foreach ((array)c('testimonials.items') as $r): ?>
      <figure class="review glass"><blockquote><?= h($r['text']) ?></blockquote><figcaption><b><?= h($r['name']) ?></b><span><?= h($r['shop']) ?></span></figcaption></figure>
<?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($posts): ?>
<!-- BLOG -->
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

<!-- FAQ -->
<section id="faq" style="padding-top:0">
  <div class="wrap">
<?php sec_head('faq', false); ?>
    <div class="faq">
<?php foreach ($faq as $i => $f): ?>
      <details class="glass"<?= $i === 0 ? ' open' : '' ?>><summary><?= h($f['q']) ?></summary><p><?= h($f['a']) ?></p></details>
<?php endforeach; ?>
    </div>
  </div>
</section>

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

<!-- DEMO -->
<section id="demo" class="demo" style="padding-top:0">
  <div class="wrap">
    <div>
      <div class="kicker"><?= t('demo.kicker') ?></div>
      <h2><?= t('demo.title') ?></h2>
      <p class="lead"><?= t('demo.lead') ?></p>
      <div class="contact">
        <div class="row glass"><span class="neon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg></span><div><small>تلفن و واتساپ</small><a class="num" href="<?= h(tel()) ?>" dir="ltr"><?= t('contact.phone_display') ?></a></div></div>
        <div class="row glass"><span class="neon t"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg></span><div><small>سایت و پنل</small><a href="<?= h(c('contact.panel_url')) ?>" target="_blank" rel="noopener" dir="ltr"><?= t('contact.site_label') ?></a></div></div>
        <div class="row glass"><span class="neon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/></svg></span><div><small>دفتر و دمو حضوری</small><b><?= t('contact.address') ?></b></div></div>
      </div>
    </div>
    <form id="demo-form" class="glass" novalidate>
<?php if (c('offer.enabled')): ?>      <div class="form-offer"><span class="pill"><?= t('offer.badge') ?></span> <?= t('offer.title') ?></div>
<?php endif; ?>
      <div class="form-row">
        <label for="f-name">نام و نام خانوادگی<input id="f-name" name="name" required autocomplete="name"></label>
        <label for="f-phone">شماره موبایل<input id="f-phone" name="phone" required inputmode="tel" autocomplete="tel" placeholder="۰۹۱۲۱۲۳۴۵۶۷" dir="ltr" style="text-align:right"></label>
      </div>
      <div class="form-row">
        <label for="f-role">صنف
          <select id="f-role" name="role"><?php foreach ((array)c('roles.items') as $r): ?><option><?= h($r['tab'] ?? '') ?></option><?php endforeach; ?></select>
        </label>
        <label for="f-city">شهر<input id="f-city" name="city" autocomplete="address-level2"></label>
      </div>
      <label for="f-src">از کجا با فَروَم آشنا شدید؟
        <select id="f-src" name="src"><option value="">انتخاب کنید</option><?php foreach ((array)c('demo.sources') as $s): ?><option><?= h($s) ?></option><?php endforeach; ?></select>
      </label>
      <input type="text" name="website" id="f-web" tabindex="-1" autocomplete="off" class="hp" aria-hidden="true">
      <label for="f-note">بزرگ‌ترین دغدغه‌تان در کار (اختیاری)<textarea id="f-note" name="note" rows="2"></textarea></label>
      <button class="btn btn-gold" type="submit"><?= t('demo.submit') ?></button>
      <p class="hint err" id="f-error" role="alert" hidden>لطفاً نام و شماره موبایل را وارد کنید.</p>
      <div class="out" id="f-out" hidden>
        <p class="hint ok-msg" id="f-saved" hidden>✓ درخواست شما ثبت شد؛ همکاران ما به‌زودی تماس می‌گیرند.</p>
        <p class="hint">برای پاسخ سریع‌تر، همین متن را در واتساپ هم بفرستید:</p>
        <pre id="f-text"></pre>
        <div class="acts" id="f-acts"></div>
      </div>
    </form>
  </div>
</section>
</main>
<?php page_end(true);
