<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/affiliation.php';
require_once __DIR__ . '/layout.php';

require_admin();

$error = '';
$success = '';
$editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editingCreative = $editingId > 0 ? affiliation_creative_by_id($editingId) : null;
$selectedCampaignId = isset($_GET['campaign_id']) ? (int) $_GET['campaign_id'] : (int) ($editingCreative['campaign_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        $savedId = affiliation_save_creative($_POST);
        $success = 'Criativo salvo com sucesso.';
        $editingId = $savedId;
        $editingCreative = affiliation_creative_by_id($editingId);
        $selectedCampaignId = (int) ($editingCreative['campaign_id'] ?? 0);
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$campaigns = affiliation_campaign_rows(['network' => '', 'status' => '', 'q' => '']);
$rows = affiliation_creative_rows(['campaign_id' => $selectedCampaignId]);
$form = [
    'id' => (string) ($editingCreative['id'] ?? ''),
    'campaign_id' => (string) ($editingCreative['campaign_id'] ?? $selectedCampaignId),
    'creative_type' => (string) ($editingCreative['creative_type'] ?? 'banner'),
    'title' => (string) ($editingCreative['title'] ?? ''),
    'asset_url' => (string) ($editingCreative['asset_url'] ?? ''),
    'destination_url' => (string) ($editingCreative['destination_url'] ?? ''),
    'width' => (string) ($editingCreative['width'] ?? ''),
    'height' => (string) ($editingCreative['height'] ?? ''),
    'status' => (string) ($editingCreative['status'] ?? 'ativo'),
    'notes' => (string) ($editingCreative['notes'] ?? ''),
];
?>
<?php admin_layout_start('Criativos de afiliação - Oferto Cupons', 'afiliacao', 'Afiliação'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Criativos</p>
          <h1>Banners, links e materiais por campanha</h1>
          <p>Esta subguia organiza o material que pode ser entregue aos afiliados: imagem, destino, tamanho, status e observações. Ela pertence ao módulo de afiliação, não à vitrine pública de cupons.</p>
        </div>
        <form class="admin-report-filter" method="get">
          <label>Campanha
            <select name="campaign_id">
              <option value="">Todas</option>
              <?php foreach ($campaigns as $campaign): ?>
                <option value="<?= (int) $campaign['id'] ?>" <?= $selectedCampaignId === (int) $campaign['id'] ? 'selected' : '' ?>>
                  <?= e($campaign['advertiser']) ?> - <?= e($campaign['title']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>
          <button type="submit">Filtrar</button>
        </form>
      </section>

      <?php admin_affiliation_subnav('criativos'); ?>

      <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="admin-success"><?= e($success) ?></p><?php endif; ?>

      <section class="admin-panel">
        <div class="admin-panel-title-row">
          <div>
            <p class="section-kicker"><?= $editingCreative ? 'Editar criativo' : 'Novo criativo' ?></p>
            <h2><?= $editingCreative ? e($editingCreative['title']) : 'Material de divulgação' ?></h2>
            <p>Cadastre banners, links de texto, assets de WhatsApp ou observações de mídia para cada campanha afiliada.</p>
          </div>
          <?php if ($editingCreative): ?>
            <a class="admin-secondary-link" href="afiliacao-criativos.php<?= $selectedCampaignId ? '?campaign_id=' . (int) $selectedCampaignId : '' ?>">Novo criativo</a>
          <?php endif; ?>
        </div>

        <form method="post" class="coupon-admin-form">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
          <input type="hidden" name="id" value="<?= e($form['id']) ?>" />
          <div class="admin-form-grid">
            <fieldset class="admin-fieldset">
              <div class="admin-fieldset-heading">
                <span>1</span>
                <div>
                  <h2>Material</h2>
                  <p>Vincule o criativo à campanha e descreva o formato.</p>
                </div>
              </div>
              <label>Campanha
                <select name="campaign_id" required>
                  <option value="">Selecione</option>
                  <?php foreach ($campaigns as $campaign): ?>
                    <option value="<?= (int) $campaign['id'] ?>" <?= (int) $form['campaign_id'] === (int) $campaign['id'] ? 'selected' : '' ?>>
                      <?= e($campaign['advertiser']) ?> - <?= e($campaign['title']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>Nome do criativo
                <input name="title" value="<?= e($form['title']) ?>" required />
              </label>
              <label>Tipo
                <select name="creative_type">
                  <option value="banner" <?= $form['creative_type'] === 'banner' ? 'selected' : '' ?>>Banner</option>
                  <option value="texto" <?= $form['creative_type'] === 'texto' ? 'selected' : '' ?>>Link de texto</option>
                  <option value="whatsapp" <?= $form['creative_type'] === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                  <option value="email" <?= $form['creative_type'] === 'email' ? 'selected' : '' ?>>Email</option>
                  <option value="social" <?= $form['creative_type'] === 'social' ? 'selected' : '' ?>>Social</option>
                </select>
              </label>
              <label>Status
                <select name="status">
                  <option value="ativo" <?= $form['status'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                  <option value="pausado" <?= $form['status'] === 'pausado' ? 'selected' : '' ?>>Pausado</option>
                </select>
              </label>
            </fieldset>

            <fieldset class="admin-fieldset">
              <div class="admin-fieldset-heading">
                <span>2</span>
                <div>
                  <h2>URLs e dimensões</h2>
                  <p>Informe o asset e o destino entregue ao afiliado.</p>
                </div>
              </div>
              <label>URL do asset
                <input name="asset_url" value="<?= e($form['asset_url']) ?>" placeholder="https://..." />
              </label>
              <label>URL de destino
                <input name="destination_url" value="<?= e($form['destination_url']) ?>" placeholder="https://..." />
              </label>
              <label>Largura
                <input name="width" value="<?= e($form['width']) ?>" inputmode="numeric" placeholder="800" />
              </label>
              <label>Altura
                <input name="height" value="<?= e($form['height']) ?>" inputmode="numeric" placeholder="534" />
              </label>
              <label>Observações
                <textarea name="notes"><?= e($form['notes']) ?></textarea>
              </label>
              <div class="admin-actions">
                <button type="submit">Salvar criativo</button>
              </div>
            </fieldset>
          </div>
        </form>
      </section>

      <section class="admin-panel">
        <div class="section-heading admin-section-heading">
          <div>
            <p class="section-kicker">Biblioteca</p>
            <h2><?= count($rows) ?> criativos encontrados</h2>
          </div>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table admin-report-table">
            <thead>
              <tr>
                <th>Criativo</th>
                <th>Campanha</th>
                <th>Tipo</th>
                <th>Tamanho</th>
                <th>Status</th>
                <th>Asset</th>
                <th>Ação</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td>
                    <div class="affiliate-creative-cell">
                      <?php if ($row['asset_url'] && preg_match('/\.(png|jpe?g|gif|webp|svg)(?:\?|$)/i', (string) $row['asset_url'])): ?>
                        <img src="<?= e($row['asset_url']) ?>" alt="" loading="lazy" />
                      <?php else: ?>
                        <span><?= e(strtoupper(substr((string) $row['creative_type'], 0, 2))) ?></span>
                      <?php endif; ?>
                      <div>
                        <strong><?= e($row['title']) ?></strong><br />
                        <span><?= e($row['notes'] ?: '') ?></span>
                      </div>
                    </div>
                  </td>
                  <td><strong><?= e($row['advertiser']) ?></strong><br /><span><?= e($row['campaign_title']) ?></span></td>
                  <td><?= e($row['creative_type']) ?></td>
                  <td><?= $row['width'] && $row['height'] ? (int) $row['width'] . 'x' . (int) $row['height'] : '-' ?></td>
                  <td><span class="status-pill status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                  <td>
                    <div class="row-actions">
                      <?php if ($row['asset_url']): ?>
                        <a href="<?= e($row['asset_url']) ?>" target="_blank" rel="noopener">Abrir</a>
                        <button type="button" data-copy-value="<?= e($row['asset_url']) ?>">Copiar asset</button>
                      <?php else: ?>
                        <span>-</span>
                      <?php endif; ?>
                      <?php if ($row['destination_url']): ?>
                        <button type="button" data-copy-value="<?= e($row['destination_url']) ?>">Copiar destino</button>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td><a class="admin-secondary-link" href="afiliacao-criativos.php?edit=<?= (int) $row['id'] ?>">Editar</a></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$rows): ?>
                <tr><td colspan="7" class="admin-empty-cell">Nenhum criativo cadastrado ainda.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php admin_layout_end(); ?>
