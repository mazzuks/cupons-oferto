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
$debugResult = null;

function hasoffers_posted_array(string $key): array
{
    return array_values(array_filter(array_map('strval', (array) ($_POST[$key] ?? []))));
}

function hasoffers_posted_filters(): array
{
    return [
        'account_index' => (int) ($_POST['account_index'] ?? 0),
        'page' => (int) ($_POST['page'] ?? 1),
        'limit' => (int) ($_POST['limit'] ?? 100),
        'status' => (string) ($_POST['status'] ?? 'active'),
        'query' => trim((string) ($_POST['query'] ?? '')),
        'categories' => hasoffers_posted_array('categories'),
        'excluded_terms' => trim((string) ($_POST['excluded_terms'] ?? hasoffers_default_excluded_terms())),
        'publish_status' => (string) ($_POST['publish_status'] ?? 'rascunho'),
        'selected_external_ids' => hasoffers_posted_array('selected_external_ids'),
    ];
}

$filters = hasoffers_normalize_filters(integration_profile('hasoffers', [
    'account_index' => 0,
    'page' => 1,
    'limit' => 100,
    'status' => 'active',
    'excluded_terms' => hasoffers_default_excluded_terms(),
    'publish_status' => 'rascunho',
]));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $filters = hasoffers_normalize_filters(hasoffers_posted_filters());

    try {
        if ($action === 'save_hasoffers_account') {
            save_hasoffers_account([
                'label' => $_POST['hasoffers_label'] ?? '',
                'network_id' => $_POST['hasoffers_network_id'] ?? '',
                'api_key' => $_POST['hasoffers_api_key'] ?? '',
                'affiliate_id' => $_POST['hasoffers_affiliate_id'] ?? '',
            ]);
            $success = 'Conta HasOffers salva.';
        }

        if ($action === 'preview_hasoffers') {
            $previewResult = hasoffers_preview_offers($filters);
            $success = 'Previa do HasOffers carregada.';
        }

        if ($action === 'debug_hasoffers') {
            $debugResult = hasoffers_debug_probe($filters);
            $success = 'Diagnostico bruto da HasOffers carregado.';
        }

        if ($action === 'save_hasoffers_defaults') {
            save_integration_profile('hasoffers', $filters);
            $success = 'Padrao do HasOffers salvo para proximas buscas e cron.';
        }

        if ($action === 'import_hasoffers') {
            if (!$filters['selected_external_ids']) {
                throw new RuntimeException('Selecione pelo menos uma campanha para importar.');
            }

            $importResult = hasoffers_import_offers($filters);
            $previewResult = hasoffers_preview_offers($filters);
            $success = 'Importacao do HasOffers concluida.';
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$accounts = hasoffers_accounts();
$monitoredBrands = monitored_integration_brands('HasOffers');
?>
<?php admin_layout_start('HasOffers - Oferto Cupons', 'afiliacao', 'HasOffers'); ?>
      <section class="admin-hero admin-api-hero">
        <div>
          <p class="section-kicker">HasOffers / TUNE</p>
          <h1>Campanhas de redes HasOffers</h1>
          <p>Cadastre qualquer Network ID, liste campanhas ativas da rede e importe o que fizer sentido publicar no Oferto.</p>
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
              <p>Use o Network ID da URL HasOffers e a API key de afiliado. As chaves ficam apenas no servidor.</p>
            </div>
          </div>

          <div class="admin-api-roadmap">
            <?php foreach ($accounts as $account): ?>
              <span><?= e($account['label'] ?? 'HasOffers') ?> - <?= e($account['network_id'] ?? '') ?> - <?= e(offer18_mask((string) ($account['api_key'] ?? ''))) ?></span>
            <?php endforeach; ?>
            <?php if (!$accounts): ?>
              <span>Nenhuma conta HasOffers cadastrada ainda.</span>
            <?php endif; ?>
          </div>

          <form method="post" class="coupon-admin-form admin-inline-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
            <input type="hidden" name="action" value="save_hasoffers_account" />
            <label>Nome da conta<input name="hasoffers_label" placeholder="Ex: Rede HasOffers" /></label>
            <label>Network ID<input name="hasoffers_network_id" placeholder="Ex: minharede" /></label>
            <label>API key<input name="hasoffers_api_key" type="password" autocomplete="off" /></label>
            <label>Affiliate ID opcional<input name="hasoffers_affiliate_id" inputmode="numeric" placeholder="Opcional" /></label>
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
              <p>Campanhas importadas entram no monitoramento da cron e geram aviso se sumirem.</p>
            </div>
          </div>
          <div class="admin-api-roadmap">
            <?php foreach (array_slice($monitoredBrands, 0, 8) as $brand): ?>
              <span><?= e($brand['brand_name']) ?></span>
            <?php endforeach; ?>
            <?php if (!$monitoredBrands): ?>
              <span>Nenhuma marca HasOffers monitorada ainda.</span>
            <?php endif; ?>
          </div>
        </section>

        <section class="admin-panel admin-api-card">
          <div class="admin-panel-title-row">
            <div>
              <p class="section-kicker">Busca</p>
              <h2>Pesquisar campanhas</h2>
              <p>Para listar tudo que estiver ativo, deixe busca, segmentos e termos bloqueados em branco.</p>
            </div>
          </div>

          <form method="post" class="coupon-admin-form admin-api-filter-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />

            <div class="admin-two-cols admin-two-cols-wide-left">
              <label>Conta
                <select name="account_index">
                  <?php foreach ($accounts as $index => $account): ?>
                    <option value="<?= (int) $index ?>" <?= $filters['account_index'] === $index ? 'selected' : '' ?>><?= e($account['label'] ?? 'HasOffers') ?> - <?= e($account['network_id'] ?? '') ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>Buscar campanha opcional
                <input name="query" value="<?= e($filters['query']) ?>" placeholder="Deixe em branco para trazer todas as ativas" />
              </label>
            </div>

            <fieldset class="admin-choice-field">
              <legend>Segmentos opcionais</legend>
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
                  <?php foreach (hasoffers_status_options() as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>Itens por busca<input name="limit" type="number" min="10" max="200" value="<?= (int) $filters['limit'] ?>" /></label>
            </div>

            <div class="admin-two-cols">
              <label>Pagina<input name="page" type="number" min="1" max="50" value="<?= (int) $filters['page'] ?>" /></label>
              <label>Status ao importar
                <select name="publish_status">
                  <option value="rascunho" <?= $filters['publish_status'] === 'rascunho' ? 'selected' : '' ?>>Rascunho para revisar</option>
                  <option value="ativo" <?= $filters['publish_status'] === 'ativo' ? 'selected' : '' ?>>Publicar direto</option>
                </select>
              </label>
            </div>

            <label>Bloquear termos opcional
              <input name="excluded_terms" value="<?= e(implode(', ', $filters['excluded_terms'])) ?>" placeholder="Deixe em branco para nao bloquear nenhuma campanha" />
            </label>

            <div class="admin-actions">
              <button type="submit" name="action" value="preview_hasoffers" <?= !$accounts ? 'disabled' : '' ?>>Buscar campanhas ativas</button>
              <button type="submit" name="action" value="debug_hasoffers" <?= !$accounts ? 'disabled' : '' ?>>Diagnostico bruto</button>
              <button type="submit" name="action" value="save_hasoffers_defaults" <?= !$accounts ? 'disabled' : '' ?>>Salvar como padrao</button>
            </div>
          </form>
        </section>
      </div>

      <?php if ($debugResult): ?>
        <section class="admin-panel admin-api-preview">
          <div class="admin-panel-title-row">
            <div>
              <p class="section-kicker">Diagnostico bruto</p>
              <h2>Retorno direto da HasOffers</h2>
              <p>Este bloco nao aplica importacao nem exige tracking. Ele serve para descobrir qual metodo da Ybox realmente retorna campanhas.</p>
            </div>
          </div>

          <div class="admin-table-wrap">
            <table class="admin-table admin-api-table">
              <thead>
                <tr>
                  <th>Metodo</th>
                  <th>Status filter</th>
                  <th>Itens</th>
                  <th>Chaves</th>
                  <th>Erro ou amostra</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($debugResult as $probe): ?>
                  <tr>
                    <td><strong><?= e($probe['target']) ?>::<?= e($probe['method']) ?></strong></td>
                    <td><?= $probe['with_status_filter'] ? 'sim' : 'nao' ?></td>
                    <td><span class="status-pill <?= (int) $probe['items'] > 0 ? 'status-ativo' : 'status-pausado' ?>"><?= (int) $probe['items'] ?></span></td>
                    <td><?= e($probe['response_keys']) ?><br /><span><?= e($probe['data_keys']) ?></span></td>
                    <td>
                      <?php if ($probe['error']): ?>
                        <span><?= e($probe['error']) ?></span>
                      <?php else: ?>
                        <pre class="admin-debug-json"><?= e(json_encode($probe['first_item'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)) ?></pre>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($importResult): ?>
        <section class="admin-kpi-grid admin-api-result-grid">
          <article class="admin-kpi-card"><span>Criadas</span><strong><?= (int) $importResult['created'] ?></strong></article>
          <article class="admin-kpi-card"><span>Atualizadas</span><strong><?= (int) $importResult['updated'] ?></strong></article>
          <article class="admin-kpi-card"><span>Ignoradas</span><strong><?= (int) $importResult['skipped'] ?></strong></article>
          <article class="admin-kpi-card"><span>Lidas no feed</span><strong><?= (int) $importResult['total'] ?></strong></article>
        </section>
        <?php if (!empty($importResult['diagnostics'])): ?>
          <section class="admin-panel admin-api-card">
            <p class="section-kicker">Diagnostico HasOffers</p>
            <h2>Por que algumas campanhas nao entraram</h2>
            <div class="admin-api-roadmap">
              <span><?= (int) ($importResult['diagnostics']['filtered_out'] ?? 0) ?> filtradas por busca, segmento opcional ou termos bloqueados</span>
              <span><?= (int) ($importResult['diagnostics']['missing_tracking'] ?? 0) ?> sem tracking link valido</span>
              <span><?= (int) ($importResult['diagnostics']['missing_offer_id'] ?? 0) ?> sem ID de campanha</span>
              <span><?= (int) ($importResult['diagnostics']['not_selected'] ?? 0) ?> nao selecionadas na importacao</span>
            </div>
          </section>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($previewResult): ?>
        <section class="admin-panel admin-api-preview">
          <div class="admin-panel-title-row">
            <div>
              <p class="section-kicker">Previa</p>
              <h2><?= (int) $previewResult['matched'] ?> campanhas encontradas</h2>
              <p>Foram lidos <?= (int) $previewResult['total'] ?> itens no HasOffers.</p>
            </div>
          </div>

          <?php if (!empty($previewResult['diagnostics'])): ?>
            <div class="admin-api-roadmap">
              <span><?= (int) ($previewResult['diagnostics']['filtered_out'] ?? 0) ?> filtradas por busca, segmento opcional ou termos bloqueados</span>
              <span><?= (int) ($previewResult['diagnostics']['missing_tracking'] ?? 0) ?> sem tracking link valido</span>
              <span><?= (int) ($previewResult['diagnostics']['missing_offer_id'] ?? 0) ?> sem ID de campanha</span>
              <span><?= (int) ($previewResult['diagnostics']['invalid_rows'] ?? 0) ?> linhas invalidas no retorno</span>
            </div>
          <?php endif; ?>

          <form method="post" class="coupon-admin-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
            <input type="hidden" name="action" value="import_hasoffers" />
            <input type="hidden" name="account_index" value="<?= (int) $filters['account_index'] ?>" />
            <input type="hidden" name="page" value="<?= (int) $filters['page'] ?>" />
            <input type="hidden" name="limit" value="<?= (int) $filters['limit'] ?>" />
            <input type="hidden" name="status" value="<?= e($filters['status']) ?>" />
            <input type="hidden" name="query" value="<?= e($filters['query']) ?>" />
            <input type="hidden" name="excluded_terms" value="<?= e(implode(', ', $filters['excluded_terms'])) ?>" />
            <input type="hidden" name="publish_status" value="<?= e($filters['publish_status']) ?>" />
            <?php foreach ($filters['categories'] as $category): ?><input type="hidden" name="categories[]" value="<?= e($category) ?>" /><?php endforeach; ?>

            <div class="admin-table-wrap">
              <table class="admin-table admin-api-table">
                <thead>
                  <tr>
                    <th>Importar</th>
                    <th>Anunciante e campanha</th>
                    <th>Tipo</th>
                    <th>Segmento</th>
                    <th>Resgate</th>
                    <th>Tracking</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$previewResult['items']): ?>
                    <tr><td colspan="6" class="admin-empty-cell">Nenhuma campanha voltou da HasOffers para estes parametros. Deixe busca, segmentos e termos bloqueados em branco para testar todas as ativas.</td></tr>
                  <?php endif; ?>
                  <?php foreach ($previewResult['items'] as $item): ?>
                    <tr>
                      <td>
                        <label class="admin-mini-check">
                          <input type="checkbox" name="selected_external_ids[]" value="<?= e($item['external_id']) ?>" <?= $item['tracking_ready'] && $item['external_id'] !== '' ? 'checked' : 'disabled' ?> />
                          <span><?= $item['tracking_ready'] ? 'Selecionar' : 'Sem link' ?></span>
                        </label>
                      </td>
                      <td><strong><?= e($item['store']) ?></strong><br /><span><?= e($item['title']) ?></span></td>
                      <td><span class="admin-pill admin-pill-type"><?= e(offer_type_label($item['offer_type'])) ?></span></td>
                      <td><?= e($item['category']) ?></td>
                      <td><?= e(redemption_type_label($item['redemption_type'])) ?></td>
                      <td>
                        <span class="status-pill <?= $item['tracking_ready'] ? 'status-ativo' : 'status-pausado' ?>">
                          <?= $item['tracking_ready'] ? ($item['existing'] ? 'tracking ok / atualiza' : 'tracking ok / novo') : 'sem tracking' ?>
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
