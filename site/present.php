<?php
/* ارائه تعاملی معرفی فَروَم (ساخته‌شده از data/presentation.json) */
require_once __DIR__ . '/lib.php';
$P = load_json('presentation');
$slides = $P['slides'] ?? [];
$b = base();
$home = url_home();
$pptx = $b . 'files/farvam-presentation.pptx';
$canon = abs_url(is_static() ? 'present.html' : 'present.php');
function pimg(string $k): string { $b = base(); return is_file(__DIR__ . "/images/$k.webp") ? $b . "images/$k.webp" : ''; }
$ic = [
 'receipt' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h4"/>',
 'scale' => '<path d="M12 3v18M5 7h14M3 14l2-7 2 7a2 2 0 0 1-4 0ZM17 14l2-7 2 7a2 2 0 0 1-4 0Z"/>',
 'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
 'stamp' => '<circle cx="12" cy="12" r="8"/><path d="M12 8v4l2.5 2.5"/>',
 'tool' => '<path d="M14 4l6 6-9 9H5v-6z"/><path d="M12 6l6 6"/>',
 'coins' => '<path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/>',
 'camera' => '<rect x="3" y="6" width="18" height="14" rx="2"/><circle cx="12" cy="13" r="3.5"/><path d="M8 6l1.5-2h5L16 6"/>',
 'chart' => '<path d="M3 17l5-5 4 4 8-8"/><path d="M15 8h5v5"/>',
 'users' => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c.5-3.5 3-5.5 6-5.5s5.5 2 6 5.5"/><path d="M16 4.5a3 3 0 0 1 0 6M18 14.5c1.8.7 2.8 2.6 3 5.5"/>',
 'vault' => '<rect x="4" y="4" width="16" height="16" rx="2"/><circle cx="12" cy="12" r="3"/>',
 'mobile' => '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>',
 'shield' => '<path d="M12 3l8 3v6c0 4.5-3.5 8-8 9-4.5-1-8-4.5-8-9V6z"/><path d="M8.5 12l2.5 2.5 4.5-5"/>'];
$svg = fn($k) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">' . $ic[$k] . '</svg>';
?><!doctype html>
<html lang="fa-IR" dir="rtl" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>معرفی تعاملی فَروَم | <?= t('seo.site_name') ?></title>
<meta name="description" content="معرفی قدم‌به‌قدم فَروَم، اکوسیستم مدیریت، عملیات و معاملات طلا و جواهر؛ در <?= fa_digits(count($slides)) ?> اسلاید.">
<link rel="canonical" href="<?= h($canon) ?>">
<meta property="og:title" content="معرفی تعاملی فَروَم"><meta property="og:image" content="<?= h(abs_url(c('seo.og_image'))) ?>">
<meta name="color-scheme" content="dark"><meta name="theme-color" content="#060910">
<link rel="icon" href="<?= $b ?>images/logo-mark-gold.png">
<link rel="stylesheet" href="<?= $b ?>assets/style.css?v=<?= asset_v('style.css') ?>">
<style>
:root{color-scheme:dark;--bg:#060910;--text:#EEF2F8;--muted:#A3AEC2;--gold:#FFC84A;--teal:#2EF2D0;--rose:#FF6B8B;--card:rgba(255,255,255,.06);--edge:rgba(255,255,255,.12)}
html,body{height:100%}
body{margin:0;background:radial-gradient(900px 600px at 90% -10%,rgba(255,200,74,.18),transparent 60%),radial-gradient(800px 600px at 0 100%,rgba(46,242,208,.1),transparent 60%),#060910;color:var(--text);font-family:var(--body);overflow:hidden}
.deck{position:fixed;inset:0;display:grid;grid-template-rows:auto 1fr auto;padding:max(12px,env(safe-area-inset-top)) 16px max(12px,env(safe-area-inset-bottom))}
.top{display:flex;align-items:center;justify-content:space-between;gap:12px}
.top a:not(.btn){color:var(--muted);text-decoration:none;font-size:.9rem}
.top img{height:34px;width:auto}
.prog{position:absolute;top:0;inset-inline:0;height:3px;background:rgba(255,255,255,.08)}
.prog i{display:block;height:100%;background:linear-gradient(90deg,var(--teal),var(--gold));transition:width .5s ease;width:0}
.stage{position:relative;overflow:hidden}
.sl{position:absolute;inset:0;display:grid;align-content:center;padding:clamp(8px,3vw,40px) clamp(4px,4vw,60px);opacity:0;transform:translateX(-40px) scale(.98);transition:opacity .55s ease,transform .55s ease;pointer-events:none;overflow-y:auto}
.sl.on{opacity:1;transform:none;pointer-events:auto}
.sl.prev{transform:translateX(40px) scale(.98)}
.sl h1,.sl h2{font-family:var(--display);font-weight:700;line-height:1.9;margin:0;text-wrap:balance}
.sl h2{font-size:clamp(1.5rem,3.6vw,2.6rem)}
.sl .sub{color:var(--muted);font-size:clamp(.95rem,1.7vw,1.15rem);max-width:60ch;margin:6px 0 20px}
.sl .kick{color:var(--teal);font-weight:800;font-size:.95rem}
.two{display:grid;grid-template-columns:1.05fr .95fr;gap:clamp(18px,4vw,48px);align-items:center}
.two>*{min-width:0}
.bul{list-style:none;margin:0;padding:0;display:grid;gap:10px}
.bul li{position:relative;padding-inline-start:22px;line-height:1.9;font-size:clamp(.92rem,1.5vw,1.05rem)}
.bul li::before{content:"";position:absolute;inset-inline-start:0;top:.75em;width:9px;height:9px;background:var(--gold);transform:rotate(45deg);box-shadow:0 0 10px rgba(255,200,74,.6)}
.sl.on .bul li{animation:li .5s ease both}
.sl.on .bul li:nth-child(2){animation-delay:.08s}.sl.on .bul li:nth-child(3){animation-delay:.16s}.sl.on .bul li:nth-child(4){animation-delay:.24s}.sl.on .bul li:nth-child(5){animation-delay:.32s}
@keyframes li{from{opacity:0;transform:translateX(-14px)}to{opacity:1;transform:none}}
.shotf{border:1px solid rgba(255,200,74,.5);border-radius:16px;padding:8px;background:var(--card);box-shadow:0 0 34px -12px rgba(255,200,74,.6),0 30px 60px -30px #000}
.shotf img{display:block;width:100%;height:auto;border-radius:10px;max-height:58vh;object-fit:contain;background:#E9ECF1}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px}
.card{background:var(--card);border:1px solid var(--edge);border-radius:16px;padding:16px;display:grid;gap:8px;align-content:start}
.card b{font-size:1rem}.card span{color:var(--muted);font-size:.88rem}
.sl.on .card{animation:li .5s ease both}
.sl.on .card:nth-child(2){animation-delay:.07s}.sl.on .card:nth-child(3){animation-delay:.14s}.sl.on .card:nth-child(4){animation-delay:.21s}.sl.on .card:nth-child(5){animation-delay:.28s}
.ico{width:44px;height:44px;border-radius:13px;display:grid;place-items:center;border:1.5px solid var(--gold);color:var(--gold);box-shadow:0 0 14px -4px rgba(255,200,74,.7)}
.ico.r{border-color:var(--rose);color:var(--rose);box-shadow:0 0 14px -4px rgba(255,107,139,.7)}.ico.t{border-color:var(--teal);color:var(--teal);box-shadow:0 0 14px -4px rgba(46,242,208,.6)}
.ico svg{width:22px;height:22px}
.flowr{display:flex;flex-wrap:wrap;gap:10px;align-items:stretch}
.flowr .card{flex:1 1 150px;border-color:rgba(255,200,74,.45)}
.flowr .n{color:var(--gold);font-weight:800;font-size:1.3rem}
.chipsx{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px;color:var(--muted);font-size:.88rem;align-items:center}
.chipsx s{border:1px solid var(--edge);border-radius:999px;padding:.1em .8em;text-decoration-color:var(--rose)}
.cover{display:grid;grid-template-columns:auto 1fr;gap:clamp(20px,5vw,60px);align-items:center}
.cover img{width:clamp(150px,26vw,330px);height:auto;filter:drop-shadow(0 0 30px rgba(255,200,74,.45));animation:float 6s ease-in-out infinite}
@keyframes float{0%,100%{transform:translateY(0) rotateY(-10deg)}50%{transform:translateY(-10px) rotateY(10deg)}}
.cover h1{font-size:clamp(2.6rem,7vw,5rem);color:var(--gold);line-height:1.4;margin-bottom:1.1em!important}
.cover .lead{font-size:clamp(1.05rem,2vw,1.4rem);font-weight:700}
.medal{width:clamp(150px,22vw,230px);aspect-ratio:1;border-radius:50%;display:grid;place-items:center;align-content:center;background:radial-gradient(circle at 32% 28%,rgba(255,255,255,.55),transparent 42%),linear-gradient(115deg,#9A6A10,#E0AE3E 22%,#FFE39A 44%,#FFF7DA 50%,#FFD066 58%,#C8901A 80%,#8A5A08);color:#241803;box-shadow:inset 0 0 0 6px #FFC84A,0 0 50px -10px rgba(255,200,74,.7)}
.medal b{font-size:clamp(3rem,7vw,5rem);line-height:1}.medal span{font-weight:800}
.ctaa{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}
.bar{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.nav-b{display:flex;gap:8px;align-items:center}
.nb{width:46px;height:46px;border-radius:14px;border:1px solid var(--edge);background:var(--card);color:var(--text);cursor:pointer;font-size:1.3rem;display:grid;place-items:center}
.nb:hover{border-color:var(--teal)}
.dots{display:flex;gap:6px;flex-wrap:wrap;justify-content:center}
.dots button{width:9px;height:9px;border-radius:50%;border:0;padding:0;background:rgba(255,255,255,.2);cursor:pointer}
.dots button.on{background:var(--gold);box-shadow:0 0 8px var(--gold);width:22px;border-radius:5px}
.cnt{color:var(--muted);font-size:.9rem;min-width:4.5em;text-align:center}
.side-acts{display:flex;gap:8px;flex-wrap:wrap}
.side-acts .btn{padding:.55em 1.1em;font-size:.9rem}
@media (max-width:800px){.two,.cover{grid-template-columns:1fr}.cover{justify-items:start}.shotf img{max-height:32vh}.dots{display:none}.side-acts .hide-sm{display:none}}
@media (prefers-reduced-motion:reduce){.sl,.sl.on .bul li,.sl.on .card,.cover img{transition:none;animation:none}}
</style>
</head>
<body>
<div class="deck">
  <div class="prog"><i id="pg"></i></div>
  <div class="top">
    <a href="<?= h($home) ?>"><img src="<?= $b ?>images/logo-metal.webp" alt="فَروَم" width="45" height="34"></a>
    <div class="side-acts">
      <button class="btn btn-glass hide-sm" id="auto" type="button">▶ پخش خودکار</button>
      <a class="btn btn-glass" href="<?= h($pptx) ?>" download>دانلود پاورپوینت</a>
      <a class="btn btn-gold" href="<?= h(url_home('#demo')) ?>"><?= t('offer.badge') ?></a>
    </div>
  </div>
  <main class="stage" id="stage" aria-live="polite">
<?php foreach ($slides as $i => $s): $k = $s['kind']; ?>
    <section class="sl<?= $i === 0 ? ' on' : '' ?>" aria-label="اسلاید <?= fa_digits($i + 1) ?>">
<?php if ($k === 'cover'): ?>
      <div class="cover"><img src="<?= $b ?>images/logo-metal.webp" alt="لوگوی فَروَم" width="560" height="425"><div><h1><?= h($s['title']) ?></h1><p class="lead"><?= h($s['subtitle']) ?></p><p class="sub"><?= h($s['tagline']) ?></p><div class="ctaa"><button class="btn btn-gold" type="button" data-go="1">شروع معرفی ←</button><span class="pill"><?= t('offer.badge') ?></span></div></div></div>
<?php elseif ($k === 'problem' || $k === 'grid'): $icons = $k === 'problem' ? ['receipt','scale','eye','stamp','tool'] : ['coins','camera','chart','users']; ?>
      <h2><?= h($s['title']) ?></h2><p class="sub"><?= h($s['subtitle']) ?></p>
      <div class="cards"><?php foreach ($s['items'] as $j => [$t, $d]): ?><div class="card"><span class="ico <?= $k === 'problem' ? 'r' : ($j % 2 ? 't' : '') ?>"><?= $svg($icons[$j % count($icons)]) ?></span><b><?= h($t) ?></b><span><?= h($d) ?></span></div><?php endforeach; ?></div>
<?php elseif ($k === 'feature' || $k === 'role'): $im = pimg($s['image'] ?? ''); ?>
      <div class="two">
        <div><?php if ($k === 'role'): ?><div class="kick"><?= h($s['role']) ?></div><?php endif; ?><h2><?= h($s['title']) ?></h2><?php if (!empty($s['subtitle'])): ?><p class="sub"><?= h($s['subtitle']) ?></p><?php endif; ?>
          <ul class="bul"><?php foreach ($s['bullets'] as $bl): ?><li><?= h($bl) ?></li><?php endforeach; ?></ul></div>
        <div><?php if ($im): ?><div class="shotf"><img src="<?= h($im) ?>" alt="<?= h($s['title']) ?>" loading="lazy"></div><?php else: ?><div class="cards"><div class="card"><span class="ico"><?= $svg('chart') ?></span><b>مظنه لحظه‌ای</b></div><div class="card"><span class="ico t"><?= $svg('shield') ?></span><b>فی محرمانه</b></div><div class="card"><span class="ico"><?= $svg('users') ?></span><b>برند شما</b></div></div><?php endif; ?></div>
      </div>
<?php elseif ($k === 'flow'): ?>
      <h2><?= h($s['title']) ?></h2><p class="sub"><?= h($s['subtitle']) ?></p>
      <div class="flowr"><?php foreach ($s['items'] as $j => [$t, $d]): ?><div class="card"><span class="n"><?= fa_digits($j + 1) ?></span><b><?= h($t) ?></b><span><?= h($d) ?></span></div><?php endforeach; ?></div>
      <div class="chipsx"><span>به‌جای:</span><?php foreach ($s['replaces'] as $r): ?><s><?= h($r) ?></s><?php endforeach; ?></div>
<?php elseif ($k === 'chain'): $icons = ['vault','mobile','shield']; ?>
      <h2><?= h($s['title']) ?></h2><p class="sub"><?= h($s['subtitle']) ?></p>
      <div class="cards"><?php foreach ($s['items'] as $j => [$t, $d]): ?><div class="card"><span class="ico <?= $j === 1 ? 't' : '' ?>"><?= $svg($icons[$j]) ?></span><span class="kick">لایه <?= fa_digits($j + 1) ?></span><b><?= h($t) ?></b><span><?= h($d) ?></span></div><?php endforeach; ?></div>
<?php elseif ($k === 'panels'): ?>
      <h2><?= h($s['title']) ?></h2><p class="sub"><?= h($s['subtitle']) ?></p>
      <div class="cards"><?php foreach ($s['items'] as [$t, $imk]): $im = pimg($imk); ?><div class="card"><?php if ($im): ?><div class="shotf" style="padding:5px"><img src="<?= h($im) ?>" alt="<?= h($t) ?>" loading="lazy" style="max-height:30vh"></div><?php endif; ?><b style="color:var(--gold)"><?= h($t) ?></b></div><?php endforeach; ?></div>
<?php elseif ($k === 'cta'): ?>
      <div class="cover"><div class="medal"><b>۱</b><span>ماه رایگان</span></div><div><h2 style="color:var(--gold)"><?= h($s['title']) ?></h2><p class="sub"><?= h($s['subtitle']) ?></p>
        <div class="cards"><?php foreach ($s['items'] as $j => [$t, $v]): ?><div class="card"><span><?= h($t) ?></span><b<?= $j < 2 ? ' dir="ltr" style="text-align:right"' : '' ?>><?= h($v) ?></b></div><?php endforeach; ?></div>
        <div class="ctaa"><a class="btn btn-gold" href="<?= h(url_home('#demo')) ?>"><?= t('offer.cta') ?></a><a class="btn btn-glass" href="<?= h(tel()) ?>">تماس</a><a class="btn btn-glass" href="<?= h(wa(c('contact.whatsapp_message'))) ?>" target="_blank" rel="noopener">واتساپ</a></div></div></div>
<?php endif; ?>
    </section>
<?php endforeach; ?>
  </main>
  <div class="bar">
    <div class="nav-b"><button class="nb" id="prev" type="button" aria-label="اسلاید قبلی">→</button><span class="cnt" id="cnt"></span><button class="nb" id="next" type="button" aria-label="اسلاید بعدی">←</button></div>
    <div class="dots" id="dots"></div>
    <a class="btn btn-glass" href="<?= h($home) ?>" style="padding:.5em 1em;font-size:.88rem">بازگشت به سایت</a>
  </div>
</div>
<script>
(function(){
  var S=[].slice.call(document.querySelectorAll('.sl')),n=S.length,i=0,timer=0,dots=document.getElementById('dots');
  var fa=function(x){return Number(x).toLocaleString('fa-IR')};
  S.forEach(function(_,k){var b=document.createElement('button');b.type='button';b.setAttribute('aria-label','اسلاید '+fa(k+1));b.onclick=function(){go(k)};dots.appendChild(b)});
  function go(k){k=Math.max(0,Math.min(n-1,k));S.forEach(function(s,j){s.classList.toggle('on',j===k);s.classList.toggle('prev',j<k)});i=k;
    document.getElementById('cnt').textContent=fa(i+1)+' / '+fa(n);document.getElementById('pg').style.width=((i+1)/n*100)+'%';
    [].forEach.call(dots.children,function(d,j){d.classList.toggle('on',j===i)});try{history.replaceState(null,'','#'+(i+1))}catch(e){}}
  document.getElementById('next').onclick=function(){go(i+1)};document.getElementById('prev').onclick=function(){go(i-1)};
  [].forEach.call(document.querySelectorAll('[data-go]'),function(b){b.onclick=function(){go(+b.dataset.go)}});
  addEventListener('keydown',function(e){if(e.key==='ArrowLeft'||e.key===' '||e.key==='PageDown'){e.preventDefault();go(i+1)}if(e.key==='ArrowRight'||e.key==='PageUp')go(i-1);if(e.key==='Home')go(0);if(e.key==='End')go(n-1)});
  var x0=null;addEventListener('touchstart',function(e){x0=e.touches[0].clientX},{passive:true});
  addEventListener('touchend',function(e){if(x0===null)return;var dx=e.changedTouches[0].clientX-x0;if(Math.abs(dx)>50)go(i+(dx>0?1:-1));x0=null},{passive:true});
  var ab=document.getElementById('auto');ab.onclick=function(){if(timer){clearInterval(timer);timer=0;ab.textContent='▶ پخش خودکار'}else{timer=setInterval(function(){if(i>=n-1){clearInterval(timer);timer=0;ab.textContent='▶ پخش خودکار';return}go(i+1)},7000);ab.textContent='❚❚ توقف'}};
  var h=parseInt((location.hash||'').slice(1),10);go(h>0?h-1:0);
})();
</script>
</body>
</html>
