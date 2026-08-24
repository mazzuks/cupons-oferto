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
        event_type VARCHAR(40) NOT NULL DEFAULT 'cta',
        referer VARCHAR(500) DEFAULT NULL,
        user_agent VARCHAR(500) DEFAULT NULL,
        ip_hash CHAR(64) DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY coupon_clicks_coupon_idx (coupon_id, created_at),
        KEY coupon_clicks_event_idx (event_type, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $couponCount = (int) $pdo->query('SELECT COUNT(*) FROM coupons')->fetchColumn();
    if ($couponCount > 0) {
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
        'members_only' => "ALTER TABLE coupons ADD members_only TINYINT(1) NOT NULL DEFAULT 0 AFTER pixel_event",
    ];

    foreach ($columns as $column => $sql) {
        if (!coupon_column_exists($pdo, $column)) {
            $pdo->exec($sql);
        }
    }
}

function coupon_column_exists(PDO $pdo, string $column): bool
{
    $statement = $pdo->prepare("SHOW COLUMNS FROM coupons LIKE ?");
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
