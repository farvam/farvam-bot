<?php
/* Reusable page sections (home page and trade landing pages). */
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

function calc_box(): void { ?>
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
        <button class="btn btn-gold" type="button" id="c-to-form" style="justify-self:start"><?= t('calc.cta') ?></button>
      </div>
    </div>
<?php }
function sec_demo(?string $role = null): void { ?>
<section id="demo" class="demo" style="padding-top:0">
  <div class="wrap">
    <div>
      <div class="kicker"><?= t('demo.kicker') ?></div>
      <h2><?= t('demo.title') ?></h2>
      <p class="lead"><?= t('demo.lead') ?></p>
<?php if (c('contact.callback_text')): ?>      <p class="callback">⏱ <?= t('contact.callback_text') ?></p>
<?php endif; ?>
<?php guarantees(); ?>
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
          <select id="f-role" name="role"><?php foreach ((array)c('roles.items') as $r): ?><option<?= $role !== null && ($r['tab'] ?? '') === $role ? ' selected' : '' ?>><?= h($r['tab'] ?? '') ?></option><?php endforeach; ?></select>
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
<?php }
function sec_faq(array $items): void { ?>
<section id="faq" style="padding-top:0">
  <div class="wrap">
<?php sec_head('faq', false); ?>
    <div class="faq">
<?php foreach ($items as $i => $f): ?>
      <details class="glass"<?= $i === 0 ? ' open' : '' ?>><summary><?= h($f['q']) ?></summary><p><?= h($f['a']) ?></p></details>
<?php endforeach; ?>
    </div>
  </div>
</section>
<?php }
function sec_pitch(): void { ?>
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
<?php }
function sec_testimonials(): void { if (!c('testimonials.enabled') || !c('testimonials.items')) return; ?>
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
<?php }

function guarantees(): void { $g = (array)c('trust.guarantees'); if (!$g) return; ?>
<ul class="guar"><?php foreach ($g as $x): ?><li><?= h($x) ?></li><?php endforeach; ?></ul>
<?php }

function deadline_badge(): void { $d = (string)c('offer.deadline'); if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) || strtotime($d . ' 23:59:59') < time()) return; ?>
<div class="deadline" data-deadline="<?= h($d) ?>T23:59:59+03:30"><span><?= t('offer.deadline_label') ?>: <?= jalali_label($d) ?></span><b class="cd" aria-live="off"></b></div>
<?php }

function sec_presentation(): void { if (!c('presentation.enabled')) return; ?>
<section id="present" style="padding-top:0">
  <div class="wrap">
    <a class="present-card glass" href="<?= h(url_present()) ?>">
      <div class="pc-visual" aria-hidden="true">
        <span class="pc-slide s3"></span><span class="pc-slide s2"></span>
        <span class="pc-slide s1"><img src="<?= base() ?>images/logo-metal.webp" alt="" width="560" height="425" loading="lazy"></span>
        <span class="pc-play">▶</span>
      </div>
      <div class="pc-text">
        <div class="kicker"><?= t('presentation.kicker') ?></div>
        <h2><?= t('presentation.title') ?></h2>
        <p><?= t('presentation.text') ?></p>
        <span class="btn btn-gold"><?= t('presentation.cta') ?> ←</span>
      </div>
    </a>
    <p class="pc-dl"><a href="<?= h(base() . 'files/farvam-presentation.pptx') ?>" download>⬇ <?= t('presentation.download') ?></a></p>
  </div>
</section>
<?php }

function sec_videos(?string $role = null): void {
    $v = $role === null ? videos_for() : videos_for($role);
    if (!$v) return; ?>
<section id="videos" style="padding-top:0">
  <div class="wrap">
<?php sec_head('videos', false); ?>
    <div class="vids<?= count($v) === 1 ? ' one' : '' ?>">
<?php foreach ($v as $it): ?>
      <figure class="vid glass"><div class="vframe"><?= video_embed($it['url'], $it['title']) ?></div><figcaption><b><?= h($it['title']) ?></b><?php if (!empty($it['desc'])): ?><span><?= h($it['desc']) ?></span><?php endif; ?></figcaption></figure>
<?php endforeach; ?>
    </div>
  </div>
</section>
<?php }

function video_modal(): void { $v = videos_for(''); if (!$v) $v = videos_for(); if (!$v) return; ?>
<dialog class="vmodal" id="vmodal" aria-label="<?= h($v[0]['title']) ?>"><form method="dialog"><button class="vclose" aria-label="بستن">×</button></form><div class="vframe" data-embed="<?= h(video_embed($v[0]['url'], $v[0]['title'])) ?>"></div></dialog>
<?php }

function sec_follow(): void {
    if (!c('follow.enabled')) return;
    $ig = trim((string)c('social.instagram')); $site = trim((string)c('follow.site_url'));
    if ($ig === '' && $site === '') return; ?>
<section id="follow" style="padding-top:0">
  <div class="wrap">
    <div class="follow glass">
      <div class="follow-text">
        <div class="kicker"><?= t('follow.kicker') ?></div>
        <h2><?= t('follow.title') ?></h2>
        <p><?= t('follow.text') ?></p>
      </div>
      <div class="follow-cards">
<?php if ($ig !== ''): ?>
        <a class="fcard ig" href="https://instagram.com/<?= h(ltrim($ig, '@')) ?>" target="_blank" rel="noopener">
          <span class="fic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg></span>
          <span><small>اینستاگرام</small><b dir="ltr">@<?= h(ltrim($ig, '@')) ?></b></span><em><?= t('follow.instagram_cta') ?> ←</em>
        </a>
<?php endif; if ($site !== ''): ?>
        <a class="fcard web" href="<?= h($site) ?>" target="_blank" rel="noopener">
          <span class="fic"><img src="<?= base() ?>images/logo-metal.webp" alt="" width="56" height="42"></span>
          <span><small>سایت فَروَم</small><b dir="ltr"><?= t('follow.site_label') ?></b></span><em><?= t('follow.site_cta') ?> ←</em>
        </a>
<?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php }

function sec_pricing(): void { if (!c('pricing.enabled') || !c('pricing.plans')) return; ?>
<section id="pricing" style="padding-top:0">
  <div class="wrap">
<?php sec_head('pricing', false); ?>
    <div class="plans">
<?php foreach ((array)c('pricing.plans') as $p): ?>
      <div class="plan glass<?= !empty($p['highlight']) ? ' hot' : '' ?>">
        <?php if (!empty($p['highlight'])): ?><span class="pill">پیشنهاد ما</span><?php endif; ?>
        <h3><?= h($p['name']) ?></h3>
        <div class="price"><b><?= h($p['price']) ?></b><span><?= h($p['period']) ?></span></div>
        <ul class="ticks"><?php foreach ((array)$p['features'] as $f): ?><li><?= h($f) ?></li><?php endforeach; ?></ul>
        <a class="btn <?= !empty($p['highlight']) ? 'btn-gold' : 'btn-glass' ?>" href="#demo"><?= h($p['cta'] ?? c('offer.cta')) ?></a>
      </div>
<?php endforeach; ?>
    </div>
    <p class="plans-note"><?= t('pricing.note') ?></p>
  </div>
</section>
<?php }

function sec_about(): void { if (!c('about.enabled')) return; $ph = trim((string)c('about.photo')); ?>
<section id="about" style="padding-top:0">
  <div class="wrap">
    <div class="about glass">
      <?php if ($ph !== ''): ?><img class="about-ph" src="<?= h(file_url($ph)) ?>" alt="<?= t('about.name') ?>" loading="lazy" width="220" height="220"><?php endif; ?>
      <div>
        <div class="kicker"><?= t('about.kicker') ?></div>
        <h2><?= t('about.title') ?></h2>
        <p class="about-story"><?= x('about.story_html') ?></p>
        <p class="about-sign"><b><?= t('about.name') ?></b> · <?= t('about.role') ?></p>
        <?php if (c('about.facts')): ?><ul class="chips"><?php foreach ((array)c('about.facts') as $f): ?><li><?= h($f) ?></li><?php endforeach; ?></ul><?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php }

function sec_referral(): void { if (!c('referral.enabled')) return;
    $msg = c('referral.message') . ' ' . abs_url() . '?utm_source=referral&utm_campaign=friend'; ?>
<section id="referral" style="padding-top:0">
  <div class="wrap">
    <div class="referral glass">
      <div class="ref-ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M8 11l3 3 5-6"/><path d="M3 12c2-4 5-6 9-6s7 2 9 6c-2 4-5 6-9 6s-7-2-9-6z"/></svg></div>
      <div>
        <div class="kicker"><?= t('referral.kicker') ?></div>
        <h2><?= t('referral.title') ?></h2>
        <p><?= t('referral.text') ?></p>
      </div>
      <a class="btn btn-gold" href="<?= h('https://wa.me/?text=' . rawurlencode($msg)) ?>" target="_blank" rel="noopener"><?= t('referral.cta') ?></a>
    </div>
  </div>
</section>
<?php }

function sec_segments_links(): void { ?>
<div class="seglinks"><span>صفحه مخصوص صنف شما:</span><?php foreach ((array)c('roles.items') as $k => $r): ?><a href="<?= h(url_segment($k)) ?>"><?= h($r['tab'] ?? $k) ?></a><?php endforeach; ?></div>
<?php }
