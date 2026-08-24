<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/coupons.php';

require_admin();

$error = '';
$success = '';
$editing = null;

function coupon_payload(array $source, string $bannerUrl): array
{
    $code = trim($source['code'] ?? '');
    $offerType = normalize_offer_type($source['offer_type'] ?? 'cupom');
    $redemptionType = normalize_redemption_type($source['redemption_type'] ?? ($code !== '' ? 'texto' : 'redirect'));

    return [
        'category' => trim($source['category'] ?? ''),
        'store' => trim($source['store'] ?? ''),
        'title' => trim($source['title'] ?? ''),
        'description' => trim($source['description'] ?? ''),
        'code' => $code,
        'target_url' => trim($source['target_url'] ?? ''),
        'banner_url' => $bannerUrl,
        'starts_at' => $source['starts_at'] ?? date('Y-m-d'),
        'ends_at' => $source['ends_at'] ?? date('Y-m-d'),
        'status' => $source['status'] ?? 'rascunho',
        'featured' => truthy($source['featured'] ?? '') ? 1 : 0,
        'rules' => trim($source['rules'] ?? ''),
        'redemption_type' => $redemptionType,
        'offer_type' => $offerType,
        'cta_label' => trim($source['cta_label'] ?? '') ?: default_cta_label($offerType, $redemptionType === 'texto' ? $code : ''),
        'tracking_url' => trim($source['tracking_url'] ?? ''),
        'partner_network' => trim($source['partner_network'] ?? ''),
        'payout' => decimal_or_null($source['payout'] ?? ''),
        'campaign_cap' => integer_or_null($source['campaign_cap'] ?? ''),
        'sponsored' => truthy($source['sponsored'] ?? '') ? 1 : 0,
        'priority' => (int) ($source['priority'] ?? 0),
        'tags' => trim($source['tags'] ?? ''),
        'requirements' => trim($source['requirements'] ?? ''),
        'pixel_event' => trim($source['pixel_event'] ?? ''),
        'members_only' => truthy($source['members_only'] ?? '') ? 1 : 0,
    ];
}

function normalize_redemption_type(string $type): string
{
    $type = strtolower(trim($type));
    $type = str_replace([' ', '-'], '_', $type);
    $aliases = [
        'codigo' => 'texto',
        'código' => 'texto',
        'cupom' => 'texto',
        'texto_codigo' => 'texto',
        'texto_código' => 'texto',
        'copiar' => 'texto',
        'site' => 'redirect',
        'cadastro' => 'redirect',
        'link' => 'redirect',
        'url' => 'redirect',
        'redirecionamento' => 'redirect',
    ];
    $type = $aliases[$type] ?? $type;

    return array_key_exists($type, redemption_types()) ? $type : 'texto';
}

function normalize_offer_type(string $type): string
{
    $type = strtolower(trim($type));
    $type = str_replace([' ', '-'], '_', $type);
    $aliases = [
        'cupom_de_desconto' => 'cupom',
        'oferta' => 'oferta_direta',
        'direta' => 'oferta_direta',
        'lead' => 'cadastro',
        'cpl' => 'cadastro',
        'promocao' => 'sorteio',
        'promoção' => 'sorteio',
        'compre_e_concorra' => 'compre_concorra',
    ];
    $type = $aliases[$type] ?? $type;

    return array_key_exists($type, offer_types()) ? $type : 'cupom';
}

function decimal_or_null(string $value): ?string
{
    $value = trim(str_replace(',', '.', $value));
    return is_numeric($value) ? number_format((float) $value, 2, '.', '') : null;
}

function integer_or_null(string $value): ?int
{
    $value = trim($value);
    return $value !== '' && ctype_digit($value) ? (int) $value : null;
}

function truthy($value): bool
{
    return in_array(strtolower(trim((string) $value)), ['1', 'sim', 's', 'true', 'yes', 'on', 'destaque', 'patrocinado'], true);
}

function uploaded_banner_url(?string $current): string
{
    if (empty($_FILES['banner_file']['name'])) {
        return trim($_POST['banner_url'] ?? $current ?? '');
    }

    $file = $_FILES['banner_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Falha no upload do banner.');
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('Banner muito grande. Use arquivo de ate 2MB.');
    }

    $info = getimagesize($file['tmp_name']);
    $allowed = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp',
        IMAGETYPE_GIF => 'gif',
    ];

    if (!$info || !isset($allowed[$info[2]])) {
        throw new RuntimeException('Use JPG, PNG, WEBP ou GIF.');
    }

    $config = app_config()['app'];
    if (!is_dir($config['upload_dir'])) {
        mkdir($config['upload_dir'], 0755, true);
    }

    $name = bin2hex(random_bytes(12)) . '.' . $allowed[$info[2]];
    $target = rtrim($config['upload_dir'], '/\\') . DIRECTORY_SEPARATOR . $name;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Nao foi possivel salvar o banner.');
    }

    return rtrim($config['upload_url'], '/') . '/' . $name;
}

function import_campaigns_from_csv(): int
{
    if (empty($_FILES['campaigns_csv']['name'])) {
        throw new RuntimeException('Envie um arquivo CSV para importar.');
    }

    $file = $_FILES['campaigns_csv'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Falha no upload do CSV.');
    }
    if ($file['size'] > 1024 * 1024) {
        throw new RuntimeException('CSV muito grande. Use arquivo de ate 1MB.');
    }

    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        throw new RuntimeException('Nao foi possivel ler o CSV.');
    }

    $delimiter = detect_csv_delimiter($file['tmp_name']);
    $headers = fgetcsv($handle, 0, $delimiter);
    if (!$headers) {
        fclose($handle);
        throw new RuntimeException('CSV sem cabecalho.');
    }

    $headers = array_map('normalize_csv_header', $headers);
    $imported = 0;

    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        if (count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
            continue;
        }

        $source = csv_row_to_coupon_source($headers, $row);
        $payload = coupon_payload($source, trim($source['banner_url'] ?? ''));

        foreach (['category', 'store', 'title', 'description', 'target_url', 'banner_url', 'starts_at', 'ends_at'] as $field) {
            if (!$payload[$field]) {
                fclose($handle);
                throw new RuntimeException('Linha ' . ($imported + 2) . ': preencha categoria, loja, titulo, descricao, URL, banner, inicio e fim.');
            }
        }
        if ($payload['redemption_type'] === 'texto' && !$payload['code']) {
            fclose($handle);
            throw new RuntimeException('Linha ' . ($imported + 2) . ': resgate por texto/codigo precisa do campo codigo preenchido.');
        }

        save_coupon($payload, null);
        $imported++;
    }

    fclose($handle);
    return $imported;
}

function detect_csv_delimiter(string $path): string
{
    $sample = (string) file_get_contents($path, false, null, 0, 2048);
    return substr_count($sample, ';') > substr_count($sample, ',') ? ';' : ',';
}

function normalize_csv_header(string $header): string
{
    $header = strtolower(trim($header));
    $header = strtr($header, [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
        'é' => 'e', 'ê' => 'e',
        'í' => 'i',
        'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ú' => 'u',
        'ç' => 'c',
    ]);
    $header = preg_replace('/[^a-z0-9]+/', '_', $header);
    $header = trim((string) $header, '_');

    $aliases = [
        'categoria' => 'category',
        'segmento' => 'category',
        'loja' => 'store',
        'marca' => 'store',
        'titulo' => 'title',
        'titulo_do_cupom' => 'title',
        'chamada' => 'title',
        'descricao' => 'description',
        'descricao_curta' => 'description',
        'codigo' => 'code',
        'codigo_do_cupom' => 'code',
        'url' => 'target_url',
        'link' => 'target_url',
        'url_final' => 'target_url',
        'url_do_cupom' => 'target_url',
        'banner' => 'banner_url',
        'imagem' => 'banner_url',
        'url_do_banner' => 'banner_url',
        'inicio' => 'starts_at',
        'data_inicio' => 'starts_at',
        'data_de_inicio' => 'starts_at',
        'fim' => 'ends_at',
        'validade' => 'ends_at',
        'data_fim' => 'ends_at',
        'data_de_fim' => 'ends_at',
        'observacao' => 'rules',
        'obs' => 'rules',
        'regra' => 'rules',
        'modo_resgate' => 'redemption_type',
        'modo_de_resgate' => 'redemption_type',
        'resgate' => 'redemption_type',
        'tipo_de_resgate' => 'redemption_type',
        'tipo' => 'offer_type',
        'tipo_de_oferta' => 'offer_type',
        'cta' => 'cta_label',
        'texto_do_botao' => 'cta_label',
        'url_tracking' => 'tracking_url',
        'url_de_tracking' => 'tracking_url',
        'rede' => 'partner_network',
        'parceiro' => 'partner_network',
        'rede_parceiro' => 'partner_network',
        'cap' => 'campaign_cap',
        'limite' => 'campaign_cap',
        'patrocinado' => 'sponsored',
        'prioridade' => 'priority',
        'requisitos' => 'requirements',
        'mecanica' => 'requirements',
        'evento_pixel' => 'pixel_event',
        'usuarios_conectados' => 'members_only',
        'somente_logados' => 'members_only',
        'somente_usuarios_conectados' => 'members_only',
        'privado' => 'members_only',
    ];

    return $aliases[$header] ?? $header;
}

function csv_row_to_coupon_source(array $headers, array $row): array
{
    $source = [
        'status' => 'rascunho',
        'starts_at' => date('Y-m-d'),
        'ends_at' => date('Y-m-d', strtotime('+7 days')),
        'redemption_type' => 'redirect',
        'offer_type' => 'cupom',
    ];

    foreach ($headers as $index => $field) {
        $source[$field] = trim((string) ($row[$index] ?? ''));
    }

    return $source;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $action = $_POST['action'] ?? '';

        if ($action === 'delete') {
            delete_coupon((int) $_POST['id']);
            redirect('index.php?deleted=1');
        }

        if ($action === 'save') {
            $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;
            $current = $id ? coupon_by_id($id) : null;
            $bannerUrl = uploaded_banner_url($current['banner_url'] ?? null);
            $payload = coupon_payload($_POST, $bannerUrl);

            foreach (['category', 'store', 'title', 'description', 'target_url', 'banner_url', 'starts_at', 'ends_at'] as $field) {
                if (!$payload[$field]) {
                    throw new RuntimeException('Preencha os campos obrigatorios.');
                }
            }
            if ($payload['redemption_type'] === 'texto' && !$payload['code']) {
                throw new RuntimeException('Quando o resgate for por texto/codigo, preencha o texto do cupom.');
            }

            save_coupon($payload, $id);
            redirect('index.php?saved=1');
        }

        if ($action === 'import_csv') {
            $count = import_campaigns_from_csv();
            redirect('index.php?imported=' . $count);
        }
    }
} catch (Throwable $exception) {
    $error = $exception->getMessage();
}

if (isset($_GET['edit'])) {
    $editing = coupon_by_id((int) $_GET['edit']);
}

$coupons = all_coupons();
$defaults = [
    'id' => '',
    'category' => 'Alimentação e Bebidas',
    'store' => '',
    'title' => '',
    'description' => '',
    'code' => '',
    'target_url' => '',
    'banner_url' => '',
    'starts_at' => date('Y-m-d'),
    'ends_at' => date('Y-m-d', strtotime('+7 days')),
    'status' => 'rascunho',
    'featured' => 0,
    'rules' => '',
    'redemption_type' => 'redirect',
    'offer_type' => 'cupom',
    'cta_label' => '',
    'tracking_url' => '',
    'partner_network' => '',
    'payout' => '',
    'campaign_cap' => '',
    'sponsored' => 0,
    'priority' => 0,
    'tags' => '',
    'requirements' => '',
    'pixel_event' => '',
    'members_only' => 0,
];
$form = array_merge($defaults, $editing ?: []);
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin - Oferto Cupons</title>
    <link rel="icon" href="../assets/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" href="../assets/favicon.png" />
    <link rel="stylesheet" href="../styles.css?v=20260824-resgate" />
    <link rel="stylesheet" href="admin.css?v=20260824-resgate" />
  </head>
  <body>
    <header class="admin-header">
      <a class="brand" href="../index.php">
        <img src="https://oferto.digital/wp-content/uploads/2024/08/oferto.png" alt="Oferto" />
        <span>Admin</span>
      </a>
      <nav>
        <a href="../index.php">Ver site</a>
        <a href="logout.php">Sair</a>
      </nav>
    </header>

    <main class="admin-layout">
      <section class="admin-panel">
        <p class="section-kicker"><?= $editing ? 'Editar oferta' : 'Nova oferta' ?></p>
        <h1><?= $editing ? e($editing['store']) : 'Cadastrar oferta' ?></h1>
        <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
        <?php if (isset($_GET['saved'])): ?><p class="admin-success">Oferta salva.</p><?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?><p class="admin-success">Oferta excluida.</p><?php endif; ?>
        <?php if (isset($_GET['imported'])): ?><p class="admin-success"><?= (int) $_GET['imported'] ?> campanhas importadas.</p><?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="coupon-admin-form">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
          <input type="hidden" name="action" value="save" />
          <input type="hidden" name="id" value="<?= e((string) $form['id']) ?>" />

          <div class="admin-fieldset">
            <h2>Conteudo publico</h2>
            <div class="admin-two-cols">
              <label>Tipo de oferta
                <select name="offer_type" required>
                  <?php foreach (offer_types() as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $form['offer_type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>Categoria
                <select name="category" required>
                  <?php foreach (['Alimentação e Bebidas', 'Compras', 'Games', 'Educação', 'Entretenimento', 'Kids', 'Serviços', 'Outros'] as $category): ?>
                    <option <?= $form['category'] === $category ? 'selected' : '' ?>><?= e($category) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
            </div>
            <label>Loja / marca<input name="store" value="<?= e($form['store']) ?>" required /></label>
            <label>Titulo<input name="title" value="<?= e($form['title']) ?>" required /></label>
            <label>Descricao<textarea name="description" required><?= e($form['description']) ?></textarea></label>
            <div class="admin-two-cols">
              <label>Como o usuario resgata?
                <select name="redemption_type" required>
                  <?php foreach (redemption_types() as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $form['redemption_type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>Texto do botao<input name="cta_label" value="<?= e($form['cta_label']) ?>" placeholder="Ex: Cadastre-se, Resgatar, Participar" /></label>
            </div>
            <div class="admin-two-cols">
              <label>Texto/codigo para copiar<input name="code" value="<?= e($form['code']) ?>" placeholder="Ex: YBOX, OFERTO10 ou instrucao curta" /></label>
              <label class="admin-check"><input name="members_only" type="checkbox" value="1" <?= (int) $form['members_only'] === 1 ? 'checked' : '' ?> /> Somente usuarios conectados</label>
            </div>
            <label>Mecanica/requisito curto<input name="requirements" value="<?= e($form['requirements']) ?>" placeholder="Ex: Cadastro gratuito, Comprar produto, Sem codigo" /></label>
            <label>Regra/observacao<textarea name="rules"><?= e($form['rules']) ?></textarea></label>
          </div>

          <div class="admin-fieldset">
            <h2>Links e banner</h2>
            <label>URL final da campanha<input name="target_url" type="url" value="<?= e($form['target_url']) ?>" required /></label>
            <label>URL de tracking/afiliado<input name="tracking_url" type="url" value="<?= e($form['tracking_url']) ?>" placeholder="Opcional. Se preenchida, o botao publico usa esta URL." /></label>
            <label>URL do banner<input name="banner_url" type="text" value="<?= e($form['banner_url']) ?>" placeholder="Ou envie um arquivo abaixo" /></label>
            <label>Upload do banner<input name="banner_file" type="file" accept="image/jpeg,image/png,image/webp,image/gif" /></label>
          </div>

          <div class="admin-two-cols">
            <label>Inicio<input name="starts_at" type="date" value="<?= e($form['starts_at']) ?>" required /></label>
            <label>Fim<input name="ends_at" type="date" value="<?= e($form['ends_at']) ?>" required /></label>
          </div>
          <div class="admin-two-cols">
            <label>Status
              <select name="status">
                <?php foreach (['ativo', 'rascunho', 'pausado'] as $status): ?>
                  <option value="<?= e($status) ?>" <?= $form['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label class="admin-check"><input name="featured" type="checkbox" value="1" <?= (int) $form['featured'] === 1 ? 'checked' : '' ?> /> Destaque</label>
          </div>
          <div class="admin-fieldset">
            <h2>Comercial e tracking</h2>
            <div class="admin-two-cols">
              <label>Rede/parceiro<input name="partner_network" value="<?= e($form['partner_network']) ?>" placeholder="Ex: Ybox, Tofu, direto" /></label>
              <label>Payout estimado<input name="payout" inputmode="decimal" value="<?= e((string) $form['payout']) ?>" placeholder="Ex: 2,00" /></label>
            </div>
            <div class="admin-two-cols">
              <label>Cap da campanha<input name="campaign_cap" inputmode="numeric" value="<?= e((string) $form['campaign_cap']) ?>" placeholder="Ex: 500" /></label>
              <label>Prioridade<input name="priority" type="number" value="<?= e((string) $form['priority']) ?>" /></label>
            </div>
            <label>Tags<input name="tags" value="<?= e($form['tags']) ?>" placeholder="Ex: seguro, cpl, mobile, servicos" /></label>
            <label>Evento/pixel<input name="pixel_event" value="<?= e($form['pixel_event']) ?>" placeholder="Ex: kakau_click, lead_submit" /></label>
            <label class="admin-check"><input name="sponsored" type="checkbox" value="1" <?= (int) $form['sponsored'] === 1 ? 'checked' : '' ?> /> Campanha patrocinada</label>
          </div>
          <div class="admin-actions">
            <button type="submit">Salvar oferta</button>
            <?php if ($editing): ?><a href="index.php">Cancelar edicao</a><?php endif; ?>
          </div>
        </form>

        <form method="post" enctype="multipart/form-data" class="coupon-admin-form import-form">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
          <input type="hidden" name="action" value="import_csv" />
          <div class="admin-fieldset">
            <h2>Importar campanhas em lote</h2>
            <p>Use CSV com cabecalhos como modo_resgate, categoria, loja, titulo, descricao, url_final, banner, inicio, fim, tipo, codigo, cta, status, parceiro, payout, cap, tags e somente_logados.</p>
            <p><a href="modelo-campanhas.csv" download>Baixar modelo de CSV</a></p>
            <label>Arquivo CSV<input name="campaigns_csv" type="file" accept=".csv,text/csv" required /></label>
            <div class="admin-actions">
              <button type="submit">Importar CSV</button>
            </div>
          </div>
        </form>
      </section>

      <section class="admin-panel">
        <p class="section-kicker">Ofertas cadastradas</p>
        <h2><?= count($coupons) ?> itens</h2>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Loja</th>
                <th>Tipo</th>
                <th>Resgate</th>
                <th>Categoria</th>
                <th>Status</th>
                <th>Acesso</th>
                <th>Parceiro</th>
                <th>Validade</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($coupons as $coupon): ?>
                <tr>
                  <td><strong><?= e($coupon['store']) ?></strong><br /><span><?= e($coupon['title']) ?></span></td>
                  <td><?= e(offer_type_label($coupon['offer_type'] ?? 'cupom')) ?></td>
                  <td><?= e(redemption_type_label($coupon['redemption_type'] ?? 'texto')) ?></td>
                  <td><?= e($coupon['category']) ?></td>
                  <td><span class="status-pill"><?= e($coupon['status']) ?></span></td>
                  <td><?= (int) ($coupon['members_only'] ?? 0) === 1 ? 'Conectados' : 'Publico' ?></td>
                  <td><?= e($coupon['partner_network'] ?? '') ?></td>
                  <td><?= e(date('d/m/Y', strtotime($coupon['ends_at']))) ?></td>
                  <td class="row-actions">
                    <a href="?edit=<?= (int) $coupon['id'] ?>">Editar</a>
                    <form method="post" onsubmit="return confirm('Excluir esta oferta?');">
                      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
                      <input type="hidden" name="action" value="delete" />
                      <input type="hidden" name="id" value="<?= (int) $coupon['id'] ?>" />
                      <button type="submit">Excluir</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </body>
</html>

