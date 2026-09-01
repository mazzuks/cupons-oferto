<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/coupons.php';

const OFERTO_API_BASE_URL = 'https://cupons.oferto.digital/';

function api_json_response(array $payload, int $statusCode = 200): void
{
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
        'offer_url' => $offerUrl,
        'rescue_url' => $rescueUrl,
        'share_text' => api_share_text($coupon, $offerUrl),
    ];
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
    $store = trim((string) ($_GET['store'] ?? ''));
    $query = trim((string) ($_GET['q'] ?? ''));
    $featured = api_bool_param('featured');

    $categorySlug = api_slug($category);
    $queryText = function_exists('normalize_search_text') ? normalize_search_text($query) : strtolower($query);
    $storeText = function_exists('normalize_search_text') ? normalize_search_text($store) : strtolower($store);

    return array_values(array_filter($coupons, function (array $coupon) use ($category, $categorySlug, $storeText, $queryText, $featured): bool {
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
