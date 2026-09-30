<?php
require_once dirname(__DIR__) . '/lib.php';
require_once dirname(__DIR__) . '/partials/layout.php';

$posts = articles();
$crumbs = [['خانه', url_home()], ['مقالات', url_blog()]];
$ld = [
    org_ld(),
    breadcrumb_ld([['خانه', abs_url()], ['مقالات', abs_url('blog/')]]),
    ['@type' => 'CollectionPage', 'name' => c('blog.page_title'), 'description' => c('blog.page_description'), 'url' => abs_url('blog/'), 'inLanguage' => 'fa-IR',
     'mainEntity' => ['@type' => 'ItemList', 'itemListElement' => array_map(fn($p, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'url' => canonical_article($p['slug']), 'name' => $p['title']], $posts, array_keys($posts))]],
];
page_start(['title' => c('blog.page_title'), 'description' => c('blog.page_description'), 'canonical' => abs_url('blog/'), 'ld' => $ld]);
?>
<main id="top">
<section class="blog-head">
  <div class="wrap">
    <?php breadcrumbs($crumbs); ?>
    <div class="sec-head">
      <div class="kicker"><?= t('blog.kicker') ?></div>
      <h1><?= t('blog.title') ?></h1>
      <p><?= t('blog.intro') ?></p>
    </div>
    <div class="posts">
<?php foreach ($posts as $p): ?>
      <a class="post glass" href="<?= h(url_article($p['slug'])) ?>">
        <span class="post-tag"><?= h($p['tag'] ?? 'راهنما') ?></span>
        <h2><?= h($p['title']) ?></h2>
        <p><?= h($p['excerpt'] ?? $p['description']) ?></p>
        <span class="post-meta"><?= jalali_label($p['date']) ?> · <?= fa_digits(reading_minutes($p['body'])) ?> دقیقه مطالعه</span>
      </a>
<?php endforeach; ?>
    </div>
    <div style="margin-top:40px"><?php offer_box(c('blog.cta_title'), c('blog.cta_text')); ?></div>
  </div>
</section>
</main>
<?php page_end();
