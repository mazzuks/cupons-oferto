<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/affiliation.php';
require_once __DIR__ . '/layout.php';

require_admin();

$filters = [
    'network' => trim((string) ($_GET['network'] ?? '')),
    'status' => trim((string) ($_GET['status'] ?? '')),
    'q' => trim((string) ($_GET['q'] ?? '')),
];
$networks = affiliation_network_options();
$rows = affiliation_campaign_rows($filters);
?>
<?php admin_layout_start('Campanhas afiliadas - Oferto Cupons', 'afiliacao', 'Afiliação'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Campanhas apartadas</p>
          <h1>Ofertas vindas de afiliação</h1>
          <p>Esta tela lê apenas a tabela de campanhas afiliadas. A vitrine pública de cupons fica em outra base e só aparece aqui quando for selecionada ou importada como operação de afiliação.</p>
        </div>
        <form class="admin-report-filter" method="get">
          <label>Rede
            <select name="network">
              <option value="">Todas</option>
              <?php foreach ($networks as $network): ?>
                <option value="<?= e($network) ?>" <?= $filters['network'] === $network ? 'selected' : '' ?>><?= e($network) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>Status
            <select name="status">
              <option value="">Todos</option>
              <?php foreach (affiliation_campaign_statuses() as $status => $label): ?>
                <option value="<?= e($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>Busca<input name="q" value="<?= e($filters['q']) ?>" placeholder="Loja, título, categoria ou ID" /></label>
          <button type="submit">Filtrar</button>
        </form>
      </section>

      <?php admin_affiliation_subnav('campanhas'); ?>

      <section class="admin-panel">
        <div class="section-heading admin-section-heading">
          <div>
            <p class="section-kicker">Lista operacional</p>
            <h2><?= count($rows) ?> campanhas encontradas</h2>
          </div>
          <a class="admin-secondary-link" href="afiliacao-selecionar.php">Selecionar cupons</a>
        </div>

        <div class="admin-table-wrap">
          <table class="admin-table admin-report-table">
            <thead>
              <tr>
                <th>Campanha</th>
                <th>Rede</th>
                <th>Status</th>
                <th>Tipo</th>
                <th>Validade</th>
                <th>Payout</th>
                <th>Cliques</th>
                <th>Conversões</th>
                <th>Comissão</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td>
                    <strong><?= e($row['advertiser']) ?></strong><br />
                    <span><?= e($row['title']) ?></span><br />
                    <small><?= e($row['external_id'] ?: '-') ?></small>
                  </td>
                  <td><?= e($row['network'] ?: 'manual') ?></td>
                  <td><span class="status-pill status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                  <td><span class="admin-pill admin-pill-type"><?= e($row['payout_model'] ?: 'campanha') ?></span></td>
                  <td><?= e($row['starts_at'] ? date('d/m/Y', strtotime($row['starts_at'])) : '-') ?> a <?= e($row['ends_at'] ? date('d/m/Y', strtotime($row['ends_at'])) : '-') ?></td>
                  <td><?= $row['payout'] !== null ? 'R$ ' . e(number_format((float) $row['payout'], 2, ',', '.')) : '-' ?></td>
                  <td><strong><?= (int) $row['click_count'] ?></strong></td>
                  <td><?= (int) $row['conversion_count'] ?></td>
                  <td>R$ <?= number_format((float) $row['commission_total'], 2, ',', '.') ?></td>
                  <td>
                    <?php if (!empty($row['published_coupon_id'])): ?>
                      <a class="admin-primary-link" href="index.php?edit=<?= (int) $row['published_coupon_id'] ?>">Cupom</a>
                    <?php else: ?>
                      <span class="admin-secondary-link">Interna</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$rows): ?>
                <tr><td colspan="10" class="admin-empty-cell">Nenhuma campanha afiliada encontrada com estes filtros.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php admin_layout_end(); ?>
