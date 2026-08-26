<?php

declare(strict_types=1);

function admin_nav_items(): array
{
    return [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'hint' => 'Visao geral', 'href' => 'dashboard.php'],
        ['key' => 'ofertas', 'label' => 'Ofertas', 'hint' => 'Cupons e campanhas', 'href' => 'index.php'],
        ['key' => 'apis', 'label' => 'APIs', 'hint' => 'Parceiros e feeds', 'href' => 'apis.php'],
        ['key' => 'notificacoes', 'label' => 'Notificacoes', 'hint' => 'Alertas do sistema', 'href' => 'notificacoes.php'],
        ['key' => 'logs', 'label' => 'Logs', 'hint' => 'Alteracoes e eventos', 'href' => 'logs.php'],
        ['key' => 'relatorios', 'label' => 'Relatorios', 'hint' => 'Cliques e CSV', 'href' => 'relatorios.php'],
        ['key' => 'usuarios', 'label' => 'Usuarios', 'hint' => 'Acessos do CRM', 'href' => 'usuarios.php'],
        ['key' => 'site', 'label' => 'Ver site', 'hint' => 'Vitrine publica', 'href' => '../index.php'],
        ['key' => 'logout', 'label' => 'Sair', 'hint' => 'Encerrar sessao', 'href' => 'logout.php'],
    ];
}

function admin_layout_start(string $title, string $activeKey, string $eyebrow = 'CRM Oferto'): void
{
    $admin = current_admin();
    ?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= e($title) ?></title>
    <link rel="icon" href="../assets/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" href="../assets/favicon.png" />
    <link rel="stylesheet" href="../styles.css?v=20260824-campaign-flow" />
    <link rel="stylesheet" href="admin.css?v=20260825-monitoring" />
  </head>
  <body>
    <div class="admin-app">
      <aside class="admin-sidebar" aria-label="Navegacao do CRM">
        <a class="admin-sidebar-brand" href="dashboard.php">
          <img src="https://oferto.digital/wp-content/uploads/2024/08/oferto.png" alt="Oferto" />
          <span><?= e($eyebrow) ?></span>
        </a>

        <nav class="admin-sidebar-nav">
          <?php foreach (admin_nav_items() as $item): ?>
            <a class="admin-nav-item <?= $activeKey === $item['key'] ? 'is-active' : '' ?>" href="<?= e($item['href']) ?>">
              <strong><?= e($item['label']) ?></strong>
              <span><?= e($item['hint']) ?></span>
            </a>
          <?php endforeach; ?>
        </nav>

        <div class="admin-sidebar-user">
          <span>Logado como</span>
          <strong><?= e($admin['name'] ?? 'Administrador') ?></strong>
        </div>
      </aside>

      <div class="admin-main">
        <main class="admin-shell">
<?php
}

function admin_layout_end(): void
{
    ?>
        </main>
      </div>
    </div>
  </body>
</html>
<?php
}
