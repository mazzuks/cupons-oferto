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

function normalized_text_contains(string $haystack, string $needle): bool
{
    return text_contains(normalize_search_text($haystack), normalize_search_text($needle));
}

function text_ends_with(string $haystack, string $needle): bool
{
    return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
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

function offer18_accounts(): array
{
    $config = app_config();
    $fromConfig = $config['integrations']['offer18']['accounts'] ?? [];
    if (is_array($fromConfig) && $fromConfig) {
        return array_values($fromConfig);
    }

    return integration_json_setting('offer18_accounts', []);
}

function save_offer18_account(array $account): void
{
    $accounts = offer18_accounts();
    $account = [
        'label' => trim((string) ($account['label'] ?? 'Offer18')),
        'mid' => trim((string) ($account['mid'] ?? '')),
        'api_key' => trim((string) ($account['api_key'] ?? '')),
        'secret_key' => trim((string) ($account['secret_key'] ?? '')),
        'affiliate_id' => trim((string) ($account['affiliate_id'] ?? '')),
    ];
    if ($account['mid'] === '' || $account['api_key'] === '' || $account['secret_key'] === '') {
        throw new RuntimeException('Informe nome, MID, API key e secret key da conta Offer18.');
    }

    $key = offer18_account_key($account);
    $updated = false;
    foreach ($accounts as $index => $existing) {
        if (offer18_account_key($existing) === $key) {
            $accounts[$index] = $account;
            $updated = true;
            break;
        }
    }
    if (!$updated) {
        $accounts[] = $account;
    }

    save_integration_json_setting('offer18_accounts', $accounts);
}

function offer18_account_key(array $account): string
{
    return normalize_search_text((string) ($account['label'] ?? '')) . ':' . trim((string) ($account['mid'] ?? ''));
}

function offer18_account(int $index = 0): array
{
    $accounts = offer18_accounts();
    return $accounts[$index] ?? [];
}

function offer18_mask(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return 'Nao configurado';
    }

    return substr($value, 0, 6) . str_repeat('*', 10) . substr($value, -4);
}

function offer18_status_options(): array
{
    return [
        '1' => 'Aprovadas/ativas',
        '2' => 'Pendentes',
        '3' => 'Encerradas',
    ];
}

function offer18_model_options(): array
{
    return [
        '' => 'Todos',
        'CPA' => 'CPA',
        'CPL' => 'CPL',
        'CPC' => 'CPC',
        'CPS' => 'CPS',
    ];
}

function offer18_default_excluded_terms(): string
{
    return awin_default_excluded_terms();
}

function offer18_normalize_filters(array $filters): array
{
    $status = (string) ($filters['status'] ?? '1');
    $publishStatus = (string) ($filters['publish_status'] ?? 'rascunho');
    $publishStatus = in_array($publishStatus, ['ativo', 'rascunho'], true) ? $publishStatus : 'rascunho';

    return [
        'account_index' => max(0, (int) ($filters['account_index'] ?? 0)),
        'page' => max(1, min(50, (int) ($filters['page'] ?? 1))),
        'limit' => max(10, min(200, (int) ($filters['limit'] ?? 100))),
        'status' => array_key_exists($status, offer18_status_options()) ? $status : '1',
        'query' => trim((string) ($filters['query'] ?? '')),
        'model_affiliate' => trim((string) ($filters['model_affiliate'] ?? '')),
        'categories' => array_values(array_unique(array_filter(array_map(
            fn ($category) => canonical_category((string) $category),
            (array) ($filters['categories'] ?? [])
        )))),
        'excluded_terms' => array_values(array_filter(array_map('trim', explode(',', (string) ($filters['excluded_terms'] ?? offer18_default_excluded_terms()))))),
        'publish_status' => $publishStatus,
        'selected_external_ids' => array_values(array_filter(array_map('strval', (array) ($filters['selected_external_ids'] ?? [])))),
    ];
}

function offer18_request(array $account, string $path, array $query = []): array
{
    if (empty($account['mid']) || empty($account['api_key']) || empty($account['secret_key'])) {
        throw new RuntimeException('Configure uma conta Offer18 antes de buscar ofertas.');
    }

    $query = array_merge([
        'mid' => $account['mid'],
        'api-key' => $account['api_key'],
        'secret-key' => $account['secret_key'],
    ], $query);

    $url = 'https://api.offer18.com' . $path . '?' . http_build_query($query);
    if (!function_exists('curl_init')) {
        throw new RuntimeException('A extensao cURL do PHP precisa estar ativa para usar a Offer18.');
    }

    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);
    $body = curl_exec($curl);
    $error = curl_error($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);

    if ($body === false || $error) {
        throw new RuntimeException('Falha ao chamar a Offer18: ' . $error);
    }

    $decoded = json_decode((string) $body, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Resposta invalida da Offer18.');
    }
    if ($status < 200 || $status >= 300) {
        $message = $decoded['message'] ?? $decoded['error'] ?? 'Erro HTTP ' . $status;
        throw new RuntimeException('Offer18 recusou a chamada: ' . $message);
    }

    return $decoded;
}

function offer18_response_items(array $response): array
{
    foreach ([
        ['data', 'offers'],
        ['data', 'items'],
        ['response', 'offers'],
        ['result', 'offers'],
    ] as $path) {
        $value = $response;
        foreach ($path as $part) {
            if (!is_array($value) || !isset($value[$part])) {
                $value = null;
                break;
            }
            $value = $value[$part];
        }
        if (is_array($value)) {
            return array_values($value);
        }
    }

    foreach (['data', 'offers', 'response', 'result'] as $key) {
        if (!empty($response[$key]) && is_array($response[$key])) {
            return array_values($response[$key]);
        }
    }

    return offer18_array_is_list($response) ? $response : [];
}

function offer18_array_is_list(array $value): bool
{
    if ($value === []) {
        return true;
    }

    return array_keys($value) === range(0, count($value) - 1);
}

function offer18_offers(array $filters): array
{
    $filters = offer18_normalize_filters($filters);
    $account = offer18_account($filters['account_index']);
    $query = [
        'status' => $filters['status'],
        'limit' => $filters['limit'],
        'page' => $filters['page'],
    ];
    if ($filters['query'] !== '') {
        $query['offer_name'] = $filters['query'];
    }
    if ($filters['model_affiliate'] !== '') {
        $query['model_affiliate'] = $filters['model_affiliate'];
    }

    return offer18_response_items(offer18_request($account, '/api/m/offers', $query));
}

function offer18_preview_offers(array $filters = []): array
{
    $filters = offer18_normalize_filters($filters);
    $offers = offer18_offers($filters);
    $items = [];

    foreach ($offers as $offer) {
        if (!is_array($offer) || !offer18_offer_passes_filters($offer, $filters)) {
            continue;
        }

        $payload = offer18_offer_payload($offer, $filters['publish_status']);
        if (!$payload) {
            continue;
        }

        $items[] = [
            'external_id' => $payload['external_id'],
            'store' => $payload['store'],
            'title' => $payload['title'],
            'category' => $payload['category'],
            'offer_type' => $payload['offer_type'],
            'redemption_type' => $payload['redemption_type'],
            'status' => $payload['status'],
            'existing' => (bool) coupon_by_external_id($payload['external_id']),
        ];
    }

    return ['filters' => $filters, 'items' => $items, 'total' => count($offers), 'matched' => count($items)];
}

function offer18_import_offers(array $filters = []): array
{
    $filters = offer18_normalize_filters($filters);
    $selectedExternalIds = $filters['selected_external_ids'];
    $offers = offer18_offers($filters);
    $created = 0;
    $updated = 0;
    $skipped = 0;

    foreach ($offers as $offer) {
        if (!is_array($offer) || !offer18_offer_passes_filters($offer, $filters)) {
            $skipped++;
            continue;
        }

        $payload = offer18_offer_payload($offer, $filters['publish_status']);
        if (!$payload || ($selectedExternalIds && !in_array($payload['external_id'], $selectedExternalIds, true))) {
            $skipped++;
            continue;
        }

        $existing = coupon_by_external_id($payload['external_id']);
        save_coupon($payload, $existing ? (int) $existing['id'] : null);
        monitor_integration_brand('Offer18', offer18_value($offer, ['advertiser_id', 'advertiser.id'], $payload['store']), $payload['store'], $payload['category']);
        monitor_integration_offer('Offer18', $payload, offer18_offer_id($offer), offer18_value($offer, ['advertiser_id', 'advertiser.id'], ''));
        $existing ? $updated++ : $created++;
    }

    create_system_log('offer18_import', 'Importacao Offer18', $created . ' criadas, ' . $updated . ' atualizadas e ' . $skipped . ' ignoradas.', 'Offer18');
    return ['created' => $created, 'updated' => $updated, 'skipped' => $skipped, 'total' => count($offers)];
}

function sync_offer18_watchlist(): array
{
    $profile = integration_profile('offer18', []);
    $filters = offer18_normalize_filters($profile);
    $offers = offer18_offers($filters);
    $seen = [];
    $updated = 0;
    $new = 0;

    foreach ($offers as $offer) {
        if (!is_array($offer) || !offer18_offer_passes_filters($offer, $filters)) {
            continue;
        }

        $payload = offer18_offer_payload($offer, $filters['publish_status']);
        if (!$payload) {
            continue;
        }

        $seen[$payload['external_id']] = true;
        $existing = coupon_by_external_id($payload['external_id']);
        save_coupon($payload, $existing ? (int) $existing['id'] : null);
        monitor_integration_offer('Offer18', $payload, offer18_offer_id($offer), offer18_value($offer, ['advertiser_id', 'advertiser.id'], ''));
        $existing ? $updated++ : $new++;
    }

    $missing = 0;
    foreach (monitored_integration_offers('Offer18') as $watch) {
        if (!isset($seen[(string) $watch['external_id']])) {
            mark_monitor_missing($watch);
            $missing++;
        } else {
            mark_monitor_seen('Offer18', (string) $watch['external_id']);
        }
    }

    return ['partner' => 'Offer18', 'read' => count($offers), 'updated' => $updated, 'new' => $new, 'missing' => $missing];
}

function offer18_offer_passes_filters(array $offer, array $filters): bool
{
    $haystack = strtolower(implode(' ', [
        offer18_value($offer, ['offer_name', 'name', 'title']),
        offer18_value($offer, ['advertiser.name', 'advertiser', 'advertiser_name']),
        offer18_value($offer, ['category', 'offer_category']),
        strip_tags(offer18_value($offer, ['description', 'offer_terms', 'terms'])),
    ]));

    if ($filters['query'] !== '' && !normalized_text_contains($haystack, $filters['query'])) {
        return false;
    }
    foreach ($filters['excluded_terms'] as $term) {
        if ($term !== '' && normalized_text_contains($haystack, $term)) {
            return false;
        }
    }
    if (awin_should_filter_categories($filters['categories'])) {
        $category = offer18_offer_category($offer);
        if (!in_array($category, $filters['categories'], true)) {
            return false;
        }
    }

    return true;
}

function offer18_offer_payload(array $offer, string $status = 'rascunho'): ?array
{
    $offerId = offer18_offer_id($offer);
    $trackingUrl = offer18_tracking_url($offer);
    if ($offerId === '' || !coupon_is_offer18_tracking_url($trackingUrl)) {
        return null;
    }

    $title = offer18_value($offer, ['offer_name', 'name', 'title'], 'Oferta Offer18');
    $store = offer18_value($offer, ['advertiser.name', 'advertiser_name', 'advertiser'], 'Offer18');
    $code = offer18_value($offer, ['coupon_code', 'code', 'voucher_code', 'promo_code']);
    $model = strtoupper(offer18_value($offer, ['model_affiliate', 'affiliate_model']));

    return [
        'category' => offer18_offer_category($offer),
        'store' => $store,
        'title' => $title,
        'description' => offer18_description($offer, $store),
        'code' => $code,
        'target_url' => offer18_value($offer, ['preview_url', 'offer_url', 'fallback_url'], $trackingUrl),
        'banner_url' => offer18_banner($offer),
        'logo_url' => offer18_value($offer, ['logo', 'logo_url', 'advertiser.logo', 'advertiser.logoUrl']),
        'starts_at' => lomadee_date(offer18_value($offer, ['start_datetime', 'start_date', 'created_at'])) ?: date('Y-m-d'),
        'ends_at' => lomadee_date(offer18_value($offer, ['end_datetime', 'expiration_date', 'end_date'])) ?: date('Y-m-d', strtotime('+30 days')),
        'status' => $status,
        'featured' => 0,
        'rules' => trim(strip_tags(offer18_value($offer, ['offer_terms', 'terms', 'kpi'], 'Confira as regras no site parceiro antes de finalizar.'))),
        'redemption_type' => $code !== '' ? 'texto_redirect' : 'redirect',
        'offer_type' => $code !== '' ? 'cupom' : ($model === 'CPL' ? 'cadastro' : 'oferta_direta'),
        'cta_label' => $code !== '' ? 'Resgatar cupom' : ($model === 'CPL' ? 'Cadastre-se' : 'Resgatar oferta'),
        'tracking_url' => $trackingUrl,
        'partner_network' => 'Offer18',
        'payout' => offer18_decimal_or_null(offer18_value($offer, ['price_affiliate', 'affiliate_price', 'payout'])),
        'campaign_cap' => null,
        'sponsored' => 0,
        'priority' => 0,
        'tags' => 'offer18,' . strtolower($model ?: 'offer'),
        'requirements' => $code !== '' ? 'Copie o cupom e use no site parceiro' : 'Resgate no site parceiro',
        'pixel_event' => 'offer18_' . preg_replace('/[^a-z0-9_]+/i', '_', $offerId),
        'external_id' => 'offer18:' . $offerId,
        'members_only' => 0,
    ];
}

function offer18_offer_id(array $offer): string
{
    return offer18_value($offer, ['offer_id', 'id', 'oid']);
}

function offer18_tracking_url(array $offer): string
{
    foreach (['tracking_url', 'trackingUrl', 'click_url', 'clickUrl'] as $field) {
        $value = offer18_value($offer, [$field]);
        if (coupon_is_offer18_tracking_url($value)) {
            return $value;
        }
    }

    $affiliates = $offer['affiliates'] ?? [];
    if (is_array($affiliates)) {
        foreach ($affiliates as $affiliate) {
            if (!is_array($affiliate)) {
                continue;
            }
            $value = trim((string) ($affiliate['tracking_url'] ?? ''));
            if (coupon_is_offer18_tracking_url($value)) {
                return $value;
            }
        }
    }

    return '';
}

function offer18_banner(array $offer): string
{
    $creatives = $offer['creatives'] ?? [];
    if (is_array($creatives)) {
        foreach ($creatives as $creative) {
            $url = is_array($creative) ? trim((string) ($creative['url'] ?? '')) : '';
            if (coupon_is_image_url($url)) {
                return $url;
            }
        }
    }

    return offer18_value($offer, ['thumbnail', 'image', 'logo'], 'assets/og-cupons.png') ?: 'assets/og-cupons.png';
}

function offer18_offer_category(array $offer): string
{
    return canonical_category(offer18_value($offer, ['category', 'offer_category']), implode(' ', [
        offer18_value($offer, ['offer_name', 'name', 'title']),
        offer18_value($offer, ['advertiser.name', 'advertiser_name', 'advertiser']),
        strip_tags(offer18_value($offer, ['description', 'offer_terms', 'terms'])),
    ]));
}

function offer18_description(array $offer, string $store): string
{
    $description = trim(strip_tags(offer18_value($offer, ['description', 'offer_description', 'offer_terms'])));
    return $description !== '' ? $description : 'Oferta disponivel na ' . $store . ' por tempo limitado.';
}

function offer18_value(array $source, array $paths, string $default = ''): string
{
    foreach ($paths as $path) {
        $value = $source;
        foreach (explode('.', $path) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                $value = null;
                break;
            }
            $value = $value[$part];
        }
        if (is_array($value)) {
            $value = $value['name'] ?? $value['title'] ?? '';
        }
        $value = trim((string) $value);
        if ($value !== '') {
            return $value;
        }
    }

    return $default;
}

function offer18_decimal_or_null(string $value): ?string
{
    $value = trim(str_replace(',', '.', $value));
    return is_numeric($value) ? number_format((float) $value, 2, '.', '') : null;
}

function hasoffers_accounts(): array
{
    $config = app_config();
    $fromConfig = $config['integrations']['hasoffers']['accounts'] ?? [];
    if (is_array($fromConfig) && $fromConfig) {
        return array_values($fromConfig);
    }

    return integration_json_setting('hasoffers_accounts', []);
}

function save_hasoffers_account(array $account): void
{
    $accounts = hasoffers_accounts();
    $accountIndex = isset($account['account_index']) ? (int) $account['account_index'] : -1;
    $existingAccount = $accountIndex >= 0 && isset($accounts[$accountIndex]) && is_array($accounts[$accountIndex])
        ? $accounts[$accountIndex]
        : [];

    $account = [
        'label' => trim((string) ($account['label'] ?? 'HasOffers')),
        'network_id' => hasoffers_normalize_network_id((string) ($account['network_id'] ?? '')),
        'api_key' => trim((string) ($account['api_key'] ?? '')),
        'affiliate_id' => trim((string) ($account['affiliate_id'] ?? '')),
    ];

    if ($account['api_key'] === '' && $existingAccount) {
        $account['api_key'] = trim((string) ($existingAccount['api_key'] ?? ''));
    }

    if ($account['network_id'] === '' || $account['api_key'] === '') {
        throw new RuntimeException('Informe nome da conta, Network ID e API key do HasOffers.');
    }

    if ($accountIndex >= 0 && isset($accounts[$accountIndex])) {
        $accounts[$accountIndex] = $account;
        save_integration_json_setting('hasoffers_accounts', array_values($accounts));
        return;
    }

    $key = hasoffers_account_key($account);
    foreach ($accounts as $index => $existing) {
        if (hasoffers_account_key($existing) === $key) {
            $accounts[$index] = $account;
            save_integration_json_setting('hasoffers_accounts', array_values($accounts));
            return;
        }
    }

    $accounts[] = $account;
    save_integration_json_setting('hasoffers_accounts', array_values($accounts));
}

function delete_hasoffers_account(int $index): void
{
    $accounts = hasoffers_accounts();
    if (!isset($accounts[$index])) {
        throw new RuntimeException('Conta HasOffers nao encontrada.');
    }

    unset($accounts[$index]);
    save_integration_json_setting('hasoffers_accounts', array_values($accounts));
}

function hasoffers_account_key(array $account): string
{
    return normalize_search_text((string) ($account['label'] ?? '')) . ':' . trim((string) ($account['network_id'] ?? ''));
}

function hasoffers_normalize_network_id(string $value): string
{
    $value = strtolower(trim($value));
    $host = parse_url($value, PHP_URL_HOST);
    if (is_string($host) && $host !== '') {
        $value = $host;
    }
    $value = preg_replace('/^https?:\/\//', '', $value) ?: $value;
    $value = preg_replace('/\.api\.hasoffers\.com.*$/', '', $value) ?: $value;
    $value = preg_replace('/\.hasoffers\.com.*$/', '', $value) ?: $value;

    return trim($value, "/ \t\n\r\0\x0B");
}

function hasoffers_account(int $index = 0): array
{
    $accounts = hasoffers_accounts();
    return $accounts[$index] ?? [];
}

function hasoffers_account_endpoint(array $account): string
{
    $networkId = trim((string) ($account['network_id'] ?? ''));
    return $networkId === '' ? '' : 'https://' . $networkId . '.api.hasoffers.com';
}

function hasoffers_status_options(): array
{
    return [
        'active' => 'Ativas',
        'pending' => 'Pendentes',
        'paused' => 'Pausadas',
        'expired' => 'Encerradas',
    ];
}

function hasoffers_default_excluded_terms(): string
{
    return '';
}

function hasoffers_normalize_filters(array $filters): array
{
    $status = (string) ($filters['status'] ?? 'active');
    $publishStatus = (string) ($filters['publish_status'] ?? 'rascunho');
    $publishStatus = in_array($publishStatus, ['ativo', 'rascunho'], true) ? $publishStatus : 'rascunho';

    return [
        'account_index' => max(0, (int) ($filters['account_index'] ?? 0)),
        'page' => max(1, min(50, (int) ($filters['page'] ?? 1))),
        'limit' => max(10, min(200, (int) ($filters['limit'] ?? 100))),
        'status' => array_key_exists($status, hasoffers_status_options()) ? $status : 'active',
        'query' => trim((string) ($filters['query'] ?? '')),
        'categories' => array_values(array_unique(array_filter(array_map(
            fn ($category) => canonical_category((string) $category),
            (array) ($filters['categories'] ?? [])
        )))),
        'excluded_terms' => array_values(array_filter(array_map('trim', explode(',', (string) ($filters['excluded_terms'] ?? hasoffers_default_excluded_terms()))))),
        'publish_status' => $publishStatus,
        'selected_external_ids' => array_values(array_filter(array_map('strval', (array) ($filters['selected_external_ids'] ?? [])))),
    ];
}

function hasoffers_request(array $account, string $target, string $method, array $query = []): array
{
    if (empty($account['network_id']) || empty($account['api_key'])) {
        throw new RuntimeException('Configure uma conta HasOffers antes de buscar ofertas.');
    }

    $query = array_merge([
        'api_key' => $account['api_key'],
        'Target' => $target,
        'Method' => $method,
    ], $query);

    $url = 'https://' . $account['network_id'] . '.api.hasoffers.com/Apiv3/json?' . hasoffers_query_string($query);
    if (!function_exists('curl_init')) {
        throw new RuntimeException('A extensao cURL do PHP precisa estar ativa para usar HasOffers.');
    }

    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);
    $body = curl_exec($curl);
    $error = curl_error($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);

    if ($body === false || $error) {
        throw new RuntimeException('Falha ao chamar HasOffers: ' . $error);
    }

    $decoded = json_decode((string) $body, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Resposta invalida do HasOffers.');
    }
    if ($status < 200 || $status >= 300) {
        throw new RuntimeException('HasOffers recusou a chamada: HTTP ' . $status);
    }

    $response = is_array($decoded['response'] ?? null) ? $decoded['response'] : $decoded;
    $ok = (int) ($response['status'] ?? 1);
    if ($ok !== 1) {
        $errors = $response['errors'] ?? $response['errorMessage'] ?? 'Erro na API';
        throw new RuntimeException('HasOffers recusou a chamada: ' . (is_array($errors) ? implode(', ', array_map('strval', $errors)) : (string) $errors));
    }

    return $response;
}

function hasoffers_query_string(array $query): string
{
    $parts = [];
    foreach ($query as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $item) {
                $parts[] = rawurlencode((string) $key) . '=' . rawurlencode((string) $item);
            }
            continue;
        }

        $parts[] = rawurlencode((string) $key) . '=' . rawurlencode((string) $value);
    }

    return implode('&', $parts);
}

function hasoffers_offers(array $filters): array
{
    $filters = hasoffers_normalize_filters($filters);
    $account = hasoffers_account($filters['account_index']);
    $query = hasoffers_base_offer_query($filters, $account);

    foreach (hasoffers_offer_methods() as $attempt) {
        try {
            $attemptQuery = $query;
            if (!empty($attempt['without_status'])) {
                unset($attemptQuery['filters[status]']);
            }

            $response = hasoffers_request($account, $attempt['target'], $attempt['method'], $attemptQuery);
            $items = hasoffers_response_items($response);
            if ($items) {
                if ($attempt['method'] !== 'findAll' || !empty($attempt['without_status'])) {
                    create_system_log('hasoffers_fallback', 'HasOffers usou metodo alternativo', $attempt['target'] . '::' . $attempt['method'] . ' retornou ' . count($items) . ' campanhas.', 'HasOffers');
                }

                return !empty($attempt['without_status']) ? hasoffers_filter_active_rows($items, $filters['status']) : $items;
            }
        } catch (Throwable $exception) {
            create_system_log('hasoffers_method_error', 'HasOffers tentativa falhou', $attempt['target'] . '::' . $attempt['method'] . ': ' . $exception->getMessage(), 'HasOffers');
        }
    }

    return [];
}

function hasoffers_base_offer_query(array $filters, array $account): array
{
    $query = [
        'limit' => $filters['limit'],
        'page' => $filters['page'],
        'filters[status]' => $filters['status'],
        'contain[]' => ['Advertiser', 'OfferCategory', 'Thumbnail'],
        'fields[]' => ['id', 'name', 'description', 'preview_url', 'expiration_date', 'status', 'default_payout', 'percent_payout', 'payout_type', 'terms_and_conditions'],
    ];
    if (!empty($account['affiliate_id'])) {
        $query['contain[TrackingLink][affiliate_id]'] = $account['affiliate_id'];
    } else {
        $query['contain[]'][] = 'TrackingLink';
    }
    if ($filters['query'] !== '') {
        $query['filters[name][LIKE]'] = '%' . $filters['query'] . '%';
    }

    return $query;
}

function hasoffers_offer_methods(): array
{
    return [
        ['target' => 'Affiliate_Offer', 'method' => 'findAll', 'without_status' => false],
        ['target' => 'Affiliate_Offer', 'method' => 'findAll', 'without_status' => true],
        ['target' => 'Affiliate_Offer', 'method' => 'findMyApprovedOffers', 'without_status' => true],
        ['target' => 'Affiliate_Offer', 'method' => 'findMyOffers', 'without_status' => true],
    ];
}

function hasoffers_response_items(array $response): array
{
    $data = $response['data'] ?? [];
    if (!is_array($data)) {
        return [];
    }

    return hasoffers_extract_offer_rows($data);
}

function hasoffers_extract_offer_rows(array $value): array
{
    if (hasoffers_looks_like_offer_row($value)) {
        return [$value];
    }

    if (array_key_exists('data', $value) && is_array($value['data'])) {
        $nested = hasoffers_extract_offer_rows($value['data']);
        if ($nested) {
            return $nested;
        }
    }

    $rows = [];
    foreach ($value as $item) {
        if (!is_array($item)) {
            continue;
        }

        if (hasoffers_looks_like_offer_row($item)) {
            $rows[] = $item;
            continue;
        }

        foreach (hasoffers_extract_offer_rows($item) as $nestedRow) {
            $rows[] = $nestedRow;
        }
    }

    return $rows;
}

function hasoffers_looks_like_offer_row(array $value): bool
{
    return isset($value['Offer'])
        || isset($value['id'])
        || isset($value['offer_id'])
        || isset($value['AffiliateOffer'])
        || isset($value['Affiliate_Offer']);
}

function hasoffers_filter_active_rows(array $items, string $status): array
{
    if ($status === '') {
        return $items;
    }

    return array_values(array_filter($items, function ($row) use ($status): bool {
        if (!is_array($row)) {
            return false;
        }

        $rowStatus = strtolower(hasoffers_value($row, ['Offer.status', 'status']));
        return $rowStatus === '' || $rowStatus === strtolower($status);
    }));
}

function hasoffers_debug_probe(array $filters = []): array
{
    $filters = hasoffers_normalize_filters($filters);
    $account = hasoffers_account($filters['account_index']);
    $baseQuery = hasoffers_base_offer_query($filters, $account);
    unset($baseQuery['filters[status]']);

    $results = [];
    foreach (hasoffers_offer_methods() as $attempt) {
        $attemptQuery = $baseQuery;
        if (empty($attempt['without_status'])) {
            $attemptQuery['filters[status]'] = $filters['status'];
        }

        try {
            $response = hasoffers_request($account, $attempt['target'], $attempt['method'], $attemptQuery);
            $items = hasoffers_response_items($response);
            $results[] = [
                'target' => $attempt['target'],
                'method' => $attempt['method'],
                'with_status_filter' => empty($attempt['without_status']),
                'response_keys' => implode(', ', array_keys($response)),
                'data_keys' => is_array($response['data'] ?? null) ? implode(', ', array_slice(array_keys($response['data']), 0, 8)) : '',
                'items' => count($items),
                'first_item' => hasoffers_debug_sanitize($items[0] ?? []),
                'error' => '',
            ];
        } catch (Throwable $exception) {
            $results[] = [
                'target' => $attempt['target'],
                'method' => $attempt['method'],
                'with_status_filter' => empty($attempt['without_status']),
                'response_keys' => '',
                'data_keys' => '',
                'items' => 0,
                'first_item' => [],
                'error' => $exception->getMessage(),
            ];
        }
    }

    return $results;
}

function hasoffers_debug_sanitize($value, int $depth = 0)
{
    if ($depth > 3) {
        return '[truncated]';
    }

    if (!is_array($value)) {
        $text = (string) $value;
        if (preg_match('/api[_-]?key|token|password|secret/i', $text)) {
            return '[redacted]';
        }

        return mb_strlen($text) > 180 ? mb_substr($text, 0, 180) . '...' : $text;
    }

    $safe = [];
    foreach (array_slice($value, 0, 14, true) as $key => $item) {
        $keyText = (string) $key;
        if (preg_match('/api[_-]?key|token|password|secret/i', $keyText)) {
            $safe[$keyText] = '[redacted]';
            continue;
        }

        $safe[$keyText] = hasoffers_debug_sanitize($item, $depth + 1);
    }

    return $safe;
}

function hasoffers_preview_offers(array $filters = []): array
{
    $filters = hasoffers_normalize_filters($filters);
    $offers = hasoffers_offers($filters);
    $items = [];
    $diagnostics = hasoffers_empty_diagnostics();
    foreach ($offers as $offer) {
        if (!is_array($offer)) {
            $diagnostics['invalid_rows']++;
            continue;
        }
        if (!hasoffers_offer_passes_filters($offer, $filters)) {
            $diagnostics['filtered_out']++;
            continue;
        }
        $items[] = hasoffers_preview_item($offer, $filters['account_index']);
    }

    return ['filters' => $filters, 'items' => $items, 'total' => count($offers), 'matched' => count($items), 'diagnostics' => $diagnostics];
}

function hasoffers_import_offers(array $filters = []): array
{
    $filters = hasoffers_normalize_filters($filters);
    $selectedExternalIds = $filters['selected_external_ids'];
    $offers = hasoffers_offers($filters);
    $created = 0;
    $updated = 0;
    $skipped = 0;
    $diagnostics = hasoffers_empty_diagnostics();
    foreach ($offers as $offer) {
        if (!is_array($offer)) {
            $diagnostics['invalid_rows']++;
            $skipped++;
            continue;
        }
        if (!hasoffers_offer_passes_filters($offer, $filters)) {
            $diagnostics['filtered_out']++;
            $skipped++;
            continue;
        }
        $payload = hasoffers_offer_payload($offer, $filters['publish_status'], $filters['account_index'], $diagnostics);
        if (!$payload || ($selectedExternalIds && !in_array($payload['external_id'], $selectedExternalIds, true))) {
            if ($payload && $selectedExternalIds && !in_array($payload['external_id'], $selectedExternalIds, true)) {
                $diagnostics['not_selected']++;
            }
            $skipped++;
            continue;
        }
        $existing = coupon_by_external_id($payload['external_id']);
        save_coupon($payload, $existing ? (int) $existing['id'] : null);
        monitor_integration_brand('HasOffers', hasoffers_value($offer, ['Advertiser.id', 'advertiser_id'], $payload['store']), $payload['store'], $payload['category']);
        monitor_integration_offer('HasOffers', $payload, hasoffers_offer_id($offer), hasoffers_value($offer, ['Advertiser.id', 'advertiser_id'], ''));
        $existing ? $updated++ : $created++;
    }

    create_system_log('hasoffers_import', 'Importacao HasOffers', $created . ' criadas, ' . $updated . ' atualizadas e ' . $skipped . ' ignoradas. ' . hasoffers_diagnostics_summary($diagnostics), 'HasOffers');
    return ['created' => $created, 'updated' => $updated, 'skipped' => $skipped, 'total' => count($offers), 'diagnostics' => $diagnostics];
}

function sync_hasoffers_watchlist(): array
{
    $profile = integration_profile('hasoffers', []);
    $accounts = hasoffers_accounts();
    $seen = [];
    $updated = 0;
    $new = 0;
    $read = 0;
    $accountErrors = 0;
    $diagnostics = hasoffers_empty_diagnostics();

    if (!$accounts) {
        throw new RuntimeException('Configure uma conta HasOffers antes de buscar ofertas.');
    }

    foreach ($accounts as $accountIndex => $account) {
        $filters = hasoffers_normalize_filters(array_merge($profile, ['account_index' => $accountIndex]));

        try {
            $offers = hasoffers_offers($filters);
        } catch (Throwable $exception) {
            $accountErrors++;
            create_system_log(
                'hasoffers_sync_account_error',
                'Erro em conta HasOffers',
                (string) ($account['label'] ?? 'HasOffers') . ': ' . $exception->getMessage(),
                'HasOffers'
            );
            continue;
        }

        $read += count($offers);
        foreach ($offers as $offer) {
            if (!is_array($offer)) {
                $diagnostics['invalid_rows']++;
                continue;
            }
            if (!hasoffers_offer_passes_filters($offer, $filters)) {
                $diagnostics['filtered_out']++;
                continue;
            }
            $payload = hasoffers_offer_payload($offer, $filters['publish_status'], $filters['account_index'], $diagnostics);
            if (!$payload) {
                continue;
            }
            $seen[$payload['external_id']] = true;
            $existing = coupon_by_external_id($payload['external_id']);
            save_coupon($payload, $existing ? (int) $existing['id'] : null);
            monitor_integration_offer('HasOffers', $payload, hasoffers_offer_id($offer), hasoffers_value($offer, ['Advertiser.id', 'advertiser_id'], ''));
            $existing ? $updated++ : $new++;
        }
    }

    if ($accountErrors === count($accounts)) {
        throw new RuntimeException('Nenhuma conta HasOffers respondeu durante a sincronizacao.');
    }

    $missing = 0;
    if ($accountErrors === 0) {
        foreach (monitored_integration_offers('HasOffers') as $watch) {
            if (!isset($seen[(string) $watch['external_id']])) {
                mark_monitor_missing($watch);
                $missing++;
            } else {
                mark_monitor_seen('HasOffers', (string) $watch['external_id']);
            }
        }
    }

    $errorSummary = $accountErrors > 0 ? ' Contas com erro: ' . $accountErrors . '.' : '';
    create_system_log('hasoffers_sync', 'Sincronizacao HasOffers', $read . ' lidas, ' . $updated . ' atualizadas, ' . $new . ' novas e ' . $missing . ' ausentes.' . $errorSummary . ' ' . hasoffers_diagnostics_summary($diagnostics), 'HasOffers');

    return ['partner' => 'HasOffers', 'read' => $read, 'updated' => $updated, 'new' => $new, 'missing' => $missing, 'errors' => $accountErrors, 'diagnostics' => $diagnostics];
}

function hasoffers_empty_diagnostics(): array
{
    return [
        'invalid_rows' => 0,
        'filtered_out' => 0,
        'missing_offer_id' => 0,
        'missing_tracking' => 0,
        'not_selected' => 0,
    ];
}

function hasoffers_diagnostics_summary(array $diagnostics): string
{
    $parts = [];
    if (!empty($diagnostics['filtered_out'])) {
        $parts[] = $diagnostics['filtered_out'] . ' filtradas';
    }
    if (!empty($diagnostics['missing_offer_id'])) {
        $parts[] = $diagnostics['missing_offer_id'] . ' sem ID';
    }
    if (!empty($diagnostics['missing_tracking'])) {
        $parts[] = $diagnostics['missing_tracking'] . ' sem tracking link';
    }
    if (!empty($diagnostics['not_selected'])) {
        $parts[] = $diagnostics['not_selected'] . ' nao selecionadas';
    }
    if (!empty($diagnostics['invalid_rows'])) {
        $parts[] = $diagnostics['invalid_rows'] . ' linhas invalidas';
    }

    return $parts ? 'Diagnostico: ' . implode(', ', $parts) . '.' : 'Diagnostico: sem bloqueios.';
}

function hasoffers_preview_item(array $row, int $accountIndex = 0): array
{
    $offerId = hasoffers_offer_id($row);
    $accountKey = hasoffers_account_key(hasoffers_account($accountIndex));
    $externalId = $offerId !== '' ? 'hasoffers:' . $accountKey . ':' . $offerId : '';
    $title = hasoffers_value($row, ['Offer.name', 'name'], 'Campanha HasOffers');
    $store = hasoffers_value($row, ['Advertiser.company', 'Advertiser.name', 'advertiser_name'], 'HasOffers');
    $category = hasoffers_offer_category($row);
    $trackingUrl = $offerId !== '' ? hasoffers_tracking_url($row, $accountIndex) : '';
    $code = hasoffers_value($row, ['Offer.coupon_code', 'coupon_code', 'code']);

    return [
        'external_id' => $externalId,
        'offer_id' => $offerId,
        'store' => $store,
        'title' => $title,
        'category' => $category,
        'offer_type' => $code !== '' ? 'cupom' : 'campanha',
        'redemption_type' => $code !== '' ? 'texto_redirect' : 'redirect',
        'status' => hasoffers_value($row, ['Offer.status', 'status'], 'active'),
        'tracking_ready' => coupon_is_hasoffers_tracking_url($trackingUrl),
        'existing' => $externalId !== '' && (bool) coupon_by_external_id($externalId),
    ];
}

function hasoffers_offer_payload(array $row, string $status = 'rascunho', int $accountIndex = 0, ?array &$diagnostics = null): ?array
{
    $offer = hasoffers_offer_data($row);
    $offerId = hasoffers_offer_id($row);
    if ($offerId === '') {
        if (is_array($diagnostics)) {
            $diagnostics['missing_offer_id']++;
        }
        return null;
    }

    $trackingUrl = hasoffers_tracking_url($row, $accountIndex);
    if (!coupon_is_hasoffers_tracking_url($trackingUrl)) {
        if (is_array($diagnostics)) {
            $diagnostics['missing_tracking']++;
        }
        return null;
    }

    $title = hasoffers_value($row, ['Offer.name', 'name'], 'Oferta HasOffers');
    $store = hasoffers_value($row, ['Advertiser.company', 'Advertiser.name', 'advertiser_name'], 'HasOffers');
    $category = hasoffers_offer_category($row);
    $code = hasoffers_value($row, ['Offer.coupon_code', 'coupon_code', 'code']);

    return [
        'category' => $category,
        'store' => $store,
        'title' => $title,
        'description' => hasoffers_description($row, $store),
        'code' => $code,
        'target_url' => hasoffers_value($row, ['Offer.preview_url', 'preview_url'], $trackingUrl),
        'banner_url' => hasoffers_banner($row),
        'logo_url' => hasoffers_value($row, ['Thumbnail.url', 'Offer.thumbnail_url', 'thumbnail_url']),
        'starts_at' => date('Y-m-d'),
        'ends_at' => lomadee_date(hasoffers_value($row, ['Offer.expiration_date', 'expiration_date'])) ?: date('Y-m-d', strtotime('+30 days')),
        'status' => $status,
        'featured' => 0,
        'rules' => trim(strip_tags(hasoffers_value($row, ['Offer.terms_and_conditions', 'terms_and_conditions'], 'Confira as regras no site parceiro antes de finalizar.'))),
        'redemption_type' => $code !== '' ? 'texto_redirect' : 'redirect',
        'offer_type' => $code !== '' ? 'cupom' : 'oferta_direta',
        'cta_label' => $code !== '' ? 'Resgatar cupom' : 'Resgatar oferta',
        'tracking_url' => $trackingUrl,
        'partner_network' => 'HasOffers',
        'payout' => offer18_decimal_or_null(hasoffers_value($row, ['Offer.default_payout', 'default_payout'])),
        'campaign_cap' => null,
        'sponsored' => 0,
        'priority' => 0,
        'tags' => 'hasoffers,' . strtolower(hasoffers_value($offer, ['payout_type'], 'offer')),
        'requirements' => $code !== '' ? 'Copie o cupom e use no site parceiro' : 'Resgate no site parceiro',
        'pixel_event' => 'hasoffers_' . preg_replace('/[^a-z0-9_]+/i', '_', $offerId),
        'external_id' => 'hasoffers:' . hasoffers_account_key(hasoffers_account($accountIndex)) . ':' . $offerId,
        'members_only' => 0,
    ];
}

function hasoffers_offer_passes_filters(array $row, array $filters): bool
{
    $haystack = strtolower(implode(' ', [
        hasoffers_value($row, ['Offer.name', 'name']),
        hasoffers_value($row, ['Advertiser.company', 'Advertiser.name', 'advertiser_name']),
        hasoffers_value($row, ['OfferCategory.name', 'OfferCategory.0.name']),
        strip_tags(hasoffers_value($row, ['Offer.description', 'description', 'Offer.terms_and_conditions'])),
    ]));
    if ($filters['query'] !== '' && !normalized_text_contains($haystack, $filters['query'])) {
        return false;
    }
    foreach ($filters['excluded_terms'] as $term) {
        if ($term !== '' && normalized_text_contains($haystack, $term)) {
            return false;
        }
    }
    if ($filters['categories']) {
        $category = hasoffers_offer_category($row);
        if (!in_array($category, $filters['categories'], true)) {
            return false;
        }
    }

    return true;
}

function hasoffers_tracking_url(array $row, int $accountIndex = 0): string
{
    $direct = hasoffers_value($row, ['TrackingLink.click_url', 'TrackingLink.tracking_url', 'tracking_link', 'tracking_url']);
    if (coupon_is_hasoffers_tracking_url($direct)) {
        return $direct;
    }

    $offerId = hasoffers_offer_id($row);
    if ($offerId === '') {
        return '';
    }

    try {
        $account = hasoffers_account($accountIndex);
        $params = [
            'offer_id' => $offerId,
            'params[aff_sub2]' => 'oferto',
            'options[tiny_url]' => '0',
        ];
        if (!empty($account['affiliate_id'])) {
            $params['affiliate_id'] = $account['affiliate_id'];
        }
        $response = hasoffers_request($account, 'Affiliate_Offer', 'generateTrackingLink', $params);
    } catch (Throwable $exception) {
        create_system_log('hasoffers_tracking_error', 'HasOffers sem tracking link', 'Oferta ' . $offerId . ' nao gerou tracking: ' . $exception->getMessage(), 'HasOffers', $offerId);
        return '';
    }

    $data = is_array($response['data'] ?? null) ? $response['data'] : [];
    return hasoffers_value($data, ['click_url', 'tracking_link', 'tracking_url', 'url']);
}

function hasoffers_offer_id(array $row): string
{
    return hasoffers_value($row, ['Offer.id', 'id', 'offer_id']);
}

function hasoffers_offer_data(array $row): array
{
    return is_array($row['Offer'] ?? null) ? $row['Offer'] : $row;
}

function hasoffers_offer_category(array $row): string
{
    return canonical_category(hasoffers_value($row, ['OfferCategory.name', 'OfferCategory.0.name', 'category']), implode(' ', [
        hasoffers_value($row, ['Offer.name', 'name']),
        hasoffers_value($row, ['Advertiser.company', 'Advertiser.name']),
        strip_tags(hasoffers_value($row, ['Offer.description', 'description'])),
    ]));
}

function hasoffers_description(array $row, string $store): string
{
    $description = trim(strip_tags(hasoffers_value($row, ['Offer.description', 'description', 'Offer.terms_and_conditions'])));
    return $description !== '' ? $description : 'Oferta disponivel na ' . $store . ' por tempo limitado.';
}

function hasoffers_banner(array $row): string
{
    return hasoffers_value($row, ['Thumbnail.url', 'Offer.thumbnail_url', 'thumbnail_url', 'Offer.image_url'], 'assets/og-cupons.png') ?: 'assets/og-cupons.png';
}

function hasoffers_value(array $source, array $paths, string $default = ''): string
{
    foreach ($paths as $path) {
        $value = $source;
        foreach (explode('.', $path) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                $value = null;
                break;
            }
            $value = $value[$part];
        }
        if (is_array($value)) {
            $value = $value['name'] ?? $value['company'] ?? $value['url'] ?? '';
        }
        $value = trim((string) $value);
        if ($value !== '') {
            return $value;
        }
    }

    return $default;
}

function awin_normalize_filters(array $filters): array
{
    $type = (string) ($filters['type'] ?? 'all');
    $membership = (string) ($filters['membership'] ?? 'joined');
    $status = (string) ($filters['status'] ?? 'active');
    $publishStatus = (string) ($filters['publish_status'] ?? 'rascunho');
    $publishStatus = in_array($publishStatus, ['ativo', 'rascunho'], true) ? $publishStatus : 'rascunho';
    $region = strtoupper(trim((string) ($filters['region'] ?? 'BR')));
    if (!preg_match('/^[A-Z]{2}$/', $region)) {
        $region = 'BR';
    }

    if (!array_key_exists($type, awin_offer_type_options())) {
        $type = 'all';
    }
    if (!array_key_exists($membership, awin_membership_options())) {
        $membership = 'joined';
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
        'region' => $region,
        'query' => trim((string) ($filters['query'] ?? '')),
        'categories' => array_values(array_unique(array_filter(array_map(
            fn ($category) => canonical_category((string) $category),
            (array) ($filters['categories'] ?? [])
        )))),
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

function awin_programmes(array $filters = []): array
{
    $publisherId = awin_publisher_id();
    if ($publisherId === '') {
        awin_connect_first_publisher();
        $publisherId = awin_publisher_id();
    }
    if ($publisherId === '') {
        throw new RuntimeException('Conecte a Awin antes de buscar anunciantes.');
    }

    $filters = awin_normalize_filters($filters);
    $response = awin_request('/publishers/' . rawurlencode($publisherId) . '/programmes', [
        'accessToken' => awin_access_token(),
        'relationship' => 'joined',
        'countryCode' => $filters['region'],
    ]);

    $programmes = $response['data'] ?? $response['programmes'] ?? $response;
    return is_array($programmes) ? array_values($programmes) : [];
}

function awin_preview_programmes(array $filters = []): array
{
    $filters = awin_normalize_filters($filters);
    $programmes = awin_programmes($filters);
    $items = [];

    foreach ($programmes as $programme) {
        if (!is_array($programme) || empty($programme['id']) || empty($programme['name'])) {
            continue;
        }

        if (!awin_programme_matches_filters($programme, $filters)) {
            continue;
        }

        $items[] = [
            'brand_id' => (string) $programme['id'],
            'name' => (string) $programme['name'],
            'description' => trim(strip_tags((string) ($programme['description'] ?? ''))),
            'display_url' => (string) ($programme['displayUrl'] ?? ''),
            'click_url' => (string) ($programme['clickThroughUrl'] ?? ''),
            'logo_url' => (string) ($programme['logoUrl'] ?? ''),
            'region' => (string) ($programme['primaryRegion']['countryCode'] ?? $filters['region']),
            'status' => (string) ($programme['status'] ?? ''),
            'link_status' => (string) ($programme['linkStatus'] ?? ''),
            'monitored' => in_array((string) $programme['id'], monitored_integration_brand_ids('Awin'), true),
        ];
    }

    return [
        'filters' => $filters,
        'items' => $items,
        'total' => count($programmes),
        'matched' => count($items),
    ];
}

function awin_programme_matches_filters(array $programme, array $filters): bool
{
    $region = strtoupper(trim((string) ($filters['region'] ?? 'BR')));
    $programmeRegion = strtoupper(trim((string) ($programme['primaryRegion']['countryCode'] ?? '')));

    if ($programmeRegion !== '' && $programmeRegion !== $region) {
        return false;
    }

    $haystack = implode(' ', [
        (string) ($programme['name'] ?? ''),
        strip_tags((string) ($programme['description'] ?? '')),
        (string) ($programme['displayUrl'] ?? ''),
        (string) ($programme['primarySector'] ?? ''),
    ]);

    if ($filters['query'] !== '' && !normalized_text_contains($haystack, $filters['query'])) {
        return false;
    }

    return true;
}

function save_awin_monitored_programmes(array $brandIds, array $programmes): int
{
    $saved = 0;
    foreach ($programmes as $programme) {
        if (!is_array($programme)) {
            continue;
        }

        $brandId = (string) ($programme['brand_id'] ?? $programme['id'] ?? '');
        $brandName = (string) ($programme['name'] ?? '');
        if ($brandId === '' || $brandName === '') {
            continue;
        }

        if (!in_array($brandId, $brandIds, true)) {
            continue;
        }

        monitor_integration_brand(
            'Awin',
            $brandId,
            $brandName,
            (string) ($programme['primarySector'] ?? '')
        );
        $saved++;
    }

    return $saved;
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
        if (!awin_advertiser_is_joined($advertiser)) {
            continue;
        }
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
    $filters['membership'] = 'joined';
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
        $advertiser = is_array($offer['advertiser'] ?? null) ? $offer['advertiser'] : [];
        if (!empty($advertiser['id'])) {
            monitor_integration_brand('Awin', (string) $advertiser['id'], (string) ($payload['store'] ?? 'Awin'), '');
        }
        monitor_integration_offer('Awin', $payload, (string) $offer['promotionId'], (string) ($advertiser['id'] ?? ''));
        $existing ? $updated++ : $created++;
    }

    create_system_log('awin_import', 'Importacao Awin', $created . ' criadas, ' . $updated . ' atualizadas e ' . $skipped . ' ignoradas.', 'Awin');

    return [
        'created' => $created,
        'updated' => $updated,
        'skipped' => $skipped,
        'total' => count($offers),
    ];
}

function awin_offer_passes_filters(array $offer, array $filters): bool
{
    if (!awin_offer_matches_region($offer, (string) ($filters['region'] ?? 'BR'))) {
        return false;
    }

    $advertiser = is_array($offer['advertiser'] ?? null) ? $offer['advertiser'] : [];
    if (($filters['membership'] ?? 'joined') === 'joined' && !awin_advertiser_is_joined($advertiser)) {
        return false;
    }

    $haystack = strtolower(implode(' ', [
        (string) ($advertiser['name'] ?? ''),
        (string) ($offer['title'] ?? ''),
        strip_tags((string) ($offer['description'] ?? '')),
        strip_tags((string) ($offer['terms'] ?? '')),
    ]));

    if ($filters['query'] !== '' && !normalized_text_contains($haystack, $filters['query'])) {
        return false;
    }

    foreach ($filters['excluded_terms'] as $term) {
        if ($term !== '' && normalized_text_contains($haystack, $term)) {
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

function awin_advertiser_is_joined(array $advertiser): bool
{
    if (array_key_exists('joined', $advertiser)) {
        return filter_var($advertiser['joined'], FILTER_VALIDATE_BOOLEAN);
    }

    foreach (['membership', 'relationship', 'status'] as $field) {
        $value = normalize_search_text((string) ($advertiser[$field] ?? ''));
        if (in_array($value, ['joined', 'active', 'approved', 'aprovado', 'ativo'], true)) {
            return true;
        }
        if (in_array($value, ['notjoined', 'not joined', 'pending', 'suspended', 'rejected'], true)) {
            return false;
        }
    }

    return true;
}

function awin_offer_matches_region(array $offer, string $region): bool
{
    $region = strtoupper(trim($region));
    if (!preg_match('/^[A-Z]{2}$/', $region)) {
        $region = 'BR';
    }

    $regions = is_array($offer['regions'] ?? null) ? $offer['regions'] : [];
    if (!$regions) {
        return true;
    }

    if (!empty($regions['all'])) {
        return true;
    }

    $codes = awin_offer_region_codes($regions);
    if (!$codes) {
        return true;
    }

    return in_array($region, $codes, true);
}

function awin_offer_region_codes(array $regions): array
{
    $codes = [];
    $list = is_array($regions['list'] ?? null) ? $regions['list'] : [];

    foreach ($list as $region) {
        if (!is_array($region)) {
            continue;
        }

        foreach (['countryCode', 'country_code', 'code'] as $field) {
            $code = strtoupper(trim((string) ($region[$field] ?? '')));
            if (preg_match('/^[A-Z]{2}$/', $code)) {
                $codes[] = $code;
                break;
            }
        }
    }

    foreach (['countryCode', 'country_code', 'regionCode', 'region_code'] as $field) {
        $code = strtoupper(trim((string) ($regions[$field] ?? '')));
        if (preg_match('/^[A-Z]{2}$/', $code)) {
            $codes[] = $code;
        }
    }

    foreach (['countryCodes', 'country_codes', 'regionCodes', 'region_codes'] as $field) {
        if (!is_array($regions[$field] ?? null)) {
            continue;
        }

        foreach ($regions[$field] as $code) {
            $code = strtoupper(trim((string) $code));
            if (preg_match('/^[A-Z]{2}$/', $code)) {
                $codes[] = $code;
            }
        }
    }

    return array_values(array_unique($codes));
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
    $targetUrl = trim((string) ($offer['url'] ?? ''));
    $trackingUrl = trim((string) ($offer['urlTracking'] ?? ''));
    if ($promotionId === '' || !coupon_is_awin_tracking_url($trackingUrl)) {
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
        'target_url' => $targetUrl !== '' ? $targetUrl : $trackingUrl,
        'banner_url' => 'assets/og-cupons.png',
        'logo_url' => awin_advertiser_logo($offer),
        'starts_at' => lomadee_date($offer['startDate'] ?? null) ?: date('Y-m-d'),
        'ends_at' => lomadee_date($offer['endDate'] ?? null) ?: date('Y-m-d', strtotime('+30 days')),
        'status' => $status,
        'featured' => 0,
        'rules' => trim(strip_tags((string) ($offer['terms'] ?? 'Confira as regras no site parceiro antes de finalizar.'))),
        'redemption_type' => $code !== '' ? 'texto_redirect' : 'redirect',
        'offer_type' => $type === 'voucher' ? 'cupom' : 'oferta_direta',
        'cta_label' => $code !== '' ? 'Resgatar cupom' : 'Ver oferta',
        'tracking_url' => $trackingUrl,
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

function awin_advertiser_logo(array $offer): string
{
    $advertiser = is_array($offer['advertiser'] ?? null) ? $offer['advertiser'] : [];
    foreach (['logoUrl', 'logo', 'imageUrl'] as $field) {
        $value = trim((string) ($advertiser[$field] ?? $offer[$field] ?? ''));
        if (coupon_is_image_url($value)) {
            return $value;
        }
    }

    return '';
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
        if ($search !== '' && !normalized_text_contains($haystack, $search)) {
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
    return category_options();
}

function lomadee_normalize_filters(array $filters): array
{
    $types = $filters['types'] ?? lomadee_default_campaign_types();
    $types = array_values(array_intersect((array) $types, array_keys(lomadee_campaign_type_options())));

    $categories = array_values(array_unique(array_filter(array_map(
        fn ($category) => canonical_category((string) $category),
        (array) ($filters['categories'] ?? [])
    ))));
    $brandIds = array_values(array_filter(array_map('strval', (array) ($filters['brand_ids'] ?? []))));
    $excludedTerms = array_values(array_filter(array_map('trim', explode(',', (string) ($filters['excluded_terms'] ?? 'BANNERS:')))));
    $status = (string) ($filters['publish_status'] ?? 'rascunho');
    $status = in_array($status, ['ativo', 'rascunho'], true) ? $status : 'rascunho';

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
    $campaigns = lomadee_campaigns_for_filters($filters);

    $items = [];
    foreach ($campaigns as $campaign) {
        if (!is_array($campaign) || empty($campaign['id'])) {
            continue;
        }

        $brand = $brands[(string) ($campaign['organizationId'] ?? '')] ?? [];
        if (!lomadee_campaign_passes_filters($campaign, $brand, $filters)) {
            continue;
        }

        $type = (string) ($campaign['type'] ?? '');
        $code = trim((string) ($campaign['code'] ?? ''));
        $externalId = 'lomadee:' . (string) $campaign['id'];

        $items[] = [
            'external_id' => $externalId,
            'campaign_id' => (string) $campaign['id'],
            'brand_id' => (string) ($campaign['organizationId'] ?? ''),
            'store' => trim((string) ($brand['name'] ?? 'Lomadee')),
            'title' => trim((string) ($campaign['name'] ?? 'Oferta Lomadee')),
            'category' => lomadee_category($campaign, $brand),
            'type' => $type,
            'offer_type' => in_array($type, ['GenericCoupon', 'PersonalCoupon'], true) ? 'cupom' : 'oferta_direta',
            'redemption_type' => $code !== '' ? 'texto_redirect' : 'redirect',
            'has_code' => $code !== '',
            'banner_url' => lomadee_banner($campaign, $brand),
            'status' => $filters['publish_status'],
            'existing' => (bool) coupon_by_external_id($externalId),
        ];
    }

    return [
        'filters' => $filters,
        'items' => $items,
        'total' => count($campaigns),
        'matched' => count($items),
    ];
}

function lomadee_campaigns_for_filters(array $filters): array
{
    $filters = lomadee_normalize_filters($filters);
    $brandIds = $filters['brand_ids'];
    if (!$brandIds && $filters['brand_query'] !== '') {
        $brandIds = array_values(array_map(
            fn (array $brand): string => (string) $brand['id'],
            lomadee_brand_options($filters['brand_query'], max($filters['max_pages'], 20))
        ));
    }

    if (!$brandIds) {
        return lomadee_fetch_campaigns_resilient($filters);
    }

    $campaigns = [];
    $seen = [];
    foreach ($brandIds as $brandId) {
        $items = lomadee_fetch_campaigns_resilient($filters, $brandId);

        foreach ($items as $item) {
            if (!is_array($item) || empty($item['id'])) {
                continue;
            }

            $id = (string) $item['id'];
            if (isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            $campaigns[] = $item;
        }
    }

    return $campaigns;
}

function lomadee_fetch_campaigns_resilient(array $filters, string $brandId = ''): array
{
    $query = [
        'types' => implode(',', $filters['types']),
        'status' => 'onTime',
    ];
    if ($brandId !== '') {
        $query['organizationId'] = $brandId;
    }

    try {
        return lomadee_fetch_all('/affiliate/campaigns', $query, $filters['max_pages']);
    } catch (RuntimeException $error) {
        if (count($filters['types']) <= 1 || stripos($error->getMessage(), 'campaigns_request_failed') === false) {
            throw $error;
        }
    }

    $items = [];
    foreach ($filters['types'] as $type) {
        $typeQuery = $query;
        $typeQuery['types'] = $type;

        try {
            $items = array_merge($items, lomadee_fetch_all('/affiliate/campaigns', $typeQuery, $filters['max_pages']));
        } catch (RuntimeException $error) {
            if (stripos($error->getMessage(), 'campaigns_request_failed') === false) {
                throw $error;
            }
        }
    }

    return $items;
}

function lomadee_import_campaigns(int $maxPages = 10, array $filters = []): array
{
    $filters['max_pages'] = $maxPages;
    $filters = lomadee_normalize_filters($filters);
    $selectedExternalIds = $filters['selected_external_ids'];
    $brands = lomadee_brand_map(max($maxPages, 20));
    $campaigns = lomadee_campaigns_for_filters($filters);

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

    create_system_log('lomadee_import', 'Importacao Lomadee', $created . ' criadas, ' . $updated . ' atualizadas e ' . $skipped . ' ignoradas.', 'Lomadee');

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
    $campaigns = lomadee_campaigns_for_filters($filters);
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
            create_system_log('lomadee_campaign_new', 'Nova campanha Lomadee', ($payload['store'] ?? 'Lomadee') . ' - ' . ($payload['title'] ?? 'campanha') . ' foi adicionada pela sincronizacao.', 'Lomadee', $externalId);
            $new++;
        }
    }

    foreach (monitored_integration_offers('Lomadee') as $watch) {
        if (empty($seen[(string) $watch['external_id']])) {
            mark_monitor_missing($watch);
            create_system_log('lomadee_campaign_missing', 'Campanha sumiu da Lomadee', ($watch['store'] ?? 'Lomadee') . ' - ' . ($watch['title'] ?? 'campanha') . ' nao apareceu na sincronizacao.', 'Lomadee', (string) $watch['external_id']);
            $missing++;
        }
    }

    return ['partner' => 'Lomadee', 'updated' => $updated, 'missing' => $missing, 'new' => $new, 'read' => count($campaigns)];
}

function sync_awin_watchlist(): array
{
    $profile = integration_profile('awin', []);
    $filters = awin_normalize_filters($profile);
    $filters['membership'] = 'joined';
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

        if (!awin_offer_passes_filters($offer, $filters)) {
            continue;
        }

        $seen[$externalId] = true;

        $payload = awin_offer_payload($offer, $filters['publish_status']);
        $existing = coupon_by_external_id($externalId);
        if ($payload && $existing) {
            save_coupon($payload, (int) $existing['id']);
            mark_monitor_seen('Awin', $externalId);
            $updated++;
        } elseif ($payload && $monitoredBrandIds) {
            save_coupon($payload);
            monitor_integration_offer('Awin', $payload, (string) $offer['promotionId'], (string) ($offer['advertiserId'] ?? ''));
            create_admin_notification(
                'campaign_new',
                'Nova oferta de anunciante monitorado',
                ($payload['store'] ?? 'Awin') . ' - ' . ($payload['title'] ?? 'oferta') . ' apareceu no feed.',
                'Awin',
                $externalId
            );
            create_system_log('awin_campaign_new', 'Nova oferta Awin', ($payload['store'] ?? 'Awin') . ' - ' . ($payload['title'] ?? 'oferta') . ' foi adicionada pela sincronizacao.', 'Awin', $externalId);
            $new++;
        }
    }

    foreach (monitored_integration_offers('Awin') as $watch) {
        if (empty($seen[(string) $watch['external_id']])) {
            if (!coupon_by_external_id((string) $watch['external_id'])) {
                continue;
            }

            mark_monitor_missing($watch);
            create_system_log('awin_campaign_missing', 'Oferta sumiu da Awin', ($watch['store'] ?? 'Awin') . ' - ' . ($watch['title'] ?? 'oferta') . ' nao apareceu na sincronizacao.', 'Awin', (string) $watch['external_id']);
            $missing++;
        }
    }

    return ['partner' => 'Awin', 'updated' => $updated, 'missing' => $missing, 'new' => $new, 'read' => count($offers)];
}

function sync_all_integrations(): array
{
    $results = [];
    foreach (['Lomadee' => 'sync_lomadee_watchlist', 'Awin' => 'sync_awin_watchlist', 'Offer18' => 'sync_offer18_watchlist', 'HasOffers' => 'sync_hasoffers_watchlist'] as $partner => $callback) {
        try {
            $results[] = $callback();
        } catch (Throwable $exception) {
            create_admin_notification('sync_error', 'Erro ao sincronizar ' . $partner, $exception->getMessage(), $partner);
            create_system_log('integration_error', 'Erro ao sincronizar ' . $partner, $exception->getMessage(), $partner);
            $results[] = ['partner' => $partner, 'error' => $exception->getMessage()];
        }
    }

    create_system_log(
        'integration_cron',
        'Cron de integracoes executada',
        sync_results_summary($results),
        'Sistema'
    );

    return $results;
}

function sync_results_summary(array $results): string
{
    $parts = [];
    $errors = 0;

    foreach ($results as $result) {
        $partner = (string) ($result['partner'] ?? 'Integracao');
        if (!empty($result['error'])) {
            $errors++;
            $parts[] = $partner . ': erro - ' . (string) $result['error'];
            continue;
        }

        $parts[] = $partner
            . ': lidas=' . (int) ($result['read'] ?? 0)
            . ', atualizadas=' . (int) ($result['updated'] ?? 0)
            . ', novas=' . (int) ($result['new'] ?? 0)
            . ', ausentes=' . (int) ($result['missing'] ?? 0);
    }

    $prefix = $errors > 0
        ? $errors . ' integracao(oes) com erro. '
        : 'Todas as integracoes foram chamadas. ';

    return $prefix . implode(' | ', $parts);
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

    if ($filters['brand_query'] !== '' && !$filters['brand_ids'] && !normalized_text_contains($haystack, $filters['brand_query'])) {
        return false;
    }

    foreach ($filters['excluded_terms'] as $term) {
        if ($term !== '' && normalized_text_contains($haystack, $term)) {
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
        if ($term !== '' && normalized_text_contains($text, $term)) {
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
    $targetUrl = lomadee_campaign_target_url($campaign, $brand);
    $trackingUrl = lomadee_campaign_tracking_url($campaign, $brand, $targetUrl);
    if (!lomadee_is_tracking_url($trackingUrl, $targetUrl)) {
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
        'target_url' => $targetUrl,
        'banner_url' => lomadee_banner($campaign, $brand),
        'logo_url' => lomadee_brand_logo($campaign, $brand),
        'starts_at' => $startsAt,
        'ends_at' => $endsAt,
        'status' => $status,
        'featured' => !empty($campaign['isHighlight']) ? 1 : 0,
        'rules' => trim((string) ($campaign['channels'][0]['message'] ?? 'Confira as regras no site parceiro antes de finalizar.')),
        'redemption_type' => $code !== '' ? 'texto_redirect' : 'redirect',
        'offer_type' => in_array($type, ['GenericCoupon', 'PersonalCoupon'], true) ? 'cupom' : 'oferta_direta',
        'cta_label' => $code !== '' ? 'Resgatar cupom' : 'Ver oferta',
        'tracking_url' => $trackingUrl,
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

function lomadee_brand_logo(array $campaign, array $brand): string
{
    foreach (['logo', 'logoUrl', 'image', 'imageUrl'] as $field) {
        $value = trim((string) ($brand[$field] ?? $campaign[$field] ?? ''));
        if (coupon_is_image_url($value)) {
            return $value;
        }
    }

    return '';
}

function lomadee_campaign_target_url(array $campaign, array $brand): string
{
    foreach (['url', 'site', 'destinationUrl', 'destination_url'] as $field) {
        $value = trim((string) ($campaign[$field] ?? $brand[$field] ?? ''));
        if (lomadee_is_usable_url($value)) {
            return $value;
        }
    }

    return '';
}

function lomadee_campaign_tracking_url(array $campaign, array $brand, string $targetUrl = ''): string
{
    $mdasc = lomadee_campaign_mdasc($campaign);
    $transactionUrl = lomadee_find_transaction_url($campaign, $targetUrl, $mdasc);
    if ($transactionUrl !== '') {
        return $transactionUrl;
    }

    return '';
}

function lomadee_find_transaction_url($value, string $targetUrl = '', string $mdasc = '', bool $trustedValue = false): string
{
    if (is_string($value)) {
        $value = trim($value);
        if (!$trustedValue || !lomadee_is_tracking_url($value, $targetUrl)) {
            return '';
        }

        return $mdasc !== '' ? lomadee_add_mdasc($value, $mdasc) : $value;
    }

    if (!is_array($value)) {
        return '';
    }

    $parameterizedKeys = [
        'trackingUrl',
        'tracking_url',
        'urlTracking',
        'url_tracking',
        'affiliateUrl',
        'affiliate_url',
        'transactionUrl',
        'transaction_url',
        'redirectUrl',
        'redirect_url',
        'deepLink',
        'deeplink',
    ];

    foreach ($parameterizedKeys as $key) {
        if (!array_key_exists($key, $value)) {
            continue;
        }

        $found = lomadee_find_transaction_url($value[$key], $targetUrl, $mdasc, true);
        if ($found !== '') {
            return $found;
        }
    }

    foreach (['shortUrls', 'shortUrl'] as $key) {
        if (!array_key_exists($key, $value)) {
            continue;
        }

        $found = lomadee_find_transaction_url($value[$key], $targetUrl, '', true);
        if ($found !== '') {
            return $found;
        }
    }

    foreach ($value as $item) {
        if (!$trustedValue && !is_array($item)) {
            continue;
        }

        $found = lomadee_find_transaction_url($item, $targetUrl, $mdasc, $trustedValue);
        if ($found !== '') {
            return $found;
        }
    }

    return '';
}

function lomadee_is_tracking_url(string $url, string $targetUrl = ''): bool
{
    if (!lomadee_is_usable_url($url)) {
        return false;
    }

    return lomadee_normalize_url($url) !== lomadee_normalize_url($targetUrl);
}

function lomadee_is_usable_url(string $url): bool
{
    if (!preg_match('/^https?:\/\//i', $url)) {
        return false;
    }

    $path = strtolower((string) parse_url($url, PHP_URL_PATH));
    return !preg_match('/\.(jpe?g|png|gif|webp|svg)(\?.*)?$/i', $path);
}

function lomadee_normalize_url(string $url): string
{
    return rtrim(trim($url), '/');
}

function lomadee_campaign_mdasc(array $campaign): string
{
    $featureId = trim((string) ($campaign['id'] ?? ''));
    if ($featureId === '') {
        return '';
    }

    return substr('oferto_' . preg_replace('/[^a-z0-9_-]+/i', '_', $featureId), 0, 90);
}

function lomadee_add_mdasc(string $url, string $mdasc): string
{
    if ($url === '' || $mdasc === '' || !lomadee_is_usable_url($url)) {
        return $url;
    }

    if (preg_match('/(?:\\?|&)mdasc=/i', $url)) {
        return $url;
    }

    $separator = strpos($url, '?') === false ? '?' : '&';
    return $url . $separator . 'mdasc=' . rawurlencode($mdasc);
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
    $context = implode(' ', [
        (string) ($brand['name'] ?? ''),
        (string) ($campaign['name'] ?? ''),
        strip_tags((string) ($campaign['description'] ?? '')),
    ]);
    $categories = $campaign['categories'] ?? [];
    if (is_array($categories) && !empty($categories[0])) {
        return lomadee_site_category(trim((string) $categories[0]), $context);
    }

    return lomadee_site_category(trim((string) ($brand['segment'] ?? 'Ofertas')), $context);
}

function lomadee_site_category(string $value, string $context = ''): string
{
    return canonical_category($value, $context);
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
