<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/affiliation.php';
require_once __DIR__ . '/layout.php';

require_admin();

$error = '';
$success = '';
$editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editingPartner = $editingId > 0 ? affiliation_partner_by_id($editingId) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        $savedId = affiliation_save_partner($_POST);
        $success = $savedId > 0 ? 'Parceiro salvo com sucesso.' : '';
        $editingId = $savedId;
        $editingPartner = affiliation_partner_by_id($editingId);
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$rows = affiliation_partner_rows();
$form = [
    'id' => (string) ($editingPartner['id'] ?? ''),
    'name' => (string) ($editingPartner['name'] ?? ''),
    'email' => (string) ($editingPartner['email'] ?? ''),
    'company_name' => (string) ($editingPartner['company_name'] ?? ''),
    'phone' => (string) ($editingPartner['phone'] ?? ''),
    'website' => (string) ($editingPartner['website'] ?? ''),
    'status' => (string) ($editingPartner['status'] ?? 'ativo'),
    'partner_code' => (string) ($editingPartner['partner_code'] ?? ''),
    'document' => (string) ($editingPartner['document'] ?? ''),
    'traffic_source' => (string) ($editingPartner['traffic_source'] ?? ''),
    'audience_profile' => (string) ($editingPartner['audience_profile'] ?? ''),
    'payment_method' => (string) ($editingPartner['payment_method'] ?? ''),
    'payment_reference' => (string) ($editingPartner['payment_reference'] ?? ''),
    'notes' => (string) ($editingPartner['notes'] ?? ''),
];
?>
<?php admin_layout_start('Parceiros afiliados - Oferto Cupons', 'afiliacao', 'Afiliação'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Parceiros</p>
          <h1>Afiliados separados da vitrine de cupons</h1>
          <p>Esta subguia acompanha quem pode receber smartlinks, com cliques, conversões e carteira próprios. O leitor do site continua vendo cupons; o parceiro afiliado fica nesta camada operacional.</p>
        </div>
      </section>

      <?php admin_affiliation_subnav('parceiros'); ?>

      <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="admin-success"><?= e($success) ?></p><?php endif; ?>

      <section class="admin-panel">
        <div class="admin-panel-title-row">
          <div>
            <p class="section-kicker"><?= $editingPartner ? 'Editar parceiro' : 'Novo parceiro' ?></p>
            <h2><?= $editingPartner ? e($editingPartner['name']) : 'Cadastrar afiliado' ?></h2>
            <p>Use este cadastro para gerar smartlinks, atribuir cliques e controlar carteira. Ele não cria cupom público.</p>
          </div>
          <?php if ($editingPartner): ?>
            <a class="admin-secondary-link" href="afiliacao-parceiros.php">Novo cadastro</a>
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
                  <h2>Identificação</h2>
                  <p>Dados básicos do parceiro afiliado.</p>
                </div>
              </div>
              <label>Nome
                <input name="name" value="<?= e($form['name']) ?>" required />
              </label>
              <label>E-mail
                <input name="email" type="email" value="<?= e($form['email']) ?>" required />
              </label>
              <label>Empresa
                <input name="company_name" value="<?= e($form['company_name']) ?>" />
              </label>
              <label>Telefone
                <input name="phone" value="<?= e($form['phone']) ?>" />
              </label>
              <label>Site
                <input name="website" value="<?= e($form['website']) ?>" placeholder="https://..." />
              </label>
              <label>Código do afiliado
                <input name="partner_code" value="<?= e($form['partner_code']) ?>" placeholder="Ex: parceiro-midiasocial" />
              </label>
              <label>Documento
                <input name="document" value="<?= e($form['document']) ?>" placeholder="CPF, CNPJ ou identificação interna" />
              </label>
            </fieldset>

            <fieldset class="admin-fieldset">
              <div class="admin-fieldset-heading">
                <span>2</span>
                <div>
                  <h2>Operação</h2>
                  <p>Status e dados financeiros para controle interno.</p>
                </div>
              </div>
              <label>Status
                <select name="status">
                  <option value="ativo" <?= $form['status'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                  <option value="pausado" <?= $form['status'] === 'pausado' ? 'selected' : '' ?>>Pausado</option>
                </select>
              </label>
              <label>Fonte de tráfego
                <input name="traffic_source" value="<?= e($form['traffic_source']) ?>" placeholder="Instagram, WhatsApp, SEO, mídia paga..." />
              </label>
              <label>Perfil de audiência
                <input name="audience_profile" value="<?= e($form['audience_profile']) ?>" placeholder="Famílias, pets, beleza, delivery..." />
              </label>
              <label>Método de pagamento
                <input name="payment_method" value="<?= e($form['payment_method']) ?>" placeholder="Pix, TED, PayPal..." />
              </label>
              <label>Chave ou referência de pagamento
                <input name="payment_reference" value="<?= e($form['payment_reference']) ?>" />
              </label>
              <label>Observações
                <textarea name="notes"><?= e($form['notes']) ?></textarea>
              </label>
              <div class="admin-actions">
                <button type="submit">Salvar parceiro</button>
              </div>
            </fieldset>
          </div>
        </form>
      </section>

      <section class="admin-panel">
        <div class="section-heading admin-section-heading">
          <div>
            <p class="section-kicker">Cadastro operacional</p>
            <h2><?= count($rows) ?> parceiros encontrados</h2>
          </div>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table admin-report-table">
            <thead>
              <tr>
                <th>Parceiro</th>
                <th>Status</th>
                <th>Tráfego</th>
                <th>Pagamento</th>
                <th>Cliques</th>
                <th>Conversões</th>
                <th>Ganhos</th>
                <th>Saques</th>
                <th>Último clique</th>
                <th>Ação</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td><strong><?= e($row['name']) ?></strong><br /><span><?= e($row['email']) ?></span><br /><small><?= e($row['partner_code'] ?: '-') ?></small></td>
                  <td><span class="status-pill status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                  <td><?= e($row['traffic_source'] ?: '-') ?><br /><span><?= e($row['audience_profile'] ?: '') ?></span></td>
                  <td><?= e($row['payment_method'] ?: '-') ?></td>
                  <td><strong><?= (int) $row['click_count'] ?></strong></td>
                  <td><?= (int) $row['conversion_count'] ?></td>
                  <td>R$ <?= number_format((float) $row['total_earned'], 2, ',', '.') ?></td>
                  <td>R$ <?= number_format((float) $row['total_withdrawn'], 2, ',', '.') ?></td>
                  <td><?= e($row['last_click_at'] ? date('d/m/Y H:i', strtotime($row['last_click_at'])) : '-') ?></td>
                  <td><a class="admin-secondary-link" href="afiliacao-parceiros.php?edit=<?= (int) $row['id'] ?>">Editar</a></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$rows): ?>
                <tr><td colspan="10" class="admin-empty-cell">Nenhum parceiro afiliado cadastrado ainda.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php admin_layout_end(); ?>
