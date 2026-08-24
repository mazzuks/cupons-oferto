<?php
require_once __DIR__ . '/includes/coupons.php';
require_once __DIR__ . '/includes/guides.php';

$host = strtolower($_SERVER['HTTP_HOST'] ?? '');
if (strpos($host, 'crm.') === 0) {
    header('Location: /admin/');
    exit;
}

$coupons = active_coupons();
$categories = array_values(array_unique(array_map(fn ($coupon) => $coupon['category'], $coupons)));
sort($categories);
$categoryCounts = array_count_values(array_map(fn ($coupon) => $coupon['category'], $coupons));
$availableOfferTypes = array_values(array_unique(array_map(fn ($coupon) => $coupon['offer_type'] ?? 'cupom', $coupons)));
$featured = array_values(array_filter($coupons, fn ($coupon) => (int) ($coupon['featured'] ?? 0) === 1));
$spotlight = $featured[0] ?? ($coupons[0] ?? null);
$expiring = array_slice($coupons, 0, 4);
$guides = all_guides();
$shareTitle = 'Oferto Cupons V2 - ofertas por categoria';
$shareDescription = 'Uma nova experiencia para encontrar cupons, sorteios e campanhas com validade clara e categorias faceis de explorar.';
$shareUrl = 'https://cupons.oferto.digital/v2.php';
$shareImage = 'https://cupons.oferto.digital/assets/og-cupons.png';
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= e($shareTitle) ?></title>
    <meta name="robots" content="noindex,follow" />
    <meta name="theme-color" content="#162a4e" />
    <meta name="description" content="<?= e($shareDescription) ?>" />
    <link rel="canonical" href="<?= e($shareUrl) ?>" />
    <link rel="icon" href="assets/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" href="assets/favicon.png" />
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
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Kanit:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css?v=20260824-v2" />
  </head>
  <body class="site-v2">
    <header class="site-header v2-header">
      <a class="brand" href="/" aria-label="Oferto Cupons">
        <img src="https://oferto.digital/wp-content/uploads/2024/08/oferto.png" alt="Oferto" />
        <span>Cupons V2</span>
      </a>
      <nav class="nav-links" aria-label="Navegacao principal">
        <a href="#categorias">Categorias</a>
        <a href="#cupons">Ofertas</a>
        <a href="#guias">Guias</a>
      </nav>
      <a class="header-cta" href="/">Voltar para home atual</a>
    </header>

    <main id="top">
      <section class="v2-hero">
        <div class="v2-hero-copy">
          <p class="eyebrow">Vitrine experimental de cupons</p>
          <h1>Descontos organizados para decidir mais rapido.</h1>
          <p>Explore cupons, sorteios e campanhas por categoria, veja validade antes de clicar e escolha o melhor caminho para economizar sem garimpar oferta vencida.</p>
          <div class="hero-actions">
            <a class="primary-action" href="#cupons">Explorar ofertas</a>
            <a class="text-action" href="#categorias">Ver categorias</a>
          </div>
          <div class="v2-proof-row" aria-label="Resumo de ofertas">
            <span><strong><?= count($coupons) ?></strong> ofertas ativas</span>
            <span><strong><?= count($categories) ?></strong> categorias</span>
            <span><strong><?= count($availableOfferTypes) ?></strong> tipos de campanha</span>
          </div>
        </div>

        <?php if ($spotlight): ?>
          <article class="v2-spotlight-card">
            <span class="v2-card-label">Destaque agora</span>
            <div class="v2-spotlight-media">
              <img src="<?= e(coupon_banner_src($spotlight)) ?>" alt="Banner da campanha <?= e($spotlight['store']) ?>" />
              <span><?= e(validity_label($spotlight['ends_at'])) ?></span>
            </div>
            <div class="v2-spotlight-body">
              <p><?= e($spotlight['category']) ?></p>
              <h2><?= e($spotlight['title']) ?></h2>
              <small><?= e($spotlight['description']) ?></small>
              <a class="use-button" href="<?= e(coupon_go_url($spotlight, 'v2_spotlight')) ?>" target="_blank" rel="noopener"><?= e(coupon_cta_label($spotlight)) ?></a>
            </div>
          </article>
        <?php endif; ?>
      </section>

      <section class="v2-category-board" id="categorias" aria-label="Categorias em destaque">
        <div class="section-heading">
          <div>
            <p class="section-kicker">Categorias</p>
            <h2>Comece pelo tipo de economia que procura.</h2>
          </div>
          <a class="text-action" href="#cupons">Ver todas</a>
        </div>
        <div class="v2-category-grid">
          <button class="v2-category-tile is-active" type="button" data-category="Todos">
            <strong>Todas</strong>
            <span><?= count($coupons) ?> ofertas ativas</span>
          </button>
          <?php foreach (array_slice($categories, 0, 7) as $category): ?>
            <button class="v2-category-tile" type="button" data-category="<?= e($category) ?>">
              <strong><?= e($category) ?></strong>
              <span><?= (int) ($categoryCounts[$category] ?? 0) ?> ofertas</span>
            </button>
          <?php endforeach; ?>
        </div>
      </section>

      <aside class="inventory-band" aria-label="Publicidade">
        <div class="inventory-slot inventory-slot-wide" data-inventory-slot="v2_topo_responsivo">
          <span>Publicidade</span>
        </div>
      </aside>

      <section class="v2-offer-shell" id="cupons">
        <aside class="v2-filter-panel">
          <p class="section-kicker">Filtro rapido</p>
          <h2>Refine a lista</h2>
          <label class="search-box">
            <span>Buscar</span>
            <input id="coupon-search" type="search" placeholder="Pizza, seguros, games..." />
          </label>
          <div class="v2-chip-stack" aria-label="Tipos de campanha">
            <button class="category-chip is-active" type="button" data-offer-type="Todos">Todas</button>
            <?php foreach (offer_types() as $type => $label): ?>
              <?php if (in_array($type, $availableOfferTypes, true)): ?>
                <button class="category-chip" type="button" data-offer-type="<?= e($type) ?>"><?= e($label) ?></button>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
          <div class="v2-expiring-box">
            <strong>Vencem em breve</strong>
            <?php foreach ($expiring as $coupon): ?>
              <a href="<?= e(coupon_go_url($coupon, 'v2_expiring')) ?>" target="_blank" rel="noopener">
                <span><?= e($coupon['store']) ?></span>
                <small><?= e(validity_label($coupon['ends_at'])) ?></small>
              </a>
            <?php endforeach; ?>
          </div>
        </aside>

        <section class="v2-coupon-list" aria-live="polite">
          <div class="section-heading">
            <div>
              <p class="section-kicker">Ofertas ativas</p>
              <h2 id="coupon-title">Todas as ofertas</h2>
            </div>
            <span id="result-count"><?= count($coupons) ?> encontrados</span>
          </div>

          <div class="v2-coupon-grid" id="coupon-grid">
            <?php foreach ($coupons as $coupon): ?>
              <article class="coupon-card v2-coupon-card" data-category="<?= e($coupon['category']) ?>" data-offer-type="<?= e($coupon['offer_type'] ?? 'cupom') ?>" data-search="<?= e(strtolower($coupon['category'] . ' ' . $coupon['store'] . ' ' . $coupon['title'] . ' ' . $coupon['description'] . ' ' . $coupon['code'] . ' ' . ($coupon['tags'] ?? '') . ' ' . offer_type_label($coupon['offer_type'] ?? 'cupom') . ' ' . redemption_type_label($coupon['redemption_type'] ?? 'texto'))) ?>">
                <div class="v2-coupon-image">
                  <img src="<?= e(coupon_banner_src($coupon)) ?>" alt="Banner da campanha <?= e($coupon['store']) ?>" />
                  <span><?= e(validity_label($coupon['ends_at'])) ?></span>
                </div>
                <div class="v2-coupon-content">
                  <div class="coupon-meta">
                    <span class="store"><?= e($coupon['store']) ?></span>
                    <span class="coupon-type"><?= e(offer_type_label($coupon['offer_type'] ?? 'cupom')) ?></span>
                  </div>
                  <h3><?= e($coupon['title']) ?></h3>
                  <p><?= e($coupon['description']) ?></p>
                  <div class="v2-coupon-details">
                    <span><?= e($coupon['category']) ?></span>
                    <strong><?= e(coupon_mechanic_value($coupon)) ?></strong>
                  </div>
                  <div class="coupon-actions">
                    <?php if (coupon_uses_text_redemption($coupon)): ?>
                      <button class="copy-button" type="button" data-code="<?= e($coupon['code']) ?>">Copiar codigo</button>
                    <?php else: ?>
                      <a class="copy-button" href="<?= e(coupon_go_url($coupon, 'v2_details')) ?>" target="_blank" rel="noopener">Ver detalhes</a>
                    <?php endif; ?>
                    <a class="use-button" href="<?= e(coupon_go_url($coupon, 'v2_cta')) ?>" target="_blank" rel="noopener"><?= e(coupon_cta_label($coupon)) ?></a>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
          <p class="empty-state" id="empty-state" hidden>Nenhuma campanha ativa encontrada para esse filtro.</p>
        </section>
      </section>

      <section class="v2-guides" id="guias">
        <div class="section-heading">
          <div>
            <p class="section-kicker">Conteudo util</p>
            <h2>Guias para escolher melhor antes de clicar.</h2>
          </div>
        </div>
        <div class="guide-grid">
          <?php foreach ($guides as $guide): ?>
            <a class="guide-card" href="guia.php?tema=<?= e($guide['slug']) ?>">
              <span><?= e($guide['category']) ?></span>
              <h3><?= e($guide['title']) ?></h3>
              <p><?= e($guide['summary']) ?></p>
              <strong class="guide-link">Ler guia</strong>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    </main>

    <footer class="site-footer">
      <strong>Oferto Cupons V2</strong>
      <span>Ambiente de teste, sem alterar a home atual.</span>
    </footer>
    <script src="php-site.js?v=20260824-v2"></script>
    <script src="pwa.js"></script>
  </body>
</html>
