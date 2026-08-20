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
$featured = array_slice(array_values(array_filter($coupons, fn ($coupon) => (int) $coupon['featured'] === 1)), 0, 3);
$expiring = array_slice($coupons, 0, 5);
$guides = all_guides();
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Oferto Cupons - Cupons por categoria</title>
    <link rel="icon" href="assets/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" href="assets/favicon.png" />
    <meta name="description" content="Encontre cupons ativos por categoria, veja ofertas que vencem em breve e economize em alimentação, bebidas, compras, games, educação e mais." />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Kanit:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css?v=20260820-guides" />
  </head>
  <body>
    <header class="site-header">
      <a class="brand" href="#top" aria-label="Oferto Cupons">
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
            <a class="text-action" href="admin/">Gerenciar cupons</a>
          </div>
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
              <a class="mini-coupon" href="<?= e($coupon['target_url']) ?>" target="_blank" rel="noopener">
                <img src="<?= e($coupon['banner_url']) ?>" alt="" />
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

      <section class="category-strip" id="categorias" aria-label="Categorias de cupons">
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
              <h2 id="coupon-title">Todos os cupons</h2>
            </div>
            <span id="result-count"><?= count($coupons) ?> encontrados</span>
          </div>
          <div class="coupon-grid" id="coupon-grid">
            <?php foreach ($coupons as $coupon): ?>
              <article class="coupon-card" data-category="<?= e($coupon['category']) ?>" data-search="<?= e(strtolower($coupon['category'] . ' ' . $coupon['store'] . ' ' . $coupon['title'] . ' ' . $coupon['description'] . ' ' . $coupon['code'])) ?>">
                <div class="coupon-media">
                  <img src="<?= e($coupon['banner_url']) ?>" alt="Banner do cupom <?= e($coupon['store']) ?>" />
                  <span class="coupon-badge"><?= e($coupon['category']) ?></span>
                  <span class="validity"><?= e(validity_label($coupon['ends_at'])) ?></span>
                </div>
                <div class="coupon-body">
                  <div class="coupon-meta">
                    <span class="store"><?= e($coupon['store']) ?></span>
                    <span class="coupon-type">Cupom verificado</span>
                  </div>
                  <h3><?= e($coupon['title']) ?></h3>
                  <p><?= e($coupon['description']) ?></p>
                  <div class="coupon-code-box">
                    <span class="code-label">Código</span>
                    <strong class="code-value"><?= e($coupon['code'] ?: 'Oferta direta') ?></strong>
                  </div>
                  <div class="coupon-actions">
                    <button class="copy-button" type="button" data-code="<?= e($coupon['code']) ?>"><?= $coupon['code'] ? 'Copiar código' : 'Ver oferta' ?></button>
                    <a class="use-button" href="<?= e($coupon['target_url']) ?>" target="_blank" rel="noopener">Usar cupom</a>
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
            <p class="section-kicker">SEO que ajuda de verdade</p>
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
    <script src="php-site.js"></script>
  </body>
</html>

