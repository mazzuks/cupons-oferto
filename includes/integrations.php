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

function integration_json_setting(string $key, array $default = []): array
{
    $raw = integration_setting($key, '');
    if ($raw === '') {
        return $default;
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $default;
}

function save_integration_json_setting(string $key, array $value): void
{
    save_integration_setting($key, json_encode($value, JSON_UNESCAPED_SLASHES));
}

function integration_profile(string $partner, array $default = []): array
{
    return integration_json_setting('profile_' . strtolower($partner), $default);
}

function save_integration_profile(string $partner, array $filters): void
{
    save_integration_json_setting('profile_' . strtolower($partner), $filters);
}

function text_contains(string $haystack, string $needle): bool
{
    return $needle === '' || strpos($haystack, $needle) !== false;
}

function create_admin_notification(string $type, string $title, string $body, string $partner = '', string $externalId = ''): void
{
    $pdo = db();
    if (!$pdo) {
        return;
    }

    $recent = $pdo->prepare("SELECT id FROM admin_notifications
        WHERE type = ? AND partner = ? AND external_id = ?
          AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
        LIMIT 1");
    $recent->execute([$type, $partner, $externalId]);
    if ($recent->fetch()) {
        return;
    }

    $statement = $pdo->prepare('INSERT INTO admin_notifications (type, title, body, partner, external_id) VALUES (?, ?, ?, ?, ?)');
    $statement->execute([$type, $title, $body, $partner ?: null, $externalId ?: null]);
}

function admin_notifications(int $limit = 30, bool $onlyUnread = false): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    $where = $onlyUnread ? 'WHERE read_at IS NULL' : '';
    $statement = $pdo->prepare("SELECT * FROM admin_notifications $where ORDER BY created_at DESC LIMIT ?");
    $statement->bindValue(1, $limit, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetchAll();
}

function unread_notification_count(): int
{
    $pdo = db();
    if (!$pdo) {
        return 0;
    }

    return (int) $pdo->query('SELECT COUNT(*) FROM admin_notifications WHERE read_at IS NULL')->fetchColumn();
}

function mark_notifications_read(): void
{
    $pdo = db();
    if (!$pdo) {
        return;
    }

    $pdo->exec('UPDATE admin_notifications SET read_at = COALESCE(read_at, NOW()) WHERE read_at IS NULL');
}

function monitor_integration_offer(string $partner, array $payload, string $sourceId = '', string $brandId = ''): void
{
    $pdo = db();
    if (!$pdo || empty($payload['external_id'])) {
        return;
    }

    $statement = $pdo->prepare("INSERT INTO integration_watchlist
        (partner, external_id, source_id, brand_id, store, title, status, last_seen_at, missing_since)
        VALUES (?, ?, ?, ?, ?, ?, 'monitorado', NOW(), NULL)
        ON DUPLICATE KEY UPDATE
          source_id = VALUES(source_id),
          brand_id = VALUES(brand_id),
          store = VALUES(store),
          title = VALUES(title),
          status = 'monitorado',
          last_seen_at = NOW(),
          missing_since = NULL");
    $statement->execute([
        $partner,
        $payload['external_id'],
        $sourceId ?: null,
        $brandId ?: null,
        $payload['store'] ?? '',
        $payload['title'] ?? '',
    ]);
}

function monitor_integration_brand(string $partner, string $brandId, string $brandName, string $segment = ''): void
{
    $pdo = db();
    if (!$pdo || $brandId === '' || $brandName === '') {
        return;
    }

    $statement = $pdo->prepare("INSERT INTO integration_brand_watchlist
        (partner, brand_id, brand_name, segment, status, last_seen_at)
        VALUES (?, ?, ?, ?, 'monitorado', NOW())
        ON DUPLICATE KEY UPDATE
          brand_name = VALUES(brand_name),
          segment = VALUES(segment),
          status = 'monitorado',
          last_seen_at = NOW()");
    $statement->execute([$partner, $brandId, $brandName, $segment ?: null]);
}

function monitored_integration_brands(string $partner): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    $statement = $pdo->prepare("SELECT * FROM integration_brand_watchlist WHERE partner = ? AND status = 'monitorado' ORDER BY brand_name ASC");
    $statement->execute([$partner]);
    return $statement->fetchAll();
}

function monitored_integration_brand_ids(string $partner): array
{
    return array_values(array_map('strval', array_column(monitored_integration_brands($partner), 'brand_id')));
}

function save_lomadee_monitored_brands(array $brandIds, array $brandOptions): int
{
    $saved = 0;
    foreach ($brandOptions as $brand) {
        if (!in_array((string) $brand['id'], $brandIds, true)) {
            continue;
        }

        monitor_integration_brand('Lomadee', (string) $brand['id'], (string) $brand['name'], (string) ($brand['segment'] ?? ''));
        $saved++;
    }

    return $saved;
}

function monitored_integration_offers(string $partner): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    $statement = $pdo->prepare("SELECT * FROM integration_watchlist WHERE partner = ? AND status IN ('monitorado', 'sumiu') ORDER BY store ASC, title ASC");
    $statement->execute([$partner]);
    return $statement->fetchAll();
}

function mark_monitor_seen(string $partner, string $externalId): void
{
    $pdo = db();
    if (!$pdo) {
        return;
    }

    $statement = $pdo->prepare("UPDATE integration_watchlist SET status = 'monitorado', last_seen_at = NOW(), missing_since = NULL WHERE partner = ? AND external_id = ?");
    $statement->execute([$partner, $externalId]);
}

function mark_monitor_missing(array $watch): void
{
    $pdo = db();
    if (!$pdo) {
        return;
    }

    $statement = $pdo->prepare("UPDATE integration_watchlist
        SET status = 'sumiu', missing_since = COALESCE(missing_since, NOW())
        WHERE id = ?");
    $statement->execute([(int) $watch['id']]);

    create_admin_notification(
        'campaign_missing',
        'Campanha saiu do feed',
        ($watch['store'] ?? 'Parceiro') . ' - ' . ($watch['title'] ?? 'campanha') . ' nao apareceu na ultima sincronizacao.',
        (string) $watch['partner'],
        (string) $watch['external_id']
    );
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

function awin_access_token(): string
{
    $config = app_config();
    $fromConfig = trim((string) ($config['integrations']['awin']['access_token'] ?? ''));
    if ($fromConfig !== '') {
        return $fromConfig;
    }

    return trim(integration_setting('awin_access_token'));
}

function awin_publisher_id(): string
{
    $config = app_config();
    $fromConfig = trim((string) ($config['integrations']['awin']['publisher_id'] ?? ''));
    if ($fromConfig !== '') {
        return $fromConfig;
    }

    return trim(integration_setting('awin_publisher_id'));
}

function awin_publisher_name(): string
{
    return trim(integration_setting('awin_publisher_name'));
}

function awin_request(string $path, array $query = [], string $method = 'GET', array $payload = []): array
{
    $token = awin_access_token();
    if ($token === '') {
        throw new RuntimeException('Informe o token da Awin antes de integrar.');
    }

    $url = 'https://api.awin.com' . $path;
    if ($query) {
        $url .= '?' . http_build_query($query);
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('A extensao cURL do PHP precisa estar ativa para usar a Awin.');
    }

    $headers = [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
    ];
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
    ];

    if ($method === 'POST') {
        $headers[] = 'Content-Type: application/json';
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = json_encode($payload);
        $options[CURLOPT_HTTPHEADER] = $headers;
    }

    $curl = curl_init($url);
    curl_setopt_array($curl, $options);

    $body = curl_exec($curl);
    $error = curl_error($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);

    if ($body === false || $error) {
        throw new RuntimeException('Falha ao chamar a Awin: ' . $error);
    }

    $decoded = json_decode((string) $body, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Resposta invalida da Awin.');
    }

    if ($status < 200 || $status >= 300) {
        $message = $decoded['message'] ?? $decoded['error'] ?? 'Erro HTTP ' . $status;
        throw new RuntimeException('Awin recusou a chamada: ' . $message);
    }

    return $decoded;
}

function awin_publisher_accounts(): array
{
    $response = awin_request('/accounts', ['type' => 'publisher']);
    $accounts = $response['accounts'] ?? [];
    return is_array($accounts) ? array_values($accounts) : [];
}

function awin_connect_first_publisher(): array
{
    $accounts = awin_publisher_accounts();
    if (!$accounts) {
        throw new RuntimeException('Awin autenticou, mas nao retornou conta publisher para este token.');
    }

    $account = $accounts[0];
    $publisherId = trim((string) ($account['accountId'] ?? ''));
    $publisherName = trim((string) ($account['accountName'] ?? ''));
    if ($publisherId === '') {
        throw new RuntimeException('Awin retornou uma conta sem publisherId.');
    }

    save_integration_setting('awin_publisher_id', $publisherId);
    save_integration_setting('awin_publisher_name', $publisherName);

    return [
        'publisher_id' => $publisherId,
        'publisher_name' => $publisherName,
        'accounts' => count($accounts),
    ];
}

function awin_offer_type_options(): array
{
    return [
        'all' => 'Todos',
        'voucher' => 'Cupons com codigo',
        'promotion' => 'Promocoes sem codigo',
    ];
}

function awin_membership_options(): array
{
    return [
        'all' => 'Todos os anunciantes',
        'joined' => 'Apenas parceiros aprovados',
        'notJoined' => 'Ainda nao aprovados',
    ];
}

function awin_status_options(): array
{
    return [
        'active' => 'Ativas',
        'expiringSoon' => 'Vencendo em breve',
        'upcoming' => 'Agendadas',
    ];
}

function awin_default_excluded_terms(): string
{
    return 'adulto, erotico, erotica, sex shop, sexy, sensual, lingerie, cassino, bet, betting, apostas, vape, tabaco';
}

function awin_normalize_filters(array $filters): array
{
    $type = (string) ($filters['type'] ?? 'all');
    $membership = (string) ($filters['membership'] ?? 'all');
    $status = (string) ($filters['status'] ?? 'active');
    $publishStatus = in_array($filters['publish_status'] ?? 'rascunho', ['ativo', 'rascunho'], true) ? $filters['publish_status'] : 'rascunho';

    if (!array_key_exists($type, awin_offer_type_options())) {
        $type = 'all';
    }
    if (!array_key_exists($membership, awin_membership_options())) {
        $membership = 'all';
    }
    if (!array_key_exists($status, awin_status_options())) {
        $status = 'active';
    }

    return [
        'page' => max(1, min(50, (int) ($filters['page'] ?? 1))),
        'page_size' => max(10, min(200, (int) ($filters['page_size'] ?? 50))),
        'type' => $type,
        'membership' => $membership,
        'status' => $status,
        'region' => strtoupper(trim((string) ($filters['region'] ?? 'BR'))),
        'query' => trim((string) ($filters['query'] ?? '')),
        'categories' => array_values(array_filter(array_map('trim', (array) ($filters['categories'] ?? [])))),
        'advertiser_ids' => array_values(array_filter(array_map('intval', (array) ($filters['advertiser_ids'] ?? [])))),
        'excluded_terms' => array_values(array_filter(array_map('trim', explode(',', (string) ($filters['excluded_terms'] ?? awin_default_excluded_terms()))))),
        'publish_status' => $publishStatus,
        'selected_external_ids' => array_values(array_filter(array_map('strval', (array) ($filters['selected_external_ids'] ?? [])))),
    ];
}

function awin_promotions(array $filters): array
{
    $publisherId = awin_publisher_id();
    if ($publisherId === '') {
        awin_connect_first_publisher();
        $publisherId = awin_publisher_id();
    }
    if ($publisherId === '') {
        throw new RuntimeException('Conecte a Awin antes de buscar ofertas.');
    }

    $filters = awin_normalize_filters($filters);
    $bodyFilters = [
        'membership' => $filters['membership'],
        'status' => $filters['status'],
        'type' => $filters['type'],
    ];

    if ($filters['region'] !== '') {
        $bodyFilters['regionCodes'] = [$filters['region']];
    }
    if ($filters['advertiser_ids']) {
        $bodyFilters['advertiserIds'] = $filters['advertiser_ids'];
    }

    $response = awin_request('/publisher/' . rawurlencode($publisherId) . '/promotions', [
        'accessToken' => awin_access_token(),
    ], 'POST', [
        'filters' => $bodyFilters,
        'pagination' => [
            'page' => $filters['page'],
            'pageSize' => $filters['page_size'],
        ],
    ]);

    $offers = $response['data'] ?? $response['offers'] ?? $response['promotions'] ?? $response;
    return is_array($offers) ? array_values($offers) : [];
}

function awin_preview_offers(array $filters = []): array
{
    $filters = awin_normalize_filters($filters);
    $offers = awin_promotions($filters);
    $items = [];

    foreach ($offers as $offer) {
        if (!is_array($offer) || empty($offer['promotionId'])) {
            continue;
        }
        if (!awin_offer_passes_filters($offer, $filters)) {
            continue;
        }

        $payload = awin_offer_payload($offer, $filters['publish_status']);
        if (!$payload) {
            continue;
        }

        $items[] = [
            'external_id' => $payload['external_id'],
            'brand_id' => (string) ($offer['advertiser']['id'] ?? ''),
            'store' => $payload['store'],
            'title' => $payload['title'],
            'category' => $payload['category'],
            'offer_type' => $payload['offer_type'],
            'redemption_type' => $payload['redemption_type'],
            'has_code' => $payload['code'] !== '',
            'status' => $payload['status'],
            'existing' => (bool) coupon_by_external_id($payload['external_id']),
        ];
    }

    return [
        'filters' => $filters,
        'items' => $items,
        'total' => count($offers),
        'matched' => count($items),
    ];
}

function save_awin_monitored_brands_from_selection(array $selectedExternalIds, array $offers): int
{
    $saved = 0;
    foreach ($offers as $offer) {
        if (!is_array($offer) || empty($offer['promotionId'])) {
            continue;
        }

        $externalId = 'awin:' . (string) $offer['promotionId'];
        if (!in_array($externalId, $selectedExternalIds, true)) {
            continue;
        }

        $advertiser = is_array($offer['advertiser'] ?? null) ? $offer['advertiser'] : [];
        if (empty($advertiser['id']) || empty($advertiser['name'])) {
            continue;
        }

        monitor_integration_brand('Awin', (string) $advertiser['id'], (string) $advertiser['name'], '');
        $saved++;
    }

    return $saved;
}

function awin_import_offers(array $filters = []): array
{
    $filters = awin_normalize_filters($filters);
    $selectedExternalIds = $filters['selected_external_ids'];
    $offers = awin_promotions($filters);
    $created = 0;
    $updated = 0;
    $skipped = 0;

    foreach ($offers as $offer) {
        if (!is_array($offer) || empty($offer['promotionId']) || !awin_offer_passes_filters($offer, $filters)) {
            $skipped++;
            continue;
        }

        $externalId = 'awin:' . (string) $offer['promotionId'];
        if ($selectedExternalIds && !in_array($externalId, $selectedExternalIds, true)) {
            $skipped++;
            continue;
        }

        $payload = awin_offer_payload($offer, $filters['publish_status']);
        if (!$payload) {
            $skipped++;
            continue;
        }

        $existing = coupon_by_external_id($payload['external_id']);
        save_coupon($payload, $existing ? (int) $existing['id'] : null);
        if (!empty($campaign['organizationId'])) {
            monitor_integration_brand('Lomadee', (string) $campaign['organizationId'], (string) ($payload['store'] ?? 'Lomadee'), (string) ($brand['segment'] ?? ''));
        }
        monitor_integration_offer('Lomadee', $payload, (string) $campaign['id'], (string) ($campaign['organizationId'] ?? ''));
        $existing ? $updated++ : $created++;
    }

    return [
        'created' => $created,
        'updated' => $updated,
        'skipped' => $skipped,
        'total' => count($offers),
    ];
}

function awin_offer_passes_filters(array $offer, array $filters): bool
{
    $advertiser = is_array($offer['advertiser'] ?? null) ? $offer['advertiser'] : [];
    $haystack = strtolower(implode(' ', [
        (string) ($advertiser['name'] ?? ''),
        (string) ($offer['title'] ?? ''),
        strip_tags((string) ($offer['description'] ?? '')),
        strip_tags((string) ($offer['terms'] ?? '')),
    ]));

    if ($filters['query'] !== '' && !text_contains($haystack, strtolower($filters['query']))) {
        return false;
    }

    foreach ($filters['excluded_terms'] as $term) {
        if ($term !== '' && text_contains($haystack, strtolower($term))) {
            return false;
        }
    }

    if (awin_should_filter_categories($filters['categories'])) {
        $category = awin_offer_category($offer);
        if (!in_array($category, $filters['categories'], true)) {
            return false;
        }
    }

    return true;
}

function awin_should_filter_categories(array $categories): bool
{
    $categories = array_values(array_unique($categories));
    if (!$categories) {
        return false;
    }

    $available = lomadee_category_options();
    return count(array_intersect($available, $categories)) < count($available);
}

function awin_offer_payload(array $offer, string $status = 'rascunho'): ?array
{
    $advertiser = is_array($offer['advertiser'] ?? null) ? $offer['advertiser'] : [];
    $promotionId = trim((string) ($offer['promotionId'] ?? ''));
    $destination = trim((string) ($offer['urlTracking'] ?? $offer['url'] ?? ''));
    if ($promotionId === '' || $destination === '') {
        return null;
    }

    $voucher = is_array($offer['voucher'] ?? null) ? $offer['voucher'] : [];
    $code = trim((string) ($voucher['code'] ?? ''));
    $type = (string) ($offer['type'] ?? '');
    $title = trim((string) ($offer['title'] ?? 'Oferta Awin'));

    return [
        'category' => awin_offer_category($offer),
        'store' => trim((string) ($advertiser['name'] ?? 'Awin')),
        'title' => $title,
        'description' => awin_offer_description($offer),
        'code' => $code,
        'target_url' => $destination,
        'banner_url' => 'assets/og-cupons.png',
        'starts_at' => lomadee_date($offer['startDate'] ?? null) ?: date('Y-m-d'),
        'ends_at' => lomadee_date($offer['endDate'] ?? null) ?: date('Y-m-d', strtotime('+30 days')),
        'status' => $status,
        'featured' => 0,
        'rules' => trim(strip_tags((string) ($offer['terms'] ?? 'Confira as regras no site parceiro antes de finalizar.'))),
        'redemption_type' => $code !== '' ? 'texto_redirect' : 'redirect',
        'offer_type' => $type === 'voucher' ? 'cupom' : 'oferta_direta',
        'cta_label' => $code !== '' ? 'Resgatar cupom' : 'Ver oferta',
        'tracking_url' => $destination,
        'partner_network' => 'Awin',
        'payout' => null,
        'campaign_cap' => null,
        'sponsored' => 0,
        'priority' => 0,
        'tags' => 'awin,' . strtolower($type ?: 'offer'),
        'requirements' => $code !== '' ? 'Copie o cupom e use no site parceiro' : 'Oferta disponivel no site parceiro',
        'pixel_event' => 'awin_' . preg_replace('/[^a-z0-9_]+/i', '_', $promotionId),
        'external_id' => 'awin:' . $promotionId,
        'members_only' => 0,
    ];
}

function awin_offer_category(array $offer): string
{
    $advertiser = is_array($offer['advertiser'] ?? null) ? $offer['advertiser'] : [];
    $value = implode(' ', [
        (string) ($advertiser['name'] ?? ''),
        (string) ($offer['title'] ?? ''),
        strip_tags((string) ($offer['description'] ?? '')),
    ]);

    return lomadee_site_category($value);
}

function awin_offer_description(array $offer): string
{
    $description = trim(strip_tags((string) ($offer['description'] ?? '')));
    if ($description !== '') {
        return $description;
    }

    return trim((string) ($offer['title'] ?? 'Oferta disponivel por tempo limitado.'));
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

function lomadee_default_campaign_types(): array
{
    return ['GenericCoupon', 'PersonalCoupon'];
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
    $types = $filters['types'] ?? lomadee_default_campaign_types();
    $types = array_values(array_intersect((array) $types, array_keys(lomadee_campaign_type_options())));

    $categories = array_values(array_filter(array_map('trim', (array) ($filters['categories'] ?? []))));
    $brandIds = array_values(array_filter(array_map('strval', (array) ($filters['brand_ids'] ?? []))));
    $excludedTerms = array_values(array_filter(array_map('trim', explode(',', (string) ($filters['excluded_terms'] ?? 'BANNERS:')))));
    $status = in_array($filters['publish_status'] ?? 'rascunho', ['ativo', 'rascunho'], true) ? $filters['publish_status'] : 'rascunho';

    return [
        'max_pages' => max(1, min(50, (int) ($filters['max_pages'] ?? 20))),
        'types' => $types ?: lomadee_default_campaign_types(),
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
        if (!empty($campaign['organizationId'])) {
            $brandName = trim((string) ($brand['name'] ?? $payload['store']));
            monitor_integration_brand('Lomadee', (string) $campaign['organizationId'], $brandName, (string) ($brand['segment'] ?? ''));
        }
        monitor_integration_offer('Lomadee', $payload, (string) $campaign['id'], (string) ($campaign['organizationId'] ?? ''));
        $existing ? $updated++ : $created++;
    }

    return [
        'created' => $created,
        'updated' => $updated,
        'skipped' => $skipped,
        'total' => count($campaigns),
    ];
}

function sync_lomadee_watchlist(): array
{
    $profile = integration_profile('lomadee', []);
    $filters = lomadee_normalize_filters($profile);
    $monitoredBrandIds = monitored_integration_brand_ids('Lomadee');
    if ($monitoredBrandIds) {
        $filters['brand_ids'] = $monitoredBrandIds;
    }
    $brands = lomadee_brand_map(max($filters['max_pages'], 20));
    $campaigns = lomadee_fetch_all('/affiliate/campaigns', [
        'types' => implode(',', $filters['types']),
        'status' => 'onTime',
    ], $filters['max_pages']);
    $seen = [];
    $updated = 0;
    $missing = 0;
    $new = 0;

    foreach ($campaigns as $campaign) {
        if (!is_array($campaign) || empty($campaign['id'])) {
            continue;
        }
        $externalId = 'lomadee:' . (string) $campaign['id'];
        $seen[$externalId] = true;

        $brand = $brands[(string) ($campaign['organizationId'] ?? '')] ?? [];
        if (!lomadee_campaign_passes_filters($campaign, $brand, $filters)) {
            continue;
        }

        $payload = lomadee_campaign_payload($campaign, $brands, $filters['publish_status']);
        $existing = coupon_by_external_id($externalId);
        if ($payload && $existing) {
            save_coupon($payload, (int) $existing['id']);
            mark_monitor_seen('Lomadee', $externalId);
            $updated++;
        } elseif ($payload && $monitoredBrandIds) {
            save_coupon($payload);
            monitor_integration_offer('Lomadee', $payload, (string) $campaign['id'], (string) ($campaign['organizationId'] ?? ''));
            create_admin_notification(
                'campaign_new',
                'Nova campanha de marca monitorada',
                ($payload['store'] ?? 'Lomadee') . ' - ' . ($payload['title'] ?? 'campanha') . ' apareceu no feed.',
                'Lomadee',
                $externalId
            );
            $new++;
        }
    }

    foreach (monitored_integration_offers('Lomadee') as $watch) {
        if (empty($seen[(string) $watch['external_id']])) {
            mark_monitor_missing($watch);
            $missing++;
        }
    }

    return ['partner' => 'Lomadee', 'updated' => $updated, 'missing' => $missing, 'new' => $new, 'read' => count($campaigns)];
}

function sync_awin_watchlist(): array
{
    $profile = integration_profile('awin', []);
    $filters = awin_normalize_filters($profile);
    $monitoredBrandIds = monitored_integration_brand_ids('Awin');
    if ($monitoredBrandIds) {
        $filters['advertiser_ids'] = array_map('intval', $monitoredBrandIds);
    }
    $offers = awin_promotions($filters);
    $seen = [];
    $updated = 0;
    $missing = 0;
    $new = 0;

    foreach ($offers as $offer) {
        if (!is_array($offer) || empty($offer['promotionId'])) {
            continue;
        }
        $externalId = 'awin:' . (string) $offer['promotionId'];
        $seen[$externalId] = true;

        if (!awin_offer_passes_filters($offer, $filters)) {
            continue;
        }

        $payload = awin_offer_payload($offer, $filters['publish_status']);
        $existing = coupon_by_external_id($externalId);
        if ($payload && $existing) {
            save_coupon($payload, (int) $existing['id']);
            mark_monitor_seen('Awin', $externalId);
            $updated++;
        } elseif ($payload && $monitoredBrandIds) {
            create_admin_notification(
                'campaign_new',
                'Nova oferta de anunciante monitorado',
                ($payload['store'] ?? 'Awin') . ' - ' . ($payload['title'] ?? 'oferta') . ' apareceu no feed.',
                'Awin',
                $externalId
            );
            $new++;
        }
    }

    foreach (monitored_integration_offers('Awin') as $watch) {
        if (empty($seen[(string) $watch['external_id']])) {
            mark_monitor_missing($watch);
            $missing++;
        }
    }

    return ['partner' => 'Awin', 'updated' => $updated, 'missing' => $missing, 'new' => $new, 'read' => count($offers)];
}

function sync_all_integrations(): array
{
    $results = [];
    foreach (['Lomadee' => 'sync_lomadee_watchlist', 'Awin' => 'sync_awin_watchlist'] as $partner => $callback) {
        try {
            $results[] = $callback();
        } catch (Throwable $exception) {
            create_admin_notification('sync_error', 'Erro ao sincronizar ' . $partner, $exception->getMessage(), $partner);
            $results[] = ['partner' => $partner, 'error' => $exception->getMessage()];
        }
    }

    return $results;
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

    if (lomadee_is_media_material($title, $description)) {
        return false;
    }

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

function lomadee_is_media_material(string $title, string $description = ''): bool
{
    $text = strtolower(trim($title . ' ' . $description));
    foreach (['banners:', 'banner:', 'material de divulgacao', 'material de divulgação'] as $term) {
        if ($term !== '' && text_contains($text, $term)) {
            return true;
        }
    }

    return false;
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
