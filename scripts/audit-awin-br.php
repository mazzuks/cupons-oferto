<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/integrations.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$pause = in_array('--pause', $argv, true);
$maxPages = 10;
foreach ($argv as $argument) {
    if (preg_match('/^--pages=(\d+)$/', $argument, $matches)) {
        $maxPages = max(1, min(20, (int) $matches[1]));
    }
}

$filters = awin_normalize_filters(array_merge(integration_profile('awin', []), [
    'region' => 'BR',
    'page_size' => 200,
    'status' => 'active',
]));

$activeIds = [];
try {
    for ($page = 1; $page <= $maxPages; $page++) {
        $pageFilters = array_merge($filters, ['page' => $page]);
        $offers = awin_promotions($pageFilters);
        if (!$offers) {
            break;
        }

        foreach ($offers as $offer) {
            if (!is_array($offer) || empty($offer['promotionId'])) {
                continue;
            }
            if (!awin_offer_passes_filters($offer, $pageFilters)) {
                continue;
            }

            $activeIds['awin:' . (string) $offer['promotionId']] = true;
        }

        if (count($offers) < $filters['page_size']) {
            break;
        }
    }
} catch (Throwable $error) {
    fwrite(STDERR, 'Nao foi possivel consultar a Awin: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}

$pdo = db();
if (!$pdo) {
    fwrite(STDERR, "Banco de dados indisponivel.\n");
    exit(1);
}

$statement = $pdo->query("SELECT id, store, title, external_id, status
    FROM coupons
    WHERE partner_network = 'Awin'
      AND external_id IS NOT NULL
      AND external_id <> ''
    ORDER BY store ASC, title ASC");
$coupons = $statement->fetchAll();
$outside = array_values(array_filter($coupons, fn (array $coupon): bool => empty($activeIds[(string) $coupon['external_id']])));

echo 'Awin BR ativos no feed: ' . count($activeIds) . PHP_EOL;
echo 'Paginas consultadas no maximo: ' . $maxPages . PHP_EOL;
echo 'Cupons Awin no banco: ' . count($coupons) . PHP_EOL;
echo 'Possivelmente fora do feed BR ativo: ' . count($outside) . PHP_EOL;

foreach ($outside as $coupon) {
    echo '- #' . (int) $coupon['id'] . ' [' . (string) $coupon['status'] . '] ' . (string) $coupon['store'] . ' - ' . (string) $coupon['title'] . PHP_EOL;
}

if ($pause && $outside) {
    $update = $pdo->prepare("UPDATE coupons SET status = 'pausado', updated_at = NOW() WHERE id = ?");
    foreach ($outside as $coupon) {
        $update->execute([(int) $coupon['id']]);
    }

    create_system_log('awin_br_audit_pause', 'Auditoria Awin BR', count($outside) . ' cupons Awin fora do feed BR ativo foram pausados.', 'Awin');
    echo 'Pausados: ' . count($outside) . PHP_EOL;
} elseif ($outside) {
    echo "Rode com --pause para pausar estes cupons.\n";
}
