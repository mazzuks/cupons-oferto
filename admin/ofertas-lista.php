<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/coupons.php';
require_once __DIR__ . '/layout.php';

require_admin();

$error = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $action = $_POST['action'] ?? '';

        if ($action === 'delete') {
            $deleted = coupon_by_id((int) $_POST['id']);
            delete_coupon((int) $_POST['id']);
            create_system_log('coupon_deleted', 'Campanha excluida', ($deleted['store'] ?? 'Oferta') . ' - ' . ($deleted['title'] ?? 'campanha') . ' foi removida do CRM.');
            redirect('ofertas-lista.php?deleted=1');
        }

        if ($action === 'clear_campaigns') {
            $count = clear_campaign_data();
            create_system_log('campaigns_cleared', 'Campanhas limpas', $count . ' campanhas foram removidas da base pelo CRM.');
            redirect('ofertas-lista.php?cleared=' . $count);
        }
    }
} catch (Throwable $exception) {
    $error = $exception->getMessage();
}

$coupons = all_coupons();
?>
<?php admin_layout_start('Ofertas cadastradas - Oferto Cupons', 'ofertas'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">CRM Oferto</p>
          <h1>Ofertas criadas</h1>
          <p>Consulte o que já está na base, revise status, validade, tracking, banner e parceiro. Para cadastrar uma nova oferta, use a tela de criação.</p>
        </div>
        <div class="admin-hero-stats" aria-label="Resumo do CRM">
          <span><strong><?= count($coupons) ?></strong> ofertas</span>
          <span><strong><?= count(array_filter($coupons, fn ($coupon) => ($coupon['status'] ?? '') === 'ativo')) ?></strong> ativas</span>
        </div>
      </section>

      <?php admin_offers_subnav('lista'); ?>

      <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
      <?php if (isset($_GET['saved'])): ?><p class="admin-success">Oferta salva.</p><?php endif; ?>
      <?php if (isset($_GET['deleted'])): ?><p class="admin-success">Oferta excluida.</p><?php endif; ?>
      <?php if (isset($_GET['cleared'])): ?><p class="admin-success"><?= (int) $_GET['cleared'] ?> campanhas removidas da base.</p><?php endif; ?>
      <?php if (isset($_GET['imported'])): ?><p class="admin-success"><?= (int) $_GET['imported'] ?> campanhas importadas.</p><?php endif; ?>

      <section class="admin-panel admin-campaign-list">
        <div class="admin-panel-title-row">
          <div>
            <p class="section-kicker">Lista operacional</p>
            <h2>Campanhas cadastradas</h2>
            <p><?= count($coupons) ?> itens na base, entre cupons, sorteios, cadastros e ofertas diretas.</p>
          </div>
          <a href="index.php" class="admin-primary-link">Criar nova oferta</a>
        </div>

        <?php if (count($coupons) > 0): ?>
          <form method="post" class="admin-clear-form" onsubmit="return confirm('Apagar todas as campanhas cadastradas? Esta acao nao apaga usuarios, chaves de API nem marcas monitoradas.');">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
            <input type="hidden" name="action" value="clear_campaigns" />
            <button type="submit">Limpar campanhas cadastradas</button>
            <span>Remove apenas as campanhas cadastradas. Usuarios, APIs, historico e marcas monitoradas continuam salvos.</span>
          </form>
        <?php endif; ?>

        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Loja</th>
                <th>Logo</th>
                <th>Tipo</th>
                <th>Resgate</th>
                <th>Categoria</th>
                <th>Status</th>
                <th>Acesso</th>
                <th>Tracking</th>
                <th>Banner</th>
                <th>Parceiro</th>
                <th>Validade</th>
                <th>Acoes</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($coupons as $coupon): ?>
                <tr>
                  <td><strong><?= e($coupon['store']) ?></strong><br /><span><?= e($coupon['title']) ?></span></td>
                  <td>
                    <span class="admin-brand-logo" aria-label="Logo <?= e($coupon['store']) ?>">
                      <?php if (coupon_logo_src($coupon)): ?>
                        <img src="<?= e(coupon_logo_src($coupon)) ?>" alt="" />
                      <?php else: ?>
                        <?= e(coupon_brand_initials($coupon)) ?>
                      <?php endif; ?>
                    </span>
                  </td>
                  <td><span class="admin-pill admin-pill-type"><?= e(offer_type_label($coupon['offer_type'] ?? 'cupom')) ?></span></td>
                  <td><span class="admin-pill"><?= e(redemption_type_label($coupon['redemption_type'] ?? 'texto')) ?></span></td>
                  <td><?= e($coupon['category']) ?></td>
                  <td><span class="status-pill status-<?= e($coupon['status']) ?>"><?= e($coupon['status']) ?></span></td>
                  <td><span class="admin-pill <?= (int) ($coupon['members_only'] ?? 0) === 1 ? 'admin-pill-locked' : '' ?>"><?= (int) ($coupon['members_only'] ?? 0) === 1 ? 'Conectados' : 'Publico' ?></span></td>
                  <td><span class="status-pill <?= e(coupon_tracking_status_class($coupon)) ?>"><?= e(coupon_tracking_label($coupon)) ?></span></td>
                  <td><span class="admin-pill <?= coupon_uses_fallback_banner($coupon) ? 'admin-pill-fallback' : '' ?>"><?= e(coupon_banner_status_label($coupon)) ?></span></td>
                  <td><?= e($coupon['partner_network'] ?? '') ?></td>
                  <td><?= e(date('d/m/Y', strtotime($coupon['ends_at']))) ?></td>
                  <td class="row-actions">
                    <a href="index.php?edit=<?= (int) $coupon['id'] ?>">Editar</a>
                    <form method="post" onsubmit="return confirm('Excluir esta oferta?');">
                      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
                      <input type="hidden" name="action" value="delete" />
                      <input type="hidden" name="id" value="<?= (int) $coupon['id'] ?>" />
                      <button type="submit">Excluir</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$coupons): ?>
                <tr><td colspan="12" class="admin-empty-cell">Nenhuma oferta cadastrada ainda.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php admin_layout_end(); ?>
