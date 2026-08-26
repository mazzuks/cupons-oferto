<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/coupons.php';
require_once __DIR__ . '/layout.php';

require_admin();

$logs = system_logs(150);
?>
<?php admin_layout_start('Logs - Oferto Cupons', 'logs', 'Sistema'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Logs do sistema</p>
          <h1>Alteracoes e eventos recentes</h1>
          <p>Acompanhe cadastros, importacoes, sincronizacoes e alertas importantes sem depender de memoria ou conversa solta.</p>
        </div>
      </section>

      <section class="admin-panel">
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Quando</th>
                <th>Tipo</th>
                <th>Evento</th>
                <th>Parceiro</th>
                <th>Referencia</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $log): ?>
                <tr>
                  <td><?= e(date('d/m/Y H:i', strtotime($log['created_at']))) ?></td>
                  <td><span class="admin-pill"><?= e($log['type']) ?></span></td>
                  <td><strong><?= e($log['title']) ?></strong><br /><span><?= e($log['body']) ?></span></td>
                  <td><?= e($log['partner'] ?: '-') ?></td>
                  <td><?= e($log['external_id'] ?: '-') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$logs): ?>
                <tr><td colspan="5" class="admin-empty-cell">Nenhum log registrado por enquanto.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php admin_layout_end(); ?>
