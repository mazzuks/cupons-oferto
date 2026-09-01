<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/affiliation.php';
require_once __DIR__ . '/layout.php';

require_admin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $created = affiliation_select_coupon_campaigns((array) ($_POST['coupon_ids'] ?? []));
        $success = $created . ' campanha(s) enviada(s) para o módulo de afiliação.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$rows = affiliation_candidate_coupon_rows();
?>
<?php admin_layout_start('Selecionar cupons para afiliação - Oferto Cupons', 'afiliacao', 'Afiliação'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Ponte controlada</p>
          <h1>Escolha o que vira campanha afiliada</h1>
          <p>Cupons e descontos não entram automaticamente no módulo de afiliação. Selecione apenas os itens que fazem sentido para tracking, payout, postback e relatório separado.</p>
        </div>
      </section>

      <?php admin_affiliation_subnav('selecionar'); ?>

      <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="admin-success"><?= e($success) ?></p><?php endif; ?>

      <form method="post" class="admin-panel">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
        <div class="section-heading admin-section-heading">
          <div>
            <p class="section-kicker">Vitrine pública</p>
            <h2><?= count($rows) ?> cupons disponíveis para avaliação</h2>
          </div>
          <button class="admin-primary-link" type="submit">Enviar selecionados</button>
        </div>

        <div class="admin-table-wrap">
          <table class="admin-table admin-report-table">
            <thead>
              <tr>
                <th>Selecionar</th>
                <th>Cupom/desconto</th>
                <th>Categoria</th>
                <th>Status público</th>
                <th>Rede original</th>
                <th>Afiliação</th>
                <th>Validade</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td>
                    <input type="checkbox" name="coupon_ids[]" value="<?= (int) $row['id'] ?>" <?= !empty($row['affiliate_campaign_id']) ? 'disabled' : '' ?> />
                  </td>
                  <td><strong><?= e($row['store']) ?></strong><br /><span><?= e($row['title']) ?></span></td>
                  <td><?= e($row['category']) ?></td>
                  <td><span class="status-pill status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                  <td><?= e($row['partner_network'] ?: 'manual') ?></td>
                  <td>
                    <?php if (!empty($row['affiliate_campaign_id'])): ?>
                      <span class="admin-pill admin-pill-type"><?= e($row['affiliate_status']) ?></span>
                    <?php else: ?>
                      <span>-</span>
                    <?php endif; ?>
                  </td>
                  <td><?= e(date('d/m/Y', strtotime($row['ends_at']))) ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$rows): ?>
                <tr><td colspan="7" class="admin-empty-cell">Nenhum cupom encontrado para selecionar.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </form>
<?php admin_layout_end(); ?>
