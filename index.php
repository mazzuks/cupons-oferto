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
$availableOfferTypes = array_values(array_unique(array_map(fn ($coupon) => $coupon['offer_type'] ?? 'cupom', $coupons)));
$featured = array_slice(array_values(array_filter($coupons, fn ($coupon) => (int) $coupon['featured'] === 1)), 0, 3);
$expiring = array_slice($coupons, 0, 5);
$guides = all_guides();
$shareTitle = 'Oferto Cupons - Cupons válidos para economizar hoje';
$shareDescription = 'Encontre cupons ativos por categoria, copie códigos válidos e aproveite ofertas em alimentação, compras, games, educação e serviços.';
$shareUrl = 'https://cupons.oferto.digital/';
$shareImage = 'https://cupons.oferto.digital/assets/og-cupons.png';
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= e($shareTitle) ?></title>
    <meta name="theme-color" content="#162a4e" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-title" content="Oferto Cupons" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <link rel="icon" href="assets/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" href="assets/favicon.png" />
    <link rel="apple-touch-icon" href="/assets/icon-180.png" />
    <link rel="manifest" href="/manifest.webmanifest" />
    <meta name="description" content="<?= e($shareDescription) ?>" />
    <link rel="canonical" href="<?= e($shareUrl) ?>" />
    <meta property="og:type" content="website" />
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
    <link rel="stylesheet" href="styles.css?v=20260824-crm" />
  </head>
  <body>
    <header class="site-header">
      <a class="brand" href="/" aria-label="Oferto Cupons">
        <img src="https://oferto.digital/wp-content/uploads/2024/08/oferto.png" alt="Oferto" />
        <span>Cupons</span>
      </a>
      <nav class="nav-links" aria-label="Navegação principal">
        <a href="#categorias">Categorias</a>
        <a href="#vencendo">Vencendo hoje</a>
        <a href="#guias">Guias de economia</a>
      </nav>
      <a class="header-cta" href="admin/">Admin</a>
    </header>

    <main id="top">
      <section class="hero">
        <div class="hero-copy">
          <p class="eyebrow">Cupons válidos para economizar hoje</p>
          <h1>Encontre descontos antes de finalizar sua compra.</h1>
          <p>Escolha uma categoria, veja ofertas ativas e copie o cupom ideal para pagar menos em alimentação, compras, games, educação e serviços.</p>
          <div class="hero-actions">
            <a class="primary-action" href="#cupons">Ver cupons</a>
            <button class="install-action" type="button" data-install-app hidden>Instalar app</button>
            <a class="text-action" href="admin/">Gerenciar cupons</a>
          </div>
          <p class="install-help" data-install-help hidden></p>
        </div>
        <div class="hero-panel" aria-label="Resumo dos cupons">
          <div class="hero-panel-top">
            <span class="live-dot"></span>
            <span>Atualizado pelo painel administrativo</span>
          </div>
          <div class="hero-stat">
            <strong><?= count($coupons) ?></strong>
            <span>cupons ativos</span>
          </div>
          <div class="mini-coupons">
            <?php foreach ($featured as $coupon): ?>
              <a class="mini-coupon" href="<?= e(coupon_go_url($coupon, 'mini')) ?>" target="_blank" rel="noopener">
                <img src="<?= e(coupon_banner_src($coupon)) ?>" alt="" />
                <strong><?= e($coupon['store']) ?></strong>
                <span><?= e(validity_label($coupon['ends_at'])) ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="search-band" aria-label="Buscar cupons">
        <div>
          <p class="section-kicker">Busca rápida</p>
          <h2>O desconto certo, na hora certa.</h2>
        </div>
        <label class="search-box">
          <span>Buscar</span>
          <input id="coupon-search" type="search" placeholder="Pizza, mercado, games..." />
        </label>
      </section>

      <section class="category-strip offer-type-strip" id="categorias" aria-label="Tipos de oferta">
        <button class="category-chip is-active" type="button" data-offer-type="Todos">Todas as ofertas</button>
        <?php foreach (offer_types() as $type => $label): ?>
          <?php if (in_array($type, $availableOfferTypes, true)): ?>
            <button class="category-chip" type="button" data-offer-type="<?= e($type) ?>"><?= e($label) ?></button>
          <?php endif; ?>
        <?php endforeach; ?>
      </section>

      <section class="category-strip category-filter-strip" aria-label="Categorias de cupons">
        <button class="category-chip is-active" type="button" data-category="Todos">Todos</button>
        <?php foreach ($categories as $category): ?>
          <button class="category-chip" type="button" data-category="<?= e($category) ?>"><?= e($category) ?></button>
        <?php endforeach; ?>
      </section>

      <aside class="inventory-band" aria-label="Publicidade">
        <div class="inventory-slot inventory-slot-wide" data-inventory-slot="cupons_topo_responsivo">
          <span>Publicidade</span>
        </div>
      </aside>

      <section class="content-grid" id="cupons">
        <aside class="side-rail">
          <section class="rail-block" id="vencendo">
            <p class="section-kicker">Urgência boa</p>
            <h2>Vencem em breve</h2>
            <div class="expiring-list">
              <?php foreach ($expiring as $coupon): ?>
                <div class="expiring-item">
                  <strong><?= e($coupon['store']) ?></strong>
                  <span><?= e(validity_label($coupon['ends_at'])) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </section>
          <section class="rail-block" id="alertas">
            <p class="section-kicker">Fidelização</p>
            <h2>Receba só o que combina com você</h2>
            <p>Escolha suas categorias favoritas e receba uma seleção enxuta de cupons novos.</p>
            <form class="alert-form">
              <input type="email" placeholder="seu@email.com" aria-label="E-mail" />
              <button type="button">Quero receber</button>
            </form>
          </section>
          <aside class="inventory-slot inventory-slot-rectangle" aria-label="Publicidade" data-inventory-slot="cupons_lateral_300x250">
            <span>Publicidade</span>
          </aside>
        </aside>

        <section class="coupon-section" aria-live="polite">
          <div class="section-heading">
            <div>
              <p class="section-kicker">Selecionados para hoje</p>
              <h2 id="coupon-title">Todas as ofertas</h2>
            </div>
            <span id="result-count"><?= count($coupons) ?> encontrados</span>
          </div>
          <div class="coupon-grid" id="coupon-grid">
            <?php foreach ($coupons as $coupon): ?>
              <article class="coupon-card" data-category="<?= e($coupon['category']) ?>" data-offer-type="<?= e($coupon['offer_type'] ?? 'cupom') ?>" data-search="<?= e(strtolower($coupon['category'] . ' ' . $coupon['store'] . ' ' . $coupon['title'] . ' ' . $coupon['description'] . ' ' . $coupon['code'] . ' ' . ($coupon['tags'] ?? '') . ' ' . offer_type_label($coupon['offer_type'] ?? 'cupom') . ' ' . redemption_type_label($coupon['redemption_type'] ?? 'texto'))) ?>">
                <div class="coupon-media">
                  <img src="<?= e(coupon_banner_src($coupon)) ?>" alt="Banner do cupom <?= e($coupon['store']) ?>" />
                  <span class="coupon-badge"><?= e($coupon['category']) ?></span>
                  <span class="validity"><?= e(validity_label($coupon['ends_at'])) ?></span>
                </div>
                <div class="coupon-body">
                  <div class="coupon-meta">
                    <span class="store"><?= e($coupon['store']) ?></span>
                    <span class="coupon-type"><?= e(offer_type_label($coupon['offer_type'] ?? 'cupom')) ?> verificado</span>
                  </div>
                  <h3><?= e($coupon['title']) ?></h3>
                  <p><?= e($coupon['description']) ?></p>
                  <?php if (coupon_shows_public_code($coupon)): ?>
                    <div class="coupon-code-box">
                      <span class="code-label"><?= e(coupon_mechanic_label($coupon)) ?></span>
                      <strong class="code-value"><?= e(coupon_mechanic_value($coupon)) ?></strong>
                    </div>
                  <?php endif; ?>
                  <div class="coupon-actions coupon-actions-single">
                    <?php if (coupon_shows_public_code($coupon)): ?>
                      <button class="copy-button" type="button" data-code="<?= e($coupon['code']) ?>">Copiar código</button>
                    <?php else: ?>
                      <a class="use-button" href="<?= e(coupon_go_url($coupon, 'cta')) ?>" target="_blank" rel="noopener"><?= e(coupon_cta_label($coupon)) ?></a>
                    <?php endif; ?>
                  </div>
                  <small class="coupon-note"><?= e($coupon['rules']) ?></small>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
          <p class="empty-state" id="empty-state" hidden>Nenhum cupom ativo encontrado para esse filtro.</p>
        </section>
      </section>

      <aside class="inventory-band inventory-band-before-guides" aria-label="Publicidade">
        <div class="inventory-slot inventory-slot-wide" data-inventory-slot="cupons_entre_lista_e_guias">
          <span>Publicidade</span>
        </div>
      </aside>

      <section class="guides-section" id="guias">
        <div class="section-heading">
          <div>
            <h2>Guias para economizar sem complicar</h2>
          </div>
          <a class="text-action" href="#cupons">Explorar cupons</a>
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
      <strong>Oferto Cupons</strong>
      <span>Compras inteligentes, ofertas imperdíveis.</span>
    </footer>
    <script src="php-site.js?v=20260825-copy-fix"></script>
    <script src="pwa.js?v=20260825-cache-fix"></script>
  </body>
</html>


