<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/layout.php';

require_admin();

function admin_users(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    return $pdo->query('SELECT id, name, email, created_at FROM admins ORDER BY created_at DESC, id DESC')->fetchAll();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Preencha nome, e-mail e senha.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Informe um e-mail valido.';
    } elseif (strlen($password) < 8) {
        $error = 'Use uma senha com pelo menos 8 caracteres.';
    } else {
        try {
            create_admin($name, $email, $password);
            redirect('usuarios.php?created=1');
        } catch (Throwable $exception) {
            $error = 'Nao foi possivel criar este usuario. Verifique se o e-mail ja existe.';
        }
    }
}

$users = admin_users();
?>
<?php admin_layout_start('Usuarios - Oferto Cupons', 'usuarios', 'Acessos'); ?>
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Usuarios</p>
          <h1>Acessos do CRM</h1>
          <p>Cadastre as pessoas que podem administrar cupons, campanhas, relatorios e importacoes em lote.</p>
        </div>
        <div class="admin-hero-stats" aria-label="Resumo de usuarios">
          <span><strong><?= count($users) ?></strong> usuarios</span>
        </div>
      </section>

      <div class="admin-layout admin-layout-users">
        <section class="admin-panel">
          <p class="section-kicker">Novo acesso</p>
          <h2>Cadastrar usuario</h2>
          <?php if ($error): ?><p class="admin-alert"><?= e($error) ?></p><?php endif; ?>
          <?php if (isset($_GET['created'])): ?><p class="admin-success">Usuario criado.</p><?php endif; ?>

          <form method="post" class="coupon-admin-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
            <label>Nome<input name="name" type="text" placeholder="Ex: Ana Souza" required /></label>
            <label>E-mail<input name="email" type="email" placeholder="ana@empresa.com.br" required /></label>
            <label>Senha temporaria<input name="password" type="password" minlength="8" required /></label>
            <div class="admin-actions">
              <button type="submit">Criar acesso</button>
            </div>
          </form>
        </section>

        <section class="admin-panel">
          <p class="section-kicker">Equipe</p>
          <h2>Usuarios cadastrados</h2>
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Nome</th>
                  <th>E-mail</th>
                  <th>Criado em</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $user): ?>
                  <tr>
                    <td><strong><?= e($user['name']) ?></strong></td>
                    <td><?= e($user['email']) ?></td>
                    <td><?= e(date('d/m/Y H:i', strtotime($user['created_at']))) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      </div>
<?php admin_layout_end(); ?>
