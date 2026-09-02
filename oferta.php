<?php
require_once __DIR__ . '/includes/coupons.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$coupon = $id > 0 ? coupon_by_id($id) : null;

if (!$coupon) {
    http_response_code(404);
    exit('Oferta nao encontrada.');
}

$related = array_values(array_filter(active_coupons(), function (array $item) use ($coupon): bool {
    return (int) ($item['id'] ?? 0) !== (int) ($coupon['id'] ?? 0)
        && ($item['category'] ?? '') === ($coupon['category'] ?? '');
}));
$related = array_slice($related, 0, 3);

$store = trim((string) ($coupon['store'] ?? 'loja parceira'));
$title = trim((string) ($coupon['title'] ?? 'Oferta selecionada'));
$description = trim((string) ($coupon['description'] ?? ''));
$rules = trim((string) ($coupon['rules'] ?? ''));
$category = trim((string) ($coupon['category'] ?? 'Ofertas'));
$hasCode = coupon_shows_public_code($coupon);
$canGo = coupon_shows_rescue_button($coupon) && coupon_has_valid_destination($coupon);
$validity = validity_label((string) ($coupon['ends_at'] ?? date('Y-m-d')));
$shareUrl = 'https://cupons.oferto.digital/oferta.php?id=' . (int) ($coupon['id'] ?? 0);
$shareTitle = $title . ' | Oferto Cupons';
$shareDescription = $description !== ''
    ? $description
    : 'Veja detalhes, validade e forma de usar esta oferta antes de acessar a loja parceira.';
$shareImage = is_remote_banner_url(coupon_banner_src($coupon))
    ? coupon_banner_src($coupon)
    : 'https://cupons.oferto.digital/assets/og-cupons.png';

$brandCopy = $description !== ''
    ? $description
    : 'Esta oferta foi separada pelo Oferto para quem quer comparar oportunidades antes de comprar. Confira o cupom, veja a validade e leia as condicoes antes de seguir para o site parceiro.';

$howToUse = $hasCode
    ? 'Copie o codigo, abra a loja parceira e cole no campo de cupom ou vale-desconto antes de finalizar a compra.'
    : 'Abra a loja parceira pelo botao principal e confira se o desconto, promocao ou condicao especial aparece antes de concluir a compra.';
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
    <meta property="og:type" content="article" />
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
    <link rel="stylesheet" href="/styles.css?v=20260828-offer-page" />
  </head>
  <body class="site-v2 site-v2-compact offer-page">
    <header class="site-header v2-compact-header">
      <a class="brand" href="/" aria-label="Oferto Cupons">
        <img src="https://oferto.digital/wp-content/uploads/2024/08/oferto.png" alt="Oferto" />
        <span>Cupons</span>
      </a>
      <nav class="nav-links" aria-label="Navegacao principal">
        <a href="/#top-cupons">Destaques</a>
        <a href="/#cupons">Todos</a>
        <a href="/sorteios/">Sorteios</a>
        <a href="/blog/">Dicas de economia</a>
        <a href="/sobre-a-oferto-digital.php">Sobre</a>
      </nav>
      <a class="header-cta" href="/admin/">Admin</a>
    </header>

    <main id="top">
      <section class="offer-hero">
        <div class="offer-hero-copy">
          <a class="offer-back-link" href="/">Voltar para ofertas</a>
          <p class="eyebrow"><?= e($category) ?></p>
          <h1><?= e($title) ?></h1>
          <p><?= e($brandCopy) ?></p>
          <div class="offer-badges">
            <span><?= e($store) ?></span>
            <span><?= e($validity) ?></span>
          </div>
        </div>

        <aside class="offer-rescue-card" aria-label="Como usar esta oferta">
          <div class="offer-rescue-media">
            <?= coupon_brand_image_markup($coupon) ?>
          </div>

          <?php if ($hasCode): ?>
            <div class="offer-code-box">
              <span>Cupom para copiar</span>
              <strong><?= e(coupon_mechanic_value($coupon)) ?></strong>
              <button class="copy-button" type="button" data-code="<?= e($coupon['code']) ?>">Copiar cupom</button>
            </div>
          <?php else: ?>
            <div class="offer-code-box">
              <span>Oferta sem codigo</span>
              <strong>Ativar no site</strong>
            </div>
          <?php endif; ?>

          <?php if ($canGo): ?>
            <a class="use-button offer-main-button" href="<?= e(coupon_go_url($coupon, 'offer_page_cta')) ?>" target="_blank" rel="noopener"><?= e(coupon_cta_label($coupon)) ?></a>
          <?php else: ?>
            <p class="offer-unavailable">Esta oferta esta sem link de resgate no momento.</p>
          <?php endif; ?>
        </aside>
      </section>

      <aside class="inventory-band v2-ad-band offer-ad-band" aria-label="Publicidade">
        <?php render_ad_slot('oferta_topo_responsivo'); ?>
      </aside>

      <section class="offer-content-shell">
        <article class="offer-content">
          <section>
            <p class="section-kicker">Resumo da oferta</p>
            <h2>O que vale conferir antes de usar</h2>
            <p><?= e($brandCopy) ?></p>
            <p><?= e($howToUse) ?></p>
          </section>

          <section>
            <p class="section-kicker">Condicoes</p>
            <h2>Regras e validade</h2>
            <ul class="offer-check-list">
              <li>Loja ou marca: <strong><?= e($store) ?></strong></li>
              <li>Categoria: <strong><?= e($category) ?></strong></li>
              <li>Validade: <strong><?= e($validity) ?></strong></li>
              <?php if ($rules !== ''): ?>
                <li><?= e($rules) ?></li>
              <?php else: ?>
                <li>Confira pedido minimo, produtos participantes e disponibilidade antes de finalizar.</li>
              <?php endif; ?>
            </ul>
          </section>
        </article>

        <aside class="offer-side">
          <section class="offer-side-card">
            <p class="section-kicker">Dica rapida</p>
            <h2>Copie antes de sair</h2>
            <p>Se a oferta tiver cupom, copie o codigo primeiro. Depois abra a loja em uma nova aba e cole o cupom no carrinho antes do pagamento.</p>
          </section>
          <aside aria-label="Publicidade">
            <?php render_ad_slot('oferta_meio_responsivo', 'inventory-slot-rectangle'); ?>
          </aside>
        </aside>
      </section>

      <?php if ($related): ?>
        <section class="v2-section offer-related">
          <div class="section-heading">
            <div>
              <p class="section-kicker">Ofertas parecidas</p>
              <h2>Veja tambem nesta categoria</h2>
            </div>
            <a class="text-action" href="/#cupons">Ver todas</a>
          </div>
          <div class="v2-store-grid">
            <?php foreach ($related as $item): ?>
              <a class="v2-store-card" href="<?= e(coupon_offer_url($item, 'relacionada')) ?>">
                <div class="v2-card-media">
                  <?= coupon_brand_image_markup($item) ?>
                </div>
                <div class="v2-store-card-copy">
                  <span><?= e($item['store']) ?></span>
                  <p><?= e($item['title']) ?></p>
                  <strong><?= e(validity_label($item['ends_at'])) ?></strong>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    </main>

    <footer class="site-footer">
      <strong>Oferto Cupons</strong>
      <span>Cupons, promocoes e sorteios para economizar hoje.</span>
    </footer>
    <script src="/php-site.js?v=20260828-offer-page"></script>
    <script src="/pwa.js?v=20260825-cache-fix"></script>
  </body>
</html>
