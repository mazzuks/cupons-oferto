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
$featured = array_slice(array_values(array_filter($coupons, fn ($coupon) => (int) ($coupon['featured'] ?? 0) === 1)), 0, 6);
$topCoupons = $featured ?: array_slice($coupons, 0, 6);
$expiring = expiring_soon_coupons($coupons);
$guides = all_guides();
$homeGuides = array_slice($guides, 0, 8);
$searchSuggestions = array_values(array_unique(array_filter(array_merge(
    $categories,
    array_column($coupons, 'store'),
    array_column($coupons, 'title'),
    ['pizza', 'seguro', 'games', 'mercado', 'sorteio', 'promocao', 'cupom']
))));
sort($searchSuggestions);
$defaultCategory = $categories[0] ?? 'Todos';
$initialCoupons = $defaultCategory === 'Todos'
    ? $coupons
    : array_values(array_filter($coupons, fn ($coupon) => $coupon['category'] === $defaultCategory));
$initialTitle = $defaultCategory === 'Todos' ? 'Todas as ofertas' : 'Ofertas em ' . $defaultCategory;
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
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Kanit:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css?v=20260826-brand-logos" />
  </head>
  <body class="site-v2 site-v2-compact">
    <header class="site-header v2-compact-header">
      <a class="brand" href="/" aria-label="Oferto Cupons">
        <img src="https://oferto.digital/wp-content/uploads/2024/08/oferto.png" alt="Oferto" />
        <span>Cupons</span>
      </a>
      <nav class="nav-links" aria-label="Navegacao principal">
        <a href="#top-cupons">Destaques</a>
        <a href="#cupons">Todos</a>
        <a href="/sorteios/">Sorteios</a>
        <a href="#dicas">Dicas de economia</a>
      </nav>
      <a class="header-cta" href="admin/">Admin</a>
    </header>

    <main id="top">
      <section class="v2-compact-hero">
        <div>
          <p class="eyebrow">Cupons, promocoes e sorteios para hoje</p>
          <h1>Economize mais nas suas compras.</h1>
          <p>Antes de comprar, procure um cupom, veja uma promocao ou participe de um sorteio. O Oferto junta oportunidades em um so lugar para voce gastar menos.</p>
          <div class="v2-hero-points" aria-label="O que encontrar no Oferto Cupons">
            <span>Descontos do dia</span>
            <span>Promocoes por categoria</span>
            <span>Sorteios abertos</span>
          </div>
        </div>
        <label class="v2-hero-search">
          <span>Procure sua loja, produto ou cupom</span>
          <div class="v2-search-control">
            <input id="coupon-search" type="search" list="coupon-search-suggestions" placeholder="Pizza, seguros, games, mercado..." autocomplete="off" />
            <button id="coupon-search-submit" type="button" aria-label="Buscar ofertas">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M10.8 18.1a7.3 7.3 0 1 1 0-14.6 7.3 7.3 0 0 1 0 14.6Zm5.4-1.9 4.3 4.3" />
              </svg>
            </button>
          </div>
          <datalist id="coupon-search-suggestions">
            <?php foreach (array_slice($searchSuggestions, 0, 40) as $suggestion): ?>
              <option value="<?= e($suggestion) ?>"></option>
            <?php endforeach; ?>
          </datalist>
          <small>Exemplo: pizza, seguro, games, mercado ou o nome de uma loja.</small>
        </label>
      </section>

      <section class="v2-quick-bar" aria-label="Resumo e filtros">
        <div class="v2-quick-stat"><strong><?= count($coupons) ?></strong><span>ofertas ativas</span></div>
        <div class="v2-quick-stat"><strong><?= count($categories) ?></strong><span>categorias</span></div>
        <div class="v2-quick-stat"><strong><?= count($expiring) ?></strong><span>vencendo em breve</span></div>
        <div class="v2-quick-note">Abra a oferta, veja como aproveitar e siga para o site parceiro quando quiser resgatar.</div>
      </section>

      <aside class="inventory-band v2-ad-band" aria-label="Publicidade">
        <div class="inventory-slot inventory-slot-wide" data-inventory-slot="v2_topo_responsivo">
          <span>Publicidade</span>
        </div>
      </aside>

      <?php if ($topCoupons): ?>
        <section class="v2-section" id="top-cupons">
          <div class="section-heading">
            <div>
              <p class="section-kicker">Destaques</p>
              <h2>Destaques da vez</h2>
              <p class="v2-section-subtitle">Cupons e promocoes para olhar agora.</p>
            </div>
            <a class="text-action" href="#cupons">Ver ofertas</a>
          </div>
          <div class="v2-store-grid">
            <?php foreach ($topCoupons as $coupon): ?>
              <a class="v2-store-card" href="<?= e(coupon_go_url($coupon, 'v2_featured')) ?>" target="_blank" rel="noopener">
                <div class="v2-card-media">
                  <img src="<?= e(coupon_banner_src($coupon)) ?>" alt="" onerror="this.onerror=null;this.src='<?= e(coupon_fallback_banner_src($coupon)) ?>';" />
                  <span class="brand-mark">
                    <?php if (coupon_logo_src($coupon)): ?>
                      <img src="<?= e(coupon_logo_src($coupon)) ?>" alt="" />
                    <?php else: ?>
                      <?= e(coupon_brand_initials($coupon)) ?>
                    <?php endif; ?>
                  </span>
                </div>
                <div class="v2-store-card-copy">
                  <span><?= e($coupon['store']) ?></span>
                  <p><?= e($coupon['title']) ?></p>
                  <strong><?= e(validity_label($coupon['ends_at'])) ?></strong>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <aside class="inventory-band v2-ad-band v2-ad-band-between" aria-label="Publicidade">
        <div class="inventory-slot inventory-slot-wide" data-inventory-slot="v2_entre_destaques_e_lista">
          <span>Publicidade</span>
        </div>
      </aside>

      <section class="v2-layout" id="cupons">
        <aside class="v2-side-panel">
          <section>
            <p class="section-kicker">Vencendo</p>
            <h2>Use antes que acabe</h2>
            <div class="v2-expiring-list">
              <?php if ($expiring): ?>
                <?php foreach ($expiring as $coupon): ?>
                  <a href="<?= e(coupon_go_url($coupon, 'v2_expiring')) ?>" target="_blank" rel="noopener">
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
          <aside class="inventory-slot inventory-slot-rectangle v2-side-ad" aria-label="Publicidade" data-inventory-slot="v2_lateral_300x250">
            <span>Publicidade</span>
          </aside>
        </aside>

        <section class="v2-results" aria-live="polite">
          <div class="v2-local-filters" aria-label="Filtros de ofertas">
            <section class="v2-category-strip" aria-label="Categorias">
              <button class="category-chip <?= $defaultCategory === 'Todos' ? 'is-active' : '' ?>" type="button" data-category="Todos" data-label="Todas as ofertas">Todos</button>
              <?php foreach ($categories as $filterCategory): ?>
                <button class="category-chip <?= $defaultCategory === $filterCategory ? 'is-active' : '' ?>" type="button" data-category="<?= e($filterCategory) ?>" data-label="<?= e($filterCategory) ?>"><?= e($filterCategory) ?> <small><?= (int) ($categoryCounts[$filterCategory] ?? 0) ?></small></button>
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

          <div class="section-heading">
            <div>
              <h2 id="coupon-title"><?= e($initialTitle) ?></h2>
            </div>
            <span id="result-count"><?= count($initialCoupons) ?> <?= count($initialCoupons) === 1 ? 'encontrado' : 'encontrados' ?></span>
          </div>

          <div class="v2-list" id="coupon-grid">
            <?php foreach ($coupons as $coupon): ?>
              <article class="coupon-card v2-list-card" <?= $defaultCategory !== 'Todos' && $coupon['category'] !== $defaultCategory ? 'hidden' : '' ?> data-category="<?= e($coupon['category']) ?>" data-offer-type="<?= e($coupon['offer_type'] ?? 'cupom') ?>" data-search="<?= e(normalize_search_text($coupon['category'] . ' ' . $coupon['store'] . ' ' . $coupon['title'] . ' ' . $coupon['description'] . ' ' . $coupon['code'] . ' ' . ($coupon['tags'] ?? '') . ' ' . ($coupon['requirements'] ?? '') . ' ' . ($coupon['rules'] ?? '') . ' ' . ($coupon['partner_network'] ?? '') . ' ' . offer_type_label($coupon['offer_type'] ?? 'cupom') . ' ' . redemption_type_label($coupon['redemption_type'] ?? 'texto'))) ?>">
                <div class="v2-list-logo">
                  <img src="<?= e(coupon_banner_src($coupon)) ?>" alt="Banner da oferta <?= e($coupon['store']) ?>" onerror="this.onerror=null;this.src='<?= e(coupon_fallback_banner_src($coupon)) ?>';" />
                  <span class="brand-mark">
                    <?php if (coupon_logo_src($coupon)): ?>
                      <img src="<?= e(coupon_logo_src($coupon)) ?>" alt="" />
                    <?php else: ?>
                      <?= e(coupon_brand_initials($coupon)) ?>
                    <?php endif; ?>
                  </span>
                </div>
                <div class="v2-list-content">
                  <div class="coupon-meta">
                    <span class="store"><?= e($coupon['store']) ?></span>
                    <span class="coupon-type"><?= e(offer_type_label($coupon['offer_type'] ?? 'cupom')) ?></span>
                  </div>
                  <h3><?= e($coupon['title']) ?></h3>
                  <p><?= e($coupon['description']) ?></p>
                  <div class="v2-list-tags">
                    <span><?= e($coupon['category']) ?></span>
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
        </section>
      </section>

      <aside class="inventory-band v2-ad-band" aria-label="Publicidade">
        <div class="inventory-slot inventory-slot-wide" data-inventory-slot="v2_antes_dicas">
          <span>Publicidade</span>
        </div>
      </aside>

      <section class="v2-section v2-guides-compact" id="dicas">
        <div class="section-heading">
          <div>
            <p class="section-kicker">Dicas de economia</p>
            <h2>Aprenda a economizar melhor antes de comprar</h2>
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
    </footer>
    <script src="php-site.js?v=20260825-copy-fix"></script>
    <script>
      (() => {
        const search = document.querySelector("#coupon-search");
        const submit = document.querySelector("#coupon-search-submit");
        const goToResults = () => {
          search?.dispatchEvent(new Event("input", { bubbles: true }));
          document.querySelector("#cupons")?.scrollIntoView({ behavior: "smooth", block: "start" });
        };

        search?.addEventListener("keydown", (event) => {
          if (event.key !== "Enter") return;
          event.preventDefault();
          goToResults();
        });

        submit?.addEventListener("click", () => {
          search?.focus();
          goToResults();
        });
      })();
    </script>
    <script src="pwa.js?v=20260825-cache-fix"></script>
  </body>
</html>
