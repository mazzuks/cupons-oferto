<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/affiliation.php';
require_once __DIR__ . '/layout.php';

require_admin();

$selectedPartnerId = isset($_GET['partner_id']) ? (int) $_GET['partner_id'] : 0;
$partners = affiliation_partner_options();
$selectedPartner = $selectedPartnerId > 0 ? affiliation_partner_by_id($selectedPartnerId) : null;
$rows = affiliation_tracking_rows();
?>
<?php admin_layout_start('Tracking de afiliação - Oferto Cupons', 'afiliacao', 'Afiliação'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Tracking e smartlinks</p>
          <h1>Links, cookies e postbacks por campanha</h1>
          <p>Esta subguia espelha a lógica do Platto: cada campanha afiliada tem modo de tracking, redirect, cookie, segredo de postback e métricas separados do clique público dos cupons.</p>
        </div>
        <form class="admin-report-filter" method="get">
          <label>Parceiro
            <select name="partner_id">
              <option value="">Placeholder</option>
              <?php foreach ($partners as $partner): ?>
                <option value="<?= (int) $partner['id'] ?>" <?= $selectedPartnerId === (int) $partner['id'] ? 'selected' : '' ?>>
                  <?= e($partner['name']) ?><?= $partner['status'] !== 'ativo' ? ' (pausado)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>
          <button type="submit">Gerar links</button>
        </form>
      </section>

      <?php admin_affiliation_subnav('tracking'); ?>

      <section class="admin-panel">
        <div class="admin-panel-title-row">
          <div>
            <p class="section-kicker">Contrato técnico</p>
            <h2>Redirect e postback</h2>
            <p>O smartlink usa `a.php?cid={campanha}&aff={parceiro}`. O postback deve chamar `affiliate-postback.php` com `cid`, `tid`, `order_id`, `value`, `commission`, `status`, `currency` e `sig`. A assinatura é HMAC-SHA256 de `cid|tid|order_id|value` usando o secret da campanha.</p>
          </div>
        </div>
      </section>

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
                <th>Smartlink</th>
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
                  <td><small><?= e(affiliation_smartlink_preview((int) $row['id'], $selectedPartner ? (string) $selectedPartner['id'] : '{affiliate_id}')) ?></small></td>
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
