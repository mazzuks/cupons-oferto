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
        'intro' => 'Volte para a página inicial e escolha uma das dicas disponíveis.',
        'sections' => [],
        'tip' => '',
    ];
}

$relatedGuides = array_values(array_filter(all_guides(), fn ($item) => ($item['slug'] ?? '') !== $slug));
$publicCoupons = active_coupons();
$shareTitle = $guide['title'] . ' - Oferto Cupons';
$shareDescription = $guide['summary'];
$shareSlug = $guide['slug'] ?? $slug;
$shareUrl = $shareSlug ? 'https://cupons.oferto.digital/guia.php?tema=' . rawurlencode($shareSlug) : 'https://cupons.oferto.digital/blog/';
$shareImage = 'https://cupons.oferto.digital/assets/og-cupons.png';
$guideImageSeed = preg_replace('/[^a-z0-9-]+/', '-', normalize_search_text('oferto-' . ($guide['slug'] ?? $slug ?: 'guia-economia')));
$whatsappShareUrl = 'https://wa.me/?text=' . rawurlencode($guide['title'] . ' - ' . $shareUrl);
$guideCouponMatches = guide_coupon_matches(
    $guide['coupon_box']['coupons'] ?? [],
    $guide['coupon_box']['store'] ?? 'China in Box',
    (int) ($guide['coupon_box']['limit'] ?? 4)
);

function guide_coupon_matches(array $couponRefs, string $storeName = 'China in Box', int $limit = 4): array
{
    $matches = [];
    $wantedCodes = array_map(
        fn (array $coupon): string => normalize_search_text((string) ($coupon['code'] ?? '')),
        $couponRefs
    );
    $wantedCodes = array_values(array_filter($wantedCodes));
    $storeKey = normalize_search_text($storeName);

    foreach (active_coupons() as $coupon) {
        if (normalize_search_text((string) ($coupon['store'] ?? '')) !== $storeKey) {
            continue;
        }

        $code = normalize_search_text((string) ($coupon['code'] ?? ''));
        if ($wantedCodes && ($code === '' || !in_array($code, $wantedCodes, true))) {
            continue;
        }

        $matches[$code ?: (string) ($coupon['id'] ?? count($matches))] = $coupon;
        if (!$wantedCodes && count($matches) >= $limit) {
            break;
        }
    }

    return $matches;
}
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
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1725208559538025" crossorigin="anonymous"></script>
    <?php render_oferto_brand_schema($shareUrl, $shareTitle); ?>
    <link rel="preconnect" href="https://picsum.photos" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Kanit:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css?v=20260828-picsum-seo" />
  </head>
  <body style="--seo-bg-image: url('https://picsum.photos/seed/<?= e($guideImageSeed) ?>/1600/900.webp');">
    <header class="site-header">
      <a class="brand" href="index.php#top" aria-label="Oferto Cupons">
        <img src="https://oferto.digital/wp-content/uploads/2024/08/oferto.png" alt="Oferto" />
        <span>Cupons</span>
      </a>
      <?php render_public_nav($publicCoupons, 'blog'); ?>
      <a class="header-cta" href="/#cupons">Ver cupons</a>
    </header>

    <main>
      <section class="guide-hero">
        <p class="eyebrow"><?= e($guide['category']) ?></p>
        <h1><?= e($guide['title']) ?></h1>
        <p><?= e($guide['intro']) ?></p>
      </section>

      <aside class="inventory-band guide-inventory" aria-label="Publicidade">
        <?php render_ad_slot('guias_artigo_topo_responsivo'); ?>
      </aside>

      <section class="guide-layout">
        <article class="guide-article">
          <?php foreach ($guide['sections'] as $section): ?>
            <h2><?= e($section['title']) ?></h2>
            <p><?= e($section['body']) ?></p>
          <?php endforeach; ?>

          <?php if (!empty($guide['coupon_box'])): ?>
            <section class="guide-coupon-box">
              <?php if (!empty($guide['coupon_box']['kicker'])): ?>
                <p class="section-kicker"><?= e($guide['coupon_box']['kicker']) ?></p>
              <?php endif; ?>
              <h2><?= e($guide['coupon_box']['title'] ?? 'Cupons para testar') ?></h2>
              <p><?= e($guide['coupon_box']['body'] ?? '') ?></p>
              <div class="guide-coupon-list">
                <?php $couponRefs = $guide['coupon_box']['coupons'] ?? []; ?>
                <?php if (!$couponRefs): ?>
                  <?php foreach ($guideCouponMatches as $matchedCoupon): ?>
                    <?php $couponRefs[] = [
                        'code' => (string) ($matchedCoupon['code'] ?? ''),
                        'description' => (string) ($matchedCoupon['title'] ?? $matchedCoupon['description'] ?? 'Oferta ativa'),
                    ]; ?>
                  <?php endforeach; ?>
                <?php endif; ?>
                <?php foreach ($couponRefs as $couponRef): ?>
                  <?php
                    $couponCodeKey = normalize_search_text((string) ($couponRef['code'] ?? ''));
                    $matchedCoupon = $couponCodeKey !== '' ? ($guideCouponMatches[$couponCodeKey] ?? null) : null;
                  ?>
                  <div class="guide-coupon-item">
                    <span><?= e($couponRef['description']) ?></span>
                    <div class="guide-coupon-actions">
                      <?php if (!empty($couponRef['code'])): ?>
                        <button class="copy-button" type="button" data-code="<?= e($couponRef['code']) ?>">Copiar cupom</button>
                      <?php endif; ?>
                      <?php if ($matchedCoupon && coupon_shows_rescue_button($matchedCoupon)): ?>
                        <a class="use-button" href="<?= e(coupon_go_url($matchedCoupon, 'guide_cta')) ?>" target="_blank" rel="noopener">Resgatar oferta</a>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>

          <?php if (!empty($guide['tip'])): ?>
            <div class="guide-tip"><?= e($guide['tip']) ?></div>
          <?php endif; ?>
        </article>

        <aside class="guide-sidebar">
          <section class="rail-block share-block">
            <p class="section-kicker">Compartilhar</p>
            <h2>Envie esta dica no WhatsApp</h2>
            <p>Compartilhe o link com quem também quer economizar antes de finalizar a compra.</p>
            <a class="whatsapp-action" href="<?= e($whatsappShareUrl) ?>" target="_blank" rel="noopener">Compartilhar no WhatsApp</a>
          </section>

          <aside aria-label="Publicidade">
            <?php render_ad_slot('guias_lateral_300x250', 'inventory-slot-rectangle'); ?>
          </aside>

          <section class="rail-block">
            <p class="section-kicker">Próximo passo</p>
            <h2>Encontre um cupom ativo</h2>
            <p>Depois de entender a melhor forma de economizar, veja as ofertas disponíveis por categoria.</p>
            <a class="primary-action" href="index.php#cupons">Ver cupons</a>
          </section>

          <section class="rail-block">
            <p class="section-kicker">Mais dicas</p>
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
    <script>
      document.addEventListener('click', async (event) => {
        const button = event.target.closest('.copy-button');
        if (!button) return;

        const code = button.dataset.code;
        if (!code) return;

        await navigator.clipboard.writeText(code);
        const originalText = button.textContent;
        button.classList.add('is-copied');
        button.textContent = 'Cupom copiado';
        setTimeout(() => {
          button.classList.remove('is-copied');
          button.textContent = originalText;
        }, 1800);
      });
    </script>
    <script src="pwa.js"></script>
  </body>
</html>

