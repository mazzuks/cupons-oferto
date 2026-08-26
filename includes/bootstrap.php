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
