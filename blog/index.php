<?php
require_once __DIR__ . '/../includes/coupons.php';
require_once __DIR__ . '/../includes/guides.php';

$guides = all_guides();
$categories = array_values(array_unique(array_map(fn ($guide) => $guide['category'] ?? 'Dicas', $guides)));
sort($categories);
$shareTitle = 'Blog Oferto Cupons - dicas para economizar';
$shareDescription = 'Conteúdos simples para usar cupons, encontrar promoções e comprar melhor.';
$shareUrl = 'https://cupons.oferto.digital/blog/';
$shareImage = 'https://cupons.oferto.digital/assets/og-cupons.png';

function blog_anchor(string $value): string
{
    return preg_replace('/[^a-z0-9]+/', '-', normalize_search_text($value)) ?: 'categoria';
}
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= e($shareTitle) ?></title>
    <meta name="theme-color" content="#162a4e" />
    <meta name="lomadee" content="2324685" />
    <meta name="description" content="<?= e($shareDescription) ?>" />
    <link rel="canonical" href="<?= e($shareUrl) ?>" />
    <link rel="icon" href="/assets/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" href="/assets/favicon.png" />
    <link rel="apple-touch-icon" href="/assets/icon-180.png" />
    <link rel="manifest" href="/manifest.webmanifest" />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="pt_BR" />
    <meta property="og:site_name" content="Oferto Cupons" />
    <meta property="og:title" content="<?= e($shareTitle) ?>" />
    <meta property="og:description" content="<?= e($shareDescription) ?>" />
    <meta property="og:url" content="<?= e($shareUrl) ?>" />
    <meta property="og:image" content="<?= e($shareImage) ?>" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= e($shareTitle) ?>" />
    <meta name="twitter:description" content="<?= e($shareDescription) ?>" />
    <meta name="twitter:image" content="<?= e($shareImage) ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Kanit:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/styles.css?v=20260827-blog" />
  </head>
  <body class="site-v2 site-v2-compact">
    <header class="site-header v2-compact-header">
      <a class="brand" href="/" aria-label="Oferto Cupons">
        <img src="https://oferto.digital/wp-content/uploads/2024/08/oferto.png" alt="Oferto" />
        <span>Cupons</span>
      </a>
      <nav class="nav-links" aria-label="Navegacao principal">
        <a href="/">Cupons</a>
        <a href="/#top-cupons">Destaques</a>
        <a href="/blog/">Dicas de economia</a>
      </nav>
      <a class="header-cta" href="/admin/">Admin</a>
    </header>

    <main id="top">
      <section class="blog-hero">
        <p class="eyebrow">Dicas de economia</p>
        <h1>Leia antes de comprar e aproveite melhor cada oferta.</h1>
        <p>Conteúdos rápidos para comparar cupons, entender promoções, evitar compra por impulso e encontrar oportunidades por marca.</p>
      </section>

      <section class="blog-index">
        <div class="blog-filter-strip" aria-label="Categorias do blog">
          <a href="#todos">Todas</a>
          <?php foreach ($categories as $category): ?>
            <a href="#<?= e(blog_anchor($category)) ?>"><?= e($category) ?></a>
          <?php endforeach; ?>
        </div>

        <div class="section-heading" id="todos">
          <div>
            <p class="section-kicker">Todos os conteúdos</p>
            <h2><?= count($guides) ?> dicas publicadas</h2>
          </div>
          <a class="text-action" href="/">Ver cupons</a>
        </div>

        <?php foreach ($categories as $category): ?>
          <?php $categoryGuides = array_values(array_filter($guides, fn ($guide) => ($guide['category'] ?? '') === $category)); ?>
          <section class="blog-category" id="<?= e(blog_anchor($category)) ?>">
            <div class="blog-category-title">
              <h2><?= e($category) ?></h2>
              <span><?= count($categoryGuides) ?> <?= count($categoryGuides) === 1 ? 'conteúdo' : 'conteúdos' ?></span>
            </div>
            <div class="guide-grid blog-guide-grid">
              <?php foreach ($categoryGuides as $guide): ?>
                <a class="guide-card" href="/guia.php?tema=<?= e($guide['slug']) ?>">
                  <span><?= e($guide['category']) ?></span>
                  <h3><?= e($guide['title']) ?></h3>
                  <p><?= e($guide['summary']) ?></p>
                  <strong class="guide-link">Ler dica</strong>
                </a>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endforeach; ?>
      </section>
    </main>

    <footer class="site-footer">
      <strong>Oferto Cupons</strong>
      <span>Cupons, promoções, sorteios e dicas para economizar hoje.</span>
    </footer>
    <script src="/pwa.js?v=20260825-cache-fix"></script>
  </body>
</html>
