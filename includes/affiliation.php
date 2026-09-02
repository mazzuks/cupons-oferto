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

function affiliation_partner_by_id(int $id): ?array
{
    $pdo = db();
    if (!$pdo || $id <= 0) {
        return null;
    }

    $statement = $pdo->prepare("SELECT * FROM affiliate_partners WHERE id = ? LIMIT 1");
    $statement->execute([$id]);
    $row = $statement->fetch();

    return $row ?: null;
}

function affiliation_partner_options(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    return $pdo->query("SELECT id, name, email, status
        FROM affiliate_partners
        ORDER BY status ASC, name ASC")->fetchAll();
}

function affiliation_save_partner(array $data): int
{
    $pdo = db();
    if (!$pdo) {
        throw new RuntimeException('Banco de dados indisponível.');
    }

    $id = (int) ($data['id'] ?? 0);
    $name = trim((string) ($data['name'] ?? ''));
    $email = trim((string) ($data['email'] ?? ''));
    $companyName = trim((string) ($data['company_name'] ?? ''));
    $phone = trim((string) ($data['phone'] ?? ''));
    $website = trim((string) ($data['website'] ?? ''));
    $status = trim((string) ($data['status'] ?? 'ativo'));
    $partnerCode = affiliation_slug(trim((string) ($data['partner_code'] ?? '')));
    $document = trim((string) ($data['document'] ?? ''));
    $trafficSource = trim((string) ($data['traffic_source'] ?? ''));
    $audienceProfile = trim((string) ($data['audience_profile'] ?? ''));
    $paymentMethod = trim((string) ($data['payment_method'] ?? ''));
    $paymentReference = trim((string) ($data['payment_reference'] ?? ''));
    $notes = trim((string) ($data['notes'] ?? ''));

    if ($name === '') {
        throw new RuntimeException('Informe o nome do parceiro.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Informe um e-mail válido para o parceiro.');
    }
    if (!in_array($status, ['ativo', 'pausado'], true)) {
        $status = 'ativo';
    }
    if ($website !== '' && !preg_match('/^https?:\/\//i', $website)) {
        $website = 'https://' . $website;
    }
    if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
        throw new RuntimeException('Informe um site válido ou deixe o campo vazio.');
    }
    if ($partnerCode === '') {
        $partnerCode = affiliation_slug($name);
    }

    if ($id > 0) {
        $statement = $pdo->prepare("UPDATE affiliate_partners
            SET name = ?, email = ?, company_name = ?, phone = ?, website = ?, status = ?,
                partner_code = ?, document = ?, traffic_source = ?, audience_profile = ?,
                payment_method = ?, payment_reference = ?, notes = ?, updated_at = NOW()
            WHERE id = ?");
        $statement->execute([
            $name,
            $email,
            $companyName !== '' ? $companyName : null,
            $phone !== '' ? $phone : null,
            $website !== '' ? $website : null,
            $status,
            $partnerCode,
            $document !== '' ? $document : null,
            $trafficSource !== '' ? $trafficSource : null,
            $audienceProfile !== '' ? $audienceProfile : null,
            $paymentMethod !== '' ? $paymentMethod : null,
            $paymentReference !== '' ? $paymentReference : null,
            $notes !== '' ? $notes : null,
            $id,
        ]);

        return $id;
    }

    $statement = $pdo->prepare("INSERT INTO affiliate_partners
        (name, email, company_name, phone, website, status, partner_code, document, traffic_source, audience_profile, payment_method, payment_reference, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $statement->execute([
        $name,
        $email,
        $companyName !== '' ? $companyName : null,
        $phone !== '' ? $phone : null,
        $website !== '' ? $website : null,
        $status,
        $partnerCode,
        $document !== '' ? $document : null,
        $trafficSource !== '' ? $trafficSource : null,
        $audienceProfile !== '' ? $audienceProfile : null,
        $paymentMethod !== '' ? $paymentMethod : null,
        $paymentReference !== '' ? $paymentReference : null,
        $notes !== '' ? $notes : null,
    ]);

    return (int) $pdo->lastInsertId();
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

function affiliation_postback_preview(int $campaignId, string $tid = '{tid}', string $orderId = '{order_id}', string $value = '{value}'): string
{
    return 'https://cupons.oferto.digital/affiliate-postback.php?' . http_build_query([
        'cid' => $campaignId,
        'tid' => $tid,
        'order_id' => $orderId,
        'value' => $value,
        'commission' => '{commission}',
        'status' => '{status}',
        'currency' => 'BRL',
        'sig' => '{hmac_sha256}',
    ]);
}

function affiliation_campaign_by_id(int $id): ?array
{
    $pdo = db();
    if (!$pdo || $id <= 0) {
        return null;
    }

    $statement = $pdo->prepare("SELECT * FROM affiliate_campaigns WHERE id = ? LIMIT 1");
    $statement->execute([$id]);
    $row = $statement->fetch();

    return $row ?: null;
}

function affiliation_save_campaign(array $data): int
{
    $pdo = db();
    if (!$pdo) {
        throw new RuntimeException('Banco de dados indisponível.');
    }

    $id = (int) ($data['id'] ?? 0);
    $advertiser = trim((string) ($data['advertiser'] ?? ''));
    $title = trim((string) ($data['title'] ?? ''));
    $description = trim((string) ($data['description'] ?? ''));
    $category = trim((string) ($data['category'] ?? ''));
    $network = trim((string) ($data['network'] ?? 'manual')) ?: 'manual';
    $externalId = trim((string) ($data['external_id'] ?? ''));
    $landingUrl = trim((string) ($data['landing_url'] ?? ''));
    $trackingUrl = trim((string) ($data['tracking_url'] ?? ''));
    $bannerUrl = trim((string) ($data['banner_url'] ?? ''));
    $logoUrl = trim((string) ($data['logo_url'] ?? ''));
    $code = trim((string) ($data['code'] ?? ''));
    $rules = trim((string) ($data['rules'] ?? ''));
    $payout = affiliation_optional_decimal($data['payout'] ?? null);
    $payoutModel = trim((string) ($data['payout_model'] ?? ''));
    $commissionType = trim((string) ($data['commission_type'] ?? ''));
    $commissionRate = affiliation_optional_decimal($data['commission_rate'] ?? null);
    $campaignCap = affiliation_optional_int($data['campaign_cap'] ?? null);
    $dailyCap = affiliation_optional_int($data['daily_cap'] ?? null);
    $monthlyCap = affiliation_optional_int($data['monthly_cap'] ?? null);
    $startsAt = affiliation_optional_date($data['starts_at'] ?? null);
    $endsAt = affiliation_optional_date($data['ends_at'] ?? null);
    $status = trim((string) ($data['status'] ?? 'selecionada'));
    $approvalMode = trim((string) ($data['approval_mode'] ?? 'manual'));
    $trackingMode = trim((string) ($data['tracking_mode'] ?? 'CLASSIC_PIXEL'));
    $redirectMode = trim((string) ($data['redirect_mode'] ?? 'FAST_302'));
    $cookieTtlDays = max(1, (int) ($data['cookie_ttl_days'] ?? 180));
    $utmSourceGate = trim((string) ($data['utm_source_gate'] ?? 'oferto')) ?: 'oferto';
    $allowedDomains = trim((string) ($data['allowed_domains'] ?? ''));
    $geoCountries = trim((string) ($data['geo_countries'] ?? ''));
    $deviceRules = trim((string) ($data['device_rules'] ?? ''));
    $creativeNotes = trim((string) ($data['creative_notes'] ?? ''));

    if ($advertiser === '' || $title === '') {
        throw new RuntimeException('Informe anunciante e título da campanha.');
    }
    if ($landingUrl === '' && $trackingUrl === '') {
        throw new RuntimeException('Informe a URL final ou a URL de tracking.');
    }
    if ($landingUrl === '') {
        $landingUrl = $trackingUrl;
    }
    if ($landingUrl !== '' && !preg_match('/^https?:\/\//i', $landingUrl)) {
        $landingUrl = 'https://' . $landingUrl;
    }
    if ($trackingUrl !== '' && !preg_match('/^https?:\/\//i', $trackingUrl)) {
        $trackingUrl = 'https://' . $trackingUrl;
    }
    if (!filter_var($landingUrl, FILTER_VALIDATE_URL)) {
        throw new RuntimeException('Informe uma URL final válida.');
    }
    if ($trackingUrl !== '' && !filter_var($trackingUrl, FILTER_VALIDATE_URL)) {
        throw new RuntimeException('Informe uma URL de tracking válida ou deixe vazio.');
    }
    if (!array_key_exists($status, affiliation_campaign_statuses())) {
        $status = 'selecionada';
    }
    if (!array_key_exists($approvalMode, affiliation_approval_modes())) {
        $approvalMode = 'manual';
    }
    if (!in_array($trackingMode, ['CLASSIC_PIXEL', 'JOURNEY_JS'], true)) {
        $trackingMode = 'CLASSIC_PIXEL';
    }
    if (!in_array($redirectMode, ['FAST_302', 'HTML_BRIDGE'], true)) {
        $redirectMode = 'FAST_302';
    }

    if ($id > 0) {
        $statement = $pdo->prepare("UPDATE affiliate_campaigns
            SET network = ?, external_id = ?, advertiser = ?, title = ?, description = ?, category = ?,
                landing_url = ?, tracking_url = ?, banner_url = ?, logo_url = ?, code = ?, rules = ?,
                payout = ?, payout_model = ?, commission_type = ?, commission_rate = ?, campaign_cap = ?,
                daily_cap = ?, monthly_cap = ?, starts_at = ?, ends_at = ?, status = ?, approval_mode = ?,
                tracking_mode = ?, redirect_mode = ?, cookie_ttl_days = ?, utm_source_gate = ?, allowed_domains = ?,
                geo_countries = ?, device_rules = ?, creative_notes = ?, updated_at = NOW()
            WHERE id = ?");
        $statement->execute([
            $network,
            $externalId !== '' ? $externalId : null,
            $advertiser,
            $title,
            $description !== '' ? $description : null,
            $category !== '' ? $category : null,
            $landingUrl,
            $trackingUrl !== '' ? $trackingUrl : null,
            $bannerUrl !== '' ? $bannerUrl : null,
            $logoUrl !== '' ? $logoUrl : null,
            $code !== '' ? $code : null,
            $rules !== '' ? $rules : null,
            $payout,
            $payoutModel !== '' ? $payoutModel : null,
            $commissionType !== '' ? $commissionType : null,
            $commissionRate,
            $campaignCap,
            $dailyCap,
            $monthlyCap,
            $startsAt,
            $endsAt,
            $status,
            $approvalMode,
            $trackingMode,
            $redirectMode,
            $cookieTtlDays,
            $utmSourceGate,
            $allowedDomains !== '' ? $allowedDomains : null,
            $geoCountries !== '' ? $geoCountries : null,
            $deviceRules !== '' ? $deviceRules : null,
            $creativeNotes !== '' ? $creativeNotes : null,
            $id,
        ]);

        return $id;
    }

    $statement = $pdo->prepare("INSERT INTO affiliate_campaigns
        (network, external_id, advertiser, title, description, category, landing_url, tracking_url, banner_url, logo_url,
         code, rules, payout, payout_model, commission_type, commission_rate, campaign_cap, daily_cap, monthly_cap,
         starts_at, ends_at, status, approval_mode, tracking_mode, redirect_mode, postback_secret, cookie_ttl_days,
         utm_source_gate, allowed_domains, geo_countries, device_rules, creative_notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $statement->execute([
        $network,
        $externalId !== '' ? $externalId : null,
        $advertiser,
        $title,
        $description !== '' ? $description : null,
        $category !== '' ? $category : null,
        $landingUrl,
        $trackingUrl !== '' ? $trackingUrl : null,
        $bannerUrl !== '' ? $bannerUrl : null,
        $logoUrl !== '' ? $logoUrl : null,
        $code !== '' ? $code : null,
        $rules !== '' ? $rules : null,
        $payout,
        $payoutModel !== '' ? $payoutModel : null,
        $commissionType !== '' ? $commissionType : null,
        $commissionRate,
        $campaignCap,
        $dailyCap,
        $monthlyCap,
        $startsAt,
        $endsAt,
        $status,
        $approvalMode,
        $trackingMode,
        $redirectMode,
        bin2hex(random_bytes(24)),
        $cookieTtlDays,
        $utmSourceGate,
        $allowedDomains !== '' ? $allowedDomains : null,
        $geoCountries !== '' ? $geoCountries : null,
        $deviceRules !== '' ? $deviceRules : null,
        $creativeNotes !== '' ? $creativeNotes : null,
    ]);

    return (int) $pdo->lastInsertId();
}

function affiliation_creative_rows(array $filters = []): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    $conditions = ['1 = 1'];
    $params = [];

    if (!empty($filters['campaign_id'])) {
        $conditions[] = 'cr.campaign_id = :campaign_id';
        $params['campaign_id'] = (int) $filters['campaign_id'];
    }

    $statement = $pdo->prepare("SELECT
            cr.*,
            ac.advertiser,
            ac.title AS campaign_title,
            ac.network
        FROM affiliate_creatives cr
        INNER JOIN affiliate_campaigns ac ON ac.id = cr.campaign_id
        WHERE " . implode(' AND ', $conditions) . "
        ORDER BY cr.status ASC, ac.advertiser ASC, cr.created_at DESC
        LIMIT 250");
    $statement->execute($params);

    return $statement->fetchAll();
}

function affiliation_creative_by_id(int $id): ?array
{
    $pdo = db();
    if (!$pdo || $id <= 0) {
        return null;
    }

    $statement = $pdo->prepare('SELECT * FROM affiliate_creatives WHERE id = ? LIMIT 1');
    $statement->execute([$id]);
    $row = $statement->fetch();

    return $row ?: null;
}

function affiliation_save_creative(array $data): int
{
    $pdo = db();
    if (!$pdo) {
        throw new RuntimeException('Banco de dados indisponível.');
    }

    $id = (int) ($data['id'] ?? 0);
    $campaignId = (int) ($data['campaign_id'] ?? 0);
    $creativeType = trim((string) ($data['creative_type'] ?? 'banner')) ?: 'banner';
    $title = trim((string) ($data['title'] ?? ''));
    $assetUrl = trim((string) ($data['asset_url'] ?? ''));
    $destinationUrl = trim((string) ($data['destination_url'] ?? ''));
    $width = affiliation_optional_int($data['width'] ?? null);
    $height = affiliation_optional_int($data['height'] ?? null);
    $status = trim((string) ($data['status'] ?? 'ativo'));
    $notes = trim((string) ($data['notes'] ?? ''));

    if ($campaignId <= 0 || !affiliation_campaign_by_id($campaignId)) {
        throw new RuntimeException('Selecione uma campanha válida para o criativo.');
    }
    if ($title === '') {
        throw new RuntimeException('Informe o nome do criativo.');
    }
    foreach (['asset_url' => $assetUrl, 'destination_url' => $destinationUrl] as $label => $url) {
        if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Informe uma URL válida em ' . $label . ' ou deixe vazio.');
        }
    }
    if (!in_array($status, ['ativo', 'pausado'], true)) {
        $status = 'ativo';
    }

    if ($id > 0) {
        $statement = $pdo->prepare("UPDATE affiliate_creatives
            SET campaign_id = ?, creative_type = ?, title = ?, asset_url = ?, destination_url = ?,
                width = ?, height = ?, status = ?, notes = ?, updated_at = NOW()
            WHERE id = ?");
        $statement->execute([
            $campaignId,
            $creativeType,
            $title,
            $assetUrl !== '' ? $assetUrl : null,
            $destinationUrl !== '' ? $destinationUrl : null,
            $width,
            $height,
            $status,
            $notes !== '' ? $notes : null,
            $id,
        ]);

        return $id;
    }

    $statement = $pdo->prepare("INSERT INTO affiliate_creatives
        (campaign_id, creative_type, title, asset_url, destination_url, width, height, status, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $statement->execute([
        $campaignId,
        $creativeType,
        $title,
        $assetUrl !== '' ? $assetUrl : null,
        $destinationUrl !== '' ? $destinationUrl : null,
        $width,
        $height,
        $status,
        $notes !== '' ? $notes : null,
    ]);

    return (int) $pdo->lastInsertId();
}

function affiliation_destination_url(array $campaign, string $tid): string
{
    $url = trim((string) ($campaign['tracking_url'] ?? ''));
    if ($url === '') {
        $url = trim((string) ($campaign['landing_url'] ?? ''));
    }

    if (!preg_match('/^https?:\/\//i', $url)) {
        return '';
    }

    $params = [
        'tid' => $tid,
        'aff_sub' => $tid,
        'utm_source' => trim((string) ($campaign['utm_source_gate'] ?? 'oferto')) ?: 'oferto',
        'utm_medium' => 'affiliate',
        'utm_campaign' => 'oferto_' . (int) ($campaign['id'] ?? 0),
    ];

    foreach ($params as $key => $value) {
        if (preg_match('/(?:\\?|&)' . preg_quote($key, '/') . '=/i', $url)) {
            unset($params[$key]);
        }
    }

    if (!$params) {
        return $url;
    }

    return $url . (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
}

function affiliation_log_click(array $campaign, ?array $partner = null): string
{
    $pdo = db();
    if (!$pdo) {
        return '';
    }

    $tid = bin2hex(random_bytes(12));
    $clickRef = trim((string) ($_GET['ref'] ?? ''));
    $referer = substr((string) ($_SERVER['HTTP_REFERER'] ?? ''), 0, 500);
    $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $ipHash = $ip !== '' ? hash('sha256', $ip) : null;
    $utm = [];

    foreach ($_GET as $key => $value) {
        if (strpos((string) $key, 'utm_') === 0) {
            $utm[$key] = is_scalar($value) ? (string) $value : '';
        }
    }

    $statement = $pdo->prepare("INSERT INTO affiliate_clicks
        (campaign_id, affiliate_partner_id, tid, click_ref, referer, user_agent, ip_hash, utm_json)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $statement->execute([
        (int) $campaign['id'],
        $partner ? (int) $partner['id'] : null,
        $tid,
        $clickRef !== '' ? $clickRef : null,
        $referer !== '' ? $referer : null,
        $userAgent !== '' ? $userAgent : null,
        $ipHash,
        $utm ? json_encode($utm, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
    ]);

    return $tid;
}

function affiliation_expected_signature(array $campaign, string $tid, string $orderId, string $value): string
{
    $secret = (string) ($campaign['postback_secret'] ?? '');
    return hash_hmac('sha256', (int) $campaign['id'] . '|' . $tid . '|' . $orderId . '|' . $value, $secret);
}

function affiliation_register_conversion(array $payload): array
{
    $pdo = db();
    if (!$pdo) {
        throw new RuntimeException('Banco de dados indisponível.');
    }

    $campaignId = (int) ($payload['cid'] ?? $payload['campaign_id'] ?? 0);
    $campaign = affiliation_campaign_by_id($campaignId);
    if (!$campaign) {
        throw new RuntimeException('Campanha afiliada não encontrada.');
    }

    $tid = trim((string) ($payload['tid'] ?? ''));
    $orderId = trim((string) ($payload['order_id'] ?? $payload['order'] ?? ''));
    $value = number_format((float) str_replace(',', '.', (string) ($payload['value'] ?? $payload['sale_amount'] ?? 0)), 2, '.', '');
    $commission = number_format((float) str_replace(',', '.', (string) ($payload['commission'] ?? $payload['commission_amount'] ?? 0)), 2, '.', '');
    $currency = strtoupper(trim((string) ($payload['currency'] ?? 'BRL'))) ?: 'BRL';
    $status = trim((string) ($payload['status'] ?? 'pending')) ?: 'pending';
    $signature = trim((string) ($payload['sig'] ?? $payload['signature'] ?? ''));

    if ($tid === '' || $orderId === '') {
        throw new RuntimeException('Informe tid e order_id.');
    }

    $expected = affiliation_expected_signature($campaign, $tid, $orderId, $value);
    if ($signature === '' || !hash_equals($expected, $signature)) {
        throw new RuntimeException('Assinatura inválida.');
    }

    $click = null;
    $clickStatement = $pdo->prepare("SELECT affiliate_partner_id
        FROM affiliate_clicks
        WHERE campaign_id = ? AND tid = ?
        ORDER BY id DESC
        LIMIT 1");
    $clickStatement->execute([(int) $campaign['id'], $tid]);
    $click = $clickStatement->fetch() ?: null;
    $partnerId = $click && !empty($click['affiliate_partner_id']) ? (int) $click['affiliate_partner_id'] : null;

    $statement = $pdo->prepare("INSERT INTO affiliate_campaign_conversions
        (campaign_id, affiliate_partner_id, tid, order_id, value, commission_amount, currency, status, signature, raw_json)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
          affiliate_partner_id = VALUES(affiliate_partner_id),
          value = VALUES(value),
          commission_amount = VALUES(commission_amount),
          currency = VALUES(currency),
          status = VALUES(status),
          signature = VALUES(signature),
          raw_json = VALUES(raw_json),
          updated_at = NOW()");
    $statement->execute([
        (int) $campaign['id'],
        $partnerId,
        $tid,
        $orderId,
        $value,
        $commission,
        substr($currency, 0, 10),
        substr($status, 0, 60),
        $signature,
        json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);

    $conversionStatement = $pdo->prepare("SELECT id FROM affiliate_campaign_conversions WHERE campaign_id = ? AND order_id = ? LIMIT 1");
    $conversionStatement->execute([(int) $campaign['id'], $orderId]);
    $conversionId = (int) ($conversionStatement->fetchColumn() ?: 0);

    if ($partnerId && $conversionId > 0 && (float) $commission > 0) {
        $transactionStatement = $pdo->prepare("SELECT id FROM affiliate_transactions WHERE conversion_id = ? AND type = 'earning' LIMIT 1");
        $transactionStatement->execute([$conversionId]);
        $transactionId = (int) ($transactionStatement->fetchColumn() ?: 0);

        if ($transactionId > 0) {
            $updateTransaction = $pdo->prepare("UPDATE affiliate_transactions
                SET amount = ?, status = ?, description = ?, updated_at = NOW()
                WHERE id = ?");
            $updateTransaction->execute([
                $commission,
                in_array($status, ['approved', 'confirmed', 'paid', 'completed'], true) ? 'approved' : 'pending',
                'Comissão da campanha ' . (string) $campaign['title'],
                $transactionId,
            ]);
        } else {
            $insertTransaction = $pdo->prepare("INSERT INTO affiliate_transactions
                (affiliate_partner_id, campaign_id, conversion_id, amount, type, status, description)
                VALUES (?, ?, ?, ?, 'earning', ?, ?)");
            $insertTransaction->execute([
                $partnerId,
                (int) $campaign['id'],
                $conversionId,
                $commission,
                in_array($status, ['approved', 'confirmed', 'paid', 'completed'], true) ? 'approved' : 'pending',
                'Comissão da campanha ' . (string) $campaign['title'],
            ]);
        }
    }

    return [
        'campaign_id' => (int) $campaign['id'],
        'conversion_id' => $conversionId,
        'affiliate_partner_id' => $partnerId,
        'status' => $status,
    ];
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

function affiliation_approval_modes(): array
{
    return [
        'manual' => 'Aprovação manual',
        'auto' => 'Aprovação automática',
        'private' => 'Privada/convite',
    ];
}

function affiliation_commission_types(): array
{
    return [
        '' => 'Não informado',
        'fixa' => 'Comissão fixa',
        'percentual' => 'Percentual sobre venda',
        'hibrida' => 'Híbrida',
        'bonificacao' => 'Bônus',
    ];
}

function affiliation_optional_decimal($value): ?string
{
    $value = trim(str_replace(',', '.', (string) $value));
    if ($value === '' || !is_numeric($value)) {
        return null;
    }

    return number_format((float) $value, 4, '.', '');
}

function affiliation_optional_int($value): ?int
{
    $value = trim((string) $value);
    if ($value === '' || !ctype_digit($value)) {
        return null;
    }

    return max(0, (int) $value);
}

function affiliation_optional_date($value): ?string
{
    $value = trim((string) $value);
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
}

function affiliation_slug(string $value): string
{
    $value = strtolower(trim($value));
    $value = strtr($value, [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
        'é' => 'e', 'ê' => 'e',
        'í' => 'i',
        'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ú' => 'u',
        'ç' => 'c',
    ]);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

    return trim($value, '-');
}
