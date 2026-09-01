<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/affiliation.php';
require_once __DIR__ . '/layout.php';

require_admin();

$summary = affiliation_summary();
$networks = affiliation_network_rows();
?>
<?php admin_layout_start('Afiliacao - Oferto Cupons', 'afiliacao', 'Afiliacao'); ?>
      <section class="admin-hero admin-api-hero">
        <div>
          <p class="section-kicker">Modulo de afiliacao</p>
          <h1>Campanhas, redes e monitoramento em um so lugar</h1>
          <p>Esta area separa o operacional de afiliacao da curadoria editorial de cupons. Aqui ficam campanhas importadas, redes conectadas, monitoramento, conversoes e classificacao.</p>
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
          <small><?= (int) $summary['active_campaigns'] ?> ativas</small>
        </article>
        <article class="admin-kpi-card">
          <span>Redes conectadas</span>
          <strong><?= (int) $summary['networks'] ?></strong>
          <small>Lomadee, Awin, Offer18, HasOffers e outras</small>
        </article>
        <article class="admin-kpi-card">
          <span>Monitoradas</span>
          <strong><?= (int) $summary['monitored_offers'] ?></strong>
          <small><?= (int) $summary['monitored_brands'] ?> marcas acompanhadas</small>
        </article>
        <article class="admin-kpi-card">
          <span>Conversoes 30d</span>
          <strong><?= (int) $summary['conversions_30d'] ?></strong>
          <small>R$ <?= number_format((float) $summary['commission_30d'], 2, ',', '.') ?> em comissao</small>
        </article>
      </section>

      <section class="admin-panel">
        <div class="section-heading admin-section-heading">
          <div>
            <p class="section-kicker">Por rede</p>
            <h2>Onde as campanhas estao concentradas</h2>
          </div>
          <span><?= count($networks) ?> redes</span>
        </div>

        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Rede</th>
                <th>Total</th>
                <th>Ativas</th>
                <th>Rascunhos</th>
                <th>Pausadas</th>
                <th>Destaques</th>
                <th>Proximo vencimento</th>
                <th>Ultima atualizacao</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($networks as $row): ?>
                <tr>
                  <td><strong><?= e($row['partner']) ?></strong></td>
                  <td><?= (int) $row['total'] ?></td>
                  <td><?= (int) $row['active'] ?></td>
                  <td><?= (int) $row['draft'] ?></td>
                  <td><?= (int) $row['paused'] ?></td>
                  <td><?= (int) $row['featured'] ?></td>
                  <td><?= e($row['next_expiration'] ? date('d/m/Y', strtotime($row['next_expiration'])) : '-') ?></td>
                  <td><?= e($row['last_update'] ? date('d/m/Y H:i', strtotime($row['last_update'])) : '-') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$networks): ?>
                <tr><td colspan="8" class="admin-empty-cell">Nenhuma campanha afiliada encontrada.</td></tr>
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
            <p>Inspirado na tela de campanhas disponiveis do Platto: filtros por busca, rede e status para entender o que esta ativo antes de publicar ou pausar.</p>
          </div>
          <a class="admin-primary-link" href="afiliacao-campanhas.php">Abrir campanhas</a>
        </article>
        <article class="admin-api-hub-card is-ready">
          <div>
            <span>Redes</span>
            <h2>Conectores afiliados</h2>
            <p>Lomadee, Awin, Offer18 e HasOffers continuam com telas proprias, mas agora ficam dentro da area de afiliacao.</p>
          </div>
          <a class="admin-primary-link" href="apis.php">Abrir redes</a>
        </article>
        <article class="admin-api-hub-card is-ready">
          <div>
            <span>Base do bot</span>
            <h2>Nichos e tags</h2>
            <p>A classificacao por loja ajuda novas ofertas a entrarem com contexto melhor para busca, SEO e atendimento no WhatsApp.</p>
          </div>
          <a class="admin-primary-link" href="import-classifications.php">Classificar</a>
        </article>
      </section>
<?php admin_layout_end(); ?>

