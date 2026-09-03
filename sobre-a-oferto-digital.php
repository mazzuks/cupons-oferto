<?php
require_once __DIR__ . '/includes/coupons.php';

$publicCoupons = active_coupons();
$shareTitle = 'Oferto Digital - cupons, promoções e economia online';
$shareDescription = 'Conheça a Oferto Digital, a marca por trás do Oferto Cupons, e entenda como encontrar cupons, promoções e sorteios com mais segurança.';
$shareUrl = 'https://cupons.oferto.digital/sobre-a-oferto-digital.php';
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
    <?php render_oferto_brand_schema($shareUrl, 'Oferto Digital'); ?>
    <link rel="preconnect" href="https://picsum.photos" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Kanit:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/styles.css?v=20260828-brand-search" />
  </head>
  <body class="site-v2 site-v2-compact brand-about-page" style="--seo-bg-image: url('https://picsum.photos/seed/oferto-digital-marca/1600/900.webp');">
    <header class="site-header v2-compact-header">
      <a class="brand" href="/" aria-label="Oferto Cupons">
        <img src="https://oferto.digital/wp-content/uploads/2024/08/oferto.png" alt="Oferto" />
        <span>Cupons</span>
      </a>
      <?php render_public_nav($publicCoupons, 'sobre'); ?>
      <a class="header-cta" href="/">Ver ofertas</a>
    </header>

    <main id="top">
      <section class="blog-hero brand-about-hero">
        <p class="eyebrow">Oferto Digital</p>
        <h1>A marca por trás do Oferto Cupons.</h1>
        <p>O Oferto Digital reúne projetos criados para ajudar pessoas a encontrar oportunidades online com mais clareza: cupons, promoções, sorteios e conteúdos simples para comprar melhor.</p>
      </section>

      <aside class="inventory-band v2-ad-band" aria-label="Publicidade">
        <?php render_ad_slot('blog_topo_responsivo'); ?>
      </aside>

      <section class="brand-about-content">
        <article class="brand-about-article">
          <p class="eyebrow">Como funciona</p>
          <h2>O que é a Oferto Digital?</h2>
          <p>A Oferto Digital é a marca que organiza iniciativas de economia, descoberta de ofertas e conteúdo útil para quem compra pela internet. Dentro desse ecossistema, o Oferto Cupons funciona como uma vitrine prática: o usuário encontra uma oferta, entende a validade, copia o cupom quando existir e segue para a loja parceira para concluir a compra.</p>
          <p>A proposta é simples: antes de comprar, vale conferir se existe uma condição melhor. Pode ser um cupom, uma promoção relâmpago, um sorteio, uma campanha de compre e concorra ou uma dica de economia que evita gasto por impulso.</p>

          <h2>O Oferto vende produtos?</h2>
          <p>Não. O Oferto Cupons não finaliza pedidos, não recebe pagamento do consumidor e não faz entrega de produtos. A compra acontece no site da loja parceira. Por isso, preço final, estoque, frete, prazo, política de troca e atendimento continuam sendo responsabilidade da loja onde o pedido é concluído.</p>

          <h2>Como o site se mantém?</h2>
          <p>Alguns links podem ser links de afiliado. Isso significa que o Oferto pode receber uma comissão quando uma compra é feita depois do clique, sem custo extra para o usuário. Também existem espaços de publicidade no site. Essa estrutura ajuda a manter o conteúdo aberto e gratuito para quem procura cupons e promoções.</p>

          <h2>Como usar com mais segurança?</h2>
          <p>Confira a validade da oferta, leia as regras antes de finalizar, veja se o desconto apareceu no carrinho e só conclua a compra quando o valor final fizer sentido. Em ofertas com código, copie o cupom e cole no campo indicado pela loja. Em ofertas sem código, siga pelo botão principal e confirme a condição no site parceiro.</p>

          <div class="brand-about-actions">
            <a class="primary-action" href="/#cupons">Ver cupons ativos</a>
            <a class="text-action" href="/blog/">Ler dicas de economia</a>
          </div>
        </article>

        <aside class="brand-about-card">
          <img src="https://oferto.digital/wp-content/uploads/2024/08/oferto.png" alt="Logo Oferto" />
          <strong>Oferto Digital</strong>
          <span>Cupons, promoções, sorteios e conteúdos para economizar melhor.</span>
          <a href="https://oferto.digital/" target="_blank" rel="noopener">Conhecer oferto.digital</a>
        </aside>
      </section>
    </main>

    <footer class="site-footer">
      <strong>Oferto Cupons</strong>
      <span>Cupons, promoções e sorteios para comprar melhor.</span>
    </footer>
  </body>
</html>
