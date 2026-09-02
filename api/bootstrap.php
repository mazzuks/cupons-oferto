<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/coupons.php';

const OFERTO_API_BASE_URL = 'https://cupons.oferto.digital/';

function api_json_response(array $payload, int $statusCode = 200): void
{
    if ($statusCode < 400) {
        api_log_request($payload);
    }

    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Cache-Control: public, max-age=300, stale-while-revalidate=600');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
        exit;
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

function api_log_request(array $payload): void
{
    $pdo = db();
    if (!$pdo) {
        return;
    }

    $endpoint = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'api'));
    $queryString = substr((string) ($_SERVER['QUERY_STRING'] ?? ''), 0, 700);
    $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $ipHash = $ip !== '' ? hash('sha256', $ip) : null;
    $total = null;

    if (isset($payload['total'])) {
        $total = (int) $payload['total'];
    } elseif (isset($payload['total_categories'])) {
        $total = (int) $payload['total_categories'];
    }

    try {
        $statement = $pdo->prepare("INSERT INTO api_request_logs
            (endpoint, query_string, total_results, ip_hash, user_agent)
            VALUES (?, ?, ?, ?, ?)");
        $statement->execute([
            $endpoint,
            $queryString !== '' ? $queryString : null,
            $total,
            $ipHash,
            $userAgent !== '' ? $userAgent : null,
        ]);
    } catch (Throwable $error) {
        // A API nunca deve falhar para o bot só porque o log não foi gravado.
    }
}

function api_now(): string
{
    return (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
}

function api_slug(string $value): string
{
    $value = trim($value);
    $value = strtr($value, [
        'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'Ç' => 'C', 'ç' => 'c',
    ]);
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    $ascii = $ascii === false ? $value : $ascii;
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($ascii)) ?: '';
    return trim($slug, '-');
}

function api_active_coupons(): array
{
    return array_values(array_filter(active_coupons(), function (array $coupon): bool {
        $status = (string) ($coupon['status'] ?? 'ativo');
        if ($status !== 'ativo') {
            return false;
        }

        try {
            $today = new DateTimeImmutable('today');
            $startsAt = new DateTimeImmutable((string) ($coupon['starts_at'] ?? 'today'));
            $endsAt = new DateTimeImmutable((string) ($coupon['ends_at'] ?? 'today'));
        } catch (Throwable $error) {
            return false;
        }

        return $startsAt <= $today && $endsAt >= $today;
    }));
}

function api_absolute_url(string $url): string
{
    $url = trim($url);

    if ($url === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $url)) {
        return $url;
    }

    return OFERTO_API_BASE_URL . ltrim($url, '/');
}

function api_int_param(string $name, int $default, int $min, int $max): int
{
    $value = filter_input(INPUT_GET, $name, FILTER_VALIDATE_INT);
    if ($value === false || $value === null) {
        return $default;
    }

    return max($min, min($max, $value));
}

function api_bool_param(string $name): ?bool
{
    if (!array_key_exists($name, $_GET)) {
        return null;
    }

    $value = strtolower(trim((string) $_GET[$name]));
    if (in_array($value, ['1', 'true', 'sim', 'yes'], true)) {
        return true;
    }
    if (in_array($value, ['0', 'false', 'nao', 'não', 'no'], true)) {
        return false;
    }

    return null;
}

function api_public_coupon(array $coupon): array
{
    $id = (int) ($coupon['id'] ?? 0);
    $offerUrl = api_absolute_url(coupon_offer_url($coupon, 'whatsapp-bot'));
    $rescueUrl = api_absolute_url(coupon_go_url($coupon, 'whatsapp-bot'));
    $code = coupon_shows_public_code($coupon) ? trim((string) ($coupon['code'] ?? '')) : '';
    $daysUntilEnd = days_until((string) ($coupon['ends_at'] ?? 'today'));

    return [
        'id' => $id,
        'store' => (string) ($coupon['store'] ?? ''),
        'category' => (string) ($coupon['category'] ?? 'Outros'),
        'category_slug' => api_slug((string) ($coupon['category'] ?? 'Outros')),
        'nicho_principal' => (string) ($coupon['nicho_principal'] ?? ''),
        'title' => (string) ($coupon['title'] ?? ''),
        'description' => (string) ($coupon['description'] ?? ''),
        'rules' => (string) ($coupon['rules'] ?? ''),
        'offer_type' => (string) ($coupon['offer_type'] ?? 'cupom'),
        'offer_type_label' => offer_type_label((string) ($coupon['offer_type'] ?? 'cupom')),
        'redemption_type' => (string) ($coupon['redemption_type'] ?? 'texto'),
        'redemption_type_label' => redemption_type_label((string) ($coupon['redemption_type'] ?? 'texto')),
        'cta_label' => coupon_cta_label($coupon),
        'has_public_code' => $code !== '',
        'code' => $code,
        'mechanic_label' => coupon_mechanic_label($coupon),
        'mechanic_value' => coupon_mechanic_value($coupon),
        'banner_url' => api_absolute_url(coupon_banner_src($coupon)),
        'logo_url' => api_absolute_url(coupon_logo_src($coupon)),
        'starts_at' => (string) ($coupon['starts_at'] ?? ''),
        'ends_at' => (string) ($coupon['ends_at'] ?? ''),
        'days_until_end' => $daysUntilEnd,
        'validity_label' => validity_label((string) ($coupon['ends_at'] ?? 'today')),
        'featured' => (bool) ($coupon['featured'] ?? false),
        'sponsored' => (bool) ($coupon['sponsored'] ?? false),
        'members_only' => (bool) ($coupon['members_only'] ?? false),
        'tags' => api_split_tags((string) ($coupon['tags'] ?? '')),
        'tags_produto' => api_split_tags((string) ($coupon['tags_produto'] ?? '')),
        'flags' => api_offer_flags($coupon),
        'offer_url' => $offerUrl,
        'rescue_url' => $rescueUrl,
        'share_text' => api_share_text($coupon, $offerUrl),
    ];
}

function api_offer_flags(array $coupon): array
{
    $flags = [
        'categoria:' . api_slug((string) ($coupon['category'] ?? 'Outros')),
        'tipo:' . api_slug((string) ($coupon['offer_type'] ?? 'cupom')),
        'resgate:' . api_slug((string) ($coupon['redemption_type'] ?? 'texto')),
    ];

    $niche = trim((string) ($coupon['nicho_principal'] ?? ''));
    if ($niche !== '') {
        $flags[] = 'nicho:' . $niche;
        $flags[] = 'nicho_slug:' . api_slug(str_replace('_', ' ', $niche));
    }

    foreach (api_split_tags((string) ($coupon['tags'] ?? '')) as $tag) {
        $flags[] = 'tag:' . api_slug($tag);
    }

    foreach (api_split_tags((string) ($coupon['tags_produto'] ?? '')) as $tag) {
        $flags[] = 'produto:' . api_slug($tag);
    }

    if (!empty($coupon['featured'])) {
        $flags[] = 'destaque';
    }
    if (!empty($coupon['sponsored'])) {
        $flags[] = 'patrocinado';
    }
    if (!empty($coupon['members_only'])) {
        $flags[] = 'membros';
    }
    if (coupon_shows_public_code($coupon)) {
        $flags[] = 'tem_codigo';
    }

    return array_values(array_unique(array_filter($flags)));
}

function api_split_tags(string $tags): array
{
    if (trim($tags) === '') {
        return [];
    }

    return array_values(array_filter(array_map('trim', explode(',', $tags))));
}

function api_share_text(array $coupon, string $offerUrl): string
{
    $store = trim((string) ($coupon['store'] ?? 'essa loja'));
    $title = trim((string) ($coupon['title'] ?? 'uma oferta'));

    return "Olha essa oferta da {$store}: {$title}. Veja os detalhes e copie o cupom aqui: {$offerUrl}";
}

function api_filter_coupons(array $coupons): array
{
    $category = trim((string) ($_GET['category'] ?? ''));
    $niche = trim((string) ($_GET['niche'] ?? $_GET['nicho'] ?? ''));
    $tag = trim((string) ($_GET['tag'] ?? ''));
    $flag = trim((string) ($_GET['flag'] ?? ''));
    $store = trim((string) ($_GET['store'] ?? ''));
    $query = trim((string) ($_GET['q'] ?? ''));
    $featured = api_bool_param('featured');

    $categorySlug = api_slug($category);
    $nicheText = function_exists('normalize_search_text') ? normalize_search_text($niche) : strtolower($niche);
    $tagText = function_exists('normalize_search_text') ? normalize_search_text($tag) : strtolower($tag);
    $flagText = function_exists('normalize_search_text') ? normalize_search_text($flag) : strtolower($flag);
    $queryText = function_exists('normalize_search_text') ? normalize_search_text($query) : strtolower($query);
    $storeText = function_exists('normalize_search_text') ? normalize_search_text($store) : strtolower($store);

    return array_values(array_filter($coupons, function (array $coupon) use ($category, $categorySlug, $nicheText, $tagText, $flagText, $storeText, $queryText, $featured): bool {
        if ($featured !== null && (bool) ($coupon['featured'] ?? false) !== $featured) {
            return false;
        }

        if ($category !== '') {
            $couponCategory = (string) ($coupon['category'] ?? '');
            if (api_slug($couponCategory) !== $categorySlug && strcasecmp($couponCategory, $category) !== 0) {
                return false;
            }
        }

        if ($storeText !== '') {
            $couponStore = function_exists('normalize_search_text')
                ? normalize_search_text((string) ($coupon['store'] ?? ''))
                : strtolower((string) ($coupon['store'] ?? ''));
            if (strpos($couponStore, $storeText) === false) {
                return false;
            }
        }

        if ($nicheText !== '') {
            $couponNiche = function_exists('normalize_search_text')
                ? normalize_search_text((string) ($coupon['nicho_principal'] ?? ''))
                : strtolower((string) ($coupon['nicho_principal'] ?? ''));
            if ($couponNiche !== $nicheText && strpos($couponNiche, $nicheText) === false) {
                return false;
            }
        }

        if ($tagText !== '') {
            $couponTags = implode(' ', [
                (string) ($coupon['tags_produto'] ?? ''),
                (string) ($coupon['tags'] ?? ''),
                implode(' ', api_offer_flags($coupon)),
            ]);
            $couponTags = function_exists('normalize_search_text') ? normalize_search_text($couponTags) : strtolower($couponTags);
            if (strpos($couponTags, $tagText) === false) {
                return false;
            }
        }

        if ($flagText !== '') {
            $couponFlags = function_exists('normalize_search_text')
                ? normalize_search_text(implode(' ', api_offer_flags($coupon)))
                : strtolower(implode(' ', api_offer_flags($coupon)));
            if (strpos($couponFlags, $flagText) === false) {
                return false;
            }
        }

        if ($queryText !== '') {
            $haystack = implode(' ', [
                (string) ($coupon['store'] ?? ''),
                (string) ($coupon['category'] ?? ''),
                (string) ($coupon['title'] ?? ''),
                (string) ($coupon['description'] ?? ''),
                (string) ($coupon['rules'] ?? ''),
                (string) ($coupon['tags'] ?? ''),
                (string) ($coupon['nicho_principal'] ?? ''),
                (string) ($coupon['tags_produto'] ?? ''),
                implode(' ', api_offer_flags($coupon)),
                (string) ($coupon['requirements'] ?? ''),
            ]);
            $haystack = function_exists('normalize_search_text') ? normalize_search_text($haystack) : strtolower($haystack);
            if (strpos($haystack, $queryText) === false) {
                return false;
            }
        }

        return true;
    }));
}
