<?php
require_once __DIR__ . '/../includes/auth.php';

if (admin_count() === 0) {
    redirect('setup.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (login_admin(trim($_POST['email'] ?? ''), $_POST['password'] ?? '')) {
        redirect('dashboard.php');
    }
    $error = 'E-mail ou senha incorretos.';
}
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - Oferto Cupons</title>
    <link rel="icon" href="../assets/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" href="../assets/favicon.png" />
    <link rel="stylesheet" href="../styles.css?v=20260824-campaign-ux" />
    <link rel="stylesheet" href="admin.css?v=20260824-campaign-ux" />
  </head>
  <body class="admin-screen">
    <main class="auth-card">
      <p class="section-kicker">Painel</p>
      <h1>Entrar</h1>
      <?php if (isset($_GET['created'])): ?><p class="admin-success">Admin criado. Agora faca login.</p><?php endif; ?>
      <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
      <form method="post" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
        <label>E-mail<input name="email" type="email" required /></label>
        <label>Senha<input name="password" type="password" required /></label>
        <button type="submit">Entrar</button>
      </form>
    </main>
  </body>
</html>

