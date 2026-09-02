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
$postbackExample = $rows ? affiliation_postback_preview((int) $rows[0]['id']) : 'https://cupons.oferto.digital/affiliate-postback.php?cid={campanha}&tid={tid}&order_id={order_id}&value={value}&commission={commission}&status={status}&currency=BRL&sig={hmac_sha256}';
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
            <p>O smartlink registra o clique antes de redirecionar. O postback recebe a conversão assinada e grava o resultado na tabela própria do módulo afiliado.</p>
          </div>
        </div>
        <div class="affiliate-copy-grid">
          <article>
            <span>Formato do smartlink</span>
            <code>https://cupons.oferto.digital/a.php?cid={campanha}&amp;aff={parceiro}</code>
            <button type="button" data-copy-value="https://cupons.oferto.digital/a.php?cid={campanha}&aff={parceiro}">Copiar formato</button>
          </article>
          <article>
            <span>Postback S2S</span>
            <code><?= e($postbackExample) ?></code>
            <button type="button" data-copy-value="<?= e($postbackExample) ?>">Copiar postback</button>
          </article>
          <article>
            <span>Assinatura</span>
            <code>HMAC-SHA256: cid|tid|order_id|value</code>
            <button type="button" data-copy-value="HMAC-SHA256 de cid|tid|order_id|value usando o postback_secret da campanha">Copiar regra</button>
          </article>
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
                <?php
                  $smartlink = affiliation_smartlink_preview((int) $row['id'], $selectedPartner ? (string) $selectedPartner['id'] : '{affiliate_id}');
                  $postback = affiliation_postback_preview((int) $row['id']);
                ?>
                <tr>
                  <td><strong><?= e($row['advertiser']) ?></strong><br /><span><?= e($row['title']) ?></span><br /><small><?= e($row['network']) ?></small></td>
                  <td><span class="status-pill status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                  <td><?= e($row['tracking_mode']) ?></td>
                  <td><?= e($row['redirect_mode']) ?></td>
                  <td><?= (int) $row['cookie_ttl_days'] ?> dias</td>
                  <td>
                    <small><?= e($smartlink) ?></small>
                    <div class="row-actions"><button type="button" data-copy-value="<?= e($smartlink) ?>">Copiar</button></div>
                  </td>
                  <td><small><?= e(substr((string) $row['postback_secret'], 0, 10)) ?>...</small></td>
                  <td>
                    <?= (int) $row['click_count'] ?> cliques<br /><?= (int) $row['conversion_count'] ?> conversões
                    <div class="row-actions"><button type="button" data-copy-value="<?= e($postback) ?>">Copiar postback</button></div>
                  </td>
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
