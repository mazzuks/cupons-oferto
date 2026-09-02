<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/affiliation.php';
require_once __DIR__ . '/layout.php';

require_admin();

$error = '';
$success = '';
$editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editingCampaign = $editingId > 0 ? affiliation_campaign_by_id($editingId) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        $savedId = affiliation_save_campaign($_POST);
        $success = 'Campanha afiliada salva com sucesso.';
        $editingId = $savedId;
        $editingCampaign = affiliation_campaign_by_id($editingId);
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$filters = [
    'network' => trim((string) ($_GET['network'] ?? '')),
    'status' => trim((string) ($_GET['status'] ?? '')),
    'q' => trim((string) ($_GET['q'] ?? '')),
];
$networks = affiliation_network_options();
$rows = affiliation_campaign_rows($filters);
$form = [
    'id' => (string) ($editingCampaign['id'] ?? ''),
    'network' => (string) ($editingCampaign['network'] ?? 'manual'),
    'external_id' => (string) ($editingCampaign['external_id'] ?? ''),
    'advertiser' => (string) ($editingCampaign['advertiser'] ?? ''),
    'title' => (string) ($editingCampaign['title'] ?? ''),
    'description' => (string) ($editingCampaign['description'] ?? ''),
    'category' => (string) ($editingCampaign['category'] ?? ''),
    'landing_url' => (string) ($editingCampaign['landing_url'] ?? ''),
    'tracking_url' => (string) ($editingCampaign['tracking_url'] ?? ''),
    'banner_url' => (string) ($editingCampaign['banner_url'] ?? ''),
    'logo_url' => (string) ($editingCampaign['logo_url'] ?? ''),
    'code' => (string) ($editingCampaign['code'] ?? ''),
    'rules' => (string) ($editingCampaign['rules'] ?? ''),
    'payout' => (string) ($editingCampaign['payout'] ?? ''),
    'payout_model' => (string) ($editingCampaign['payout_model'] ?? ''),
    'commission_type' => (string) ($editingCampaign['commission_type'] ?? ''),
    'commission_rate' => (string) ($editingCampaign['commission_rate'] ?? ''),
    'campaign_cap' => (string) ($editingCampaign['campaign_cap'] ?? ''),
    'daily_cap' => (string) ($editingCampaign['daily_cap'] ?? ''),
    'monthly_cap' => (string) ($editingCampaign['monthly_cap'] ?? ''),
    'starts_at' => (string) ($editingCampaign['starts_at'] ?? ''),
    'ends_at' => (string) ($editingCampaign['ends_at'] ?? ''),
    'status' => (string) ($editingCampaign['status'] ?? 'selecionada'),
    'approval_mode' => (string) ($editingCampaign['approval_mode'] ?? 'manual'),
    'tracking_mode' => (string) ($editingCampaign['tracking_mode'] ?? 'CLASSIC_PIXEL'),
    'redirect_mode' => (string) ($editingCampaign['redirect_mode'] ?? 'FAST_302'),
    'cookie_ttl_days' => (string) ($editingCampaign['cookie_ttl_days'] ?? '180'),
    'utm_source_gate' => (string) ($editingCampaign['utm_source_gate'] ?? 'oferto'),
    'allowed_domains' => (string) ($editingCampaign['allowed_domains'] ?? ''),
    'geo_countries' => (string) ($editingCampaign['geo_countries'] ?? ''),
    'device_rules' => (string) ($editingCampaign['device_rules'] ?? ''),
    'creative_notes' => (string) ($editingCampaign['creative_notes'] ?? ''),
];
?>
<?php admin_layout_start('Campanhas afiliadas - Oferto Cupons', 'afiliacao', 'Afiliação'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Campanhas apartadas</p>
          <h1>Ofertas vindas de afiliação</h1>
          <p>Esta tela lê apenas a tabela de campanhas afiliadas. A vitrine pública de cupons fica em outra base e só aparece aqui quando for selecionada ou importada como operação de afiliação.</p>
        </div>
        <form class="admin-report-filter" method="get">
          <label>Rede
            <select name="network">
              <option value="">Todas</option>
              <?php foreach ($networks as $network): ?>
                <option value="<?= e($network) ?>" <?= $filters['network'] === $network ? 'selected' : '' ?>><?= e($network) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>Status
            <select name="status">
              <option value="">Todos</option>
              <?php foreach (affiliation_campaign_statuses() as $status => $label): ?>
                <option value="<?= e($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>Busca<input name="q" value="<?= e($filters['q']) ?>" placeholder="Loja, título, categoria ou ID" /></label>
          <button type="submit">Filtrar</button>
        </form>
      </section>

      <?php admin_affiliation_subnav('campanhas'); ?>

      <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="admin-success"><?= e($success) ?></p><?php endif; ?>

      <section class="admin-panel">
        <div class="admin-panel-title-row">
          <div>
            <p class="section-kicker"><?= $editingCampaign ? 'Editar campanha' : 'Nova campanha manual' ?></p>
            <h2><?= $editingCampaign ? e($editingCampaign['advertiser']) : 'Operação afiliada' ?></h2>
            <p>Use esta ficha para definir regras de comissão, aprovação, caps e tracking. Isso não altera a vitrine pública de cupons.</p>
          </div>
          <?php if ($editingCampaign): ?>
            <a class="admin-secondary-link" href="afiliacao-campanhas.php">Nova campanha</a>
          <?php endif; ?>
        </div>

        <?php if ($editingCampaign): ?>
          <div class="affiliate-offer-card">
            <div>
              <span class="admin-pill admin-pill-type"><?= e($editingCampaign['network'] ?: 'manual') ?></span>
              <h3><?= e($editingCampaign['title']) ?></h3>
              <p><?= e($editingCampaign['description'] ?: 'Campanha afiliada pronta para configuração operacional.') ?></p>
            </div>
            <dl>
              <div>
                <dt>Status</dt>
                <dd><span class="status-pill status-<?= e($editingCampaign['status']) ?>"><?= e($editingCampaign['status']) ?></span></dd>
              </div>
              <div>
                <dt>Comissão</dt>
                <dd><?= $editingCampaign['payout'] !== null ? 'R$ ' . e(number_format((float) $editingCampaign['payout'], 2, ',', '.')) : e($editingCampaign['commission_type'] ?: 'Não informada') ?></dd>
              </div>
              <div>
                <dt>Cookie</dt>
                <dd><?= (int) $editingCampaign['cookie_ttl_days'] ?> dias</dd>
              </div>
              <div>
                <dt>Validade</dt>
                <dd><?= e($editingCampaign['ends_at'] ? date('d/m/Y', strtotime($editingCampaign['ends_at'])) : 'Sem fim definido') ?></dd>
              </div>
            </dl>
          </div>
        <?php endif; ?>

        <form method="post" class="coupon-admin-form">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
          <input type="hidden" name="id" value="<?= e($form['id']) ?>" />
          <div class="admin-tab-shell" data-tabs>
            <div class="admin-tabs" role="tablist" aria-label="Configuração da campanha">
              <button type="button" class="is-active" data-tab-target="geral">Geral</button>
              <button type="button" data-tab-target="comissao">Comissão</button>
              <button type="button" data-tab-target="tracking">Tracking</button>
            </div>
            <div class="admin-form-grid">
            <fieldset class="admin-fieldset admin-tab-panel is-active" data-tab-panel="geral">
              <div class="admin-fieldset-heading">
                <span>1</span>
                <div>
                  <h2>Oferta afiliada</h2>
                  <p>Identificação, rede e URLs da campanha.</p>
                </div>
              </div>
              <label>Anunciante
                <input name="advertiser" value="<?= e($form['advertiser']) ?>" required />
              </label>
              <label>Título
                <input name="title" value="<?= e($form['title']) ?>" required />
              </label>
              <label>Rede
                <input name="network" value="<?= e($form['network']) ?>" placeholder="manual, lomadee, awin..." />
              </label>
              <label>ID externo
                <input name="external_id" value="<?= e($form['external_id']) ?>" />
              </label>
              <label>Categoria
                <input name="category" value="<?= e($form['category']) ?>" />
              </label>
              <label>URL final
                <input name="landing_url" value="<?= e($form['landing_url']) ?>" />
              </label>
              <label>URL de tracking
                <input name="tracking_url" value="<?= e($form['tracking_url']) ?>" />
              </label>
              <label>Descrição
                <textarea name="description"><?= e($form['description']) ?></textarea>
              </label>
            </fieldset>

            <fieldset class="admin-fieldset admin-tab-panel" data-tab-panel="comissao">
              <div class="admin-fieldset-heading">
                <span>2</span>
                <div>
                  <h2>Comissão e regras</h2>
                  <p>Payout, aprovação, limites e condições comerciais.</p>
                </div>
              </div>
              <label>Status
                <select name="status">
                  <?php foreach (affiliation_campaign_statuses() as $status => $label): ?>
                    <option value="<?= e($status) ?>" <?= $form['status'] === $status ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>Tipo de comissão
                <select name="commission_type">
                  <?php foreach (affiliation_commission_types() as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $form['commission_type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>Payout
                <input name="payout" value="<?= e($form['payout']) ?>" inputmode="decimal" placeholder="Ex: 12,50" />
              </label>
              <label>Taxa percentual
                <input name="commission_rate" value="<?= e($form['commission_rate']) ?>" inputmode="decimal" placeholder="Ex: 8,5" />
              </label>
              <label>Modelo da campanha
                <input name="payout_model" value="<?= e($form['payout_model']) ?>" placeholder="CPA, CPL, CPS..." />
              </label>
              <label>Aprovação
                <select name="approval_mode">
                  <?php foreach (affiliation_approval_modes() as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $form['approval_mode'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>Cap total
                <input name="campaign_cap" value="<?= e($form['campaign_cap']) ?>" inputmode="numeric" />
              </label>
              <label>Cap diário
                <input name="daily_cap" value="<?= e($form['daily_cap']) ?>" inputmode="numeric" />
              </label>
              <label>Cap mensal
                <input name="monthly_cap" value="<?= e($form['monthly_cap']) ?>" inputmode="numeric" />
              </label>
              <label>Regras
                <textarea name="rules"><?= e($form['rules']) ?></textarea>
              </label>
            </fieldset>

            <fieldset class="admin-fieldset admin-tab-panel" data-tab-panel="tracking">
              <div class="admin-fieldset-heading">
                <span>3</span>
                <div>
                  <h2>Tracking</h2>
                  <p>Configuração técnica para smartlink, cookie e filtros.</p>
                </div>
              </div>
              <label>Início
                <input name="starts_at" type="date" value="<?= e($form['starts_at']) ?>" />
              </label>
              <label>Fim
                <input name="ends_at" type="date" value="<?= e($form['ends_at']) ?>" />
              </label>
              <label>Modo de tracking
                <select name="tracking_mode">
                  <option value="CLASSIC_PIXEL" <?= $form['tracking_mode'] === 'CLASSIC_PIXEL' ? 'selected' : '' ?>>Classic pixel</option>
                  <option value="JOURNEY_JS" <?= $form['tracking_mode'] === 'JOURNEY_JS' ? 'selected' : '' ?>>Journey JS</option>
                </select>
              </label>
              <label>Redirect
                <select name="redirect_mode">
                  <option value="FAST_302" <?= $form['redirect_mode'] === 'FAST_302' ? 'selected' : '' ?>>302 rápido</option>
                  <option value="HTML_BRIDGE" <?= $form['redirect_mode'] === 'HTML_BRIDGE' ? 'selected' : '' ?>>Página intermediária</option>
                </select>
              </label>
              <label>Cookie TTL em dias
                <input name="cookie_ttl_days" value="<?= e($form['cookie_ttl_days']) ?>" inputmode="numeric" />
              </label>
              <label>UTM source
                <input name="utm_source_gate" value="<?= e($form['utm_source_gate']) ?>" />
              </label>
              <label>Países liberados
                <input name="geo_countries" value="<?= e($form['geo_countries']) ?>" placeholder="BR, PT, US..." />
              </label>
              <label>Domínios permitidos
                <textarea name="allowed_domains"><?= e($form['allowed_domains']) ?></textarea>
              </label>
              <label>Regras de dispositivo
                <input name="device_rules" value="<?= e($form['device_rules']) ?>" placeholder="desktop, mobile, app..." />
              </label>
              <label>Observações de criativo
                <textarea name="creative_notes"><?= e($form['creative_notes']) ?></textarea>
              </label>
              <label>Banner
                <input name="banner_url" value="<?= e($form['banner_url']) ?>" />
              </label>
              <label>Logo
                <input name="logo_url" value="<?= e($form['logo_url']) ?>" />
              </label>
              <label>Código/cupom
                <input name="code" value="<?= e($form['code']) ?>" />
              </label>
              <div class="admin-actions">
                <button type="submit">Salvar campanha</button>
              </div>
            </fieldset>
            </div>
          </div>
        </form>
      </section>

      <section class="admin-panel">
        <div class="section-heading admin-section-heading">
          <div>
            <p class="section-kicker">Lista operacional</p>
            <h2><?= count($rows) ?> campanhas encontradas</h2>
          </div>
          <a class="admin-secondary-link" href="afiliacao-selecionar.php">Selecionar cupons</a>
        </div>

        <div class="admin-table-wrap">
          <table class="admin-table admin-report-table">
            <thead>
              <tr>
                <th>Campanha</th>
                <th>Rede</th>
                <th>Status</th>
                <th>Tipo</th>
                <th>Validade</th>
                <th>Payout</th>
                <th>Cliques</th>
                <th>Conversões</th>
                <th>Comissão</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td>
                    <strong><?= e($row['advertiser']) ?></strong><br />
                    <span><?= e($row['title']) ?></span><br />
                    <small><?= e($row['external_id'] ?: '-') ?></small>
                  </td>
                  <td><?= e($row['network'] ?: 'manual') ?></td>
                  <td><span class="status-pill status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                  <td><span class="admin-pill admin-pill-type"><?= e($row['payout_model'] ?: 'campanha') ?></span></td>
                  <td><?= e($row['starts_at'] ? date('d/m/Y', strtotime($row['starts_at'])) : '-') ?> a <?= e($row['ends_at'] ? date('d/m/Y', strtotime($row['ends_at'])) : '-') ?></td>
                  <td><?= $row['payout'] !== null ? 'R$ ' . e(number_format((float) $row['payout'], 2, ',', '.')) : '-' ?></td>
                  <td><strong><?= (int) $row['click_count'] ?></strong></td>
                  <td><?= (int) $row['conversion_count'] ?></td>
                  <td>R$ <?= number_format((float) $row['commission_total'], 2, ',', '.') ?></td>
                  <td>
                    <?php if (!empty($row['published_coupon_id'])): ?>
                      <a class="admin-primary-link" href="index.php?edit=<?= (int) $row['published_coupon_id'] ?>">Cupom</a>
                    <?php else: ?>
                      <a class="admin-secondary-link" href="afiliacao-campanhas.php?edit=<?= (int) $row['id'] ?>">Editar</a>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$rows): ?>
                <tr><td colspan="10" class="admin-empty-cell">Nenhuma campanha afiliada encontrada com estes filtros.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php admin_layout_end(); ?>
