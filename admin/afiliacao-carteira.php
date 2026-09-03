<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/affiliation.php';
require_once __DIR__ . '/layout.php';

require_admin();

$rows = affiliation_wallet_rows();
?>
<?php admin_layout_start('Carteira de afiliados - Oferto Cupons', 'afiliacao', 'Afiliação'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Carteira</p>
          <h1>Ganhos, aprovações e saques dos parceiros</h1>
          <p>A carteira usa transações próprias do módulo afiliado. Isso evita confundir comissão de rede, cupom publicado e repasse para parceiro.</p>
        </div>
      </section>

      <?php admin_affiliation_subnav('carteira'); ?>

      <section class="admin-panel">
        <div class="section-heading admin-section-heading">
          <div>
            <p class="section-kicker">Financeiro afiliado</p>
            <h2><?= count($rows) ?> carteiras encontradas</h2>
          </div>
          <span>Saldo aprovado menos saques pagos</span>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table admin-report-table">
            <thead>
              <tr>
                <th>Parceiro</th>
                <th>Status</th>
                <th>Ganhos</th>
                <th>Pendente</th>
                <th>Aprovado</th>
                <th>Sacado</th>
                <th>Disponível</th>
                <th>Transações</th>
                <th>Última movimentação</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td><strong><?= e($row['name']) ?></strong><br /><span><?= e($row['email']) ?></span></td>
                  <td><span class="status-pill status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                  <td>R$ <?= number_format((float) $row['earnings'], 2, ',', '.') ?></td>
                  <td>R$ <?= number_format((float) $row['pending'], 2, ',', '.') ?></td>
                  <td>R$ <?= number_format((float) $row['approved'], 2, ',', '.') ?></td>
                  <td>R$ <?= number_format((float) $row['withdrawn'], 2, ',', '.') ?></td>
                  <td><strong>R$ <?= number_format((float) $row['available_balance'], 2, ',', '.') ?></strong></td>
                  <td><?= (int) $row['transaction_count'] ?></td>
                  <td><?= e($row['last_transaction_at'] ? date('d/m/Y H:i', strtotime($row['last_transaction_at'])) : '-') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$rows): ?>
                <tr><td colspan="9" class="admin-empty-cell">Nenhuma carteira afiliada movimentada ainda.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php admin_layout_end(); ?>
