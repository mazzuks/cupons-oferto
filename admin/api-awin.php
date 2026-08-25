<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/integrations.php';
require_once __DIR__ . '/layout.php';

require_admin();

$error = '';
$success = '';
$awinResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save_awin_key' || $action === 'connect_awin') {
            $accessToken = trim((string) ($_POST['awin_access_token'] ?? ''));
            if ($accessToken !== '') {
                save_integration_setting('awin_access_token', $accessToken);
            }

            if (awin_access_token() === '') {
                throw new RuntimeException('Informe o token da Awin.');
            }

            if ($action === 'connect_awin') {
                $awinResult = awin_connect_first_publisher();
                $success = 'Awin conectada com sucesso.';
            } else {
                $success = 'Token da Awin salvo.';
            }
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$savedAwinToken = awin_access_token();
$maskedAwinToken = $savedAwinToken === '' ? 'Nao configurado' : substr($savedAwinToken, 0, 8) . str_repeat('*', 12) . substr($savedAwinToken, -6);
$awinPublisherId = awin_publisher_id();
$awinPublisherName = awin_publisher_name();
?>
<?php admin_layout_start('Awin - Oferto Cupons', 'apis', 'Awin'); ?>
      <section class="admin-hero admin-api-hero">
        <div>
          <p class="section-kicker">Awin</p>
          <h1>Conexao com o publisher</h1>
          <p>Primeiro conectamos a conta. Depois esta tela recebe busca de anunciantes, vouchers e ofertas da Awin.</p>
        </div>
        <div class="admin-hero-actions">
          <a class="admin-secondary-link" href="apis.php">Voltar para APIs</a>
        </div>
      </section>

      <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="admin-success"><?= e($success) ?></p><?php endif; ?>

      <div class="admin-api-workspace admin-api-workspace-split">
        <section class="admin-panel admin-api-card">
          <div class="admin-panel-title-row">
            <div>
              <p class="section-kicker">Credencial</p>
              <h2>Access token</h2>
              <p>O token usa Bearer authentication e fica salvo apenas no servidor.</p>
            </div>
            <span class="admin-pill"><?= e($maskedAwinToken) ?></span>
          </div>

          <?php if ($awinPublisherId !== ''): ?>
            <div class="admin-api-connected">
              <span>Publisher conectado</span>
              <strong><?= e($awinPublisherName ?: 'Conta Awin') ?></strong>
              <small>ID <?= e($awinPublisherId) ?></small>
            </div>
          <?php endif; ?>

          <?php if ($awinResult): ?>
            <div class="admin-api-connected">
              <span>Conexao validada</span>
              <strong><?= e($awinResult['publisher_name'] ?: 'Conta Awin') ?></strong>
              <small><?= e($awinResult['publisher_id']) ?> encontrado em <?= (int) $awinResult['accounts'] ?> conta(s)</small>
            </div>
          <?php endif; ?>

          <form method="post" class="coupon-admin-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
            <input type="hidden" name="action" value="connect_awin" />
            <label>Access token
              <input name="awin_access_token" type="password" placeholder="Cole o token da Awin" autocomplete="off" />
              <small>Ao integrar, o CRM consulta /accounts?type=publisher e salva o publisherId.</small>
            </label>
            <div class="admin-actions">
              <button type="submit">Integrar Awin</button>
            </div>
          </form>
        </section>

        <section class="admin-panel admin-api-card">
          <div class="admin-panel-title-row">
            <div>
              <p class="section-kicker">Proxima etapa</p>
              <h2>Campanhas da Awin</h2>
              <p>A tela dedicada permite evoluir sem misturar com a Lomadee.</p>
            </div>
          </div>

          <div class="admin-api-roadmap">
            <span>Buscar anunciantes por nome ou categoria</span>
            <span>Listar vouchers e promocoes do publisher</span>
            <span>Gerar tracking link quando a oferta precisar</span>
            <span>Importar selecionadas como rascunho ou ativo</span>
          </div>
        </section>
      </div>
<?php admin_layout_end(); ?>
