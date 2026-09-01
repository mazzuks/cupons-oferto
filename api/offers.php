<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$limit = api_int_param('limit', 50, 1, 100);
$coupons = api_filter_coupons(api_active_coupons());
$total = count($coupons);
$coupons = array_slice($coupons, 0, $limit);

api_json_response([
    'ok' => true,
    'generated_at' => api_now(),
    'source' => 'mysql_live',
    'refresh' => 'dynamic_from_database',
    'total' => $total,
    'limit' => $limit,
    'filters' => [
        'category' => trim((string) ($_GET['category'] ?? '')),
        'niche' => trim((string) ($_GET['niche'] ?? $_GET['nicho'] ?? '')),
        'tag' => trim((string) ($_GET['tag'] ?? '')),
        'store' => trim((string) ($_GET['store'] ?? '')),
        'q' => trim((string) ($_GET['q'] ?? '')),
        'featured' => array_key_exists('featured', $_GET) ? trim((string) $_GET['featured']) : null,
    ],
    'offers' => array_map('api_public_coupon', $coupons),
]);
