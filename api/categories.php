<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$coupons = api_active_coupons();
$categories = [];
$niches = [];
$flags = [];

foreach ($coupons as $coupon) {
    $name = (string) ($coupon['category'] ?? 'Outros');
    $slug = api_slug($name);
    $niche = trim((string) ($coupon['nicho_principal'] ?? ''));
    $nicheSlug = $niche !== '' ? api_slug(str_replace('_', ' ', $niche)) : '';
    $productTags = api_split_tags((string) ($coupon['tags_produto'] ?? ''));
    $offerFlags = api_offer_flags($coupon);

    if (!isset($categories[$slug])) {
        $categories[$slug] = [
            'id' => $slug,
            'name' => $name,
            'slug' => $slug,
            'offer_count' => 0,
            'featured_count' => 0,
            'expires_today_count' => 0,
            'expires_soon_count' => 0,
            'niches' => [],
            'product_tags' => [],
            'flags' => [],
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

    if ($niche !== '') {
        if (!isset($categories[$slug]['niches'][$nicheSlug])) {
            $categories[$slug]['niches'][$nicheSlug] = [
                'id' => $nicheSlug,
                'name' => $niche,
                'offer_count' => 0,
                'api_url' => OFERTO_API_BASE_URL . 'api/offers.php?niche=' . rawurlencode($niche),
            ];
        }
        $categories[$slug]['niches'][$nicheSlug]['offer_count']++;

        if (!isset($niches[$nicheSlug])) {
            $niches[$nicheSlug] = [
                'id' => $nicheSlug,
                'name' => $niche,
                'offer_count' => 0,
                'categories' => [],
                'api_url' => OFERTO_API_BASE_URL . 'api/offers.php?niche=' . rawurlencode($niche),
            ];
        }
        $niches[$nicheSlug]['offer_count']++;
        $niches[$nicheSlug]['categories'][$slug] = $name;
    }

    foreach ($productTags as $tag) {
        $tagKey = api_slug($tag);
        if ($tagKey !== '') {
            $categories[$slug]['product_tags'][$tagKey] = $tag;
        }
    }

    foreach ($offerFlags as $flag) {
        $categories[$slug]['flags'][$flag] = $flag;
        $flags[$flag] = $flag;
    }
}

foreach ($categories as &$category) {
    $category['niches'] = array_values($category['niches']);
    usort($category['niches'], function (array $a, array $b): int {
        return $b['offer_count'] <=> $a['offer_count'] ?: strcmp($a['name'], $b['name']);
    });
    $category['product_tags'] = array_values($category['product_tags']);
    sort($category['product_tags']);
    $category['flags'] = array_values($category['flags']);
    sort($category['flags']);
}
unset($category);

usort($categories, function (array $a, array $b): int {
    return $b['offer_count'] <=> $a['offer_count'] ?: strcmp($a['name'], $b['name']);
});

foreach ($niches as &$niche) {
    $niche['categories'] = array_values($niche['categories']);
    sort($niche['categories']);
}
unset($niche);

usort($niches, function (array $a, array $b): int {
    return $b['offer_count'] <=> $a['offer_count'] ?: strcmp($a['name'], $b['name']);
});

$flags = array_values($flags);
sort($flags);

api_json_response([
    'ok' => true,
    'generated_at' => api_now(),
    'source' => 'mysql_live',
    'refresh' => 'dynamic_from_database',
    'total_categories' => count($categories),
    'total_niches' => count($niches),
    'total_active_offers' => count($coupons),
    'flags' => $flags,
    'categories' => array_values($categories),
    'niches' => array_values($niches),
]);
