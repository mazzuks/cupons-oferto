<?php

declare(strict_types=1);

require_once __DIR__ . '/coupons.php';

function affiliation_campaign_where(): string
{
    return "(partner_network IS NOT NULL AND partner_network <> '')
        OR (external_id IS NOT NULL AND external_id <> '')
        OR (tracking_url IS NOT NULL AND tracking_url <> '')";
}

function affiliation_summary(): array
{
    $pdo = db();
    if (!$pdo) {
        return [
            'total_campaigns' => 0,
            'active_campaigns' => 0,
            'draft_campaigns' => 0,
            'paused_campaigns' => 0,
            'networks' => 0,
            'monitored_offers' => 0,
            'monitored_brands' => 0,
            'conversions_30d' => 0,
            'commission_30d' => 0,
        ];
    }

    $where = affiliation_campaign_where();
    $campaigns = $pdo->query("SELECT
            COUNT(*) AS total_campaigns,
            COALESCE(SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END), 0) AS active_campaigns,
            COALESCE(SUM(CASE WHEN status = 'rascunho' THEN 1 ELSE 0 END), 0) AS draft_campaigns,
            COALESCE(SUM(CASE WHEN status = 'pausado' THEN 1 ELSE 0 END), 0) AS paused_campaigns,
            COUNT(DISTINCT NULLIF(partner_network, '')) AS networks
        FROM coupons
        WHERE {$where}")->fetch() ?: [];

    $monitoredOffers = (int) $pdo->query("SELECT COUNT(*) FROM integration_watchlist WHERE status IN ('monitorado', 'sumiu')")->fetchColumn();
    $monitoredBrands = (int) $pdo->query("SELECT COUNT(*) FROM integration_brand_watchlist WHERE status = 'monitorado'")->fetchColumn();

    $conversions = $pdo->query("SELECT
            COUNT(*) AS conversions_30d,
            COALESCE(SUM(commission_amount), 0) AS commission_30d
        FROM affiliate_conversions
        WHERE conversion_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetch() ?: [];

    return [
        'total_campaigns' => (int) ($campaigns['total_campaigns'] ?? 0),
        'active_campaigns' => (int) ($campaigns['active_campaigns'] ?? 0),
        'draft_campaigns' => (int) ($campaigns['draft_campaigns'] ?? 0),
        'paused_campaigns' => (int) ($campaigns['paused_campaigns'] ?? 0),
        'networks' => (int) ($campaigns['networks'] ?? 0),
        'monitored_offers' => $monitoredOffers,
        'monitored_brands' => $monitoredBrands,
        'conversions_30d' => (int) ($conversions['conversions_30d'] ?? 0),
        'commission_30d' => (float) ($conversions['commission_30d'] ?? 0),
    ];
}

function affiliation_network_rows(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    $where = affiliation_campaign_where();
    $statement = $pdo->query("SELECT
            COALESCE(NULLIF(partner_network, ''), 'Sem parceiro') AS partner,
            COUNT(*) AS total,
            COALESCE(SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END), 0) AS active,
            COALESCE(SUM(CASE WHEN status = 'rascunho' THEN 1 ELSE 0 END), 0) AS draft,
            COALESCE(SUM(CASE WHEN status = 'pausado' THEN 1 ELSE 0 END), 0) AS paused,
            COALESCE(SUM(CASE WHEN featured = 1 THEN 1 ELSE 0 END), 0) AS featured,
            MIN(ends_at) AS next_expiration,
            MAX(updated_at) AS last_update
        FROM coupons
        WHERE {$where}
        GROUP BY COALESCE(NULLIF(partner_network, ''), 'Sem parceiro')
        ORDER BY total DESC, partner ASC");

    return $statement->fetchAll();
}

function affiliation_network_options(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    $where = affiliation_campaign_where();
    $statement = $pdo->query("SELECT DISTINCT partner_network
        FROM coupons
        WHERE {$where}
          AND partner_network IS NOT NULL
          AND partner_network <> ''
        ORDER BY partner_network ASC");

    return array_values(array_map('strval', array_column($statement->fetchAll(), 'partner_network')));
}

function affiliation_campaign_rows(array $filters): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    $conditions = ['(' . affiliation_campaign_where() . ')'];
    $params = [];

    if (($filters['network'] ?? '') !== '') {
        $conditions[] = 'partner_network = :network';
        $params['network'] = $filters['network'];
    }

    if (($filters['status'] ?? '') !== '') {
        $conditions[] = 'status = :status';
        $params['status'] = $filters['status'];
    }

    if (($filters['q'] ?? '') !== '') {
        $conditions[] = '(store LIKE :q OR title LIKE :q OR category LIKE :q OR external_id LIKE :q)';
        $params['q'] = '%' . $filters['q'] . '%';
    }

    $sql = "SELECT
            c.*,
            (SELECT COUNT(*) FROM coupon_clicks cc WHERE cc.coupon_id = c.id) AS click_count,
            (SELECT COUNT(*) FROM affiliate_conversions ac WHERE ac.coupon_id = c.id) AS conversion_count,
            (SELECT COALESCE(SUM(ac.commission_amount), 0) FROM affiliate_conversions ac WHERE ac.coupon_id = c.id) AS commission_total
        FROM coupons c
        WHERE " . implode(' AND ', $conditions) . "
        ORDER BY
          FIELD(c.status, 'ativo', 'rascunho', 'pausado'),
          c.priority DESC,
          c.ends_at ASC,
          c.updated_at DESC
        LIMIT 250";

    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

function affiliation_watch_rows(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    return $pdo->query("SELECT *
        FROM integration_watchlist
        WHERE status IN ('monitorado', 'sumiu', 'pausado')
        ORDER BY partner ASC, status ASC, store ASC, title ASC
        LIMIT 250")->fetchAll();
}

function affiliation_brand_rows(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    return $pdo->query("SELECT *
        FROM integration_brand_watchlist
        ORDER BY partner ASC, brand_name ASC
        LIMIT 250")->fetchAll();
}
