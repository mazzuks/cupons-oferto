<?php

declare(strict_types=1);

require_once __DIR__ . '/coupons.php';

function integration_setting(string $key, string $default = ''): string
{
    $pdo = db();
    if (!$pdo) {
        return $default;
    }

    $statement = $pdo->prepare('SELECT setting_value FROM integration_settings WHERE setting_key = ? LIMIT 1');
    $statement->execute([$key]);
    $value = $statement->fetchColumn();

    return $value === false ? $default : (string) $value;
}

function save_integration_setting(string $key, string $value): void
{
    $pdo = db();
    if (!$pdo) {
        throw new RuntimeException('Banco de dados indisponivel.');
    }

    $statement = $pdo->prepare('INSERT INTO integration_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $statement->execute([$key, $value]);
}

function text_contains(string $haystack, string $needle): bool
{
    return $needle === '' || strpos($haystack, $needle) !== false;
}

function lomadee_api_key(): string
{
    $config = app_config();
    $fromConfig = trim((string) ($config['integrations']['lomadee']['api_key'] ?? ''));
    if ($fromConfig !== '') {
        return $fromConfig;
    }

    return trim(integration_setting('lomadee_api_key'));
}

function lomadee_request(string $path, array $query = [], string $method = 'GET', array $payload = []): array
{
    $apiKey = lomadee_api_key();
    if ($apiKey === '') {
        throw new RuntimeException('Informe a chave da Lomadee antes de importar.');
    }

    $url = 'https://api.lomadee.com.br' . $path;
    if ($query) {
        $url .= '?' . http_build_query($query);
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('A extensao cURL do PHP precisa estar ativa para usar a Lomadee.');
    }

    $headers = ['x-api-key: ' . $apiKey];
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ];

    if ($method === 'POST') {
        $headers[] = 'Content-Type: application/json';
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = json_encode($payload);
    }

    $options[CURLOPT_HTTPHEADER] = $headers;

    $curl = curl_init($url);
    curl_setopt_array($curl, $options);

    $body = curl_exec($curl);
    $error = curl_error($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);

    if ($body === false || $error) {
        throw new RuntimeException('Falha ao chamar a Lomadee: ' . $error);
    }

    $decoded = json_decode((string) $body, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Resposta invalida da Lomadee.');
    }

    if ($status < 200 || $status >= 300) {
        $message = $decoded['message'] ?? $decoded['error'] ?? 'Erro HTTP ' . $status;
        throw new RuntimeException('Lomadee recusou a chamada: ' . $message);
    }

    return $decoded;
}

function lomadee_fetch_all(string $path, array $query, int $maxPages = 10): array
{
    $items = [];
    for ($page = 1; $page <= $maxPages; $page++) {
        $response = lomadee_request($path, array_merge($query, ['page' => $page, 'limit' => 20]));
        $data = $response['data'] ?? [];
        if (!is_array($data) || $data === []) {
            break;
        }

        $items = array_merge($items, array_values($data));
        $meta = $response['meta'] ?? $response['pagination'] ?? [];
        $totalPages = (int) ($meta['totalPages'] ?? $page);
        if ($page >= $totalPages) {
            break;
        }
    }

    return $items;
}

function lomadee_brand_map(int $maxPages = 10): array
{
    $brands = lomadee_fetch_all('/affiliate/brands', ['isPublic' => 'true'], $maxPages);
    $map = [];
    foreach ($brands as $brand) {
        if (!is_array($brand) || empty($brand['id'])) {
            continue;
        }
        $map[(string) $brand['id']] = $brand;
    }

    return $map;
}

function lomadee_brand_options(string $search = '', int $maxPages = 20): array
{
    $search = trim($search);
    $brands = lomadee_fetch_all('/affiliate/brands', ['isPublic' => 'true'], $maxPages);
    $options = [];

    foreach ($brands as $brand) {
        if (!is_array($brand) || empty($brand['id'])) {
            continue;
        }

        $name = trim((string) ($brand['name'] ?? ''));
        $segment = trim((string) ($brand['segment'] ?? ''));
        $haystack = strtolower($name . ' ' . $segment);
        if ($search !== '' && !text_contains($haystack, strtolower($search))) {
            continue;
        }

        $options[] = [
            'id' => (string) $brand['id'],
            'name' => $name ?: 'Marca sem nome',
            'segment' => $segment,
            'site' => trim((string) ($brand['site'] ?? '')),
        ];
    }

    usort($options, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));
    return array_slice($options, 0, 80);
}

function lomadee_campaign_type_options(): array
{
    return [
        'GenericCoupon' => 'Cupom geral',
        'PersonalCoupon' => 'Cupom pessoal',
        'Offer' => 'Oferta sem codigo',
    ];
}

function lomadee_category_options(): array
{
    return [
        'Alimentacao e Bebidas',
        'Compras',
        'Games',
        'Educacao',
        'Servicos',
        'Entretenimento',
        'Kids',
        'Viagem',
    ];
}

function lomadee_normalize_filters(array $filters): array
{
    $types = $filters['types'] ?? array_keys(lomadee_campaign_type_options());
    $types = array_values(array_intersect((array) $types, array_keys(lomadee_campaign_type_options())));

    $categories = array_values(array_filter(array_map('trim', (array) ($filters['categories'] ?? []))));
    $brandIds = array_values(array_filter(array_map('strval', (array) ($filters['brand_ids'] ?? []))));
    $excludedTerms = array_values(array_filter(array_map('trim', explode(',', (string) ($filters['excluded_terms'] ?? 'BANNERS:')))));
    $status = in_array($filters['publish_status'] ?? 'rascunho', ['ativo', 'rascunho'], true) ? $filters['publish_status'] : 'rascunho';

    return [
        'max_pages' => max(1, min(50, (int) ($filters['max_pages'] ?? 20))),
        'types' => $types ?: array_keys(lomadee_campaign_type_options()),
        'categories' => $categories,
        'brand_query' => trim((string) ($filters['brand_query'] ?? '')),
        'brand_ids' => $brandIds,
        'excluded_terms' => $excludedTerms,
        'publish_status' => $status,
        'selected_external_ids' => array_values(array_filter(array_map('strval', (array) ($filters['selected_external_ids'] ?? [])))),
    ];
}

function lomadee_preview_campaigns(array $filters = []): array
{
    $filters = lomadee_normalize_filters($filters);
    $brands = lomadee_brand_map(max($filters['max_pages'], 20));
    $campaigns = lomadee_fetch_all('/affiliate/campaigns', [
        'types' => implode(',', $filters['types']),
        'status' => 'onTime',
    ], $filters['max_pages']);

    $items = [];
    foreach ($campaigns as $campaign) {
        if (!is_array($campaign) || empty($campaign['id'])) {
            continue;
        }

        $brand = $brands[(string) ($campaign['organizationId'] ?? '')] ?? [];
        if (!lomadee_campaign_passes_filters($campaign, $brand, $filters)) {
            continue;
        }

        $payload = lomadee_campaign_payload($campaign, $brands, $filters['publish_status']);
        if (!$payload) {
            continue;
        }

        $items[] = [
            'external_id' => $payload['external_id'],
            'campaign_id' => (string) $campaign['id'],
            'brand_id' => (string) ($campaign['organizationId'] ?? ''),
            'store' => $payload['store'],
            'title' => $payload['title'],
            'category' => $payload['category'],
            'type' => (string) ($campaign['type'] ?? ''),
            'offer_type' => $payload['offer_type'],
            'redemption_type' => $payload['redemption_type'],
            'has_code' => $payload['code'] !== '',
            'banner_url' => $payload['banner_url'],
            'status' => $payload['status'],
            'existing' => (bool) coupon_by_external_id($payload['external_id']),
        ];
    }

    return [
        'filters' => $filters,
        'items' => $items,
        'total' => count($campaigns),
        'matched' => count($items),
    ];
}

function lomadee_import_campaigns(int $maxPages = 10, array $filters = []): array
{
    $filters['max_pages'] = $maxPages;
    $filters = lomadee_normalize_filters($filters);
    $selectedExternalIds = $filters['selected_external_ids'];
    $brands = lomadee_brand_map(max($maxPages, 20));
    $campaigns = lomadee_fetch_all('/affiliate/campaigns', [
        'types' => implode(',', $filters['types']),
        'status' => 'onTime',
    ], $maxPages);

    $created = 0;
    $updated = 0;
    $skipped = 0;

    foreach ($campaigns as $campaign) {
        if (!is_array($campaign) || empty($campaign['id'])) {
            $skipped++;
            continue;
        }

        $brand = $brands[(string) ($campaign['organizationId'] ?? '')] ?? [];
        if (!lomadee_campaign_passes_filters($campaign, $brand, $filters)) {
            $skipped++;
            continue;
        }

        $externalId = 'lomadee:' . (string) $campaign['id'];
        if ($selectedExternalIds && !in_array($externalId, $selectedExternalIds, true)) {
            $skipped++;
            continue;
        }

        $payload = lomadee_campaign_payload($campaign, $brands, $filters['publish_status']);
        if ($payload === null) {
            $skipped++;
            continue;
        }

        $existing = coupon_by_external_id($payload['external_id']);
        save_coupon($payload, $existing ? (int) $existing['id'] : null);
        $existing ? $updated++ : $created++;
    }

    return [
        'created' => $created,
        'updated' => $updated,
        'skipped' => $skipped,
        'total' => count($campaigns),
    ];
}

function lomadee_campaign_passes_filters(array $campaign, array $brand, array $filters): bool
{
    $brandId = (string) ($campaign['organizationId'] ?? '');
    if ($filters['brand_ids'] && !in_array($brandId, $filters['brand_ids'], true)) {
        return false;
    }

    $brandName = trim((string) ($brand['name'] ?? ''));
    $brandSegment = trim((string) ($brand['segment'] ?? ''));
    $title = trim((string) ($campaign['name'] ?? ''));
    $description = trim(strip_tags((string) ($campaign['description'] ?? '')));
    $haystack = strtolower($brandName . ' ' . $brandSegment . ' ' . $title . ' ' . $description);

    if ($filters['brand_query'] !== '' && !$filters['brand_ids'] && !text_contains($haystack, strtolower($filters['brand_query']))) {
        return false;
    }

    foreach ($filters['excluded_terms'] as $term) {
        if ($term !== '' && text_contains($haystack, strtolower($term))) {
            return false;
        }
    }

    if ($filters['categories']) {
        $category = lomadee_category($campaign, $brand);
        if (!in_array($category, $filters['categories'], true)) {
            return false;
        }
    }

    return true;
}

function lomadee_campaign_payload(array $campaign, array $brands, string $status = 'ativo'): ?array
{
    $organizationId = (string) ($campaign['organizationId'] ?? '');
    $brand = $brands[$organizationId] ?? [];
    $type = (string) ($campaign['type'] ?? '');
    $code = trim((string) ($campaign['code'] ?? ''));
    $destination = lomadee_campaign_url($campaign, $brand);

    if ($destination === '') {
        return null;
    }

    $endsAt = lomadee_date($campaign['period']['endAt'] ?? null) ?: date('Y-m-d', strtotime('+30 days'));
    $startsAt = lomadee_date($campaign['period']['startAt'] ?? null) ?: date('Y-m-d');
    $category = lomadee_category($campaign, $brand);

    return [
        'category' => $category,
        'store' => trim((string) ($brand['name'] ?? 'Lomadee')),
        'title' => trim((string) ($campaign['name'] ?? 'Oferta Lomadee')),
        'description' => lomadee_description($campaign, $brand),
        'code' => $code,
        'target_url' => $destination,
        'banner_url' => lomadee_banner($campaign, $brand),
        'starts_at' => $startsAt,
        'ends_at' => $endsAt,
        'status' => $status,
        'featured' => !empty($campaign['isHighlight']) ? 1 : 0,
        'rules' => trim((string) ($campaign['channels'][0]['message'] ?? 'Confira as regras no site parceiro antes de finalizar.')),
        'redemption_type' => $code !== '' ? 'texto_redirect' : 'redirect',
        'offer_type' => in_array($type, ['GenericCoupon', 'PersonalCoupon'], true) ? 'cupom' : 'oferta_direta',
        'cta_label' => $code !== '' ? 'Resgatar cupom' : 'Ver oferta',
        'tracking_url' => $destination,
        'partner_network' => 'Lomadee',
        'payout' => null,
        'campaign_cap' => null,
        'sponsored' => 0,
        'priority' => !empty($campaign['isHighlight']) ? 20 : 0,
        'tags' => 'lomadee,' . strtolower($type ?: 'campaign'),
        'requirements' => $code !== '' ? 'Copie o cupom e use no site parceiro' : 'Oferta disponivel no site parceiro',
        'pixel_event' => 'lomadee_' . preg_replace('/[^a-z0-9_]+/i', '_', (string) $campaign['id']),
        'external_id' => 'lomadee:' . (string) $campaign['id'],
        'members_only' => 0,
    ];
}

function lomadee_campaign_url(array $campaign, array $brand): string
{
    foreach (($campaign['channels'] ?? []) as $channel) {
        $shortUrls = $channel['shortUrls'] ?? [];
        if (is_array($shortUrls) && !empty($shortUrls[0])) {
            return trim((string) $shortUrls[0]);
        }
    }

    foreach (['url', 'site'] as $field) {
        $value = trim((string) ($campaign[$field] ?? $brand[$field] ?? ''));
        if ($value !== '') {
            return lomadee_shorten_campaign($campaign, $value) ?: $value;
        }
    }

    return '';
}

function lomadee_shorten_campaign(array $campaign, string $url): string
{
    $organizationId = trim((string) ($campaign['organizationId'] ?? ''));
    $featureId = trim((string) ($campaign['id'] ?? ''));
    if ($organizationId === '' || $featureId === '' || $url === '') {
        return '';
    }

    try {
        $response = lomadee_request('/affiliate/shortener/url', [], 'POST', [
            'organizationId' => $organizationId,
            'featureId' => $featureId,
            'url' => $url,
            'mdasc' => 'oferto-cupons',
        ]);
    } catch (Throwable $exception) {
        return '';
    }

    $first = $response[0] ?? null;
    $shortUrls = is_array($first) ? ($first['shortUrls'] ?? []) : [];
    return is_array($shortUrls) && !empty($shortUrls[0]) ? trim((string) $shortUrls[0]) : '';
}

function lomadee_banner(array $campaign, array $brand): string
{
    $banners = $campaign['mediaKit']['banners'] ?? [];
    if (is_array($banners) && !empty($banners[0])) {
        return trim((string) $banners[0]);
    }

    return trim((string) ($brand['logo'] ?? 'assets/og-cupons.png')) ?: 'assets/og-cupons.png';
}

function lomadee_category(array $campaign, array $brand): string
{
    $categories = $campaign['categories'] ?? [];
    if (is_array($categories) && !empty($categories[0])) {
        return lomadee_site_category(trim((string) $categories[0]));
    }

    return lomadee_site_category(trim((string) ($brand['segment'] ?? 'Ofertas')));
}

function lomadee_site_category(string $value): string
{
    $normalized = strtolower($value);
    $map = [
        'food' => 'Alimentacao e Bebidas',
        'beverage' => 'Alimentacao e Bebidas',
        'bebida' => 'Alimentacao e Bebidas',
        'aliment' => 'Alimentacao e Bebidas',
        'yakisoba' => 'Alimentacao e Bebidas',
        'restaurant' => 'Alimentacao e Bebidas',
        'games' => 'Games',
        'game' => 'Games',
        'education' => 'Educacao',
        'educa' => 'Educacao',
        'course' => 'Educacao',
        'kids' => 'Kids',
        'infantil' => 'Kids',
        'travel' => 'Viagem',
        'viagem' => 'Viagem',
        'service' => 'Servicos',
        'servic' => 'Servicos',
        'insurance' => 'Servicos',
        'seguro' => 'Servicos',
        'entertainment' => 'Entretenimento',
        'entreten' => 'Entretenimento',
        'shopping' => 'Compras',
        'compras' => 'Compras',
        'fashion' => 'Compras',
        'moda' => 'Compras',
    ];

    foreach ($map as $needle => $category) {
        if (text_contains($normalized, $needle)) {
            return $category;
        }
    }

    return 'Compras';
}

function lomadee_description(array $campaign, array $brand): string
{
    $description = trim(strip_tags((string) ($campaign['description'] ?? '')));
    if ($description !== '') {
        return $description;
    }

    $store = trim((string) ($brand['name'] ?? 'loja parceira'));
    return 'Oferta disponivel na ' . $store . ' por tempo limitado.';
}

function lomadee_date($value): ?string
{
    if (!$value) {
        return null;
    }

    $timestamp = strtotime((string) $value);
    return $timestamp ? date('Y-m-d', $timestamp) : null;
}
