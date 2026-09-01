<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/affiliation.php';
require_once __DIR__ . '/layout.php';

require_admin();

$startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
    $startDate = date('Y-m-d', strtotime('-30 days'));
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
    $endDate = date('Y-m-d');
}

$summary = affiliation_conversion_summary($startDate, $endDate);
$rows = affiliation_conversion_rows($startDate, $endDate);
?>
<?php admin_layout_start('Conversões de afiliação - Oferto Cupons', 'afiliacao', 'Afiliação'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Conversões</p>
          <h1>Resultado financeiro das redes</h1>
          <p>Esta leitura usa a tabela própria de conversões do módulo afiliado, separada das conversões brutas recebidas de redes externas.</p>
        </div>
        <form class="admin-report-filter" method="get">
          <label>Início<input name="start" type="date" value="<?= e($startDate) ?>" /></label>
          <label>Fim<input name="end" type="date" value="<?= e($endDate) ?>" /></label>
          <button type="submit">Filtrar</button>
        </form>
      </section>

      <?php admin_affiliation_subnav('conversoes'); ?>

      <section class="admin-kpi-grid">
        <article class="admin-kpi-card">
          <span>Conversões</span>
          <strong><?= (int) $summary['total_conversions'] ?></strong>
          <small>No período selecionado</small>
        </article>
        <article class="admin-kpi-card">
          <span>Vendas</span>
          <strong>R$ <?= number_format((float) $summary['total_sales'], 2, ',', '.') ?></strong>
          <small>Valor total informado pelas redes</small>
        </article>
        <article class="admin-kpi-card">
          <span>Comissão</span>
          <strong>R$ <?= number_format((float) $summary['total_commission'], 2, ',', '.') ?></strong>
          <small>Comissão bruta recebida</small>
        </article>
        <article class="admin-kpi-card">
          <span>Aprovada</span>
          <strong>R$ <?= number_format((float) $summary['approved_commission'], 2, ',', '.') ?></strong>
          <small>Approved, confirmed ou paid</small>
        </article>
      </section>

      <section class="admin-panel">
        <div class="admin-table-wrap">
          <table class="admin-table admin-report-table">
            <thead>
              <tr>
                <th>Parceiro</th>
                <th>Oferta</th>
                <th>Status</th>
                <th>Conversões</th>
                <th>Vendas</th>
                <th>Comissão</th>
                <th>Última conversão</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td><?= e($row['network']) ?></td>
                  <td><strong><?= e($row['advertiser'] ?: '-') ?></strong><br /><span><?= e($row['title'] ?: '-') ?></span></td>
                  <td><span class="status-pill status-rascunho"><?= e($row['status']) ?></span></td>
                  <td><strong><?= (int) $row['total_conversions'] ?></strong></td>
                  <td>R$ <?= number_format((float) $row['total_sales'], 2, ',', '.') ?></td>
                  <td>R$ <?= number_format((float) $row['total_commission'], 2, ',', '.') ?></td>
                  <td><?= e($row['last_conversion_at'] ? date('d/m/Y H:i', strtotime($row['last_conversion_at'])) : '-') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$rows): ?>
                <tr><td colspan="7" class="admin-empty-cell">Nenhuma conversão sincronizada neste período.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php admin_layout_end(); ?>
