<?php
require_once dirname(__DIR__) . '/lib.php';
require_once dirname(__DIR__) . '/partials/layout.php';

$slug = $GLOBALS['ARTICLE_SLUG'] ?? preg_replace('/[^a-z0-9\-]/', '', (string)($_GET['slug'] ?? ''));
$a = article_by_slug($slug);
if (!$a) {
    http_response_code(404);
    page_start(['title' => 'صفحه پیدا نشد | ' . c('seo.site_name'), 'robots' => 'noindex']);
    echo '<main id="top"><section class="blog-head"><div class="wrap"><div class="sec-head"><h1>این مقاله پیدا نشد</h1><p><a href="' . h(url_blog()) . '">همه مقاله‌ها</a></p></div></div></section></main>';
    page_end();
    return;
}
[$toc, $body] = toc_and_body(expand_links(safe_html($a['body'])));
$canon = canonical_article($a['slug']);
$crumbs = [['خانه', url_home()], ['مقالات', url_blog()], [$a['title'], '']];
$ld = [
    org_ld(),
    breadcrumb_ld([['خانه', abs_url()], ['مقالات', abs_url('blog/')], [$a['title'], $canon]]),
    ['@type' => 'BlogPosting', 'headline' => $a['title'], 'description' => $a['description'], 'mainEntityOfPage' => $canon, 'url' => $canon,
     'datePublished' => $a['date'], 'dateModified' => $a['updated'] ?? $a['date'], 'inLanguage' => 'fa-IR',
     'image' => abs_url($a['image'] ?? c('seo.og_image')), 'keywords' => $a['keywords'] ?? '',
     'wordCount' => count(preg_split('/\s+/u', trim(strip_tags($a['body'])))),
     'author' => ['@type' => 'Organization', 'name' => c('seo.site_name'), 'url' => abs_url()],
     'publisher' => ['@id' => abs_url('#org')]],
];
if (!empty($a['faq'])) $ld[] = ['@type' => 'FAQPage', 'mainEntity' => array_map(fn($f) => ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]], $a['faq'])];

page_start(['title' => ($a['seo_title'] ?? $a['title']) . ' | ' . c('seo.site_name'), 'og_title' => $a['title'], 'description' => $a['description'],
    'keywords' => $a['keywords'] ?? '', 'canonical' => $canon, 'type' => 'article', 'published' => $a['date'], 'modified' => $a['updated'] ?? $a['date'],
    'image' => $a['image'] ?? c('seo.og_image'), 'ld' => $ld]);

$related = array_values(array_filter(articles(), fn($p) => $p['slug'] !== $a['slug']));
usort($related, fn($x, $y) => (($y['tag'] ?? '') === ($a['tag'] ?? '')) <=> (($x['tag'] ?? '') === ($a['tag'] ?? '')));
$related = array_slice($related, 0, 3);
?>
<main id="top">
<article class="art">
  <div class="wrap art-grid">
    <div class="art-main">
      <?php breadcrumbs($crumbs); ?>
      <header class="art-head">
        <span class="post-tag"><?= h($a['tag'] ?? 'راهنما') ?></span>
        <h1><?= h($a['title']) ?></h1>
        <p class="art-lead"><?= h($a['description']) ?></p>
        <p class="post-meta">به‌روزرسانی: <time datetime="<?= h($a['updated'] ?? $a['date']) ?>"><?= jalali_label($a['updated'] ?? $a['date']) ?></time> · <?= fa_digits(reading_minutes($a['body'])) ?> دقیقه مطالعه</p>
      </header>
      <div class="share"><span>اشتراک‌گذاری:</span>
          <a href="https://t.me/share/url?url=<?= rawurlencode($canon) ?>&text=<?= rawurlencode($a['title']) ?>" target="_blank" rel="noopener">تلگرام</a>
          <a href="https://wa.me/?text=<?= rawurlencode($a['title'] . ' ' . $canon) ?>" target="_blank" rel="noopener">واتساپ</a>
          <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($canon) ?>" target="_blank" rel="noopener">لینکدین</a>
      </div>
<?php if ($toc): ?>
      <nav class="toc glass" aria-label="فهرست مطالب"><b>در این مقاله</b><ol><?php foreach ($toc as $it): ?><li><a href="#<?= h($it['id']) ?>"><?= h($it['t']) ?></a></li><?php endforeach; ?></ol></nav>
<?php endif; ?>
      <div class="prose">
<?php
// place the offer box after the 3rd h2 so readers meet it mid-article
$parts = preg_split('/(?=<h2 id="s4")/', $body, 2);
echo $parts[0];
if (isset($parts[1])) { offer_box(); echo $parts[1]; }
?>
      </div>
<?php if (!empty($a['faq'])): ?>
      <section class="art-faq">
        <h2>پرسش‌های رایج</h2>
        <div class="faq"><?php foreach ($a['faq'] as $f): ?><details class="glass"><summary><?= h($f['q']) ?></summary><p><?= h($f['a']) ?></p></details><?php endforeach; ?></div>
      </section>
<?php endif; ?>
      <div style="margin-top:36px"><?php offer_box(c('blog.cta_title'), c('blog.cta_text')); ?></div>
    </div>
    <aside class="art-side">
      <div class="side-card glass">
        <span class="pill"><?= t('offer.badge') ?></span>
        <b><?= t('offer.title') ?></b>
        <p><?= t('blog.cta_text') ?></p>
        <a class="btn btn-gold" href="<?= h(url_home('#demo')) ?>"><?= t('offer.cta') ?></a>
        <a class="btn btn-glass" href="<?= h(tel()) ?>" dir="ltr"><?= t('contact.phone_display') ?></a>
      </div>
    </aside>
  </div>
</article>
<?php if ($related): ?>
<section style="padding-top:20px">
  <div class="wrap">
    <div class="sec-head"><div class="kicker">ادامه مطالعه</div><h2>مقاله‌های مرتبط</h2></div>
    <div class="posts">
<?php foreach ($related as $p): ?>
      <a class="post glass" href="<?= h(url_article($p['slug'])) ?>"><span class="post-tag"><?= h($p['tag'] ?? 'راهنما') ?></span><h3><?= h($p['title']) ?></h3><p><?= h($p['excerpt'] ?? $p['description']) ?></p></a>
<?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
</main>
<?php page_end();
