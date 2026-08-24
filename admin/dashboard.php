<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/coupons.php';
require_once __DIR__ . '/layout.php';

require_admin();

$coupons = all_coupons();
$activeCoupons = array_filter($coupons, fn ($coupon) => ($coupon['status'] ?? '') === 'ativo');
$sponsoredCoupons = array_filter($coupons, fn ($coupon) => (int) ($coupon['sponsored'] ?? 0) === 1);
$membersOnlyCoupons = array_filter($coupons, fn ($coupon) => (int) ($coupon['members_only'] ?? 0) === 1);
$today = date('Y-m-d');
$startDate = date('Y-m-d', strtotime('-30 days'));
$summary = click_report_summary($startDate, $today);
$rows = click_report_rows($startDate, $today);
$topOffer = $rows[0] ?? null;
?>
<?php admin_layout_start('Dashboard - Oferto Cupons', 'dashboard', 'Painel Oferto'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Dashboard</p>
          <h1>Operacao de cupons em tempo real</h1>
          <p>Acompanhe a vitrine, cadastre campanhas, veja cliques recentes e mantenha a base pronta para cupons, sorteios e ofertas de afiliados.</p>
        </div>
        <div class="admin-hero-actions">
          <a href="index.php" class="admin-primary-link">Nova oferta</a>
          <a href="relatorios.php" class="admin-secondary-link">Ver relatorios</a>
        </div>
      </section>

      <section class="admin-kpi-grid">
        <article class="admin-kpi-card">
          <span>Ofertas cadastradas</span>
          <strong><?= count($coupons) ?></strong>
          <small><?= count($activeCoupons) ?> ativas na vitrine</small>
        </article>
        <article class="admin-kpi-card">
          <span>Cliques em 30 dias</span>
          <strong><?= (int) $summary['total_clicks'] ?></strong>
          <small><?= (int) $summary['unique_users'] ?> usuarios unicos</small>
        </article>
        <article class="admin-kpi-card">
          <span>Patrocinadas</span>
          <strong><?= count($sponsoredCoupons) ?></strong>
          <small>Campanhas com prioridade comercial</small>
        </article>
        <article class="admin-kpi-card">
          <span>Somente logados</span>
          <strong><?= count($membersOnlyCoupons) ?></strong>
          <small>Preparadas para fidelizacao futura</small>
        </article>
      </section>

      <div class="admin-dashboard-grid">
        <section class="admin-panel">
          <p class="section-kicker">Proximas acoes</p>
          <h2>Atalhos de operacao</h2>
          <div class="admin-shortcut-grid">
            <a href="index.php" class="admin-shortcut">
              <strong>Cadastrar cupom</strong>
              <span>Texto, redirect, validade, banner e CTA.</span>
            </a>
            <a href="index.php#importar-lote" class="admin-shortcut">
              <strong>Importar em lote</strong>
              <span>Suba campanhas por CSV quando receber varias ofertas.</span>
            </a>
            <a href="relatorios.php" class="admin-shortcut">
              <strong>Analisar performance</strong>
              <span>Compare cliques por oferta, parceiro e tipo.</span>
            </a>
            <a href="usuarios.php" class="admin-shortcut">
              <strong>Gerenciar usuarios</strong>
              <span>Crie acessos para o time operar o CRM.</span>
            </a>
          </div>
        </section>

        <section class="admin-panel">
          <p class="section-kicker">Melhor campanha</p>
          <h2><?= $topOffer ? e($topOffer['store']) : 'Sem cliques ainda' ?></h2>
          <?php if ($topOffer): ?>
            <p><?= e($topOffer['title']) ?></p>
            <div class="admin-top-offer">
              <span><strong><?= (int) $topOffer['total_clicks'] ?></strong> cliques</span>
              <span><?= e(offer_type_label($topOffer['offer_type'] ?? 'cupom')) ?></span>
              <span><?= e($topOffer['partner_network'] ?: 'Sem parceiro') ?></span>
            </div>
          <?php else: ?>
            <p>Assim que o site registrar cliques, a campanha com melhor desempenho aparece aqui.</p>
          <?php endif; ?>
        </section>
      </div>
<?php admin_layout_end(); ?>
