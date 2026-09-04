<?php
require_once __DIR__ . '/includes/coupons.php';

$coupons = active_coupons();
$groups = coupon_niche_groups($coupons);
$slug = trim((string) ($_GET['slug'] ?? ''));
$selectedGroup = $slug !== '' ? coupon_niche_group_by_slug($slug, $coupons) : null;

if ($slug !== '' && !$selectedGroup) {
    http_response_code(404);
}

$isIndex = $slug === '';
$pageName = $selectedGroup['name'] ?? 'Categorias de cupons';
$pageCoupons = $selectedGroup['coupons'] ?? [];
$shareTitle = $isIndex
    ? 'Categorias de cupons - Oferto Cupons'
    : 'Cupons de ' . $pageName . ' - Oferto Cupons';
$shareDescription = $isIndex
    ? 'Navegue por nichos de ofertas e encontre cupons ativos por tipo de compra.'
    : 'Veja ofertas, cupons e promoções ativas em ' . $pageName . ' antes de comprar.';
$shareUrl = $isIndex
    ? 'https://cupons.oferto.digital/categorias/'
    : 'https://cupons.oferto.digital/categorias/' . rawurlencode((string) ($selectedGroup['slug'] ?? $slug));
$shareImage = 'https://cupons.oferto.digital/assets/og-cupons.png';

$categoryHeroSlug = (string) ($selectedGroup['slug'] ?? '');
$categoryHeroImage = category_hero_image($categoryHeroSlug) ?? 'assets/hero-cupons.webp';
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
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1725208559538025" crossorigin="anonymous"></script>
    <?php render_oferto_brand_schema($shareUrl, $shareTitle); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Kanit:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/styles.css?v=20260903-taxonomia" />
  </head>
  <body class="site-v2 site-v2-compact category-page" style="--seo-bg-image: url('/<?= e($categoryHeroImage) ?>');">
    <header class="site-header v2-compact-header">
      <a class="brand" href="/" aria-label="Oferto Cupons">
        <img src="https://oferto.digital/wp-content/uploads/2024/08/oferto.png" alt="Oferto" />
        <span>Cupons</span>
      </a>
      <?php render_public_nav($coupons, 'categorias'); ?>
      <a class="header-cta" href="/">Ver ofertas</a>
    </header>

    <main id="top">
      <section class="v2-compact-hero category-hero">
        <div>
          <p class="eyebrow"><?= $isIndex ? 'Categorias' : 'Categoria' ?></p>
          <h1><?= $isIndex ? 'Encontre cupons pelo tipo de compra.' : e('Cupons de ' . $pageName) ?></h1>
          <p><?= e($shareDescription) ?> As categorias são organizadas por nicho para facilitar a busca por ofertas parecidas.</p>
        </div>
      </section>

      <aside class="inventory-band v2-ad-band" aria-label="Publicidade">
        <?php render_ad_slot('categoria_topo_responsivo'); ?>
      </aside>

      <?php if ($isIndex): ?>
        <section class="v2-section category-index">
          <div class="section-heading">
            <div>
              <p class="section-kicker">Nichos em destaque</p>
              <h2><?= count($groups) ?> <?= count($groups) === 1 ? 'categoria com ofertas ativas' : 'categorias com ofertas ativas' ?></h2>
            </div>
            <a class="text-action" href="/">Voltar para a vitrine</a>
          </div>
          <div class="niche-grid">
            <?php foreach ($groups as $group): ?>
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
      <?php elseif ($selectedGroup): ?>
        <section class="v2-layout">
          <aside class="v2-side-panel">
            <section>
              <p class="section-kicker">Nesta categoria</p>
              <h2><?= (int) $selectedGroup['count'] ?> <?= (int) $selectedGroup['count'] === 1 ? 'oferta ativa' : 'ofertas ativas' ?></h2>
              <div class="v2-expiring-list">
                <?php foreach (array_slice($selectedGroup['stores'], 0, 8) as $store): ?>
                  <a href="/?q=<?= e(rawurlencode($store)) ?>#cupons">
                    <strong><?= e($store) ?></strong>
                    <span>Ver ofertas da marca</span>
                  </a>
                <?php endforeach; ?>
              </div>
            </section>
            <aside aria-label="Publicidade">
              <?php render_ad_slot('categoria_lateral_300x250', 'inventory-slot-rectangle v2-side-ad'); ?>
            </aside>
          </aside>

          <section class="v2-results">
            <div class="section-heading">
              <div>
                <p class="section-kicker">Ofertas por nicho</p>
                <h2><?= e($pageName) ?></h2>
              </div>
              <a class="text-action" href="/categorias/">Ver categorias</a>
            </div>
            <div class="v2-list" id="coupon-grid">
              <?php foreach ($pageCoupons as $coupon): ?>
                <?php
                  $couponOfferUrl = 'https://cupons.oferto.digital/' . coupon_offer_url($coupon, 'categoria_' . ($selectedGroup['slug'] ?? 'nicho'));
                  $couponDisplayTitle = coupon_display_title($coupon, 82);
                  $couponShareText = rawurlencode('Olha essa oferta da ' . $coupon['store'] . ': ' . $couponDisplayTitle . '. Veja os detalhes e copie o cupom aqui: ' . $couponOfferUrl);
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
                    <p class="offer-condition"><?= e($coupon['description']) ?></p>
                    <p class="offer-rule"><?= e(trim((string) ($coupon['rules'] ?? '')) !== '' ? $coupon['rules'] : 'Confira as regras no site parceiro antes de finalizar.') ?></p>
                    <div class="v2-list-tags">
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
                      <a class="use-button" href="<?= e(coupon_go_url($coupon, 'categoria_cta')) ?>" target="_blank" rel="noopener"><?= e(coupon_cta_label($coupon)) ?></a>
                    <?php endif; ?>
                    <a class="whatsapp-share-button" href="https://wa.me/?text=<?= e($couponShareText) ?>" target="_blank" rel="noopener">
                      <svg viewBox="0 0 32 32" aria-hidden="true">
                        <path d="M16 3.2A12.7 12.7 0 0 0 5.1 22.4L3.6 28.8l6.6-1.5A12.7 12.7 0 1 0 16 3.2Zm0 2.4a10.3 10.3 0 0 1 8.8 15.7 10.4 10.4 0 0 1-13.9 3.7l-.4-.2-3.5.8.8-3.4-.2-.4A10.3 10.3 0 0 1 16 5.6Zm-4.1 5.2c-.3 0-.7.1-1 .5-.4.4-1.3 1.2-1.3 3s1.3 3.5 1.5 3.8c.2.2 2.5 4 6.2 5.4 3.1 1.2 3.7.9 4.4.9.7-.1 2.2-.9 2.5-1.8.3-.9.3-1.7.2-1.8-.1-.2-.3-.3-.7-.5l-2.5-1.2c-.3-.1-.6-.2-.8.2-.2.3-.9 1.2-1.1 1.5-.2.2-.4.3-.8.1-.3-.2-1.4-.5-2.7-1.7-1-1-1.7-2.1-1.9-2.4-.2-.4 0-.6.2-.8l.5-.6c.2-.2.2-.4.3-.6.1-.2.1-.4 0-.6l-1.1-2.6c-.3-.6-.5-.6-.8-.6h-.8Z" />
                      </svg>
                      <span>Compartilhar</span>
                    </a>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </section>
        </section>
      <?php else: ?>
        <section class="v2-section">
          <div class="empty-state">Categoria não encontrada. Veja a lista de categorias disponíveis.</div>
          <a class="primary-action" href="/categorias/">Ver categorias</a>
        </section>
      <?php endif; ?>
    </main>

    <footer class="site-footer">
      <strong>Oferto Cupons</strong>
      <span>Cupons, promoções e sorteios para economizar hoje.</span>
    </footer>
    <script src="/php-site.js?v=20260903-taxonomia"></script>
  </body>
</html>
