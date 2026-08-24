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
        'texto' => 'Mostra texto/codigo para copiar',
        'redirect' => 'Abre site/cadastro sem mostrar codigo',
    ];
}

function redemption_type_label(?string $type): string
{
    $types = redemption_types();
    return $types[$type ?: 'texto'] ?? $types['texto'];
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
    return $tracking !== '' ? $tracking : trim((string) ($coupon['target_url'] ?? ''));
}

function coupon_has_code(array $coupon): bool
{
    return trim((string) ($coupon['code'] ?? '')) !== '';
}

function coupon_uses_text_redemption(array $coupon): bool
{
    return ($coupon['redemption_type'] ?? 'texto') === 'texto' && coupon_has_code($coupon);
}

function coupon_shows_public_code(array $coupon): bool
{
    if (!coupon_uses_text_redemption($coupon)) {
        return false;
    }

    $cta = strtolower(trim((string) ($coupon['cta_label'] ?? '')));
    $externalTerms = ['oferta', 'cadastro', 'cadastrar', 'cadastre', 'participar', 'ver ', 'acessar', 'site'];
    foreach ($externalTerms as $term) {
        if ($cta !== '' && strpos($cta, $term) !== false) {
            return false;
        }
    }

    return true;
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
        return fallback_coupons();
    }

    $sql = "SELECT * FROM coupons
            WHERE status = 'ativo'
              AND starts_at <= CURDATE()
              AND ends_at >= CURDATE()
            ORDER BY featured DESC, priority DESC, ends_at ASC, store ASC";

    return $pdo->query($sql)->fetchAll();
}

function all_coupons(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    return $pdo->query('SELECT * FROM coupons ORDER BY created_at DESC, id DESC')->fetchAll();
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

    return $coupon ?: null;
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

function delete_coupon(int $id): void
{
    $pdo = db();
    if (!$pdo) {
        throw new RuntimeException('Banco de dados indisponivel.');
    }

    $statement = $pdo->prepare('DELETE FROM coupons WHERE id = ?');
    $statement->execute([$id]);
}

function log_coupon_click(int $couponId, string $eventType): void
{
    $pdo = db();
    if (!$pdo) {
        return;
    }

    $eventType = preg_replace('/[^a-z0-9_\-]/i', '', $eventType) ?: 'cta';
    $referer = substr((string) ($_SERVER['HTTP_REFERER'] ?? ''), 0, 500);
    $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $ipHash = $ip !== '' ? hash('sha256', $ip . '|' . session_id()) : null;

    try {
        $statement = $pdo->prepare('INSERT INTO coupon_clicks (coupon_id, event_type, referer, user_agent, ip_hash) VALUES (?, ?, ?, ?, ?)');
        $statement->execute([$couponId, $eventType, $referer, $userAgent, $ipHash]);
    } catch (Throwable $error) {
        return;
    }
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
