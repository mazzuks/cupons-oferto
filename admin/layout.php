<?php

declare(strict_types=1);

function admin_nav_items(): array
{
    return [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'hint' => 'Visão geral', 'href' => 'dashboard.php'],
        ['key' => 'ofertas', 'label' => 'Ofertas', 'hint' => 'Cupons e campanhas', 'href' => 'ofertas-lista.php'],
        ['key' => 'afiliacao', 'label' => 'Afiliação', 'hint' => 'Redes e campanhas', 'href' => 'afiliacao.php'],
        ['key' => 'notificacoes', 'label' => 'Notificações', 'hint' => 'Alertas do sistema', 'href' => 'notificacoes.php'],
        ['key' => 'logs', 'label' => 'Logs', 'hint' => 'Alterações e eventos', 'href' => 'logs.php'],
        ['key' => 'relatorios', 'label' => 'Relatórios', 'hint' => 'Cliques e CSV', 'href' => 'relatorios.php'],
        ['key' => 'usuarios', 'label' => 'Usuários', 'hint' => 'Acessos do CRM', 'href' => 'usuarios.php'],
        ['key' => 'site', 'label' => 'Ver site', 'hint' => 'Vitrine pública', 'href' => '../index.php'],
        ['key' => 'logout', 'label' => 'Sair', 'hint' => 'Encerrar sessão', 'href' => 'logout.php'],
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
    <link rel="stylesheet" href="../styles.css?v=20260826-brand-logos" />
    <link rel="stylesheet" href="admin.css?v=20260826-brand-logos" />
  </head>
  <body>
    <div class="admin-app">
      <aside class="admin-sidebar" aria-label="Navegação do CRM">
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
    <script>
      document.querySelectorAll('[data-tabs]').forEach((shell) => {
        const buttons = shell.querySelectorAll('[data-tab-target]');
        const panels = shell.querySelectorAll('[data-tab-panel]');

        buttons.forEach((button) => {
          button.addEventListener('click', () => {
            const target = button.getAttribute('data-tab-target');

            buttons.forEach((item) => item.classList.toggle('is-active', item === button));
            panels.forEach((panel) => panel.classList.toggle('is-active', panel.getAttribute('data-tab-panel') === target));
          });
        });
      });

      document.querySelectorAll('[data-copy-value]').forEach((button) => {
        button.addEventListener('click', async () => {
          const value = button.getAttribute('data-copy-value') || '';

          try {
            await navigator.clipboard.writeText(value);
            button.dataset.originalText = button.dataset.originalText || button.textContent;
            button.textContent = 'Copiado';
            window.setTimeout(() => {
              button.textContent = button.dataset.originalText || 'Copiar';
            }, 1600);
          } catch (error) {
            button.textContent = 'Copie manualmente';
          }
        });
      });
    </script>
  </body>
</html>
<?php
}

function admin_affiliation_subnav(string $activeKey): void
{
    $items = [
        ['key' => 'overview', 'label' => 'Visão geral', 'href' => 'afiliacao.php'],
        ['key' => 'campanhas', 'label' => 'Campanhas', 'href' => 'afiliacao-campanhas.php'],
        ['key' => 'parceiros', 'label' => 'Parceiros', 'href' => 'afiliacao-parceiros.php'],
        ['key' => 'tracking', 'label' => 'Tracking', 'href' => 'afiliacao-tracking.php'],
        ['key' => 'criativos', 'label' => 'Criativos', 'href' => 'afiliacao-criativos.php'],
        ['key' => 'carteira', 'label' => 'Carteira', 'href' => 'afiliacao-carteira.php'],
        ['key' => 'selecionar', 'label' => 'Selecionar cupons', 'href' => 'afiliacao-selecionar.php'],
        ['key' => 'redes', 'label' => 'Redes', 'href' => 'apis.php'],
        ['key' => 'monitoramento', 'label' => 'Monitoramento', 'href' => 'afiliacao-monitoramento.php'],
        ['key' => 'conversoes', 'label' => 'Conversões', 'href' => 'afiliacao-conversoes.php'],
        ['key' => 'classificacao', 'label' => 'Classificação', 'href' => 'import-classifications.php'],
    ];
    ?>
      <nav class="admin-subnav" aria-label="Subguias de afiliação">
        <?php foreach ($items as $item): ?>
          <a class="<?= $activeKey === $item['key'] ? 'is-active' : '' ?>" href="<?= e($item['href']) ?>"><?= e($item['label']) ?></a>
        <?php endforeach; ?>
      </nav>
<?php
}

function admin_offers_subnav(string $activeKey): void
{
    $items = [
        ['key' => 'lista', 'label' => 'Ofertas criadas', 'href' => 'ofertas-lista.php'],
        ['key' => 'criar', 'label' => 'Criar oferta', 'href' => 'index.php'],
    ];
    ?>
      <nav class="admin-subnav" aria-label="Subguias de ofertas">
        <?php foreach ($items as $item): ?>
          <a class="<?= $activeKey === $item['key'] ? 'is-active' : '' ?>" href="<?= e($item['href']) ?>"><?= e($item['label']) ?></a>
        <?php endforeach; ?>
      </nav>
<?php
}
