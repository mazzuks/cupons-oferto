<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/integrations.php';
require_once __DIR__ . '/layout.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    mark_notifications_read();
    redirect('notificacoes.php');
}

$notifications = admin_notifications(80);
$unread = unread_notification_count();
?>
<?php admin_layout_start('Notificacoes - Oferto Cupons', 'notificacoes', 'Alertas'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Notificacoes</p>
          <h1>Alertas das integracoes</h1>
          <p>Veja campanhas que sumiram do feed, erros de sincronizacao e avisos importantes das APIs.</p>
        </div>
        <form method="post" class="admin-hero-actions">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
          <button class="admin-primary-link" type="submit">Marcar como lidas</button>
        </form>
      </section>

      <section class="admin-kpi-grid">
        <article class="admin-kpi-card">
          <span>Nao lidas</span>
          <strong><?= (int) $unread ?></strong>
          <small>Alertas pendentes</small>
        </article>
        <article class="admin-kpi-card">
          <span>Total recente</span>
          <strong><?= count($notifications) ?></strong>
          <small>Ultimos alertas do sistema</small>
        </article>
      </section>

      <section class="admin-panel">
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Status</th>
                <th>Alerta</th>
                <th>Parceiro</th>
                <th>Quando</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($notifications as $notification): ?>
                <tr>
                  <td><span class="status-pill <?= empty($notification['read_at']) ? 'status-pausado' : 'status-rascunho' ?>"><?= empty($notification['read_at']) ? 'novo' : 'lido' ?></span></td>
                  <td><strong><?= e($notification['title']) ?></strong><br /><span><?= e($notification['body']) ?></span></td>
                  <td><?= e($notification['partner'] ?: '-') ?></td>
                  <td><?= e(date('d/m/Y H:i', strtotime($notification['created_at']))) ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$notifications): ?>
                <tr><td colspan="4" class="admin-empty-cell">Nenhuma notificacao por enquanto.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php admin_layout_end(); ?>
