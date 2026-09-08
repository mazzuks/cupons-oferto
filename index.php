<?php
require_once __DIR__ . '/includes/coupons.php';
require_once __DIR__ . '/includes/guides.php';

$host = strtolower($_SERVER['HTTP_HOST'] ?? '');
if (strpos($host, 'crm.') === 0) {
    header('Location: /admin/');
    exit;
}

$coupons = active_coupons();
$nicheGroups = coupon_niche_groups($coupons);
$availableOfferTypes = array_values(array_unique(array_map(fn ($coupon) => $coupon['offer_type'] ?? 'cupom', $coupons)));
$featured = array_slice(array_values(array_filter($coupons, fn ($coupon) => (int) ($coupon['featured'] ?? 0) === 1)), 0, 6);
$topCoupons = $featured ?: array_slice($coupons, 0, 6);
$expiring = expiring_soon_coupons($coupons);
$guides = all_guides();
$homeGuides = array_slice($guides, 0, 4);
$defaultCategory = 'Todos';
$initialCoupons = $coupons;
$initialTitle = 'Todas as ofertas';
$shareTitle = 'Oferto Cupons - cupons, promocoes e sorteios';
$shareDescription = 'Ache cupons, promocoes e sorteios ativos por loja ou categoria e economize antes de comprar.';
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
    <meta name="lomadee" content="2324685" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-title" content="Oferto Cupons" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
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
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:alt" content="Oferto Cupons - cupons, promocoes e sorteios" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= e($shareTitle) ?>" />
    <meta name="twitter:description" content="<?= e($shareDescription) ?>" />
    <meta name="twitter:image" content="<?= e($shareImage) ?>" />
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1725208559538025" crossorigin="anonymous"></script>
    <?php render_oferto_brand_schema($shareUrl, 'Oferto Cupons'); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Kanit:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css?v=<?= asset_version('styles.css') ?>" />
  </head>
  <body class="site-v2 site-v2-compact" style="--seo-bg-image: url('/assets/hero-cupons.webp');">
    <header class="site-header v2-compact-header">
      <a class="brand" href="/" aria-label="Oferto Cupons">
        <img src="https://oferto.digital/wp-content/uploads/2024/08/oferto.png" alt="Oferto" />
        <span>Cupons</span>
      </a>
      <a class="header-cta" href="#cupons">Ver ofertas</a>
    </header>

    <main id="top">
      <section class="v2-compact-hero">
        <div>
          <p class="eyebrow">Cupons, promocoes e sorteios para hoje</p>
          <h1>Cupons Inteligentes, <span class="hero-highlight">compras imperdíveis.</span></h1>
          <p>Oferto Cupons junta oportunidades em um só lugar para voce gastar menos.</p>
        </div>
      </section>

      <aside class="inventory-band v2-ad-band" aria-label="Publicidade">
        <?php render_ad_slot('v2_topo_responsivo'); ?>
      </aside>

      <?php if ($topCoupons): ?>
        <section class="v2-layout" id="top-cupons">
          <aside class="v2-side-panel">
            <section class="v2-summary-card">
              <p class="section-kicker">Hoje no Oferto</p>
              <div class="v2-stat-list">
                <div><span class="stat-icon" aria-hidden="true">&#127991;&#65039;</span><strong><?= count($coupons) ?></strong><span class="stat-label">ofertas ativas</span></div>
                <div><span class="stat-icon" aria-hidden="true">&#128193;</span><strong><?= count($nicheGroups) ?></strong><span class="stat-label">categorias</span></div>
                <div><span class="stat-icon" aria-hidden="true">&#8987;</span><strong><?= count($expiring) ?></strong><span class="stat-label">vencendo em breve</span></div>
              </div>
            </section>
          </aside>

          <section class="v2-results">
            <div class="section-heading">
              <div>
                <p class="section-kicker">Destaques</p>
                <h2>Em alta &#128293;</h2>
                <p class="v2-section-subtitle">Cupons e promocoes para olhar agora.</p>
              </div>
              <a class="text-action" href="#cupons">Ver ofertas</a>
            </div>
            <div class="v2-list">
              <?php foreach ($topCoupons as $coupon): ?>
                <?php
                  $couponDisplayTitle = coupon_display_title($coupon, 82);
                  $couponNiche = coupon_primary_niche($coupon);
                ?>
                <article class="coupon-card v2-list-card">
                  <div class="v2-list-logo">
                    <?= coupon_brand_image_markup($coupon) ?>
                  </div>
                  <div class="v2-list-content">
                    <div class="coupon-meta">
                      <span class="store"><?= e($coupon['store']) ?></span>
                    </div>
                    <h3><?= e($couponDisplayTitle) ?></h3>
                    <?php if (!coupon_has_generic_description($coupon)): ?>
                      <p class="offer-condition"><?= e($coupon['description']) ?></p>
                    <?php endif; ?>
                    <?php if ($customRule = coupon_custom_rule($coupon)): ?>
                      <p class="offer-rule"><?= e($customRule) ?></p>
                    <?php endif; ?>
                    <div class="v2-list-tags">
                      <a href="/categorias/<?= e(coupon_niche_slug($coupon)) ?>"><?= e($couponNiche) ?></a>
                      <span><?= e(validity_label($coupon['ends_at'])) ?></span>
                      <?php if (coupon_shows_public_code($coupon)): ?>
                        <span><?= e(coupon_mechanic_label($coupon)) ?>: <?= e(coupon_mechanic_value($coupon)) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="v2-list-actions">
                    <?php if (coupon_shows_public_code($coupon)): ?>
                      <button class="copy-button" type="button" data-code="<?= e($coupon['code']) ?>">Copiar codigo</button>
                    <?php endif; ?>
                    <?php if (coupon_shows_rescue_button($coupon)): ?>
                      <a class="use-button" href="<?= e(coupon_go_url($coupon, 'destaque_cta')) ?>" target="_blank" rel="noopener"><?= e(coupon_cta_label($coupon)) ?></a>
                    <?php endif; ?>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </section>
        </section>
      <?php endif; ?>

      <?php if ($nicheGroups): ?>
        <section class="v2-section category-index" id="categorias">
          <div class="section-heading">
            <div>
              <h2>Categorias</h2>
            </div>
            <a class="text-action" href="/categorias/">Ver todas</a>
          </div>
          <div class="niche-grid">
            <?php foreach (array_slice($nicheGroups, 0, 8) as $group): ?>
              <?php $groupPhoto = category_hero_image($group['slug']); ?>
              <a
                class="niche-card<?= $groupPhoto ? ' niche-card-photo' : '' ?>"
                href="/categorias/<?= e($group['slug']) ?>"
                <?php if ($groupPhoto): ?>style="--card-photo: url('/<?= e($groupPhoto) ?>')"<?php endif; ?>
              >
                <span><?= (int) $group['count'] ?> <?= (int) $group['count'] === 1 ? 'oferta' : 'ofertas' ?></span>
                <h2><?= e($group['name']) ?></h2>
                <p><?= e(implode(', ', array_slice($group['stores'], 0, 4))) ?></p>
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <aside class="inventory-band v2-ad-band v2-ad-band-between" aria-label="Publicidade">
        <?php render_ad_slot('v2_entre_destaques_e_lista'); ?>
      </aside>

      <section class="v2-layout" id="cupons">
        <aside class="v2-side-panel">
          <section>
            <p class="section-kicker">Vencendo</p>
            <h2>Use antes que acabe</h2>
            <div class="v2-expiring-list">
              <?php if ($expiring): ?>
                <?php foreach ($expiring as $coupon): ?>
                  <a href="<?= e(coupon_offer_url($coupon, 'vencendo')) ?>">
                    <strong><?= e($coupon['store']) ?></strong>
                    <span><?= e(validity_label($coupon['ends_at'])) ?></span>
                  </a>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="v2-muted-note">Nenhuma oferta termina nos proximos 3 dias.</p>
              <?php endif; ?>
            </div>
          </section>
          <section class="v2-side-note">
            <p class="section-kicker">Antes de clicar</p>
            <h2>Confira a regra da oferta</h2>
            <p>Veja validade, categoria e forma de resgate antes de acessar o site parceiro. Assim fica mais facil escolher o cupom certo e evitar oferta vencida.</p>
          </section>
          <aside aria-label="Publicidade">
            <?php render_ad_slot('v2_lateral_300x250', 'inventory-slot-rectangle v2-side-ad'); ?>
          </aside>
        </aside>

        <section class="v2-results" aria-live="polite">
          <div class="section-heading">
            <div>
              <h2 id="coupon-title"><?= e($initialTitle) ?></h2>
            </div>
            <span id="result-count"><?= count($initialCoupons) ?> <?= count($initialCoupons) === 1 ? 'encontrado' : 'encontrados' ?></span>
          </div>

          <div class="v2-local-filters" aria-label="Filtros de ofertas">
            <section class="v2-category-strip" aria-label="Categorias">
              <button class="category-chip <?= $defaultCategory === 'Todos' ? 'is-active' : '' ?>" type="button" data-category="Todos" data-label="Todas as ofertas">Todos</button>
              <?php foreach ($nicheGroups as $filterCategory): ?>
                <button class="category-chip" type="button" data-category="<?= e($filterCategory['slug']) ?>" data-label="<?= e($filterCategory['name']) ?>"><?= e($filterCategory['name']) ?> <small><?= (int) ($filterCategory['count'] ?? 0) ?></small></button>
              <?php endforeach; ?>
            </section>

            <section class="v2-type-strip" aria-label="Tipos de oferta">
              <button class="category-chip is-active" type="button" data-offer-type="Todos" data-label="Todas as ofertas">Todas as ofertas</button>
              <?php foreach (offer_types() as $type => $label): ?>
                <?php if (in_array($type, $availableOfferTypes, true)): ?>
                  <button class="category-chip" type="button" data-offer-type="<?= e($type) ?>" data-label="<?= e($label) ?>"><?= e($label) ?></button>
                <?php endif; ?>
              <?php endforeach; ?>
            </section>
          </div>

          <div class="v2-list" id="coupon-grid">
            <?php foreach ($coupons as $coupon): ?>
              <?php
                $couponDisplayTitle = coupon_display_title($coupon, 82);
                $couponNiche = coupon_primary_niche($coupon);
              ?>
              <article class="coupon-card v2-list-card" data-category="<?= e(coupon_niche_slug($coupon)) ?>" data-offer-type="<?= e($coupon['offer_type'] ?? 'cupom') ?>" data-search="<?= e(normalize_search_text($couponNiche . ' ' . $coupon['category'] . ' ' . $coupon['store'] . ' ' . $coupon['title'] . ' ' . $coupon['description'] . ' ' . $coupon['code'] . ' ' . ($coupon['tags'] ?? '') . ' ' . ($coupon['nicho_principal'] ?? '') . ' ' . ($coupon['tags_produto'] ?? '') . ' ' . ($coupon['requirements'] ?? '') . ' ' . ($coupon['rules'] ?? '') . ' ' . ($coupon['partner_network'] ?? '') . ' ' . offer_type_label($coupon['offer_type'] ?? 'cupom') . ' ' . redemption_type_label($coupon['redemption_type'] ?? 'texto'))) ?>">
                <div class="v2-list-logo">
                  <?= coupon_brand_image_markup($coupon) ?>
                </div>
                <div class="v2-list-content">
                  <div class="coupon-meta">
                    <span class="store"><?= e($coupon['store']) ?></span>
                  </div>
                  <h3><?= e($couponDisplayTitle) ?></h3>
                  <?php if (!coupon_has_generic_description($coupon)): ?>
                    <p class="offer-condition"><?= e($coupon['description']) ?></p>
                  <?php endif; ?>
                  <?php if ($customRule = coupon_custom_rule($coupon)): ?>
                    <p class="offer-rule"><?= e($customRule) ?></p>
                  <?php endif; ?>
                  <div class="v2-list-tags">
                    <a href="/categorias/<?= e(coupon_niche_slug($coupon)) ?>"><?= e($couponNiche) ?></a>
                    <span><?= e(validity_label($coupon['ends_at'])) ?></span>
                    <?php if (coupon_shows_public_code($coupon)): ?>
                      <span><?= e(coupon_mechanic_label($coupon)) ?>: <?= e(coupon_mechanic_value($coupon)) ?></span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="v2-list-actions">
                  <?php if (coupon_shows_public_code($coupon)): ?>
                    <button class="copy-button" type="button" data-code="<?= e($coupon['code']) ?>">Copiar codigo</button>
                  <?php endif; ?>
                  <?php if (coupon_shows_rescue_button($coupon)): ?>
                    <a class="use-button" href="<?= e(coupon_go_url($coupon, 'v2_cta')) ?>" target="_blank" rel="noopener"><?= e(coupon_cta_label($coupon)) ?></a>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
          <p class="empty-state" id="empty-state" hidden>Nenhuma oferta encontrada para esse filtro.</p>
          <nav class="v2-pagination" id="coupon-pagination" aria-label="Paginacao de ofertas" hidden></nav>
        </section>
      </section>

      <aside class="inventory-band v2-ad-band" aria-label="Publicidade">
        <?php render_ad_slot('v2_antes_dicas'); ?>
      </aside>

      <section class="v2-section v2-guides-compact" id="dicas">
        <div class="section-heading">
          <div>
            <p class="section-kicker">Dicas de economia</p>
            <h2>Blog Oferto</h2>
          </div>
          <a class="primary-action v2-more-content" href="/blog/">Ver mais dicas</a>
        </div>
        <div class="guide-grid">
          <?php foreach ($homeGuides as $guide): ?>
            <a class="guide-card" href="guia.php?tema=<?= e($guide['slug']) ?>">
              <span><?= e($guide['category']) ?></span>
              <h3><?= e($guide['title']) ?></h3>
              <p><?= e($guide['summary']) ?></p>
              <strong class="guide-link">Ver dica</strong>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    </main>

    <footer class="site-footer">
      <strong>Oferto Cupons</strong>
      <span>Cupons, promocoes e sorteios para economizar hoje.</span>
      <p class="footer-disclaimer">As ofertas tem tempo limitado. Confira a validade, o codigo e as regras no site parceiro antes de finalizar sua compra.</p>
    </footer>
    <script src="php-site.js?v=<?= asset_version('php-site.js') ?>"></script>
    <script src="pwa.js?v=<?= asset_version('pwa.js') ?>"></script>
  </body>
</html>
