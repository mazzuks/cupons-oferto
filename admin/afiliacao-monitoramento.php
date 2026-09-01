<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/affiliation.php';
require_once __DIR__ . '/layout.php';

require_admin();

$offers = affiliation_watch_rows();
$brands = affiliation_brand_rows();
?>
<?php admin_layout_start('Monitoramento de afiliacao - Oferto Cupons', 'afiliacao', 'Afiliacao'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Monitoramento</p>
          <h1>Campanhas e marcas acompanhadas</h1>
          <p>Acompanhe o que a cron deve procurar nas redes e o que sumiu do feed mais recente.</p>
        </div>
        <div class="admin-hero-actions">
          <a class="admin-secondary-link" href="notificacoes.php">Ver alertas</a>
        </div>
      </section>

      <?php admin_affiliation_subnav('monitoramento'); ?>

      <section class="admin-panel">
        <div class="section-heading admin-section-heading">
          <div>
            <p class="section-kicker">Ofertas monitoradas</p>
            <h2><?= count($offers) ?> registros</h2>
          </div>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Parceiro</th>
                <th>Campanha</th>
                <th>Status</th>
                <th>Ultima leitura</th>
                <th>Ausente desde</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($offers as $row): ?>
                <tr>
                  <td><?= e($row['partner']) ?></td>
                  <td><strong><?= e($row['store']) ?></strong><br /><span><?= e($row['title']) ?></span></td>
                  <td><span class="status-pill status-<?= $row['status'] === 'sumiu' ? 'pausado' : 'ativo' ?>"><?= e($row['status']) ?></span></td>
                  <td><?= e($row['last_seen_at'] ? date('d/m/Y H:i', strtotime($row['last_seen_at'])) : '-') ?></td>
                  <td><?= e($row['missing_since'] ? date('d/m/Y H:i', strtotime($row['missing_since'])) : '-') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$offers): ?>
                <tr><td colspan="5" class="admin-empty-cell">Nenhuma campanha monitorada ainda.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <section class="admin-panel">
        <div class="section-heading admin-section-heading">
          <div>
            <p class="section-kicker">Marcas monitoradas</p>
            <h2><?= count($brands) ?> marcas</h2>
          </div>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Parceiro</th>
                <th>Marca</th>
                <th>Segmento</th>
                <th>Status</th>
                <th>Ultima leitura</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($brands as $row): ?>
                <tr>
                  <td><?= e($row['partner']) ?></td>
                  <td><strong><?= e($row['brand_name']) ?></strong><br /><span><?= e($row['brand_id']) ?></span></td>
                  <td><?= e($row['segment'] ?: '-') ?></td>
                  <td><span class="status-pill status-ativo"><?= e($row['status']) ?></span></td>
                  <td><?= e($row['last_seen_at'] ? date('d/m/Y H:i', strtotime($row['last_seen_at'])) : '-') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$brands): ?>
                <tr><td colspan="5" class="admin-empty-cell">Nenhuma marca monitorada ainda.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php admin_layout_end(); ?>

