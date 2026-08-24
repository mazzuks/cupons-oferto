<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/coupons.php';

require_admin();

$startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end'] ?? date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
    $startDate = date('Y-m-d', strtotime('-30 days'));
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
    $endDate = date('Y-m-d');
}

$summary = click_report_summary($startDate, $endDate);
$rows = click_report_rows($startDate, $endDate);

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio-cliques-' . $startDate . '-' . $endDate . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['periodo_inicio', $startDate], ';');
    fputcsv($output, ['periodo_fim', $endDate], ';');
    fputcsv($output, [], ';');
    fputcsv($output, ['loja', 'titulo', 'tipo', 'categoria', 'status', 'parceiro', 'cliques_total', 'cta', 'detalhes', 'mini_card', 'usuarios_unicos', 'ultimo_clique'], ';');
    foreach ($rows as $row) {
        fputcsv($output, [
            $row['store'],
            $row['title'],
            offer_type_label($row['offer_type'] ?? 'cupom'),
            $row['category'],
            $row['status'],
            $row['partner_network'],
            (int) $row['total_clicks'],
            (int) $row['cta_clicks'],
            (int) $row['detail_clicks'],
            (int) $row['mini_clicks'],
            (int) $row['unique_users'],
            $row['last_click_at'],
        ], ';');
    }
    fclose($output);
    exit;
}

function format_report_date(?string $date): string
{
    if (!$date) {
        return '-';
    }

    return date('d/m/Y H:i', strtotime($date));
}
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Relatorios - Oferto Cupons</title>
    <link rel="icon" href="../assets/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" href="../assets/favicon.png" />
    <link rel="stylesheet" href="../styles.css?v=20260824-platto" />
    <link rel="stylesheet" href="admin.css?v=20260824-platto" />
  </head>
  <body>
    <header class="admin-header">
      <a class="brand" href="index.php">
        <img src="https://oferto.digital/wp-content/uploads/2024/08/oferto.png" alt="Oferto" />
        <span>CRM</span>
      </a>
      <nav>
        <a href="index.php">Ofertas</a>
        <a href="relatorios.php">Relatorios</a>
        <a href="../index.php">Ver site</a>
        <a href="logout.php">Sair</a>
      </nav>
    </header>

    <main class="admin-shell">
      <section class="admin-hero">
        <div>
          <p class="section-kicker">Performance</p>
          <h1>Relatorio de cliques</h1>
          <p>Acompanhe quais ofertas estao gerando mais saidas, detalhe, clique no CTA e interesse por parceiro.</p>
        </div>
        <form class="admin-report-filter" method="get">
          <label>Inicio<input name="start" type="date" value="<?= e($startDate) ?>" /></label>
          <label>Fim<input name="end" type="date" value="<?= e($endDate) ?>" /></label>
          <button type="submit">Filtrar</button>
          <a href="?start=<?= e($startDate) ?>&end=<?= e($endDate) ?>&export=csv">Exportar CSV</a>
        </form>
      </section>

      <section class="admin-kpi-grid">
        <article class="admin-kpi-card">
          <span>Cliques totais</span>
          <strong><?= (int) $summary['total_clicks'] ?></strong>
          <small>Eventos registrados no periodo</small>
        </article>
        <article class="admin-kpi-card">
          <span>Usuarios unicos</span>
          <strong><?= (int) $summary['unique_users'] ?></strong>
          <small>Estimativa por hash anonimo</small>
        </article>
        <article class="admin-kpi-card">
          <span>Ofertas clicadas</span>
          <strong><?= (int) $summary['active_offers'] ?></strong>
          <small>Campanhas com pelo menos um clique</small>
        </article>
        <article class="admin-kpi-card">
          <span>Top oferta</span>
          <strong class="admin-kpi-text"><?= e($summary['top_offer'] ?: '-') ?></strong>
          <small>Maior volume no periodo</small>
        </article>
      </section>

      <section class="admin-panel">
        <div class="section-heading admin-section-heading">
          <div>
            <p class="section-kicker">Por campanha</p>
            <h2>Desempenho das ofertas</h2>
          </div>
          <span id="result-count"><?= count($rows) ?> itens</span>
        </div>

        <div class="admin-table-wrap">
          <table class="admin-table admin-report-table">
            <thead>
              <tr>
                <th>Oferta</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Parceiro</th>
                <th>Total</th>
                <th>CTA</th>
                <th>Detalhes</th>
                <th>Mini</th>
                <th>Unicos</th>
                <th>Ultimo clique</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td><strong><?= e($row['store']) ?></strong><br /><span><?= e($row['title']) ?></span></td>
                  <td><span class="admin-pill admin-pill-type"><?= e(offer_type_label($row['offer_type'] ?? 'cupom')) ?></span></td>
                  <td><span class="status-pill status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                  <td><?= e($row['partner_network'] ?: '-') ?></td>
                  <td><strong><?= (int) $row['total_clicks'] ?></strong></td>
                  <td><?= (int) $row['cta_clicks'] ?></td>
                  <td><?= (int) $row['detail_clicks'] ?></td>
                  <td><?= (int) $row['mini_clicks'] ?></td>
                  <td><?= (int) $row['unique_users'] ?></td>
                  <td><?= e(format_report_date($row['last_click_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$rows): ?>
                <tr>
                  <td colspan="10" class="admin-empty-cell">Nenhuma oferta encontrada.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </body>
</html>
