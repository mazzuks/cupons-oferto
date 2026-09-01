<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/affiliation.php';
require_once __DIR__ . '/layout.php';

require_admin();

$rows = affiliation_tracking_rows();
?>
<?php admin_layout_start('Tracking de afiliação - Oferto Cupons', 'afiliacao', 'Afiliação'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Tracking e smartlinks</p>
          <h1>Links, cookies e postbacks por campanha</h1>
          <p>Esta subguia espelha a lógica do Platto: cada campanha afiliada tem modo de tracking, redirect, cookie, segredo de postback e métricas separados do clique público dos cupons.</p>
        </div>
      </section>

      <?php admin_affiliation_subnav('tracking'); ?>

      <section class="admin-panel">
        <div class="section-heading admin-section-heading">
          <div>
            <p class="section-kicker">Operação afiliada</p>
            <h2><?= count($rows) ?> campanhas com estrutura de tracking</h2>
          </div>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table admin-report-table">
            <thead>
              <tr>
                <th>Campanha</th>
                <th>Status</th>
                <th>Modo</th>
                <th>Redirect</th>
                <th>Cookie</th>
                <th>Smartlink previsto</th>
                <th>Postback secret</th>
                <th>Eventos</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td><strong><?= e($row['advertiser']) ?></strong><br /><span><?= e($row['title']) ?></span><br /><small><?= e($row['network']) ?></small></td>
                  <td><span class="status-pill status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                  <td><?= e($row['tracking_mode']) ?></td>
                  <td><?= e($row['redirect_mode']) ?></td>
                  <td><?= (int) $row['cookie_ttl_days'] ?> dias</td>
                  <td><small><?= e(affiliation_smartlink_preview((int) $row['id'])) ?></small></td>
                  <td><small><?= e(substr((string) $row['postback_secret'], 0, 10)) ?>...</small></td>
                  <td><?= (int) $row['click_count'] ?> cliques<br /><?= (int) $row['conversion_count'] ?> conversões</td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$rows): ?>
                <tr><td colspan="8" class="admin-empty-cell">Nenhuma campanha afiliada pronta para tracking ainda.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php admin_layout_end(); ?>
