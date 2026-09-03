<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/affiliation.php';
require_once __DIR__ . '/layout.php';

require_admin();

$error = '';
$success = '';
$startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
    $startDate = date('Y-m-d', strtotime('-30 days'));
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
    $endDate = date('Y-m-d');
}

$filters = [
    'status' => trim((string) ($_GET['status'] ?? '')),
    'q' => trim((string) ($_GET['q'] ?? '')),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        affiliation_update_conversion_status((int) ($_POST['conversion_id'] ?? 0), (string) ($_POST['status'] ?? ''));
        $success = 'Status da conversão atualizado e carteira sincronizada.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$summary = affiliation_conversion_summary($startDate, $endDate);
$rows = affiliation_conversion_rows($startDate, $endDate);
$details = affiliation_conversion_detail_rows($startDate, $endDate, $filters);
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
          <label>Status
            <select name="status">
              <?php foreach (affiliation_conversion_statuses() as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>Busca<input name="q" value="<?= e($filters['q']) ?>" placeholder="Pedido, campanha, afiliado..." /></label>
          <button type="submit">Filtrar</button>
        </form>
      </section>

      <?php admin_affiliation_subnav('conversoes'); ?>
      <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="admin-success"><?= e($success) ?></p><?php endif; ?>

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
        <div class="section-heading admin-section-heading">
          <div>
            <p class="section-kicker">Funil por campanha</p>
            <h2>Resumo agrupado</h2>
          </div>
          <span><?= count($rows) ?> linhas</span>
        </div>
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

      <section class="admin-panel">
        <div class="section-heading admin-section-heading">
          <div>
            <p class="section-kicker">Aprovação financeira</p>
            <h2><?= count($details) ?> conversões encontradas</h2>
          </div>
          <span>Sincroniza a carteira do afiliado</span>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table admin-report-table affiliate-conversion-table">
            <thead>
              <tr>
                <th>Data</th>
                <th>Campanha</th>
                <th>Afiliado</th>
                <th>Pedido/TID</th>
                <th>Venda</th>
                <th>Comissão</th>
                <th>Status</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($details as $row): ?>
                <tr>
                  <td><?= e($row['created_at'] ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-') ?></td>
                  <td><strong><?= e($row['advertiser']) ?></strong><br /><span><?= e($row['campaign_title']) ?></span><br /><small><?= e($row['network']) ?></small></td>
                  <td><?= e($row['affiliate_name'] ?: 'Sem afiliado') ?><br /><span><?= e($row['affiliate_email'] ?: '-') ?></span></td>
                  <td><strong><?= e($row['order_id'] ?: '-') ?></strong><br /><small><?= e($row['tid']) ?></small></td>
                  <td>R$ <?= number_format((float) $row['value'], 2, ',', '.') ?></td>
                  <td>R$ <?= number_format((float) $row['commission_amount'], 2, ',', '.') ?></td>
                  <td><span class="status-pill status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                  <td>
                    <div class="admin-inline-actions">
                      <?php foreach (['approved' => 'Aprovar', 'rejected' => 'Reprovar', 'paid' => 'Pagar'] as $status => $label): ?>
                        <form method="post">
                          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
                          <input type="hidden" name="conversion_id" value="<?= (int) $row['id'] ?>" />
                          <input type="hidden" name="status" value="<?= e($status) ?>" />
                          <button type="submit" <?= $row['status'] === $status ? 'disabled' : '' ?>><?= e($label) ?></button>
                        </form>
                      <?php endforeach; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$details): ?>
                <tr><td colspan="8" class="admin-empty-cell">Nenhuma conversão detalhada encontrada neste período.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php admin_layout_end(); ?>
