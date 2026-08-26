<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function fallback_coupons(): array
{
    return [
        [
            'id' => 1,
            'category' => 'Alimentação e Bebidas',
            'store' => 'Pizza Hut',
            'title' => 'Pizza grande com desconto para dividir',
            'description' => 'Cupom para economizar no pedido de pizza do fim de semana.',
            'code' => 'PIZZAOFERTA',
            'target_url' => 'https://oferto.digital/',
            'banner_url' => 'https://oferto.digital/wp-content/uploads/2024/08/1-1.jpg',
            'starts_at' => '2026-08-10',
            'ends_at' => '2026-08-22',
            'status' => 'ativo',
            'featured' => 1,
            'rules' => 'Confira disponibilidade, lojas participantes e pedido mínimo antes de finalizar.',
            'redemption_type' => 'texto',
            'offer_type' => 'cupom',
            'cta_label' => '',
            'tracking_url' => '',
            'partner_network' => '',
            'payout' => '',
            'campaign_cap' => '',
            'sponsored' => 0,
            'priority' => 0,
            'tags' => '',
            'requirements' => '',
            'pixel_event' => '',
            'members_only' => 0,
        ],
        [
            'id' => 2,
            'category' => 'Alimentação e Bebidas',
            'store' => 'Ruffles',
            'title' => 'Salgadinho para o lanche com cupom',
            'description' => 'Oferta para economizar em snacks, bebidas e itens de conveniência.',
            'code' => 'RUFFLES10',
            'target_url' => 'https://oferto.digital/',
            'banner_url' => 'assets/ruffles-coupon.svg',
            'starts_at' => '2026-08-18',
            'ends_at' => '2026-08-24',
            'status' => 'ativo',
            'featured' => 0,
            'rules' => 'Produto alimentício: classificar sempre em Alimentação e Bebidas.',
            'redemption_type' => 'texto',
            'offer_type' => 'cupom',
            'cta_label' => '',
            'tracking_url' => '',
            'partner_network' => '',
            'payout' => '',
            'campaign_cap' => '',
            'sponsored' => 0,
            'priority' => 0,
            'tags' => '',
            'requirements' => '',
            'pixel_event' => '',
            'members_only' => 0,
        ],
    ];
}

function redemption_types(): array
{
    return [
        'texto' => 'So copiar cupom',
        'texto_redirect' => 'Copiar + resgatar',
        'redirect' => 'So resgatar oferta',
    ];
}

function redemption_type_label(?string $type): string
{
    $types = redemption_types();
    return $types[$type ?: 'texto'] ?? $types['texto'];
}

function category_options(): array
{
    return [
        'Alimentação e Bebidas',
        'Moda Feminina',
        'Moda Masculina',
        'Moda Infantil',
        'Moda Infantil Menina',
        'Moda Infantil Menino',
        'Compras',
        'Games',
        'Educação',
        'Entretenimento',
        'Kids',
        'Serviços',
        'Viagem',
        'Outros',
    ];
}

function canonical_category(string $value, string $context = ''): string
{
    $text = normalize_search_text($value . ' ' . $context);
    $rules = [
        'Alimentação e Bebidas' => ['china in box', 'temaki', 'yakisoba', 'pizza', 'restaurante', 'delivery', 'ifood', 'food', 'beverage', 'bebida', 'aliment', 'snack', 'salgad', 'lanche'],
        'Moda Infantil Menina' => ['moda infantil menina', 'moda infantil feminina', 'roupa infantil menina', 'roupa menina', 'vestido infantil', 'saia infantil', 'calcinha infantil'],
        'Moda Infantil Menino' => ['moda infantil menino', 'moda infantil masculina', 'roupa infantil menino', 'roupa menino', 'bermuda infantil', 'cueca infantil'],
        'Moda Infantil' => ['moda infantil', 'roupa infantil', 'calcado infantil', 'calçado infantil'],
        'Moda Feminina' => ['moda feminina', 'roupa feminina', 'vestido', 'saia', 'blusa feminina', 'calcinha', 'lingerie', 'salto feminino'],
        'Moda Masculina' => ['moda masculina', 'roupa masculina', 'terno', 'blazer', 'cueca', 'polo', 'camisa masculina', 'sapato masculino'],
        'Games' => ['game', 'games', 'gift card', 'playstation', 'xbox', 'nintendo', 'steam'],
        'Educação' => ['educa', 'education', 'curso', 'faculdade', 'idioma', 'ensino'],
        'Serviços' => ['service', 'servic', 'seguro', 'insurance', 'banco', 'financeiro', 'celular', 'internet'],
        'Entretenimento' => ['entreten', 'cinema', 'streaming', 'show', 'evento'],
        'Viagem' => ['travel', 'viagem', 'hotel', 'passagem', 'turismo'],
        'Kids' => ['kids', 'brinquedo', 'bebe', 'bebê', 'infantil'],
        'Compras' => ['shopping', 'compras', 'marketplace', 'mercado', 'loja', 'ecommerce', 'eletronico', 'eletro'],
    ];

    foreach ($rules as $category => $needles) {
        foreach ($needles as $needle) {
            if ($needle !== '' && strpos($text, normalize_search_text($needle)) !== false) {
                return $category;
            }
        }
    }

    foreach (category_options() as $category) {
        if (normalize_search_text($value) === normalize_search_text($category)) {
            return $category;
        }
    }

    return 'Outros';
}

function normalize_search_text(string $value): string
{
    $value = strtolower(trim($value));
    $value = strtr($value, [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c',
    ]);

    return preg_replace('/\s+/', ' ', $value) ?: '';
}

function offer_types(): array
{
    return [
        'cupom' => 'Cupom',
        'sorteio' => 'Sorteio',
        'cadastro' => 'Cadastro',
        'cashback' => 'Cashback',
        'oferta_direta' => 'Oferta direta',
        'compre_concorra' => 'Compre e concorra',
    ];
}

function offer_type_label(?string $type): string
{
    $types = offer_types();
    return $types[$type ?: 'cupom'] ?? 'Oferta';
}

function default_cta_label(?string $type, ?string $code = ''): string
{
    $labels = [
        'cupom' => $code ? 'Resgatar cupom' : 'Resgatar oferta',
        'sorteio' => 'Participar',
        'cadastro' => 'Cadastrar agora',
        'cashback' => 'Ativar cashback',
        'oferta_direta' => 'Ver oferta',
        'compre_concorra' => 'Ver como participar',
    ];

    return $labels[$type ?: 'cupom'] ?? 'Ver oferta';
}

function coupon_cta_label(array $coupon): string
{
    $custom = trim((string) ($coupon['cta_label'] ?? ''));
    if ($custom !== '') {
        return $custom;
    }

    $publicCode = coupon_shows_public_code($coupon) ? ($coupon['code'] ?? '') : '';
    return default_cta_label($coupon['offer_type'] ?? 'cupom', $publicCode);
}

function coupon_destination_url(array $coupon): string
{
    $tracking = trim((string) ($coupon['tracking_url'] ?? ''));
    $target = trim((string) ($coupon['target_url'] ?? ''));
    $partner = strtolower(trim((string) ($coupon['partner_network'] ?? '')));

    if ($partner === 'lomadee') {
        return coupon_is_lomadee_tracking_url($tracking, $target) ? $tracking : '';
    }

    if ($partner === 'awin') {
        return coupon_is_awin_tracking_url($tracking) ? $tracking : '';
    }

    return $tracking !== '' ? $tracking : $target;
}

function coupon_has_valid_destination(array $coupon): bool
{
    $url = coupon_destination_url($coupon);
    return is_remote_banner_url($url);
}

function coupon_is_ready_for_public_site(array $coupon): bool
{
    return !coupon_shows_rescue_button($coupon) || coupon_has_valid_destination($coupon);
}

function coupon_tracking_label(array $coupon): string
{
    $partner = strtolower(trim((string) ($coupon['partner_network'] ?? '')));
    if (!coupon_shows_rescue_button($coupon)) {
        return 'Sem botao externo';
    }

    if (in_array($partner, ['lomadee', 'awin'], true)) {
        return coupon_has_valid_destination($coupon) ? 'Tracking ok' : 'Sem tracking';
    }

    if (trim((string) ($coupon['tracking_url'] ?? '')) !== '') {
        return coupon_has_valid_destination($coupon) ? 'Tracking manual' : 'Tracking invalido';
    }

    return coupon_has_valid_destination($coupon) ? 'Link direto' : 'URL invalida';
}

function coupon_tracking_status_class(array $coupon): string
{
    $label = coupon_tracking_label($coupon);
    if (in_array($label, ['Tracking ok', 'Tracking manual'], true)) {
        return 'status-ativo';
    }

    if (in_array($label, ['Sem tracking', 'Tracking invalido', 'URL invalida'], true)) {
        return 'status-pausado';
    }

    return 'status-rascunho';
}

function create_system_log(string $type, string $title, string $body, string $partner = '', string $externalId = ''): void
{
    $pdo = db();
    if (!$pdo) {
        return;
    }

    $statement = $pdo->prepare('INSERT INTO admin_notifications (type, title, body, partner, external_id) VALUES (?, ?, ?, ?, ?)');
    $statement->execute([$type, $title, $body, $partner ?: null, $externalId ?: null]);
}

function system_logs(int $limit = 120): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    $statement = $pdo->prepare('SELECT * FROM admin_notifications ORDER BY created_at DESC, id DESC LIMIT ?');
    $statement->bindValue(1, $limit, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetchAll();
}

function coupon_is_partner_tracking_url(string $url, string $targetUrl = ''): bool
{
    if (!is_remote_banner_url($url)) {
        return false;
    }

    return trim_trailing_url_slash($url) !== trim_trailing_url_slash($targetUrl);
}

function coupon_is_lomadee_tracking_url(string $url, string $targetUrl = ''): bool
{
    if (!coupon_is_partner_tracking_url($url, $targetUrl)) {
        return false;
    }

    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    $query = strtolower((string) parse_url($url, PHP_URL_QUERY));

    return $host === 'lmdee.link'
        || substr($host, -11) === '.lmdee.link'
        || $host === 'acesse.vc'
        || substr($host, -10) === '.acesse.vc'
        || strpos($host, 'lomadee') !== false
        || strpos($query, 'lmdeetracking=') !== false
        || strpos($query, 'utm_source=lomadee') !== false;
}

function trim_trailing_url_slash(string $url): string
{
    return rtrim(trim($url), '/');
}

function coupon_is_awin_tracking_url(string $url): bool
{
    if (!is_remote_banner_url($url)) {
        return false;
    }

    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    return $host === 'awin1.com'
        || substr($host, -10) === '.awin1.com'
        || $host === 'awstrack.me'
        || substr($host, -12) === '.awstrack.me'
        || $host === 'awin.com'
        || substr($host, -9) === '.awin.com';
}

function coupon_has_code(array $coupon): bool
{
    return trim((string) ($coupon['code'] ?? '')) !== '';
}

function coupon_uses_text_redemption(array $coupon): bool
{
    return in_array($coupon['redemption_type'] ?? 'texto', ['texto', 'texto_redirect'], true) && coupon_has_code($coupon);
}

function coupon_shows_public_code(array $coupon): bool
{
    return coupon_uses_text_redemption($coupon);
}

function coupon_shows_rescue_button(array $coupon): bool
{
    return in_array($coupon['redemption_type'] ?? 'texto', ['redirect', 'texto_redirect'], true);
}

function coupon_mechanic_label(array $coupon): string
{
    if (coupon_shows_public_code($coupon)) {
        return 'Codigo';
    }

    $labels = [
        'sorteio' => 'Participacao',
        'cadastro' => 'Cadastro',
        'cashback' => 'Cashback',
        'oferta_direta' => 'Oferta',
        'compre_concorra' => 'Mecanica',
    ];

    return $labels[$coupon['offer_type'] ?? ''] ?? 'Oferta';
}

function coupon_mechanic_value(array $coupon): string
{
    if (coupon_shows_public_code($coupon)) {
        return trim((string) $coupon['code']);
    }

    $custom = trim((string) ($coupon['requirements'] ?? ''));
    if ($custom !== '') {
        return $custom;
    }

    $values = [
        'sorteio' => 'Ver regras',
        'cadastro' => 'Cadastro online',
        'cashback' => 'Ativacao online',
        'oferta_direta' => 'Sem codigo',
        'compre_concorra' => 'Compre e concorra',
    ];

    return $values[$coupon['offer_type'] ?? ''] ?? 'Oferta direta';
}

function active_coupons(): array
{
    $pdo = db();
    if (!$pdo) {
        return array_map('normalize_coupon_record', fallback_coupons());
    }

    $sql = "SELECT * FROM coupons
            WHERE status = 'ativo'
              AND starts_at <= CURDATE()
              AND ends_at >= CURDATE()
              AND title NOT LIKE 'BANNERS:%'
              AND title NOT LIKE 'BANNER:%'
            ORDER BY featured DESC, priority DESC, ends_at ASC, store ASC";

    return array_values(array_filter(array_map('normalize_coupon_record', $pdo->query($sql)->fetchAll()), 'coupon_is_ready_for_public_site'));
}

function all_coupons(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    return array_map('normalize_coupon_record', $pdo->query('SELECT * FROM coupons ORDER BY created_at DESC, id DESC')->fetchAll());
}

function coupon_by_id(int $id): ?array
{
    $pdo = db();
    if (!$pdo) {
        return null;
    }

    $statement = $pdo->prepare('SELECT * FROM coupons WHERE id = ?');
    $statement->execute([$id]);
    $coupon = $statement->fetch();

    return $coupon ? normalize_coupon_record($coupon) : null;
}

function normalize_coupon_record(array $coupon): array
{
    $coupon['category'] = canonical_category((string) ($coupon['category'] ?? ''), implode(' ', [
        (string) ($coupon['store'] ?? ''),
        (string) ($coupon['title'] ?? ''),
        (string) ($coupon['description'] ?? ''),
        (string) ($coupon['tags'] ?? ''),
        (string) ($coupon['requirements'] ?? ''),
    ]));

    return $coupon;
}

function save_coupon(array $data, ?int $id = null): void
{
    $pdo = db();
    if (!$pdo) {
        throw new RuntimeException('Banco de dados indisponivel.');
    }

    $fields = [
        'category',
        'store',
        'title',
        'description',
        'code',
        'target_url',
        'banner_url',
        'starts_at',
        'ends_at',
        'status',
        'featured',
        'rules',
        'redemption_type',
        'offer_type',
        'cta_label',
        'tracking_url',
        'partner_network',
        'payout',
        'campaign_cap',
        'sponsored',
        'priority',
        'tags',
        'requirements',
        'pixel_event',
        'external_id',
        'members_only',
    ];

    if ($id) {
        $sets = implode(', ', array_map(fn ($field) => "$field = :$field", $fields));
        $statement = $pdo->prepare("UPDATE coupons SET $sets, updated_at = NOW() WHERE id = :id");
        $data['id'] = $id;
        $statement->execute($data);
        return;
    }

    $columns = implode(', ', $fields);
    $params = ':' . implode(', :', $fields);
    $statement = $pdo->prepare("INSERT INTO coupons ($columns) VALUES ($params)");
    $statement->execute($data);
}

function coupon_by_external_id(string $externalId): ?array
{
    $pdo = db();
    if (!$pdo) {
        return null;
    }

    $statement = $pdo->prepare('SELECT * FROM coupons WHERE external_id = ? LIMIT 1');
    $statement->execute([$externalId]);
    $coupon = $statement->fetch();

    return $coupon ?: null;
}

function delete_coupon(int $id): void
{
    $pdo = db();
    if (!$pdo) {
        throw new RuntimeException('Banco de dados indisponivel.');
    }

    $statement = $pdo->prepare('DELETE FROM coupons WHERE id = ?');
    $statement->execute([$id]);
}

function clear_campaign_data(): int
{
    $pdo = db();
    if (!$pdo) {
        throw new RuntimeException('Banco de dados indisponivel.');
    }

    $count = (int) $pdo->query('SELECT COUNT(*) FROM coupons')->fetchColumn();

    $pdo->beginTransaction();
    try {
        $pdo->exec("INSERT INTO integration_settings (setting_key, setting_value) VALUES ('seed_coupons_disabled', '1') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $pdo->exec('DELETE FROM coupons');
        $pdo->commit();
    } catch (Throwable $error) {
        $pdo->rollBack();
        throw $error;
    }

    return $count;
}

function log_coupon_click(int $couponId, string $eventType): string
{
    $pdo = db();
    if (!$pdo) {
        return '';
    }

    $eventType = preg_replace('/[^a-z0-9_\-]/i', '', $eventType) ?: 'cta';
    $referer = substr((string) ($_SERVER['HTTP_REFERER'] ?? ''), 0, 500);
    $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $ipHash = $ip !== '' ? hash('sha256', $ip . '|' . session_id()) : null;
    $clickRef = 'oferto_' . $couponId . '_' . bin2hex(random_bytes(8));

    try {
        $statement = $pdo->prepare('INSERT INTO coupon_clicks (coupon_id, click_ref, event_type, referer, user_agent, ip_hash) VALUES (?, ?, ?, ?, ?, ?)');
        $statement->execute([$couponId, $clickRef, $eventType, $referer, $userAgent, $ipHash]);
    } catch (Throwable $error) {
        return '';
    }

    return $clickRef;
}

function coupon_go_url(array $coupon, string $eventType = 'cta'): string
{
    return 'go.php?id=' . (int) ($coupon['id'] ?? 0) . '&event=' . rawurlencode($eventType);
}

function click_report_summary(string $startDate, string $endDate): array
{
    $pdo = db();
    if (!$pdo) {
        return [
            'total_clicks' => 0,
            'unique_users' => 0,
            'active_offers' => 0,
            'top_offer' => '',
        ];
    }

    $statement = $pdo->prepare("SELECT
            COUNT(*) AS total_clicks,
            COUNT(DISTINCT ip_hash) AS unique_users,
            COUNT(DISTINCT coupon_id) AS active_offers
        FROM coupon_clicks
        WHERE created_at >= :start_date
          AND created_at < DATE_ADD(:end_date, INTERVAL 1 DAY)");
    $statement->execute([
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);
    $summary = $statement->fetch() ?: [];

    $top = $pdo->prepare("SELECT c.store, c.title, COUNT(*) AS clicks
        FROM coupon_clicks cc
        INNER JOIN coupons c ON c.id = cc.coupon_id
        WHERE cc.created_at >= :start_date
          AND cc.created_at < DATE_ADD(:end_date, INTERVAL 1 DAY)
        GROUP BY c.id, c.store, c.title
        ORDER BY clicks DESC, c.store ASC
        LIMIT 1");
    $top->execute([
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);
    $topOffer = $top->fetch();

    return [
        'total_clicks' => (int) ($summary['total_clicks'] ?? 0),
        'unique_users' => (int) ($summary['unique_users'] ?? 0),
        'active_offers' => (int) ($summary['active_offers'] ?? 0),
        'top_offer' => $topOffer ? $topOffer['store'] . ' - ' . $topOffer['title'] : '',
    ];
}

function conversion_report_summary(string $startDate, string $endDate): array
{
    $pdo = db();
    if (!$pdo) {
        return ['total_conversions' => 0, 'total_sales' => 0, 'total_commission' => 0, 'approved_commission' => 0];
    }

    $statement = $pdo->prepare("SELECT
            COUNT(*) AS total_conversions,
            COALESCE(SUM(sale_amount), 0) AS total_sales,
            COALESCE(SUM(commission_amount), 0) AS total_commission,
            COALESCE(SUM(CASE WHEN status IN ('approved', 'confirmed', 'paid') THEN commission_amount ELSE 0 END), 0) AS approved_commission
        FROM affiliate_conversions
        WHERE conversion_at >= :start_date
          AND conversion_at < DATE_ADD(:end_date, INTERVAL 1 DAY)");
    $statement->execute(['start_date' => $startDate, 'end_date' => $endDate]);
    $row = $statement->fetch() ?: [];

    return [
        'total_conversions' => (int) ($row['total_conversions'] ?? 0),
        'total_sales' => (float) ($row['total_sales'] ?? 0),
        'total_commission' => (float) ($row['total_commission'] ?? 0),
        'approved_commission' => (float) ($row['approved_commission'] ?? 0),
    ];
}

function conversion_report_rows(string $startDate, string $endDate): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    $statement = $pdo->prepare("SELECT
            ac.partner,
            ac.store,
            ac.status,
            COUNT(*) AS total_conversions,
            COALESCE(SUM(ac.sale_amount), 0) AS total_sales,
            COALESCE(SUM(ac.commission_amount), 0) AS total_commission,
            MAX(ac.conversion_at) AS last_conversion_at,
            c.title,
            c.category
        FROM affiliate_conversions ac
        LEFT JOIN coupons c ON c.id = ac.coupon_id
        WHERE ac.conversion_at >= :start_date
          AND ac.conversion_at < DATE_ADD(:end_date, INTERVAL 1 DAY)
        GROUP BY ac.partner, ac.store, ac.status, c.title, c.category
        ORDER BY total_commission DESC, total_conversions DESC");
    $statement->execute(['start_date' => $startDate, 'end_date' => $endDate]);

    return $statement->fetchAll();
}

function click_report_rows(string $startDate, string $endDate): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    $statement = $pdo->prepare("SELECT
            c.id,
            c.store,
            c.title,
            c.category,
            c.offer_type,
            c.partner_network,
            c.status,
            COUNT(cc.id) AS total_clicks,
            SUM(CASE WHEN cc.event_type = 'cta' THEN 1 ELSE 0 END) AS cta_clicks,
            SUM(CASE WHEN cc.event_type = 'details' THEN 1 ELSE 0 END) AS detail_clicks,
            SUM(CASE WHEN cc.event_type = 'mini' THEN 1 ELSE 0 END) AS mini_clicks,
            COUNT(DISTINCT cc.ip_hash) AS unique_users,
            MAX(cc.created_at) AS last_click_at
        FROM coupons c
        LEFT JOIN coupon_clicks cc
          ON cc.coupon_id = c.id
         AND cc.created_at >= :start_date
         AND cc.created_at < DATE_ADD(:end_date, INTERVAL 1 DAY)
        GROUP BY c.id, c.store, c.title, c.category, c.offer_type, c.partner_network, c.status
        ORDER BY total_clicks DESC, c.created_at DESC");
    $statement->execute([
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    return $statement->fetchAll();
}

function coupon_banner_src(array $coupon): string
{
    $url = trim((string) ($coupon['banner_url'] ?? ''));
    $id = (int) ($coupon['id'] ?? 0);

    if ($id <= 0 || !is_remote_banner_url($url) || !db()) {
        return $url;
    }

    return 'banner.php?id=' . $id;
}

function expiring_soon_coupons(array $coupons, int $days = 3, int $limit = 5): array
{
    $filtered = array_values(array_filter($coupons, function (array $coupon) use ($days): bool {
        $remaining = days_until($coupon['ends_at']);
        return $remaining >= 0 && $remaining <= $days;
    }));

    return array_slice($filtered, 0, $limit);
}

function is_remote_banner_url(string $url): bool
{
    return (bool) preg_match('/^https?:\/\//i', $url);
}

function days_until(string $date): int
{
    $today = new DateTimeImmutable('today');
    $end = new DateTimeImmutable($date);
    return (int) $today->diff($end)->format('%r%a');
}

function validity_label(string $date): string
{
    $days = days_until($date);
    if ($days < 0) {
        return 'Encerrado';
    }
    if ($days === 0) {
        return 'Vence hoje';
    }
    if ($days === 1) {
        return 'Vence amanhã';
    }
    return "Vence em {$days} dias";
}
