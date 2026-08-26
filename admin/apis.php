<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/integrations.php';
require_once __DIR__ . '/layout.php';

require_admin();

$savedLomadeeKey = lomadee_api_key();
$savedAwinToken = awin_access_token();
$awinPublisherId = awin_publisher_id();
$awinPublisherName = awin_publisher_name();
$offer18Accounts = offer18_accounts();

$partners = [
    [
        'name' => 'Lomadee',
        'status' => $savedLomadeeKey === '' ? 'Configurar' : 'Conectada',
        'text' => 'Pesquise marcas, filtre segmentos e escolha quais cupons entram no Oferto.',
        'href' => 'api-lomadee.php',
        'action' => 'Abrir Lomadee',
    ],
    [
        'name' => 'Awin',
        'status' => $awinPublisherId === '' ? 'Pronta para conectar' : 'Conectada',
        'text' => $awinPublisherId === ''
            ? 'Conecte o publisher e prepare a importacao de vouchers e ofertas.'
            : 'Publisher ' . ($awinPublisherName ?: 'Awin') . ' conectado.',
        'href' => 'api-awin.php',
        'action' => 'Abrir Awin',
    ],
    [
        'name' => 'Offer18',
        'status' => $offer18Accounts ? count($offer18Accounts) . ' conta(s)' : 'Pronta para conectar',
        'text' => 'Cadastre qualquer conta Offer18, busque campanhas aprovadas e importe com tracking preservado.',
        'href' => 'api-offer18.php',
        'action' => 'Abrir Offer18',
    ],
    [
        'name' => 'Amazon',
        'status' => 'Em breve',
        'text' => 'Associados, produtos e ofertas selecionadas para uma curadoria futura.',
        'href' => '#',
        'action' => 'Planejado',
    ],
    [
        'name' => 'Mercado Livre',
        'status' => 'Em breve',
        'text' => 'Produtos e campanhas afiliadas quando a estrategia estiver definida.',
        'href' => '#',
        'action' => 'Planejado',
    ],
    [
        'name' => 'Outros parceiros',
        'status' => 'Planejado',
        'text' => 'Rakuten, Impact ou feeds diretos podem entrar na mesma estrutura.',
        'href' => '#',
        'action' => 'Planejado',
    ],
];
?>
<?php admin_layout_start('APIs - Oferto Cupons', 'apis', 'Integracoes'); ?>
      <section class="admin-hero admin-api-hero">
        <div>
          <p class="section-kicker">Central de parceiros</p>
          <h1>Integre redes sem perder controle</h1>
          <p>Cada parceiro tem sua propria tela de busca, filtros e publicacao. Assim o CRM cresce sem virar uma lista confusa.</p>
        </div>
        <div class="admin-hero-stats" aria-label="Resumo das integracoes">
          <span><strong>3</strong> parceiros iniciados</span>
          <span><strong>1</strong> curadoria ativa</span>
        </div>
      </section>

      <section class="admin-api-hub-grid">
        <?php foreach ($partners as $partner): ?>
          <article class="admin-api-hub-card <?= $partner['href'] !== '#' ? 'is-ready' : '' ?>">
            <div>
              <span><?= e($partner['status']) ?></span>
              <h2><?= e($partner['name']) ?></h2>
              <p><?= e($partner['text']) ?></p>
            </div>
            <?php if ($partner['href'] !== '#'): ?>
              <a class="admin-primary-link" href="<?= e($partner['href']) ?>"><?= e($partner['action']) ?></a>
            <?php else: ?>
              <span class="admin-secondary-link"><?= e($partner['action']) ?></span>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </section>
<?php admin_layout_end(); ?>
