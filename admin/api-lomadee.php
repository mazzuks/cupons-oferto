<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/integrations.php';
require_once __DIR__ . '/layout.php';

require_admin();

$error = '';
$success = '';
$importResult = null;
$previewResult = null;
$brandOptions = [];

function posted_array(string $key): array
{
    return array_values(array_filter(array_map('strval', (array) ($_POST[$key] ?? []))));
}

function posted_filters(): array
{
    return [
        'max_pages' => (int) ($_POST['max_pages'] ?? 20),
        'types' => posted_array('types'),
        'categories' => posted_array('categories'),
        'brand_query' => trim((string) ($_POST['brand_query'] ?? '')),
        'brand_ids' => posted_array('brand_ids'),
        'excluded_terms' => trim((string) ($_POST['excluded_terms'] ?? 'BANNERS:')),
        'publish_status' => (string) ($_POST['publish_status'] ?? 'rascunho'),
        'selected_external_ids' => posted_array('selected_external_ids'),
    ];
}

$filters = lomadee_normalize_filters(integration_profile('lomadee', [
    'max_pages' => 20,
    'types' => array_keys(lomadee_campaign_type_options()),
    'categories' => [],
    'excluded_terms' => 'BANNERS:',
    'publish_status' => 'rascunho',
]));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $filters = lomadee_normalize_filters(posted_filters());

    try {
        if ($action === 'save_lomadee_key') {
            $apiKey = trim((string) ($_POST['lomadee_api_key'] ?? ''));
            if ($apiKey === '') {
                throw new RuntimeException('Informe a chave da Lomadee.');
            }

            save_integration_setting('lomadee_api_key', $apiKey);
            $success = 'Chave da Lomadee salva.';
        }

        if ($action === 'preview_lomadee' || $action === 'import_lomadee') {
            $brandOptions = lomadee_brand_options($filters['brand_query'], 30);
        }

        if ($action === 'save_lomadee_defaults') {
            save_integration_profile('lomadee', $filters);
            $success = 'Padrao da Lomadee salvo para proximas buscas e sincronizacoes.';
        }

        if ($action === 'preview_lomadee') {
            $previewResult = lomadee_preview_campaigns($filters);
            $success = 'Previa carregada. Selecione o que vale entrar no Oferto.';
        }

        if ($action === 'import_lomadee') {
            if (!$filters['selected_external_ids']) {
                throw new RuntimeException('Selecione pelo menos uma campanha para importar.');
            }

            $importResult = lomadee_import_campaigns($filters['max_pages'], $filters);
            $previewResult = lomadee_preview_campaigns($filters);
            $success = 'Importacao da Lomadee concluida.';
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$savedKey = lomadee_api_key();
$maskedKey = $savedKey === '' ? 'Nao configurada' : substr($savedKey, 0, 14) . str_repeat('*', 12) . substr($savedKey, -6);
?>
<?php admin_layout_start('Lomadee - Oferto Cupons', 'apis', 'Lomadee'); ?>
      <section class="admin-hero admin-api-hero">
        <div>
          <p class="section-kicker">Lomadee</p>
          <h1>Curadoria de cupons e ofertas</h1>
          <p>Busque marcas, filtre segmentos e importe apenas as campanhas que combinam com a vitrine do Oferto.</p>
        </div>
        <div class="admin-hero-actions">
          <a class="admin-secondary-link" href="apis.php">Voltar para APIs</a>
        </div>
      </section>

      <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="admin-success"><?= e($success) ?></p><?php endif; ?>

      <div class="admin-api-workspace">
        <section class="admin-panel admin-api-card">
          <div class="admin-panel-title-row">
            <div>
              <p class="section-kicker">Credencial</p>
              <h2>Chave da Lomadee</h2>
              <p>A chave fica salva no servidor e nao aparece no site publico nem no GitHub.</p>
            </div>
            <span class="admin-pill"><?= e($maskedKey) ?></span>
          </div>

          <form method="post" class="coupon-admin-form admin-inline-form">
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

        <section class="admin-panel admin-api-card">
          <div class="admin-panel-title-row">
            <div>
              <p class="section-kicker">Busca</p>
              <h2>Pesquisar campanhas</h2>
              <p>Use filtros antes de importar. Marcas como China in Box podem estar mais para frente no feed.</p>
            </div>
          </div>

          <form method="post" class="coupon-admin-form admin-api-filter-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />

            <div class="admin-two-cols admin-two-cols-wide-left">
              <label>Buscar marca
                <input name="brand_query" value="<?= e($filters['brand_query']) ?>" placeholder="Ex: China in Box, Pizza Hut, seguro..." />
                <small>Se preencher, a busca procura marca, segmento, titulo e descricao.</small>
              </label>
              <label>Paginas do feed
                <input name="max_pages" type="number" min="1" max="50" value="<?= (int) $filters['max_pages'] ?>" />
                <small>Cada pagina traz ate 20 itens.</small>
              </label>
            </div>

            <fieldset class="admin-choice-field">
              <legend>Tipos de oferta</legend>
              <div class="admin-segmented admin-segmented-compact">
                <?php foreach (lomadee_campaign_type_options() as $value => $label): ?>
                  <label>
                    <input type="checkbox" name="types[]" value="<?= e($value) ?>" <?= in_array($value, $filters['types'], true) ? 'checked' : '' ?> />
                    <span><?= e($label) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </fieldset>

            <fieldset class="admin-choice-field">
              <legend>Segmentos do Oferto</legend>
              <div class="admin-segmented admin-segmented-compact">
                <?php foreach (lomadee_category_options() as $category): ?>
                  <label>
                    <input type="checkbox" name="categories[]" value="<?= e($category) ?>" <?= in_array($category, $filters['categories'], true) ? 'checked' : '' ?> />
                    <span><?= e($category) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </fieldset>

            <?php if ($brandOptions): ?>
              <fieldset class="admin-choice-field">
                <legend>Marcas encontradas</legend>
                <div class="admin-brand-picker">
                  <?php foreach ($brandOptions as $brand): ?>
                    <label>
                      <input type="checkbox" name="brand_ids[]" value="<?= e($brand['id']) ?>" <?= in_array($brand['id'], $filters['brand_ids'], true) ? 'checked' : '' ?> />
                      <span>
                        <strong><?= e($brand['name']) ?></strong>
                        <?php if ($brand['segment']): ?><small><?= e($brand['segment']) ?></small><?php endif; ?>
                      </span>
                    </label>
                  <?php endforeach; ?>
                </div>
              </fieldset>
            <?php endif; ?>

            <div class="admin-two-cols">
              <label>Ignorar termos
                <input name="excluded_terms" value="<?= e(implode(', ', $filters['excluded_terms'])) ?>" placeholder="BANNERS:, teste, material" />
                <small>Separe por virgula. Bom para remover materiais que nao sao ofertas.</small>
              </label>
              <label>Status ao importar
                <select name="publish_status">
                  <option value="rascunho" <?= $filters['publish_status'] === 'rascunho' ? 'selected' : '' ?>>Rascunho para revisar</option>
                  <option value="ativo" <?= $filters['publish_status'] === 'ativo' ? 'selected' : '' ?>>Publicar direto</option>
                </select>
                <small>Recomendado: revisar antes de publicar.</small>
              </label>
            </div>

            <div class="admin-actions">
              <button type="submit" name="action" value="preview_lomadee">Buscar campanhas</button>
              <button type="submit" name="action" value="save_lomadee_defaults">Salvar como padrao</button>
            </div>
          </form>
        </section>
      </div>

      <?php if ($importResult): ?>
        <section class="admin-kpi-grid admin-api-result-grid">
          <article class="admin-kpi-card"><span>Criadas</span><strong><?= (int) $importResult['created'] ?></strong></article>
          <article class="admin-kpi-card"><span>Atualizadas</span><strong><?= (int) $importResult['updated'] ?></strong></article>
          <article class="admin-kpi-card"><span>Ignoradas</span><strong><?= (int) $importResult['skipped'] ?></strong></article>
          <article class="admin-kpi-card"><span>Lidas no feed</span><strong><?= (int) $importResult['total'] ?></strong></article>
        </section>
      <?php endif; ?>

      <?php if ($previewResult): ?>
        <section class="admin-panel admin-api-preview">
          <div class="admin-panel-title-row">
            <div>
              <p class="section-kicker">Previa</p>
              <h2><?= (int) $previewResult['matched'] ?> campanhas selecionaveis</h2>
              <p>Foram lidos <?= (int) $previewResult['total'] ?> itens no feed. Importe somente o que combina com a estrategia do Oferto.</p>
            </div>
          </div>

          <form method="post" class="coupon-admin-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
            <input type="hidden" name="action" value="import_lomadee" />
            <input type="hidden" name="brand_query" value="<?= e($filters['brand_query']) ?>" />
            <input type="hidden" name="max_pages" value="<?= (int) $filters['max_pages'] ?>" />
            <input type="hidden" name="excluded_terms" value="<?= e(implode(', ', $filters['excluded_terms'])) ?>" />
            <input type="hidden" name="publish_status" value="<?= e($filters['publish_status']) ?>" />
            <?php foreach ($filters['types'] as $type): ?><input type="hidden" name="types[]" value="<?= e($type) ?>" /><?php endforeach; ?>
            <?php foreach ($filters['categories'] as $category): ?><input type="hidden" name="categories[]" value="<?= e($category) ?>" /><?php endforeach; ?>
            <?php foreach ($filters['brand_ids'] as $brandId): ?><input type="hidden" name="brand_ids[]" value="<?= e($brandId) ?>" /><?php endforeach; ?>

            <div class="admin-table-wrap">
              <table class="admin-table admin-api-table">
                <thead>
                  <tr>
                    <th>Importar</th>
                    <th>Marca e campanha</th>
                    <th>Tipo</th>
                    <th>Segmento</th>
                    <th>Resgate</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$previewResult['items']): ?>
                    <tr><td colspan="6" class="admin-empty-cell">Nenhuma campanha encontrada com estes filtros.</td></tr>
                  <?php endif; ?>

                  <?php foreach ($previewResult['items'] as $item): ?>
                    <tr>
                      <td>
                        <label class="admin-mini-check">
                          <input type="checkbox" name="selected_external_ids[]" value="<?= e($item['external_id']) ?>" checked />
                          <span>Selecionar</span>
                        </label>
                      </td>
                      <td>
                        <strong><?= e($item['store']) ?></strong><br />
                        <span><?= e($item['title']) ?></span>
                      </td>
                      <td><span class="admin-pill admin-pill-type"><?= e(offer_type_label($item['offer_type'])) ?></span></td>
                      <td><?= e($item['category']) ?></td>
                      <td><?= e(redemption_type_label($item['redemption_type'])) ?></td>
                      <td>
                        <span class="status-pill <?= $item['existing'] ? 'status-pausado' : 'status-rascunho' ?>">
                          <?= $item['existing'] ? 'atualiza' : 'novo' ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="admin-actions">
              <button type="submit" <?= !$previewResult['items'] ? 'disabled' : '' ?>>Importar selecionadas</button>
            </div>
          </form>
        </section>
      <?php endif; ?>
<?php admin_layout_end(); ?>
