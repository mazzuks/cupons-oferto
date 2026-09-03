<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/affiliation.php';
require_once __DIR__ . '/layout.php';

require_admin();

$summary = affiliation_summary();
$networks = affiliation_network_rows();
?>
<?php admin_layout_start('Afiliação - Oferto Cupons', 'afiliacao', 'Afiliação'); ?>
      <section class="admin-hero admin-api-hero">
        <div>
          <p class="section-kicker">Módulo de afiliação</p>
          <h1>Campanhas afiliadas, redes e resultados em um só lugar</h1>
          <p>Esta área organiza campanhas afiliadas, parceiros, cliques, conversões e carteira em tabelas próprias. A vitrine de cupons continua separada, e só entra aqui quando uma oferta for escolhida para operação afiliada.</p>
        </div>
        <div class="admin-hero-actions">
          <a class="admin-primary-link" href="afiliacao-campanhas.php">Ver campanhas</a>
        </div>
      </section>

      <?php admin_affiliation_subnav('overview'); ?>

      <section class="admin-kpi-grid">
        <article class="admin-kpi-card">
          <span>Campanhas afiliadas</span>
          <strong><?= (int) $summary['total_campaigns'] ?></strong>
          <small><?= (int) $summary['published_campaigns'] ?> publicadas</small>
        </article>
        <article class="admin-kpi-card">
          <span>Parceiros afiliados</span>
          <strong><?= (int) $summary['total_partners'] ?></strong>
          <small><?= (int) $summary['active_partners'] ?> ativos</small>
        </article>
        <article class="admin-kpi-card">
          <span>Cliques 30d</span>
          <strong><?= (int) $summary['clicks_30d'] ?></strong>
          <small>Eventos do módulo afiliado</small>
        </article>
        <article class="admin-kpi-card">
          <span>Conversões 30d</span>
          <strong><?= (int) $summary['conversions_30d'] ?></strong>
          <small>R$ <?= number_format((float) $summary['commission_30d'], 2, ',', '.') ?> em comissão</small>
        </article>
        <article class="admin-kpi-card">
          <span>Taxa de conversão</span>
          <strong><?= number_format((float) $summary['conversion_rate_30d'], 2, ',', '.') ?>%</strong>
          <small>Conversões sobre cliques em 30 dias</small>
        </article>
        <article class="admin-kpi-card">
          <span>EPC 30d</span>
          <strong>R$ <?= number_format((float) $summary['epc_30d'], 2, ',', '.') ?></strong>
          <small>Comissão média por clique</small>
        </article>
      </section>

      <section class="admin-panel">
        <div class="section-heading admin-section-heading">
          <div>
            <p class="section-kicker">Por rede</p>
            <h2>Onde as campanhas estão concentradas</h2>
          </div>
          <span><?= count($networks) ?> redes</span>
        </div>

        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Rede</th>
                <th>Total</th>
                <th>Publicadas</th>
                <th>Selecionadas</th>
                <th>Disponíveis</th>
                <th>Pausadas</th>
                <th>Próximo vencimento</th>
                <th>Última atualização</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($networks as $row): ?>
                <tr>
                  <td><strong><?= e($row['partner']) ?></strong></td>
                  <td><?= (int) $row['total'] ?></td>
                  <td><?= (int) $row['published'] ?></td>
                  <td><?= (int) $row['selected'] ?></td>
                  <td><?= (int) $row['available'] ?></td>
                  <td><?= (int) $row['paused'] ?></td>
                  <td><?= e($row['next_expiration'] ? date('d/m/Y', strtotime($row['next_expiration'])) : '-') ?></td>
                  <td><?= e($row['last_update'] ? date('d/m/Y H:i', strtotime($row['last_update'])) : '-') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$networks): ?>
                <tr><td colspan="8" class="admin-empty-cell">Nenhuma campanha afiliada encontrada. Use "Selecionar cupons" para copiar ofertas da vitrine para a área de afiliação, ou importe campanhas diretamente por uma rede.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <section class="admin-api-hub-grid">
        <article class="admin-api-hub-card is-ready">
          <div>
            <span>Do Platto para o Oferto</span>
            <h2>Campanhas apartadas</h2>
            <p>Inspirado no módulo B2B do Platto: campanha afiliada tem status, tracking, postback secret, payout, conversões e cliques separados da vitrine pública.</p>
          </div>
          <a class="admin-primary-link" href="afiliacao-campanhas.php">Abrir campanhas</a>
        </article>
        <article class="admin-api-hub-card is-ready">
          <div>
            <span>Redes</span>
            <h2>Conectores afiliados</h2>
            <p>Lomadee, Awin, Offer18 e HasOffers continuam com telas próprias. As campanhas podem ser importadas para a área de afiliação antes de serem publicadas como cupom.</p>
          </div>
          <a class="admin-primary-link" href="apis.php">Abrir redes</a>
        </article>
        <article class="admin-api-hub-card is-ready">
          <div>
            <span>Base do bot</span>
            <h2>Seleção consciente</h2>
            <p>Nem todo cupom precisa virar campanha afiliada. O CRM permite escolher quais itens entram na mecânica de afiliação, sem misturar as bases.</p>
          </div>
          <a class="admin-primary-link" href="afiliacao-selecionar.php">Selecionar</a>
        </article>
      </section>
<?php admin_layout_end(); ?>
