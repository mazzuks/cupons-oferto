<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/integrations.php';
require_once __DIR__ . '/layout.php';

require_admin();

$error = '';
$success = '';
$previewResult = null;
$importResult = null;

function offer18_posted_array(string $key): array
{
    return array_values(array_filter(array_map('strval', (array) ($_POST[$key] ?? []))));
}

function offer18_posted_filters(): array
{
    return [
        'account_index' => (int) ($_POST['account_index'] ?? 0),
        'page' => (int) ($_POST['page'] ?? 1),
        'limit' => (int) ($_POST['limit'] ?? 100),
        'status' => (string) ($_POST['status'] ?? '1'),
        'query' => trim((string) ($_POST['query'] ?? '')),
        'model_affiliate' => trim((string) ($_POST['model_affiliate'] ?? '')),
        'categories' => offer18_posted_array('categories'),
        'excluded_terms' => trim((string) ($_POST['excluded_terms'] ?? offer18_default_excluded_terms())),
        'publish_status' => (string) ($_POST['publish_status'] ?? 'rascunho'),
        'selected_external_ids' => offer18_posted_array('selected_external_ids'),
    ];
}

$filters = offer18_normalize_filters(integration_profile('offer18', [
    'account_index' => 0,
    'page' => 1,
    'limit' => 100,
    'status' => '1',
    'excluded_terms' => offer18_default_excluded_terms(),
    'publish_status' => 'rascunho',
]));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $filters = offer18_normalize_filters(offer18_posted_filters());

    try {
        if ($action === 'save_offer18_account') {
            save_offer18_account([
                'label' => $_POST['offer18_label'] ?? '',
                'mid' => $_POST['offer18_mid'] ?? '',
                'api_key' => $_POST['offer18_api_key'] ?? '',
                'secret_key' => $_POST['offer18_secret_key'] ?? '',
                'affiliate_id' => $_POST['offer18_affiliate_id'] ?? '',
            ]);
            $success = 'Conta Offer18 salva.';
        }

        if ($action === 'preview_offer18') {
            $previewResult = offer18_preview_offers($filters);
            $success = 'Previa da Offer18 carregada.';
        }

        if ($action === 'save_offer18_defaults') {
            save_integration_profile('offer18', $filters);
            $success = 'Padrao da Offer18 salvo para proximas buscas e cron.';
        }

        if ($action === 'import_offer18') {
            if (!$filters['selected_external_ids']) {
                throw new RuntimeException('Selecione pelo menos uma oferta para importar.');
            }

            $importResult = offer18_import_offers($filters);
            $previewResult = offer18_preview_offers($filters);
            $success = 'Importacao da Offer18 concluida.';
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$accounts = offer18_accounts();
$monitoredBrands = monitored_integration_brands('Offer18');
?>
<?php admin_layout_start('Offer18 - Oferto Cupons', 'afiliacao', 'Offer18'); ?>
      <section class="admin-hero admin-api-hero">
        <div>
          <p class="section-kicker">Offer18</p>
          <h1>Campanhas de contas Offer18</h1>
          <p>Cadastre qualquer conta Offer18, busque ofertas aprovadas, filtre segmentos e importe para o Oferto com tracking preservado.</p>
        </div>
        <div class="admin-hero-actions">
          <a class="admin-secondary-link" href="apis.php">Voltar para APIs</a>
        </div>
      </section>

      <?php admin_affiliation_subnav('redes'); ?>

      <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="admin-success"><?= e($success) ?></p><?php endif; ?>

      <div class="admin-api-workspace">
        <section class="admin-panel admin-api-card">
          <div class="admin-panel-title-row">
            <div>
              <p class="section-kicker">Credenciais</p>
              <h2><?= count($accounts) ?> conta(s)</h2>
              <p>Use MID, API key e secret key da conta Offer18. As chaves ficam somente no servidor.</p>
            </div>
          </div>

          <div class="admin-api-roadmap">
            <?php foreach ($accounts as $account): ?>
              <span><?= e($account['label'] ?? 'Offer18') ?> - MID <?= e($account['mid'] ?? '') ?> - <?= e(offer18_mask((string) ($account['api_key'] ?? ''))) ?></span>
            <?php endforeach; ?>
            <?php if (!$accounts): ?>
              <span>Nenhuma conta Offer18 cadastrada ainda.</span>
            <?php endif; ?>
          </div>

          <form method="post" class="coupon-admin-form admin-inline-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
            <input type="hidden" name="action" value="save_offer18_account" />
            <label>Nome da conta<input name="offer18_label" placeholder="Ex: Adworks Offer18" /></label>
            <label>MID<input name="offer18_mid" inputmode="numeric" placeholder="Network Account ID" /></label>
            <label>API key<input name="offer18_api_key" type="password" autocomplete="off" /></label>
            <label>Secret key<input name="offer18_secret_key" type="password" autocomplete="off" /></label>
            <label>Affiliate ID opcional<input name="offer18_affiliate_id" inputmode="numeric" placeholder="Opcional para futuras chamadas affiliate" /></label>
            <div class="admin-actions">
              <button type="submit">Salvar conta</button>
            </div>
          </form>
        </section>

        <section class="admin-panel admin-api-card">
          <div class="admin-panel-title-row">
            <div>
              <p class="section-kicker">Marcas monitoradas</p>
              <h2><?= count($monitoredBrands) ?> marcas</h2>
              <p>Ao importar ofertas, marcas e campanhas entram no monitoramento da cron.</p>
            </div>
          </div>
          <div class="admin-api-roadmap">
            <?php foreach (array_slice($monitoredBrands, 0, 8) as $brand): ?>
              <span><?= e($brand['brand_name']) ?></span>
            <?php endforeach; ?>
            <?php if (!$monitoredBrands): ?>
              <span>Nenhuma marca Offer18 monitorada ainda.</span>
            <?php endif; ?>
          </div>
        </section>

        <section class="admin-panel admin-api-card">
          <div class="admin-panel-title-row">
            <div>
              <p class="section-kicker">Busca</p>
              <h2>Pesquisar campanhas</h2>
              <p>Filtre antes de importar. O site so publica campanhas com tracking Offer18 valido.</p>
            </div>
          </div>

          <form method="post" class="coupon-admin-form admin-api-filter-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />

            <div class="admin-two-cols admin-two-cols-wide-left">
              <label>Conta
                <select name="account_index">
                  <?php foreach ($accounts as $index => $account): ?>
                    <option value="<?= (int) $index ?>" <?= $filters['account_index'] === $index ? 'selected' : '' ?>><?= e($account['label'] ?? 'Offer18') ?> - MID <?= e($account['mid'] ?? '') ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>Buscar oferta
                <input name="query" value="<?= e($filters['query']) ?>" placeholder="Ex: notebook, seguro, moda..." />
              </label>
            </div>

            <fieldset class="admin-choice-field">
              <legend>Segmentos do Oferto</legend>
              <div class="admin-segmented admin-segmented-compact">
                <?php foreach (category_options() as $category): ?>
                  <label>
                    <input type="checkbox" name="categories[]" value="<?= e($category) ?>" <?= in_array($category, $filters['categories'], true) ? 'checked' : '' ?> />
                    <span><?= e($category) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </fieldset>

            <div class="admin-two-cols">
              <label>Status
                <select name="status">
                  <?php foreach (offer18_status_options() as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>Modelo
                <select name="model_affiliate">
                  <?php foreach (offer18_model_options() as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $filters['model_affiliate'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
            </div>

            <div class="admin-two-cols">
              <label>Pagina<input name="page" type="number" min="1" max="50" value="<?= (int) $filters['page'] ?>" /></label>
              <label>Itens por busca<input name="limit" type="number" min="10" max="200" value="<?= (int) $filters['limit'] ?>" /></label>
            </div>

            <div class="admin-two-cols">
              <label>Bloquear termos
                <input name="excluded_terms" value="<?= e(implode(', ', $filters['excluded_terms'])) ?>" />
              </label>
              <label>Status ao importar
                <select name="publish_status">
                  <option value="rascunho" <?= $filters['publish_status'] === 'rascunho' ? 'selected' : '' ?>>Rascunho para revisar</option>
                  <option value="ativo" <?= $filters['publish_status'] === 'ativo' ? 'selected' : '' ?>>Publicar direto</option>
                </select>
              </label>
            </div>

            <div class="admin-actions">
              <button type="submit" name="action" value="preview_offer18" <?= !$accounts ? 'disabled' : '' ?>>Buscar campanhas</button>
              <button type="submit" name="action" value="save_offer18_defaults" <?= !$accounts ? 'disabled' : '' ?>>Salvar como padrao</button>
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
              <p>Foram lidos <?= (int) $previewResult['total'] ?> itens na Offer18.</p>
            </div>
          </div>

          <form method="post" class="coupon-admin-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
            <input type="hidden" name="action" value="import_offer18" />
            <input type="hidden" name="account_index" value="<?= (int) $filters['account_index'] ?>" />
            <input type="hidden" name="page" value="<?= (int) $filters['page'] ?>" />
            <input type="hidden" name="limit" value="<?= (int) $filters['limit'] ?>" />
            <input type="hidden" name="status" value="<?= e($filters['status']) ?>" />
            <input type="hidden" name="query" value="<?= e($filters['query']) ?>" />
            <input type="hidden" name="model_affiliate" value="<?= e($filters['model_affiliate']) ?>" />
            <input type="hidden" name="excluded_terms" value="<?= e(implode(', ', $filters['excluded_terms'])) ?>" />
            <input type="hidden" name="publish_status" value="<?= e($filters['publish_status']) ?>" />
            <?php foreach ($filters['categories'] as $category): ?><input type="hidden" name="categories[]" value="<?= e($category) ?>" /><?php endforeach; ?>

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
                    <tr><td colspan="6" class="admin-empty-cell">Nenhuma campanha passou pelos filtros ou veio sem tracking Offer18 valido.</td></tr>
                  <?php endif; ?>
                  <?php foreach ($previewResult['items'] as $item): ?>
                    <tr>
                      <td>
                        <label class="admin-mini-check">
                          <input type="checkbox" name="selected_external_ids[]" value="<?= e($item['external_id']) ?>" checked />
                          <span>Selecionar</span>
                        </label>
                      </td>
                      <td><strong><?= e($item['store']) ?></strong><br /><span><?= e($item['title']) ?></span></td>
                      <td><span class="admin-pill admin-pill-type"><?= e(offer_type_label($item['offer_type'])) ?></span></td>
                      <td><?= e($item['category']) ?></td>
                      <td><?= e(redemption_type_label($item['redemption_type'])) ?></td>
                      <td><span class="status-pill <?= $item['existing'] ? 'status-pausado' : 'status-rascunho' ?>"><?= $item['existing'] ? 'atualiza' : 'novo' ?></span></td>
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
