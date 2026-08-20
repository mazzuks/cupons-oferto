<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/coupons.php';

require_admin();

$error = '';
$editing = null;

function coupon_payload(array $source, string $bannerUrl): array
{
    return [
        'category' => trim($source['category'] ?? ''),
        'store' => trim($source['store'] ?? ''),
        'title' => trim($source['title'] ?? ''),
        'description' => trim($source['description'] ?? ''),
        'code' => trim($source['code'] ?? ''),
        'target_url' => trim($source['target_url'] ?? ''),
        'banner_url' => $bannerUrl,
        'starts_at' => $source['starts_at'] ?? date('Y-m-d'),
        'ends_at' => $source['ends_at'] ?? date('Y-m-d'),
        'status' => $source['status'] ?? 'rascunho',
        'featured' => isset($source['featured']) ? 1 : 0,
        'rules' => trim($source['rules'] ?? ''),
    ];
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

            save_coupon($payload, $id);
            redirect('index.php?saved=1');
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
    <link rel="stylesheet" href="../styles.css?v=20260820-cards" />
    <link rel="stylesheet" href="admin.css" />
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
        <p class="section-kicker"><?= $editing ? 'Editar cupom' : 'Novo cupom' ?></p>
        <h1><?= $editing ? e($editing['store']) : 'Cadastrar cupom' ?></h1>
        <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
        <?php if (isset($_GET['saved'])): ?><p class="admin-success">Cupom salvo.</p><?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?><p class="admin-success">Cupom excluido.</p><?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="coupon-admin-form">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
          <input type="hidden" name="action" value="save" />
          <input type="hidden" name="id" value="<?= e((string) $form['id']) ?>" />

          <label>Categoria
            <select name="category" required>
              <?php foreach (['Alimentação e Bebidas', 'Compras', 'Games', 'Educação', 'Entretenimento', 'Kids', 'Serviços', 'Outros'] as $category): ?>
                <option <?= $form['category'] === $category ? 'selected' : '' ?>><?= e($category) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>Loja<input name="store" value="<?= e($form['store']) ?>" required /></label>
          <label>Titulo<input name="title" value="<?= e($form['title']) ?>" required /></label>
          <label>Descricao<textarea name="description" required><?= e($form['description']) ?></textarea></label>
          <label>Codigo<input name="code" value="<?= e($form['code']) ?>" /></label>
          <label>URL do cupom<input name="target_url" type="url" value="<?= e($form['target_url']) ?>" required /></label>
          <label>URL do banner<input name="banner_url" type="text" value="<?= e($form['banner_url']) ?>" placeholder="Ou envie um arquivo abaixo" /></label>
          <label>Upload do banner<input name="banner_file" type="file" accept="image/jpeg,image/png,image/webp,image/gif" /></label>
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
          <label>Regra/observacao<textarea name="rules"><?= e($form['rules']) ?></textarea></label>
          <div class="admin-actions">
            <button type="submit">Salvar cupom</button>
            <?php if ($editing): ?><a href="index.php">Cancelar edicao</a><?php endif; ?>
          </div>
        </form>
      </section>

      <section class="admin-panel">
        <p class="section-kicker">Cupons cadastrados</p>
        <h2><?= count($coupons) ?> itens</h2>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Loja</th>
                <th>Categoria</th>
                <th>Status</th>
                <th>Validade</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($coupons as $coupon): ?>
                <tr>
                  <td><strong><?= e($coupon['store']) ?></strong><br /><span><?= e($coupon['title']) ?></span></td>
                  <td><?= e($coupon['category']) ?></td>
                  <td><span class="status-pill"><?= e($coupon['status']) ?></span></td>
                  <td><?= e(date('d/m/Y', strtotime($coupon['ends_at']))) ?></td>
                  <td class="row-actions">
                    <a href="?edit=<?= (int) $coupon['id'] ?>">Editar</a>
                    <form method="post" onsubmit="return confirm('Excluir este cupom?');">
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

