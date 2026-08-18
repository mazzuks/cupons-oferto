<?php
require_once __DIR__ . '/../includes/auth.php';

$error = '';

if (admin_count() > 0) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Preencha nome, e-mail valido e senha com pelo menos 8 caracteres.';
    } else {
        create_admin($name, $email, $password);
        redirect('login.php?created=1');
    }
}
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Criar admin - Oferto Cupons</title>
    <link rel="stylesheet" href="../styles.css" />
    <link rel="stylesheet" href="admin.css" />
  </head>
  <body class="admin-screen">
    <main class="auth-card">
      <p class="section-kicker">Primeiro acesso</p>
      <h1>Criar administrador</h1>
      <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
      <form method="post" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
        <label>Nome<input name="name" required /></label>
        <label>E-mail<input name="email" type="email" required /></label>
        <label>Senha<input name="password" type="password" minlength="8" required /></label>
        <button type="submit">Criar admin</button>
      </form>
    </main>
  </body>
</html>
