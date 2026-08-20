<?php
require_once __DIR__ . '/includes/coupons.php';
require_once __DIR__ . '/includes/guides.php';

$slug = $_GET['tema'] ?? '';
$guide = guide_by_slug($slug);

if (!$guide) {
    http_response_code(404);
    $guide = [
        'category' => 'Guia',
        'title' => 'Guia não encontrado',
        'summary' => 'O conteúdo solicitado não está disponível.',
        'intro' => 'Volte para a página inicial e escolha um dos guias disponíveis.',
        'sections' => [],
        'tip' => '',
    ];
}

$relatedGuides = array_values(array_filter(all_guides(), fn ($item) => ($item['slug'] ?? '') !== $slug));
$shareTitle = $guide['title'] . ' - Oferto Cupons';
$shareDescription = $guide['summary'];
$shareSlug = $guide['slug'] ?? $slug;
$shareUrl = $shareSlug ? 'https://cupons.oferto.digital/guia.php?tema=' . rawurlencode($shareSlug) : 'https://cupons.oferto.digital/guias';
$shareImage = 'https://cupons.oferto.digital/assets/og-cupons.png';
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= e($shareTitle) ?></title>
    <link rel="icon" href="assets/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" href="assets/favicon.png" />
    <meta name="description" content="<?= e($shareDescription) ?>" />
    <link rel="canonical" href="<?= e($shareUrl) ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:locale" content="pt_BR" />
    <meta property="og:site_name" content="Oferto Cupons" />
    <meta property="og:title" content="<?= e($shareTitle) ?>" />
    <meta property="og:description" content="<?= e($shareDescription) ?>" />
    <meta property="og:url" content="<?= e($shareUrl) ?>" />
    <meta property="og:image" content="<?= e($shareImage) ?>" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:alt" content="Oferto Cupons - cupons válidos por categoria" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= e($shareTitle) ?>" />
    <meta name="twitter:description" content="<?= e($shareDescription) ?>" />
    <meta name="twitter:image" content="<?= e($shareImage) ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Kanit:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css?v=20260820-cards" />
  </head>
  <body>
    <header class="site-header">
      <a class="brand" href="index.php#top" aria-label="Oferto Cupons">
        <img src="https://oferto.digital/wp-content/uploads/2024/08/oferto.png" alt="Oferto" />
        <span>Cupons</span>
      </a>
      <nav class="nav-links" aria-label="Navegação principal">
        <a href="index.php#categorias">Categorias</a>
        <a href="index.php#cupons">Cupons</a>
        <a href="index.php#guias">Guias</a>
      </nav>
      <a class="header-cta" href="index.php#cupons">Ver cupons</a>
    </header>

    <main>
      <section class="guide-hero">
        <p class="eyebrow"><?= e($guide['category']) ?></p>
        <h1><?= e($guide['title']) ?></h1>
        <p><?= e($guide['intro']) ?></p>
      </section>

      <section class="guide-layout">
        <article class="guide-article">
          <?php foreach ($guide['sections'] as $section): ?>
            <h2><?= e($section['title']) ?></h2>
            <p><?= e($section['body']) ?></p>
          <?php endforeach; ?>

          <?php if (!empty($guide['tip'])): ?>
            <div class="guide-tip"><?= e($guide['tip']) ?></div>
          <?php endif; ?>
        </article>

        <aside class="guide-sidebar">
          <section class="rail-block">
            <p class="section-kicker">Próximo passo</p>
            <h2>Encontre um cupom ativo</h2>
            <p>Depois de entender a melhor forma de economizar, veja as ofertas disponíveis por categoria.</p>
            <a class="primary-action" href="index.php#cupons">Ver cupons</a>
          </section>

          <section class="rail-block">
            <p class="section-kicker">Mais guias</p>
            <?php foreach (array_slice($relatedGuides, 0, 3) as $related): ?>
              <a href="guia.php?tema=<?= e($related['slug']) ?>"><?= e($related['title']) ?></a>
            <?php endforeach; ?>
          </section>
        </aside>
      </section>
    </main>

    <footer class="site-footer">
      <strong>Oferto Cupons</strong>
      <span>Compras inteligentes, ofertas imperdíveis.</span>
    </footer>
  </body>
</html>

