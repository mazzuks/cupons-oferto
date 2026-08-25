<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/integrations.php';
require_once __DIR__ . '/layout.php';

require_admin();

$error = '';
$success = '';
$awinResult = null;
$previewResult = null;
$importResult = null;

function awin_posted_array(string $key): array
{
    return array_values(array_filter(array_map('strval', (array) ($_POST[$key] ?? []))));
}

function awin_posted_filters(): array
{
    return [
        'page' => (int) ($_POST['page'] ?? 1),
        'page_size' => (int) ($_POST['page_size'] ?? 50),
        'type' => (string) ($_POST['type'] ?? 'all'),
        'membership' => (string) ($_POST['membership'] ?? 'all'),
        'status' => (string) ($_POST['status'] ?? 'active'),
        'region' => (string) ($_POST['region'] ?? 'BR'),
        'query' => trim((string) ($_POST['query'] ?? '')),
        'categories' => awin_posted_array('categories'),
        'excluded_terms' => trim((string) ($_POST['excluded_terms'] ?? awin_default_excluded_terms())),
        'publish_status' => (string) ($_POST['publish_status'] ?? 'rascunho'),
        'selected_external_ids' => awin_posted_array('selected_external_ids'),
    ];
}

$filters = awin_normalize_filters(integration_profile('awin', [
    'page' => 1,
    'page_size' => 50,
    'type' => 'all',
    'membership' => 'all',
    'status' => 'active',
    'region' => 'BR',
    'excluded_terms' => awin_default_excluded_terms(),
    'publish_status' => 'rascunho',
]));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $filters = awin_normalize_filters(awin_posted_filters());

    try {
        if ($action === 'save_awin_key' || $action === 'connect_awin') {
            $accessToken = trim((string) ($_POST['awin_access_token'] ?? ''));
            if ($accessToken !== '') {
                save_integration_setting('awin_access_token', $accessToken);
            }

            if (awin_access_token() === '') {
                throw new RuntimeException('Informe o token da Awin.');
            }

            $awinResult = awin_connect_first_publisher();
            $success = 'Awin conectada com sucesso.';
        }

        if ($action === 'preview_awin') {
            $previewResult = awin_preview_offers($filters);
            $success = 'Previa da Awin carregada.';
        }

        if ($action === 'save_awin_defaults') {
            save_integration_profile('awin', $filters);
            $success = 'Padrao da Awin salvo para proximas buscas e sincronizacoes.';
        }

        if ($action === 'import_awin') {
            if (!$filters['selected_external_ids']) {
                throw new RuntimeException('Selecione pelo menos uma oferta para importar.');
            }

            $importResult = awin_import_offers($filters);
            $previewResult = awin_preview_offers($filters);
            $monitoredBrands = monitored_integration_brands('Awin');
            $success = 'Sincronizacao da Awin concluida.';
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$savedAwinToken = awin_access_token();
$maskedAwinToken = $savedAwinToken === '' ? 'Nao configurado' : substr($savedAwinToken, 0, 8) . str_repeat('*', 12) . substr($savedAwinToken, -6);
$awinPublisherId = awin_publisher_id();
$awinPublisherName = awin_publisher_name();
$monitoredBrands = monitored_integration_brands('Awin');
?>
<?php admin_layout_start('Awin - Oferto Cupons', 'apis', 'Awin'); ?>
      <section class="admin-hero admin-api-hero">
        <div>
          <p class="section-kicker">Awin</p>
          <h1>Curadoria de vouchers e promocoes</h1>
          <p>Filtre antes de listar, selecione so anunciantes relevantes e sincronize ofertas como rascunho para revisar.</p>
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

          <form method="post" class="coupon-admin-form admin-inline-form">
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
              <p class="section-kicker">Anunciantes monitorados</p>
              <h2><?= count($monitoredBrands) ?> anunciantes</h2>
              <p>Ao sincronizar ofertas selecionadas, o anunciante entra aqui e passa a ser acompanhado pela cron.</p>
            </div>
          </div>

          <div class="admin-api-roadmap">
            <?php foreach (array_slice($monitoredBrands, 0, 8) as $brand): ?>
              <span><?= e($brand['brand_name']) ?></span>
            <?php endforeach; ?>
            <?php if (!$monitoredBrands): ?>
              <span>Nenhum anunciante monitorado ainda. Busque ofertas e sincronize as marcas que interessam.</span>
            <?php endif; ?>
          </div>
        </section>

        <section class="admin-panel admin-api-card">
          <div class="admin-panel-title-row">
            <div>
              <p class="section-kicker">Busca</p>
              <h2>Pesquisar ofertas</h2>
              <p>Comece por segmentos e termos bloqueados. Depois refine por anunciante ou parceiros aprovados.</p>
            </div>
          </div>

          <form method="post" class="coupon-admin-form admin-api-filter-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />

            <div class="admin-two-cols admin-two-cols-wide-left">
              <label>Buscar anunciante ou oferta
                <input name="query" value="<?= e($filters['query']) ?>" placeholder="Ex: seguro, delivery, moda, China..." />
                <small>Filtra por anunciante, titulo, descricao e regras.</small>
              </label>
              <label>Itens por busca
                <input name="page_size" type="number" min="10" max="200" value="<?= (int) $filters['page_size'] ?>" />
                <small>A Awin permite ate 200 itens por pagina.</small>
              </label>
            </div>

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

            <div class="admin-two-cols">
              <label>Tipo
                <select name="type">
                  <?php foreach (awin_offer_type_options() as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $filters['type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>Relacionamento
                <select name="membership">
                  <?php foreach (awin_membership_options() as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $filters['membership'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
            </div>

            <div class="admin-two-cols">
              <label>Status
                <select name="status">
                  <?php foreach (awin_status_options() as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>Regiao
                <input name="region" value="<?= e($filters['region']) ?>" maxlength="2" />
                <small>Use BR para evitar campanhas de fora.</small>
              </label>
            </div>

            <div class="admin-two-cols">
              <label>Bloquear termos
                <input name="excluded_terms" value="<?= e(implode(', ', $filters['excluded_terms'])) ?>" />
                <small>Ajuda a esconder categorias sensiveis ou fora da estrategia.</small>
              </label>
              <label>Status ao importar
                <select name="publish_status">
                  <option value="rascunho" <?= $filters['publish_status'] === 'rascunho' ? 'selected' : '' ?>>Rascunho para revisar</option>
                  <option value="ativo" <?= $filters['publish_status'] === 'ativo' ? 'selected' : '' ?>>Publicar direto</option>
                </select>
              </label>
            </div>

            <div class="admin-actions">
              <button type="submit" name="action" value="preview_awin">Buscar ofertas da Awin</button>
              <button type="submit" name="action" value="save_awin_defaults">Salvar como padrao</button>
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
              <h2><?= (int) $previewResult['matched'] ?> ofertas selecionaveis</h2>
              <p>Foram lidos <?= (int) $previewResult['total'] ?> itens na Awin. Importe somente o que combina com o Oferto.</p>
            </div>
          </div>

          <form method="post" class="coupon-admin-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
            <input type="hidden" name="action" value="import_awin" />
            <input type="hidden" name="query" value="<?= e($filters['query']) ?>" />
            <input type="hidden" name="page_size" value="<?= (int) $filters['page_size'] ?>" />
            <input type="hidden" name="type" value="<?= e($filters['type']) ?>" />
            <input type="hidden" name="membership" value="<?= e($filters['membership']) ?>" />
            <input type="hidden" name="status" value="<?= e($filters['status']) ?>" />
            <input type="hidden" name="region" value="<?= e($filters['region']) ?>" />
            <input type="hidden" name="excluded_terms" value="<?= e(implode(', ', $filters['excluded_terms'])) ?>" />
            <input type="hidden" name="publish_status" value="<?= e($filters['publish_status']) ?>" />
            <?php foreach ($filters['categories'] as $category): ?><input type="hidden" name="categories[]" value="<?= e($category) ?>" /><?php endforeach; ?>

            <div class="admin-table-wrap">
              <table class="admin-table admin-api-table">
                <thead>
                  <tr>
                    <th>Importar</th>
                    <th>Anunciante e oferta</th>
                    <th>Tipo</th>
                    <th>Segmento</th>
                    <th>Resgate</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$previewResult['items']): ?>
                    <tr><td colspan="6" class="admin-empty-cell">Nenhuma oferta passou por estes filtros. Tente deixar segmentos sem marcar, usar relacionamento "Todos os anunciantes" ou remover termos bloqueados temporariamente.</td></tr>
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
              <button type="submit" <?= !$previewResult['items'] ? 'disabled' : '' ?>>Sincronizar selecionadas</button>
            </div>
          </form>
        </section>
      <?php endif; ?>
<?php admin_layout_end(); ?>
