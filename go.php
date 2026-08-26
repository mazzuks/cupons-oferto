<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/coupons.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$event = trim((string) ($_GET['event'] ?? 'cta'));
$coupon = $id > 0 ? coupon_by_id($id) : null;

if (!$coupon) {
    http_response_code(404);
    exit('Oferta nao encontrada.');
}

$url = coupon_destination_url($coupon);
if (!preg_match('/^https?:\/\//i', $url)) {
    http_response_code(400);
    exit('URL da oferta invalida.');
}

$clickRef = log_coupon_click($id, $event);
$url = coupon_url_with_click_ref($url, $clickRef, $coupon);

header('Location: ' . $url, true, 302);
exit;
