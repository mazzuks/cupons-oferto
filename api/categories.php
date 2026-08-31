<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$coupons = api_active_coupons();
$categories = [];

foreach ($coupons as $coupon) {
    $name = (string) ($coupon['category'] ?? 'Outros');
    $slug = api_slug($name);

    if (!isset($categories[$slug])) {
        $categories[$slug] = [
            'id' => $slug,
            'name' => $name,
            'slug' => $slug,
            'offer_count' => 0,
            'featured_count' => 0,
            'expires_today_count' => 0,
            'expires_soon_count' => 0,
            'url' => OFERTO_API_BASE_URL . '?categoria=' . rawurlencode($name) . '#cupons',
            'api_url' => OFERTO_API_BASE_URL . 'api/offers.php?category=' . rawurlencode($slug),
        ];
    }

    $categories[$slug]['offer_count']++;

    if (!empty($coupon['featured'])) {
        $categories[$slug]['featured_count']++;
    }

    $daysUntilEnd = days_until((string) ($coupon['ends_at'] ?? 'today'));
    if ($daysUntilEnd === 0) {
        $categories[$slug]['expires_today_count']++;
    }
    if ($daysUntilEnd >= 0 && $daysUntilEnd <= 3) {
        $categories[$slug]['expires_soon_count']++;
    }
}

usort($categories, function (array $a, array $b): int {
    return $b['offer_count'] <=> $a['offer_count'] ?: strcmp($a['name'], $b['name']);
});

api_json_response([
    'ok' => true,
    'generated_at' => api_now(),
    'source' => 'mysql_live',
    'refresh' => 'dynamic_from_database',
    'total_categories' => count($categories),
    'total_active_offers' => count($coupons),
    'categories' => array_values($categories),
]);
