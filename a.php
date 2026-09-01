<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/affiliation.php';

$campaignId = isset($_GET['cid']) ? (int) $_GET['cid'] : 0;
$partnerId = isset($_GET['aff']) ? (int) $_GET['aff'] : 0;

$campaign = affiliation_campaign_by_id($campaignId);
if (!$campaign) {
    http_response_code(404);
    exit('Campanha afiliada não encontrada.');
}

if (in_array((string) ($campaign['status'] ?? ''), ['pausada', 'encerrada'], true)) {
    http_response_code(410);
    exit('Campanha afiliada indisponível.');
}

$partner = $partnerId > 0 ? affiliation_partner_by_id($partnerId) : null;
if ($partnerId > 0 && (!$partner || (string) ($partner['status'] ?? '') !== 'ativo')) {
    http_response_code(403);
    exit('Parceiro afiliado indisponível.');
}

$tid = affiliation_log_click($campaign, $partner);
if ($tid === '') {
    http_response_code(500);
    exit('Não foi possível registrar o clique.');
}

$url = affiliation_destination_url($campaign, $tid);
if ($url === '') {
    http_response_code(400);
    exit('URL da campanha inválida.');
}

$cookieTtl = max(1, (int) ($campaign['cookie_ttl_days'] ?? 180));
setcookie('oferto_aff_tid', $tid, [
    'expires' => time() + ($cookieTtl * 86400),
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);

header('Location: ' . $url, true, 302);
exit;
