<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/affiliation.php';
require_once __DIR__ . '/layout.php';

require_admin();

$rows = affiliation_partner_rows();
?>
<?php admin_layout_start('Parceiros afiliados - Oferto Cupons', 'afiliacao', 'Afiliação'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Parceiros</p>
          <h1>Afiliados separados da vitrine de cupons</h1>
          <p>Esta subguia acompanha quem pode receber smartlinks, com cliques, conversões e carteira próprios. O leitor do site continua vendo cupons; o parceiro afiliado fica nesta camada operacional.</p>
        </div>
      </section>

      <?php admin_affiliation_subnav('parceiros'); ?>

      <section class="admin-panel">
        <div class="section-heading admin-section-heading">
          <div>
            <p class="section-kicker">Cadastro operacional</p>
            <h2><?= count($rows) ?> parceiros encontrados</h2>
          </div>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table admin-report-table">
            <thead>
              <tr>
                <th>Parceiro</th>
                <th>Status</th>
                <th>Pagamento</th>
                <th>Cliques</th>
                <th>Conversões</th>
                <th>Ganhos</th>
                <th>Saques</th>
                <th>Último clique</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td><strong><?= e($row['name']) ?></strong><br /><span><?= e($row['email']) ?></span></td>
                  <td><span class="status-pill status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                  <td><?= e($row['payment_method'] ?: '-') ?></td>
                  <td><strong><?= (int) $row['click_count'] ?></strong></td>
                  <td><?= (int) $row['conversion_count'] ?></td>
                  <td>R$ <?= number_format((float) $row['total_earned'], 2, ',', '.') ?></td>
                  <td>R$ <?= number_format((float) $row['total_withdrawn'], 2, ',', '.') ?></td>
                  <td><?= e($row['last_click_at'] ? date('d/m/Y H:i', strtotime($row['last_click_at'])) : '-') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$rows): ?>
                <tr><td colspan="8" class="admin-empty-cell">Nenhum parceiro afiliado cadastrado ainda.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php admin_layout_end(); ?>
