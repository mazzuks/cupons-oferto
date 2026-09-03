<?php
require_once __DIR__ . '/../includes/coupons.php';

$allCoupons = active_coupons();
$sweepstakes = array_values(array_filter($allCoupons, fn ($coupon) => in_array($coupon['offer_type'] ?? '', ['sorteio', 'compre_concorra'], true)));
$categories = array_values(array_unique(array_map(fn ($coupon) => $coupon['category'], $sweepstakes)));
sort($categories);
$categoryCounts = array_count_values(array_map(fn ($coupon) => $coupon['category'], $sweepstakes));
$expiring = expiring_soon_coupons($sweepstakes);
$searchSuggestions = array_values(array_unique(array_filter(array_merge(
    $categories,
    array_column($sweepstakes, 'store'),
    array_column($sweepstakes, 'title'),
    ['sorteio', 'promoção', 'brinde', 'prêmio', 'compre e concorra']
))));
sort($searchSuggestions);

$shareTitle = 'Sorteios Oferto - promoções e sorteios ativos';
$shareDescription = 'Encontre sorteios, promoções e campanhas de compre e concorra para participar hoje.';
$shareUrl = 'https://cupons.oferto.digital/sorteios/';
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
    <meta name="description" content="<?= e($shareDescription) ?>" />
    <link rel="canonical" href="<?= e($shareUrl) ?>" />
    <link rel="icon" href="/assets/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" href="/assets/favicon.png" />
    <link rel="apple-touch-icon" href="/assets/icon-180.png" />
    <link rel="manifest" href="/manifest.webmanifest" />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="pt_BR" />
    <meta property="og:site_name" content="Oferto Sorteios" />
    <meta property="og:title" content="<?= e($shareTitle) ?>" />
    <meta property="og:description" content="<?= e($shareDescription) ?>" />
    <meta property="og:url" content="<?= e($shareUrl) ?>" />
    <meta property="og:image" content="<?= e($shareImage) ?>" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= e($shareTitle) ?>" />
    <meta name="twitter:description" content="<?= e($shareDescription) ?>" />
    <meta name="twitter:image" content="<?= e($shareImage) ?>" />
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1725208559538025" crossorigin="anonymous"></script>
    <?php render_oferto_brand_schema($shareUrl, 'Oferto Sorteios'); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Kanit:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/styles.css?v=20260827-sorteios" />
  </head>
  <body class="site-v2 site-v2-compact sweepstakes-page">
    <header class="site-header v2-compact-header">
      <a class="brand" href="/" aria-label="Oferto Cupons">
        <img src="https://oferto.digital/wp-content/uploads/2024/08/oferto.png" alt="Oferto" />
        <span>Sorteios</span>
      </a>
      <?php render_public_nav($allCoupons, 'sorteios'); ?>
      <a class="header-cta" href="/">Ver ofertas</a>
    </header>

    <main id="top">
      <section class="sweepstakes-hero">
        <div>
          <p class="eyebrow">Sorteios e promoções abertas</p>
          <h1>Participe de sorteios sem perder promoção boa.</h1>
          <p>Veja oportunidades ativas, confira a regra de participação e siga para o site parceiro quando quiser participar.</p>
        </div>
        <label class="v2-hero-search">
          <span>Procure por marca, prêmio ou promoção</span>
          <div class="v2-search-control">
            <input id="coupon-search" type="search" list="sweepstakes-search-suggestions" placeholder="Ruffles, prêmio, brinde..." autocomplete="off" />
            <button id="coupon-search-submit" type="button" aria-label="Buscar sorteios">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M10.8 18.1a7.3 7.3 0 1 1 0-14.6 7.3 7.3 0 0 1 0 14.6Zm5.4-1.9 4.3 4.3" />
              </svg>
            </button>
          </div>
          <datalist id="sweepstakes-search-suggestions">
            <?php foreach (array_slice($searchSuggestions, 0, 40) as $suggestion): ?>
              <option value="<?= e($suggestion) ?>"></option>
            <?php endforeach; ?>
          </datalist>
          <small>Exemplo: nome da marca, prêmio, categoria ou tipo de promoção.</small>
        </label>
      </section>

      <section class="sweepstakes-summary" aria-label="Resumo dos sorteios">
        <div><strong><?= count($sweepstakes) ?></strong><span>sorteios ativos</span></div>
        <div><strong><?= count($categories) ?></strong><span>categorias</span></div>
        <div><strong><?= count($expiring) ?></strong><span>vencendo em breve</span></div>
      </section>

      <aside class="inventory-band v2-ad-band" aria-label="Publicidade">
        <?php render_ad_slot('sorteios_topo_responsivo'); ?>
      </aside>

      <section class="sweepstakes-content" id="sorteios">
        <div class="v2-local-filters" aria-label="Filtros de sorteios">
          <section class="v2-category-strip" aria-label="Categorias">
            <button class="category-chip is-active" type="button" data-category="Todos" data-label="Todos os sorteios">Todos</button>
            <?php foreach ($categories as $filterCategory): ?>
              <button class="category-chip" type="button" data-category="<?= e($filterCategory) ?>" data-label="<?= e($filterCategory) ?>"><?= e($filterCategory) ?> <small><?= (int) ($categoryCounts[$filterCategory] ?? 0) ?></small></button>
            <?php endforeach; ?>
          </section>
          <section class="v2-type-strip" aria-label="Tipos de promoção">
            <button class="category-chip is-active" type="button" data-offer-type="Todos" data-label="Todos os sorteios">Todos os sorteios</button>
            <button class="category-chip" type="button" data-offer-type="sorteio" data-label="Sorteios">Sorteios</button>
            <button class="category-chip" type="button" data-offer-type="compre_concorra" data-label="Compre e concorra">Compre e concorra</button>
          </section>
        </div>

        <div class="section-heading">
          <div>
            <p class="section-kicker">Lista de sorteios</p>
            <h2 id="coupon-title">Todos os sorteios</h2>
          </div>
          <span id="result-count"><?= count($sweepstakes) ?> <?= count($sweepstakes) === 1 ? 'encontrado' : 'encontrados' ?></span>
        </div>

        <?php if ($sweepstakes): ?>
          <div class="v2-list" id="coupon-grid">
            <?php foreach ($sweepstakes as $coupon): ?>
              <article class="coupon-card v2-list-card sweepstakes-card" data-category="<?= e($coupon['category']) ?>" data-offer-type="<?= e($coupon['offer_type'] ?? 'sorteio') ?>" data-search="<?= e(normalize_search_text($coupon['category'] . ' ' . $coupon['store'] . ' ' . $coupon['title'] . ' ' . $coupon['description'] . ' ' . ($coupon['tags'] ?? '') . ' ' . ($coupon['requirements'] ?? '') . ' ' . ($coupon['rules'] ?? '') . ' ' . offer_type_label($coupon['offer_type'] ?? 'sorteio'))) ?>">
                <div class="v2-list-logo">
                  <?= coupon_brand_image_markup($coupon) ?>
                </div>
                <div class="v2-list-content">
                  <div class="coupon-meta">
                    <span class="store"><?= e($coupon['store']) ?></span>
                    <span class="coupon-type"><?= e(offer_type_label($coupon['offer_type'] ?? 'sorteio')) ?></span>
                  </div>
                  <h3><?= e($coupon['title']) ?></h3>
                  <p><?= e($coupon['description']) ?></p>
                  <div class="v2-list-tags">
                    <span><?= e($coupon['category']) ?></span>
                    <span><?= e(validity_label($coupon['ends_at'])) ?></span>
                    <span><?= e(coupon_mechanic_value($coupon)) ?></span>
                  </div>
                </div>
                <div class="v2-list-actions">
                  <?php if (coupon_shows_public_code($coupon)): ?>
                    <button class="copy-button" type="button" data-code="<?= e($coupon['code']) ?>">Copiar código</button>
                  <?php endif; ?>
                  <?php if (coupon_shows_rescue_button($coupon)): ?>
                    <a class="use-button" href="<?= e(coupon_go_url($coupon, 'sorteios_cta')) ?>" target="_blank" rel="noopener"><?= e(coupon_cta_label($coupon)) ?></a>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state sweepstakes-empty">
            <h2>Nenhum sorteio ativo agora</h2>
            <p>Quando uma promoção ou sorteio entrar no CRM como ativo, ela aparece aqui automaticamente.</p>
            <a class="primary-action" href="/">Ver cupons disponíveis</a>
          </div>
        <?php endif; ?>
        <p class="empty-state" id="empty-state" hidden>Nenhum sorteio encontrado para esse filtro.</p>
      </section>
    </main>

    <footer class="site-footer">
      <strong>Oferto Sorteios</strong>
      <span>Promoções, sorteios e oportunidades para participar hoje.</span>
    </footer>
    <script src="/php-site.js?v=20260827-sorteios"></script>
    <script>
      (() => {
        const search = document.querySelector("#coupon-search");
        const submit = document.querySelector("#coupon-search-submit");
        const goToResults = () => {
          search?.dispatchEvent(new Event("input", { bubbles: true }));
          document.querySelector("#sorteios")?.scrollIntoView({ behavior: "smooth", block: "start" });
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
    <script src="/pwa.js?v=20260825-cache-fix"></script>
  </body>
</html>
