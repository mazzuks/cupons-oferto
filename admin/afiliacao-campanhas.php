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
<?php admin_layout_start('Campanhas afiliadas - Oferto Cupons', 'afiliacao', 'Afiliacao'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Campanhas apartadas</p>
          <h1>Ofertas vindas de afiliacao</h1>
          <p>Use esta tela para separar campanhas importadas por rede das ofertas criadas manualmente no CRM.</p>
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
              <?php foreach (['ativo', 'rascunho', 'pausado'] as $status): ?>
                <option value="<?= e($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>Busca<input name="q" value="<?= e($filters['q']) ?>" placeholder="Loja, titulo, categoria ou ID" /></label>
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
          <a class="admin-secondary-link" href="index.php">Criar manual</a>
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
                <th>Codigo</th>
                <th>Cliques</th>
                <th>Conversoes</th>
                <th>Comissao</th>
                <th>Acoes</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td>
                    <strong><?= e($row['store']) ?></strong><br />
                    <span><?= e($row['title']) ?></span><br />
                    <small><?= e($row['external_id'] ?: '-') ?></small>
                  </td>
                  <td><?= e($row['partner_network'] ?: 'Sem parceiro') ?></td>
                  <td><span class="status-pill status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                  <td><span class="admin-pill admin-pill-type"><?= e(offer_type_label($row['offer_type'] ?? 'cupom')) ?></span></td>
                  <td><?= e(date('d/m/Y', strtotime($row['starts_at']))) ?> a <?= e(date('d/m/Y', strtotime($row['ends_at']))) ?></td>
                  <td><?= e($row['code'] ?: '-') ?></td>
                  <td><strong><?= (int) $row['click_count'] ?></strong></td>
                  <td><?= (int) $row['conversion_count'] ?></td>
                  <td>R$ <?= number_format((float) $row['commission_total'], 2, ',', '.') ?></td>
                  <td>
                    <a class="admin-primary-link" href="index.php?edit=<?= (int) $row['id'] ?>">Editar</a>
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

