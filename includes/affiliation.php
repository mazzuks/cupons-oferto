<?php

declare(strict_types=1);

require_once __DIR__ . '/coupons.php';

function affiliation_summary(): array
{
    $pdo = db();
    if (!$pdo) {
        return affiliation_empty_summary();
    }

    $campaigns = $pdo->query("SELECT
            COUNT(*) AS total_campaigns,
            COALESCE(SUM(CASE WHEN status = 'publicada' THEN 1 ELSE 0 END), 0) AS published_campaigns,
            COALESCE(SUM(CASE WHEN status = 'selecionada' THEN 1 ELSE 0 END), 0) AS selected_campaigns,
            COALESCE(SUM(CASE WHEN status = 'disponivel' THEN 1 ELSE 0 END), 0) AS available_campaigns,
            COALESCE(SUM(CASE WHEN status = 'pausada' THEN 1 ELSE 0 END), 0) AS paused_campaigns,
            COUNT(DISTINCT network) AS networks
        FROM affiliate_campaigns")->fetch() ?: [];

    $partners = $pdo->query("SELECT
            COUNT(*) AS total_partners,
            COALESCE(SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END), 0) AS active_partners
        FROM affiliate_partners")->fetch() ?: [];

    $tracking = $pdo->query("SELECT
            (SELECT COUNT(*) FROM affiliate_clicks WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS clicks_30d,
            (SELECT COUNT(*) FROM affiliate_campaign_conversions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS conversions_30d,
            (SELECT COALESCE(SUM(commission_amount), 0) FROM affiliate_campaign_conversions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS commission_30d
        ")->fetch() ?: [];

    return [
        'total_campaigns' => (int) ($campaigns['total_campaigns'] ?? 0),
        'published_campaigns' => (int) ($campaigns['published_campaigns'] ?? 0),
        'selected_campaigns' => (int) ($campaigns['selected_campaigns'] ?? 0),
        'available_campaigns' => (int) ($campaigns['available_campaigns'] ?? 0),
        'paused_campaigns' => (int) ($campaigns['paused_campaigns'] ?? 0),
        'networks' => (int) ($campaigns['networks'] ?? 0),
        'total_partners' => (int) ($partners['total_partners'] ?? 0),
        'active_partners' => (int) ($partners['active_partners'] ?? 0),
        'clicks_30d' => (int) ($tracking['clicks_30d'] ?? 0),
        'conversions_30d' => (int) ($tracking['conversions_30d'] ?? 0),
        'commission_30d' => (float) ($tracking['commission_30d'] ?? 0),
    ];
}

function affiliation_empty_summary(): array
{
    return [
        'total_campaigns' => 0,
        'published_campaigns' => 0,
        'selected_campaigns' => 0,
        'available_campaigns' => 0,
        'paused_campaigns' => 0,
        'networks' => 0,
        'total_partners' => 0,
        'active_partners' => 0,
        'clicks_30d' => 0,
        'conversions_30d' => 0,
        'commission_30d' => 0,
    ];
}

function affiliation_network_rows(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    return $pdo->query("SELECT
            network AS partner,
            COUNT(*) AS total,
            COALESCE(SUM(CASE WHEN status = 'publicada' THEN 1 ELSE 0 END), 0) AS published,
            COALESCE(SUM(CASE WHEN status = 'selecionada' THEN 1 ELSE 0 END), 0) AS selected,
            COALESCE(SUM(CASE WHEN status = 'disponivel' THEN 1 ELSE 0 END), 0) AS available,
            COALESCE(SUM(CASE WHEN status = 'pausada' THEN 1 ELSE 0 END), 0) AS paused,
            MIN(ends_at) AS next_expiration,
            MAX(updated_at) AS last_update
        FROM affiliate_campaigns
        GROUP BY network
        ORDER BY total DESC, network ASC")->fetchAll();
}

function affiliation_network_options(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    $statement = $pdo->query("SELECT DISTINCT network FROM affiliate_campaigns ORDER BY network ASC");
    return array_values(array_map('strval', array_column($statement->fetchAll(), 'network')));
}

function affiliation_campaign_rows(array $filters): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    $conditions = ['1 = 1'];
    $params = [];

    if (($filters['network'] ?? '') !== '') {
        $conditions[] = 'ac.network = :network';
        $params['network'] = $filters['network'];
    }

    if (($filters['status'] ?? '') !== '') {
        $conditions[] = 'ac.status = :status';
        $params['status'] = $filters['status'];
    }

    if (($filters['q'] ?? '') !== '') {
        $conditions[] = '(ac.advertiser LIKE :q OR ac.title LIKE :q OR ac.category LIKE :q OR ac.external_id LIKE :q)';
        $params['q'] = '%' . $filters['q'] . '%';
    }

    $sql = "SELECT
            ac.*,
            (SELECT COUNT(*) FROM affiliate_clicks clk WHERE clk.campaign_id = ac.id) AS click_count,
            (SELECT COUNT(*) FROM affiliate_campaign_conversions conv WHERE conv.campaign_id = ac.id) AS conversion_count,
            (SELECT COALESCE(SUM(conv.commission_amount), 0) FROM affiliate_campaign_conversions conv WHERE conv.campaign_id = ac.id) AS commission_total
        FROM affiliate_campaigns ac
        WHERE " . implode(' AND ', $conditions) . "
        ORDER BY
          FIELD(ac.status, 'publicada', 'selecionada', 'disponivel', 'pausada', 'encerrada'),
          ac.ends_at ASC,
          ac.updated_at DESC
        LIMIT 250";

    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

function affiliation_candidate_coupon_rows(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    return $pdo->query("SELECT
            c.*,
            ac.id AS affiliate_campaign_id,
            ac.status AS affiliate_status
        FROM coupons c
        LEFT JOIN affiliate_campaigns ac ON ac.source_coupon_id = c.id
        WHERE c.status IN ('ativo', 'rascunho', 'pausado')
        ORDER BY ac.id IS NULL DESC, c.featured DESC, c.updated_at DESC
        LIMIT 250")->fetchAll();
}

function affiliation_select_coupon_campaigns(array $couponIds): int
{
    $pdo = db();
    if (!$pdo || !$couponIds) {
        return 0;
    }

    $selected = array_values(array_unique(array_filter(array_map('intval', $couponIds))));
    if (!$selected) {
        return 0;
    }

    $placeholders = implode(',', array_fill(0, count($selected), '?'));
    $statement = $pdo->prepare("SELECT * FROM coupons WHERE id IN ({$placeholders})");
    $statement->execute($selected);

    $created = 0;
    foreach ($statement->fetchAll() as $coupon) {
        $created += affiliation_upsert_campaign_from_coupon($coupon);
    }

    return $created;
}

function affiliation_upsert_campaign_from_coupon(array $coupon): int
{
    $pdo = db();
    if (!$pdo) {
        return 0;
    }

    $network = trim((string) ($coupon['partner_network'] ?? ''));
    $network = $network !== '' ? $network : 'oferto';
    $externalId = trim((string) ($coupon['external_id'] ?? ''));
    $externalId = $externalId !== '' ? $externalId : 'coupon:' . (int) $coupon['id'];
    $trackingUrl = trim((string) ($coupon['tracking_url'] ?? ''));
    $landingUrl = $trackingUrl !== '' ? $trackingUrl : trim((string) ($coupon['target_url'] ?? ''));
    $secret = bin2hex(random_bytes(24));

    $statement = $pdo->prepare("INSERT INTO affiliate_campaigns
        (source_coupon_id, network, external_id, advertiser, title, description, category, landing_url, tracking_url,
         banner_url, logo_url, code, rules, payout, payout_model, campaign_cap, starts_at, ends_at, status,
         postback_secret, utm_source_gate)
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'selecionada', ?, 'oferto')
        ON DUPLICATE KEY UPDATE
          source_coupon_id = VALUES(source_coupon_id),
          advertiser = VALUES(advertiser),
          title = VALUES(title),
          description = VALUES(description),
          category = VALUES(category),
          landing_url = VALUES(landing_url),
          tracking_url = VALUES(tracking_url),
          banner_url = VALUES(banner_url),
          logo_url = VALUES(logo_url),
          code = VALUES(code),
          rules = VALUES(rules),
          payout = VALUES(payout),
          payout_model = VALUES(payout_model),
          campaign_cap = VALUES(campaign_cap),
          starts_at = VALUES(starts_at),
          ends_at = VALUES(ends_at)");

    $statement->execute([
        (int) $coupon['id'],
        $network,
        $externalId,
        (string) ($coupon['store'] ?? ''),
        (string) ($coupon['title'] ?? ''),
        (string) ($coupon['description'] ?? ''),
        (string) ($coupon['category'] ?? ''),
        $landingUrl,
        $trackingUrl,
        (string) ($coupon['banner_url'] ?? ''),
        (string) ($coupon['logo_url'] ?? ''),
        (string) ($coupon['code'] ?? ''),
        (string) ($coupon['rules'] ?? ''),
        ($coupon['payout'] ?? null) !== null && $coupon['payout'] !== '' ? (string) $coupon['payout'] : null,
        (string) ($coupon['offer_type'] ?? ''),
        ($coupon['campaign_cap'] ?? null) !== null && $coupon['campaign_cap'] !== '' ? (int) $coupon['campaign_cap'] : null,
        (string) ($coupon['starts_at'] ?? date('Y-m-d')),
        (string) ($coupon['ends_at'] ?? date('Y-m-d')),
        $secret,
    ]);

    return $statement->rowCount() > 0 ? 1 : 0;
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

function affiliation_partner_rows(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    return $pdo->query("SELECT
            ap.*,
            (SELECT COUNT(*) FROM affiliate_clicks clk WHERE clk.affiliate_partner_id = ap.id) AS click_count,
            (SELECT COUNT(*) FROM affiliate_campaign_conversions conv WHERE conv.affiliate_partner_id = ap.id) AS conversion_count,
            (SELECT COALESCE(SUM(amount), 0) FROM affiliate_transactions tx WHERE tx.affiliate_partner_id = ap.id AND tx.type = 'earning') AS total_earned,
            (SELECT COALESCE(SUM(amount), 0) FROM affiliate_transactions tx WHERE tx.affiliate_partner_id = ap.id AND tx.type = 'withdrawal' AND tx.status IN ('approved', 'completed')) AS total_withdrawn,
            (SELECT MAX(created_at) FROM affiliate_clicks clk WHERE clk.affiliate_partner_id = ap.id) AS last_click_at
        FROM affiliate_partners ap
        ORDER BY ap.status ASC, ap.created_at DESC
        LIMIT 250")->fetchAll();
}

function affiliation_tracking_rows(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    return $pdo->query("SELECT
            ac.id,
            ac.network,
            ac.advertiser,
            ac.title,
            ac.status,
            ac.tracking_mode,
            ac.redirect_mode,
            ac.cookie_ttl_days,
            ac.utm_source_gate,
            ac.allowed_domains,
            ac.postback_secret,
            (SELECT COUNT(*) FROM affiliate_clicks clk WHERE clk.campaign_id = ac.id) AS click_count,
            (SELECT COUNT(*) FROM affiliate_campaign_conversions conv WHERE conv.campaign_id = ac.id) AS conversion_count
        FROM affiliate_campaigns ac
        ORDER BY FIELD(ac.status, 'publicada', 'selecionada', 'disponivel', 'pausada', 'encerrada'), ac.updated_at DESC
        LIMIT 250")->fetchAll();
}

function affiliation_wallet_rows(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    return $pdo->query("SELECT
            ap.id,
            ap.name,
            ap.email,
            ap.status,
            COALESCE(SUM(CASE WHEN tx.type = 'earning' THEN tx.amount ELSE 0 END), 0) AS earnings,
            COALESCE(SUM(CASE WHEN tx.type = 'earning' AND tx.status = 'pending' THEN tx.amount ELSE 0 END), 0) AS pending,
            COALESCE(SUM(CASE WHEN tx.type = 'earning' AND tx.status IN ('approved', 'completed') THEN tx.amount ELSE 0 END), 0) AS approved,
            COALESCE(SUM(CASE WHEN tx.type = 'withdrawal' AND tx.status IN ('approved', 'completed') THEN tx.amount ELSE 0 END), 0) AS withdrawn,
            COUNT(tx.id) AS transaction_count,
            MAX(tx.created_at) AS last_transaction_at
        FROM affiliate_partners ap
        LEFT JOIN affiliate_transactions tx ON tx.affiliate_partner_id = ap.id
        GROUP BY ap.id, ap.name, ap.email, ap.status
        ORDER BY earnings DESC, ap.created_at DESC
        LIMIT 250")->fetchAll();
}

function affiliation_smartlink_preview(int $campaignId, string $affiliatePlaceholder = '{affiliate_id}'): string
{
    return 'https://cupons.oferto.digital/a.php?cid=' . $campaignId . '&aff=' . rawurlencode($affiliatePlaceholder);
}

function affiliation_conversion_summary(string $startDate, string $endDate): array
{
    $pdo = db();
    if (!$pdo) {
        return ['total_conversions' => 0, 'total_sales' => 0, 'total_commission' => 0, 'approved_commission' => 0];
    }

    $statement = $pdo->prepare("SELECT
            COUNT(*) AS total_conversions,
            COALESCE(SUM(value), 0) AS total_sales,
            COALESCE(SUM(commission_amount), 0) AS total_commission,
            COALESCE(SUM(CASE WHEN status IN ('approved', 'confirmed', 'paid', 'completed') THEN commission_amount ELSE 0 END), 0) AS approved_commission
        FROM affiliate_campaign_conversions
        WHERE created_at >= :start_date
          AND created_at < DATE_ADD(:end_date, INTERVAL 1 DAY)");
    $statement->execute(['start_date' => $startDate, 'end_date' => $endDate]);

    return $statement->fetch() ?: ['total_conversions' => 0, 'total_sales' => 0, 'total_commission' => 0, 'approved_commission' => 0];
}

function affiliation_conversion_rows(string $startDate, string $endDate): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    $statement = $pdo->prepare("SELECT
            conv.status,
            COUNT(*) AS total_conversions,
            COALESCE(SUM(conv.value), 0) AS total_sales,
            COALESCE(SUM(conv.commission_amount), 0) AS total_commission,
            MAX(conv.created_at) AS last_conversion_at,
            ac.network,
            ac.advertiser,
            ac.title,
            ap.name AS affiliate_name
        FROM affiliate_campaign_conversions conv
        INNER JOIN affiliate_campaigns ac ON ac.id = conv.campaign_id
        LEFT JOIN affiliate_partners ap ON ap.id = conv.affiliate_partner_id
        WHERE conv.created_at >= :start_date
          AND conv.created_at < DATE_ADD(:end_date, INTERVAL 1 DAY)
        GROUP BY conv.status, ac.network, ac.advertiser, ac.title, ap.name
        ORDER BY total_commission DESC, total_conversions DESC");
    $statement->execute(['start_date' => $startDate, 'end_date' => $endDate]);

    return $statement->fetchAll();
}

function affiliation_campaign_statuses(): array
{
    return [
        'disponivel' => 'Disponível',
        'selecionada' => 'Selecionada',
        'publicada' => 'Publicada',
        'pausada' => 'Pausada',
        'encerrada' => 'Encerrada',
    ];
}
