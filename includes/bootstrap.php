<?php

declare(strict_types=1);

session_start();

function app_config(): array
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $path = __DIR__ . '/config.php';
    if (!is_file($path)) {
        $path = __DIR__ . '/config.example.php';
    }

    $config = require $path;
    return $config;
}

function db(): ?PDO
{
    static $pdo = false;

    if ($pdo !== false) {
        return $pdo;
    }

    $config = app_config()['db'];

    try {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['name'],
            $config['charset'] ?? 'utf8mb4'
        );
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        ensure_database($pdo);
    } catch (Throwable $error) {
        $pdo = null;
    }

    return $pdo;
}

function ensure_database(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY admins_email_unique (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS coupons (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        category VARCHAR(120) NOT NULL,
        store VARCHAR(160) NOT NULL,
        title VARCHAR(220) NOT NULL,
        description TEXT NOT NULL,
        code VARCHAR(80) DEFAULT NULL,
        target_url VARCHAR(500) NOT NULL,
        banner_url VARCHAR(500) NOT NULL,
        logo_url VARCHAR(500) DEFAULT NULL,
        starts_at DATE NOT NULL,
        ends_at DATE NOT NULL,
        status ENUM('ativo', 'rascunho', 'pausado') NOT NULL DEFAULT 'rascunho',
        featured TINYINT(1) NOT NULL DEFAULT 0,
        rules TEXT DEFAULT NULL,
        redemption_type VARCHAR(30) NOT NULL DEFAULT 'texto',
        offer_type VARCHAR(40) NOT NULL DEFAULT 'cupom',
        cta_label VARCHAR(80) DEFAULT NULL,
        tracking_url VARCHAR(500) DEFAULT NULL,
        partner_network VARCHAR(120) DEFAULT NULL,
        payout DECIMAL(10,2) DEFAULT NULL,
        campaign_cap INT UNSIGNED DEFAULT NULL,
        sponsored TINYINT(1) NOT NULL DEFAULT 0,
        priority INT NOT NULL DEFAULT 0,
        tags VARCHAR(500) DEFAULT NULL,
        nicho_principal VARCHAR(160) DEFAULT NULL,
        tags_produto VARCHAR(500) DEFAULT NULL,
        requirements VARCHAR(220) DEFAULT NULL,
        pixel_event VARCHAR(120) DEFAULT NULL,
        external_id VARCHAR(190) DEFAULT NULL,
        members_only TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY coupons_public_idx (status, starts_at, ends_at, featured),
        KEY coupons_category_idx (category)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    ensure_coupon_columns($pdo);

    $pdo->exec("CREATE TABLE IF NOT EXISTS coupon_clicks (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        coupon_id INT UNSIGNED NOT NULL,
        click_ref VARCHAR(120) DEFAULT NULL,
        event_type VARCHAR(40) NOT NULL DEFAULT 'cta',
        referer VARCHAR(500) DEFAULT NULL,
        user_agent VARCHAR(500) DEFAULT NULL,
        ip_hash CHAR(64) DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY coupon_clicks_coupon_idx (coupon_id, created_at),
        KEY coupon_clicks_event_idx (event_type, created_at),
        KEY coupon_clicks_ref_idx (click_ref)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    ensure_click_columns($pdo);
    ensure_integration_tables($pdo);
    ensure_api_log_tables($pdo);
    ensure_affiliation_tables($pdo);

    $couponCount = (int) $pdo->query('SELECT COUNT(*) FROM coupons')->fetchColumn();
    if ($couponCount > 0) {
        return;
    }

    $seedDisabled = (int) $pdo->query("SELECT COUNT(*) FROM integration_settings WHERE setting_key = 'seed_coupons_disabled' AND setting_value = '1'")->fetchColumn();
    if ($seedDisabled > 0) {
        return;
    }

    $statement = $pdo->prepare("INSERT INTO coupons
        (category, store, title, description, code, target_url, banner_url, starts_at, ends_at, status, featured, rules)
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach (fallback_coupons_seed() as $coupon) {
        $statement->execute($coupon);
    }
}

function ensure_coupon_columns(PDO $pdo): void
{
    $columns = [
        'redemption_type' => "ALTER TABLE coupons ADD redemption_type VARCHAR(30) NOT NULL DEFAULT 'texto' AFTER rules",
        'offer_type' => "ALTER TABLE coupons ADD offer_type VARCHAR(40) NOT NULL DEFAULT 'cupom' AFTER rules",
        'cta_label' => "ALTER TABLE coupons ADD cta_label VARCHAR(80) DEFAULT NULL AFTER offer_type",
        'tracking_url' => "ALTER TABLE coupons ADD tracking_url VARCHAR(500) DEFAULT NULL AFTER cta_label",
        'partner_network' => "ALTER TABLE coupons ADD partner_network VARCHAR(120) DEFAULT NULL AFTER tracking_url",
        'payout' => "ALTER TABLE coupons ADD payout DECIMAL(10,2) DEFAULT NULL AFTER partner_network",
        'campaign_cap' => "ALTER TABLE coupons ADD campaign_cap INT UNSIGNED DEFAULT NULL AFTER payout",
        'sponsored' => "ALTER TABLE coupons ADD sponsored TINYINT(1) NOT NULL DEFAULT 0 AFTER campaign_cap",
        'priority' => "ALTER TABLE coupons ADD priority INT NOT NULL DEFAULT 0 AFTER sponsored",
        'tags' => "ALTER TABLE coupons ADD tags VARCHAR(500) DEFAULT NULL AFTER priority",
        'nicho_principal' => "ALTER TABLE coupons ADD nicho_principal VARCHAR(160) DEFAULT NULL AFTER tags",
        'tags_produto' => "ALTER TABLE coupons ADD tags_produto VARCHAR(500) DEFAULT NULL AFTER nicho_principal",
        'requirements' => "ALTER TABLE coupons ADD requirements VARCHAR(220) DEFAULT NULL AFTER tags",
        'pixel_event' => "ALTER TABLE coupons ADD pixel_event VARCHAR(120) DEFAULT NULL AFTER requirements",
        'external_id' => "ALTER TABLE coupons ADD external_id VARCHAR(190) DEFAULT NULL AFTER pixel_event",
        'members_only' => "ALTER TABLE coupons ADD members_only TINYINT(1) NOT NULL DEFAULT 0 AFTER pixel_event",
        'logo_url' => "ALTER TABLE coupons ADD logo_url VARCHAR(500) DEFAULT NULL AFTER banner_url",
    ];

    foreach ($columns as $column => $sql) {
        if (!coupon_column_exists($pdo, $column)) {
            $pdo->exec($sql);
        }
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS integration_settings (
        setting_key VARCHAR(120) NOT NULL,
        setting_value TEXT DEFAULT NULL,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function ensure_click_columns(PDO $pdo): void
{
    if (!coupon_click_column_exists($pdo, 'click_ref')) {
        $pdo->exec('ALTER TABLE coupon_clicks ADD click_ref VARCHAR(120) DEFAULT NULL AFTER coupon_id');
    }
}

function ensure_integration_tables(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS mapa_loja_nicho (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        nome_loja VARCHAR(160) NOT NULL,
        nicho_principal VARCHAR(160) NOT NULL,
        tags_produto VARCHAR(500) DEFAULT NULL,
        status ENUM('ativo', 'pausado') NOT NULL DEFAULT 'ativo',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY mapa_loja_nicho_nome_unique (nome_loja),
        KEY mapa_loja_nicho_status_idx (status),
        KEY mapa_loja_nicho_nicho_idx (nicho_principal)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS integration_watchlist (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        partner VARCHAR(60) NOT NULL,
        external_id VARCHAR(190) NOT NULL,
        source_id VARCHAR(120) DEFAULT NULL,
        brand_id VARCHAR(120) DEFAULT NULL,
        store VARCHAR(160) NOT NULL,
        title VARCHAR(220) NOT NULL,
        status ENUM('monitorado', 'sumiu', 'pausado') NOT NULL DEFAULT 'monitorado',
        last_seen_at TIMESTAMP NULL DEFAULT NULL,
        missing_since TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY integration_watch_unique (partner, external_id),
        KEY integration_watch_status_idx (partner, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS integration_brand_watchlist (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        partner VARCHAR(60) NOT NULL,
        brand_id VARCHAR(120) NOT NULL,
        brand_name VARCHAR(180) NOT NULL,
        segment VARCHAR(220) DEFAULT NULL,
        status ENUM('monitorado', 'pausado') NOT NULL DEFAULT 'monitorado',
        last_seen_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY integration_brand_watch_unique (partner, brand_id),
        KEY integration_brand_watch_status_idx (partner, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_notifications (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        type VARCHAR(60) NOT NULL,
        title VARCHAR(180) NOT NULL,
        body TEXT NOT NULL,
        partner VARCHAR(60) DEFAULT NULL,
        external_id VARCHAR(190) DEFAULT NULL,
        read_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY admin_notifications_read_idx (read_at, created_at),
        KEY admin_notifications_partner_idx (partner, external_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS affiliate_conversions (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        partner VARCHAR(60) NOT NULL,
        external_conversion_id VARCHAR(190) NOT NULL,
        coupon_id INT UNSIGNED DEFAULT NULL,
        external_id VARCHAR(190) DEFAULT NULL,
        click_ref VARCHAR(120) DEFAULT NULL,
        store VARCHAR(160) DEFAULT NULL,
        status VARCHAR(60) NOT NULL DEFAULT 'pending',
        sale_amount DECIMAL(12,2) DEFAULT NULL,
        commission_amount DECIMAL(12,2) DEFAULT NULL,
        currency VARCHAR(10) DEFAULT NULL,
        conversion_at DATETIME DEFAULT NULL,
        raw_json MEDIUMTEXT DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY affiliate_conversions_unique (partner, external_conversion_id),
        KEY affiliate_conversions_coupon_idx (coupon_id, conversion_at),
        KEY affiliate_conversions_partner_idx (partner, conversion_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function ensure_api_log_tables(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS api_request_logs (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        endpoint VARCHAR(80) NOT NULL,
        query_string VARCHAR(700) DEFAULT NULL,
        total_results INT UNSIGNED DEFAULT NULL,
        ip_hash CHAR(64) DEFAULT NULL,
        user_agent VARCHAR(500) DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY api_request_logs_endpoint_idx (endpoint, created_at),
        KEY api_request_logs_created_idx (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function ensure_affiliation_tables(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS affiliate_partners (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(160) NOT NULL,
        email VARCHAR(190) NOT NULL,
        company_name VARCHAR(190) DEFAULT NULL,
        phone VARCHAR(80) DEFAULT NULL,
        website VARCHAR(255) DEFAULT NULL,
        status ENUM('ativo', 'pausado') NOT NULL DEFAULT 'ativo',
        payment_method VARCHAR(60) DEFAULT NULL,
        payment_reference VARCHAR(255) DEFAULT NULL,
        notes TEXT DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY affiliate_partners_email_unique (email),
        KEY affiliate_partners_status_idx (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS affiliate_campaigns (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        source_coupon_id INT UNSIGNED DEFAULT NULL,
        published_coupon_id INT UNSIGNED DEFAULT NULL,
        network VARCHAR(80) NOT NULL DEFAULT 'manual',
        external_id VARCHAR(190) DEFAULT NULL,
        advertiser VARCHAR(190) NOT NULL,
        title VARCHAR(220) NOT NULL,
        description TEXT DEFAULT NULL,
        category VARCHAR(120) DEFAULT NULL,
        landing_url VARCHAR(500) NOT NULL,
        tracking_url VARCHAR(500) DEFAULT NULL,
        banner_url VARCHAR(500) DEFAULT NULL,
        logo_url VARCHAR(500) DEFAULT NULL,
        code VARCHAR(80) DEFAULT NULL,
        rules TEXT DEFAULT NULL,
        payout DECIMAL(12,2) DEFAULT NULL,
        payout_model VARCHAR(60) DEFAULT NULL,
        campaign_cap INT UNSIGNED DEFAULT NULL,
        starts_at DATE DEFAULT NULL,
        ends_at DATE DEFAULT NULL,
        status ENUM('disponivel', 'selecionada', 'publicada', 'pausada', 'encerrada') NOT NULL DEFAULT 'disponivel',
        tracking_mode ENUM('CLASSIC_PIXEL', 'JOURNEY_JS') NOT NULL DEFAULT 'CLASSIC_PIXEL',
        redirect_mode ENUM('FAST_302', 'HTML_BRIDGE') NOT NULL DEFAULT 'FAST_302',
        postback_secret VARCHAR(120) NOT NULL,
        cookie_ttl_days INT UNSIGNED NOT NULL DEFAULT 180,
        utm_source_gate VARCHAR(120) NOT NULL DEFAULT 'oferto',
        allowed_domains TEXT DEFAULT NULL,
        retargeting_config MEDIUMTEXT DEFAULT NULL,
        raw_json MEDIUMTEXT DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY affiliate_campaigns_network_external_unique (network, external_id),
        KEY affiliate_campaigns_source_coupon_idx (source_coupon_id),
        KEY affiliate_campaigns_status_idx (status),
        KEY affiliate_campaigns_network_idx (network),
        KEY affiliate_campaigns_expiration_idx (ends_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS affiliate_clicks (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        campaign_id INT UNSIGNED NOT NULL,
        affiliate_partner_id INT UNSIGNED DEFAULT NULL,
        tid VARCHAR(120) NOT NULL,
        click_ref VARCHAR(120) DEFAULT NULL,
        referer VARCHAR(500) DEFAULT NULL,
        user_agent VARCHAR(500) DEFAULT NULL,
        ip_hash CHAR(64) DEFAULT NULL,
        utm_json TEXT DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY affiliate_clicks_campaign_idx (campaign_id, created_at),
        KEY affiliate_clicks_partner_idx (affiliate_partner_id, created_at),
        KEY affiliate_clicks_tid_idx (tid)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS affiliate_campaign_conversions (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        campaign_id INT UNSIGNED NOT NULL,
        affiliate_partner_id INT UNSIGNED DEFAULT NULL,
        tid VARCHAR(120) NOT NULL,
        order_id VARCHAR(190) DEFAULT NULL,
        value DECIMAL(12,2) DEFAULT NULL,
        commission_amount DECIMAL(12,2) DEFAULT NULL,
        currency VARCHAR(10) NOT NULL DEFAULT 'BRL',
        status VARCHAR(60) NOT NULL DEFAULT 'pending',
        signature VARCHAR(190) DEFAULT NULL,
        raw_json MEDIUMTEXT DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY affiliate_campaign_conversions_order_unique (campaign_id, order_id),
        KEY affiliate_campaign_conversions_campaign_idx (campaign_id, created_at),
        KEY affiliate_campaign_conversions_partner_idx (affiliate_partner_id, created_at),
        KEY affiliate_campaign_conversions_tid_idx (tid)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS affiliate_transactions (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        affiliate_partner_id INT UNSIGNED NOT NULL,
        campaign_id INT UNSIGNED DEFAULT NULL,
        conversion_id BIGINT UNSIGNED DEFAULT NULL,
        amount DECIMAL(12,2) NOT NULL,
        type ENUM('earning', 'withdrawal', 'bonus', 'adjustment') NOT NULL,
        status ENUM('pending', 'approved', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
        description VARCHAR(255) DEFAULT NULL,
        metadata TEXT DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY affiliate_transactions_partner_idx (affiliate_partner_id, created_at),
        KEY affiliate_transactions_campaign_idx (campaign_id),
        KEY affiliate_transactions_conversion_idx (conversion_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function import_offer_classifications(PDO $pdo, string $path): array
{
    $handle = fopen($path, 'rb');
    if (!$handle) {
        return ['updated' => 0, 'mapped_stores' => 0, 'skipped' => 0];
    }

    $delimiter = classification_csv_delimiter($path);
    $headers = fgetcsv($handle, 0, $delimiter);
    if (!is_array($headers)) {
        fclose($handle);
        return ['updated' => 0, 'mapped_stores' => 0, 'skipped' => 0];
    }

    $headers = array_map('classification_header_key', $headers);
    $rows = [];
    $storeMap = [];

    while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
        if (!is_array($values)) {
            continue;
        }

        $row = [];
        foreach ($headers as $index => $header) {
            $row[$header] = classification_clean_csv_value((string) ($values[$index] ?? ''));
        }

        $id = (int) ($row['id'] ?? 0);
        $store = $row['loja'] ?? $row['store'] ?? $row['nome_loja'] ?? '';
        $niche = $row['nicho_principal'] ?? '';
        $title = $row['titulo'] ?? $row['title'] ?? '';

        if ($id <= 0 || $store === '' || $niche === '') {
            continue;
        }

        $row['tags_produto'] = trim((string) ($row['tags_produto'] ?? $row['tags'] ?? ''));
        if ($row['tags_produto'] === '') {
            $row['tags_produto'] = classification_tags_from_row($store, $niche, $title, $row['categoria_atual'] ?? '');
        }

        $rows[] = [
            'id' => $id,
            'store' => $store,
            'niche' => $niche,
            'tags' => $row['tags_produto'],
        ];

        $storeKey = classification_store_key($store);
        if (!isset($storeMap[$storeKey])) {
            $storeMap[$storeKey] = [
                'store' => $store,
                'niches' => [],
                'tags' => [],
            ];
        }

        $storeMap[$storeKey]['niches'][$niche] = ($storeMap[$storeKey]['niches'][$niche] ?? 0) + 1;
        foreach (array_filter(array_map('trim', explode(',', $row['tags_produto']))) as $tag) {
            $storeMap[$storeKey]['tags'][$tag] = true;
        }
    }

    fclose($handle);

    $update = $pdo->prepare("UPDATE coupons
        SET nicho_principal = ?, tags_produto = ?, updated_at = NOW()
        WHERE id = ?");
    $updated = 0;
    foreach ($rows as $row) {
        $update->execute([$row['niche'], $row['tags'], $row['id']]);
        $updated += $update->rowCount();
    }

    $upsert = $pdo->prepare("INSERT INTO mapa_loja_nicho (nome_loja, nicho_principal, tags_produto, status)
        VALUES (?, ?, ?, 'ativo')
        ON DUPLICATE KEY UPDATE
          nicho_principal = VALUES(nicho_principal),
          tags_produto = VALUES(tags_produto),
          status = 'ativo'");
    $mappedStores = 0;
    foreach ($storeMap as $store) {
        arsort($store['niches']);
        $niche = (string) array_key_first($store['niches']);
        $tags = implode(', ', array_slice(array_keys($store['tags']), 0, 18));
        $upsert->execute([$store['store'], $niche, $tags]);
        $mappedStores++;
    }

    return [
        'updated' => $updated,
        'mapped_stores' => $mappedStores,
        'rows' => count($rows),
    ];
}

function classification_header_key(string $header): string
{
    $header = classification_clean_csv_value($header);
    $header = strtolower(trim($header));
    $header = strtr($header, [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
        'é' => 'e', 'ê' => 'e',
        'í' => 'i',
        'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ú' => 'u',
        'ç' => 'c',
    ]);

    return preg_replace('/[^a-z0-9]+/', '_', $header) ?: '';
}

function classification_clean_csv_value(string $value): string
{
    $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    if (function_exists('mb_check_encoding') && !mb_check_encoding($value, 'UTF-8')) {
        $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1252, ISO-8859-1');
        if (is_string($converted) && $converted !== '') {
            return trim($converted);
        }
    }

    return $value;
}

function classification_csv_delimiter(string $path): string
{
    $handle = fopen($path, 'rb');
    if (!$handle) {
        return ',';
    }

    $line = (string) fgets($handle);
    fclose($handle);

    $delimiters = [',' => substr_count($line, ','), ';' => substr_count($line, ';'), "\t" => substr_count($line, "\t")];
    arsort($delimiters);

    return (string) array_key_first($delimiters);
}

function classification_store_key(string $store): string
{
    return classification_header_key($store);
}

function classification_tags_from_row(string $store, string $niche, string $title, string $category): string
{
    $tags = array_filter([$store, str_replace('_', ' ', $niche), $category]);
    $text = classification_header_key($title);
    $stopwords = [
        'acima', 'apenas', 'compras', 'cupom', 'desconto', 'frete', 'off', 'para', 'produtos',
        'todo', 'todos', 'valido', 'validos', 'site', 'com', 'nas', 'nos', 'seu', 'sua',
    ];

    foreach (explode('_', $text) as $word) {
        if (strlen($word) < 4 || in_array($word, $stopwords, true)) {
            continue;
        }
        $tags[] = $word;
    }

    return implode(', ', array_slice(array_values(array_unique($tags)), 0, 12));
}

function coupon_column_exists(PDO $pdo, string $column): bool
{
    $statement = $pdo->prepare("SHOW COLUMNS FROM coupons LIKE ?");
    $statement->execute([$column]);
    return (bool) $statement->fetch();
}

function coupon_click_column_exists(PDO $pdo, string $column): bool
{
    $statement = $pdo->prepare("SHOW COLUMNS FROM coupon_clicks LIKE ?");
    $statement->execute([$column]);
    return (bool) $statement->fetch();
}

function fallback_coupons_seed(): array
{
    return [
        ['Alimentação e Bebidas', 'Pizza Hut', 'Pizza grande com desconto para dividir', 'Cupom para economizar no pedido de pizza do fim de semana.', 'PIZZAOFERTA', 'https://oferto.digital/', 'https://oferto.digital/wp-content/uploads/2024/08/1-1.jpg', '2026-08-10', '2026-08-22', 'ativo', 1, 'Confira disponibilidade, lojas participantes e pedido mínimo antes de finalizar.'],
        ['Alimentação e Bebidas', 'Ruffles', 'Salgadinho para o lanche com cupom', 'Oferta para economizar em snacks, bebidas e itens de conveniência.', 'RUFFLES10', 'https://oferto.digital/', 'assets/ruffles-coupon.svg', '2026-08-18', '2026-08-24', 'ativo', 0, 'Produto alimentício: classificar sempre em Alimentação e Bebidas.'],
    ];
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function render_json_ld(array $data): void
{
    echo '<script type="application/ld+json">' . json_encode(
        $data,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ) . '</script>';
}

function render_oferto_brand_schema(string $pageUrl, string $pageName = 'Oferto Cupons'): void
{
    render_json_ld([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => 'https://oferto.digital/#organization',
                'name' => 'Oferto Digital',
                'alternateName' => [
                    'Oferto',
                    'Oferto Cupons',
                    'Cupons Oferto',
                ],
                'url' => 'https://oferto.digital/',
                'logo' => 'https://oferto.digital/wp-content/uploads/2024/08/oferto.png',
                'sameAs' => [
                    'https://cupons.oferto.digital/',
                ],
            ],
            [
                '@type' => 'WebSite',
                '@id' => 'https://cupons.oferto.digital/#website',
                'name' => 'Oferto Cupons',
                'alternateName' => [
                    'Oferto Digital Cupons',
                    'Oferto',
                    'Cupons Oferto',
                ],
                'url' => 'https://cupons.oferto.digital/',
                'publisher' => [
                    '@id' => 'https://oferto.digital/#organization',
                ],
                'inLanguage' => 'pt-BR',
            ],
            [
                '@type' => 'WebPage',
                '@id' => $pageUrl . '#webpage',
                'url' => $pageUrl,
                'name' => $pageName,
                'isPartOf' => [
                    '@id' => 'https://cupons.oferto.digital/#website',
                ],
                'about' => [
                    '@id' => 'https://oferto.digital/#organization',
                ],
                'inLanguage' => 'pt-BR',
            ],
        ],
    ]);
}

function adsense_client_id(): string
{
    return (string) (app_config()['adsense']['client_id'] ?? 'ca-pub-1725208559538025');
}

function adsense_default_slots(): array
{
    return [
        'v2_topo_responsivo' => '5284508420',
        'v2_entre_destaques_e_lista' => '3971426759',
        'v2_lateral_300x250' => '9321167801',
        'v2_antes_dicas' => '3971426759',
        'blog_topo_responsivo' => '4796538786',
        'guias_artigo_topo_responsivo' => '5834288282',
        'guias_lateral_300x250' => '9112072798',
        'sorteios_topo_responsivo' => '3358394686',
        'oferta_topo_responsivo' => '3971426759',
        'oferta_meio_responsivo' => '3971426759',
    ];
}

function adsense_slot_id(string $slot): string
{
    $configured = trim((string) (app_config()['adsense']['slots'][$slot] ?? ''));
    if ($configured !== '') {
        return $configured;
    }

    return adsense_default_slots()[$slot] ?? '';
}

function render_ad_slot(string $slot, string $class = 'inventory-slot-wide'): void
{
    $slotId = adsense_slot_id($slot);
    $classes = trim('inventory-slot ' . $class);

    if ($slotId === '') {
        echo '<div class="' . e($classes) . '" data-inventory-slot="' . e($slot) . '"><span>Publicidade</span></div>';
        return;
    }

    echo '<div class="' . e($classes) . ' adsense-slot" data-filled="true" data-inventory-slot="' . e($slot) . '">';
    echo '<ins class="adsbygoogle" style="display:block" data-ad-client="' . e(adsense_client_id()) . '" data-ad-slot="' . e($slotId) . '" data-ad-format="auto" data-full-width-responsive="true"></ins>';
    echo '<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
    echo '</div>';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
        http_response_code(419);
        exit('Sessao expirada. Volte e tente novamente.');
    }
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}
