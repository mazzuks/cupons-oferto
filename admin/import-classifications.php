<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/layout.php';

require_admin();

$error = '';
$success = '';
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        if (empty($_FILES['classification_csv']['name'])) {
            throw new RuntimeException('Envie o CSV com as classificacoes.');
        }

        $file = $_FILES['classification_csv'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Falha no upload do CSV.');
        }

        if ($file['size'] > 1024 * 1024) {
            throw new RuntimeException('CSV muito grande. Use arquivo de ate 1MB.');
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            throw new RuntimeException('Use um arquivo .csv.');
        }

        $pdo = db();
        if (!$pdo) {
            throw new RuntimeException('Banco de dados indisponivel.');
        }

        $result = import_offer_classifications($pdo, $file['tmp_name']);
        $success = sprintf(
            'Classificacao importada: %d ofertas lidas, %d atualizadas e %d lojas mapeadas.',
            $result['rows'] ?? 0,
            $result['updated'] ?? 0,
            $result['mapped_stores'] ?? 0
        );
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}
?>
<?php admin_layout_start('Classificacao de ofertas - Oferto Cupons', 'afiliacao', 'Classificacao'); ?>
      <section class="admin-hero admin-api-hero">
        <div>
          <p class="section-kicker">Nichos e tags</p>
          <h1>Atualize a inteligencia das ofertas por CSV</h1>
          <p>Importe a planilha classificada para preencher o nicho principal e as tags de produto que alimentam a busca, a API e o bot no WhatsApp.</p>
        </div>
        <div class="admin-hero-actions">
          <a class="admin-secondary-link" href="apis.php">Voltar para APIs</a>
        </div>
      </section>

      <?php admin_affiliation_subnav('classificacao'); ?>

      <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="admin-success"><?= e($success) ?></p><?php endif; ?>

      <div class="admin-api-workspace">
        <section class="admin-panel admin-api-card">
          <div class="admin-panel-title-row">
            <div>
              <p class="section-kicker">Upload</p>
              <h2>CSV de classificacao</h2>
              <p>O arquivo pode vir com as colunas id, loja, categoria_atual, nicho_principal e titulo. Se tags_produto vier vazia ou ausente, o CRM cria tags a partir da oferta.</p>
            </div>
          </div>

          <form method="post" enctype="multipart/form-data" class="coupon-admin-form admin-inline-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
            <label>Arquivo CSV
              <input name="classification_csv" type="file" accept=".csv,text/csv" required />
            </label>
            <div class="admin-actions">
              <button type="submit">Importar classificacao</button>
            </div>
          </form>
        </section>

        <section class="admin-panel admin-api-card">
          <div class="admin-panel-title-row">
            <div>
              <p class="section-kicker">Como funciona</p>
              <h2>O que a importacao altera</h2>
              <p>O CRM atualiza cada oferta pelo id do cupom e salva um dicionario por loja na tabela mapa_loja_nicho. Esse mapa ajuda as proximas sincronizacoes a classificar novas ofertas sem depender de ajuste manual.</p>
            </div>
          </div>

          <div class="admin-api-roadmap">
            <span>coupons.nicho_principal recebe o nicho da linha.</span>
            <span>coupons.tags_produto recebe tags do CSV ou tags geradas automaticamente.</span>
            <span>mapa_loja_nicho guarda o nicho e as tags mais comuns por loja.</span>
          </div>
        </section>
      </div>
<?php admin_layout_end(); ?>
