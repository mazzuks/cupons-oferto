<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/integrations.php';
require_once __DIR__ . '/layout.php';

require_admin();

$error = '';
$success = '';
$importResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save_lomadee_key') {
            $apiKey = trim((string) ($_POST['lomadee_api_key'] ?? ''));
            if ($apiKey === '') {
                throw new RuntimeException('Informe a chave da Lomadee.');
            }

            save_integration_setting('lomadee_api_key', $apiKey);
            $success = 'Chave da Lomadee salva.';
        }

        if ($action === 'import_lomadee') {
            $pages = max(1, min(30, (int) ($_POST['max_pages'] ?? 10)));
            $importResult = lomadee_import_campaigns($pages);
            $success = 'Importacao da Lomadee concluida.';
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$savedKey = lomadee_api_key();
$maskedKey = $savedKey === '' ? 'Nao configurada' : substr($savedKey, 0, 14) . str_repeat('*', 12) . substr($savedKey, -6);
?>
<?php admin_layout_start('APIs - Oferto Cupons', 'apis', 'Integracoes'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">APIs e feeds</p>
          <h1>Importar cupons automaticamente</h1>
          <p>Conecte redes de afiliados para trazer cupons, promocoes e ofertas sem cadastrar tudo manualmente.</p>
        </div>
        <div class="admin-hero-stats" aria-label="Resumo da Lomadee">
          <span><strong>Lomadee</strong> primeira integracao</span>
        </div>
      </section>

      <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="admin-success"><?= e($success) ?></p><?php endif; ?>

      <div class="admin-layout admin-layout-users">
        <section class="admin-panel">
          <p class="section-kicker">Credencial</p>
          <h2>Chave da Lomadee</h2>
          <p>Salve a chave uma vez. Ela fica no servidor e nao aparece no site publico.</p>
          <p><span class="admin-pill"><?= e($maskedKey) ?></span></p>

          <form method="post" class="coupon-admin-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
            <input type="hidden" name="action" value="save_lomadee_key" />
            <label>API key
              <input name="lomadee_api_key" type="password" placeholder="Cole a chave da Lomadee" autocomplete="off" />
            </label>
            <div class="admin-actions">
              <button type="submit">Salvar chave</button>
            </div>
          </form>
        </section>

        <section class="admin-panel">
          <p class="section-kicker">Importacao</p>
          <h2>Cupons e ofertas ativas</h2>
          <p>Puxa campanhas ativas da Lomadee, cria novas ofertas e atualiza as que ja foram importadas.</p>

          <?php if ($importResult): ?>
            <div class="admin-kpi-grid">
              <article class="admin-kpi-card">
                <span>Criadas</span>
                <strong><?= (int) $importResult['created'] ?></strong>
              </article>
              <article class="admin-kpi-card">
                <span>Atualizadas</span>
                <strong><?= (int) $importResult['updated'] ?></strong>
              </article>
              <article class="admin-kpi-card">
                <span>Ignoradas</span>
                <strong><?= (int) $importResult['skipped'] ?></strong>
              </article>
            </div>
          <?php endif; ?>

          <form method="post" class="coupon-admin-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
            <input type="hidden" name="action" value="import_lomadee" />
            <label>Paginas para importar
              <input name="max_pages" type="number" min="1" max="30" value="10" />
              <small>Cada pagina traz ate 20 itens. Use 10 para uma importacao rapida ou 30 para puxar mais volume.</small>
            </label>
            <div class="admin-actions">
              <button type="submit">Importar da Lomadee</button>
            </div>
          </form>
        </section>
      </div>
<?php admin_layout_end(); ?>
