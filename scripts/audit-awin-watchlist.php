<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/integrations.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$apply = in_array('--apply', $argv, true);
$filters = awin_normalize_filters(array_merge(integration_profile('awin', []), [
    'membership' => 'joined',
    'region' => 'BR',
    'page_size' => 200,
    'status' => 'active',
]));

$joinedIds = [];

try {
    for ($page = 1; $page <= 10; $page++) {
        $offers = awin_promotions(array_merge($filters, ['page' => $page]));
        if (!$offers) {
            break;
        }

        foreach ($offers as $offer) {
            if (!is_array($offer) || empty($offer['promotionId']) || !awin_offer_passes_filters($offer, $filters)) {
                continue;
            }

            $joinedIds['awin:' . (string) $offer['promotionId']] = true;
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

$statement = $pdo->query("SELECT id, external_id, store, title, status
    FROM integration_watchlist
    WHERE partner = 'Awin'
    ORDER BY store ASC, title ASC");
$rows = $statement->fetchAll();
$invalid = array_values(array_filter($rows, fn (array $row): bool => empty($joinedIds[(string) $row['external_id']])));

echo 'Ofertas Awin aprovadas/BR no feed: ' . count($joinedIds) . PHP_EOL;
echo 'Monitoramentos Awin no banco: ' . count($rows) . PHP_EOL;
echo 'Monitoramentos fora de aprovadas/BR: ' . count($invalid) . PHP_EOL;

foreach ($invalid as $row) {
    echo '- #' . (int) $row['id'] . ' [' . (string) $row['status'] . '] ' . (string) $row['store'] . ' - ' . (string) $row['title'] . PHP_EOL;
}

if ($apply && $invalid) {
    $delete = $pdo->prepare('DELETE FROM integration_watchlist WHERE id = ? AND partner = ?');
    foreach ($invalid as $row) {
        $delete->execute([(int) $row['id'], 'Awin']);
    }

    create_system_log('awin_watchlist_cleanup', 'Limpeza Awin', count($invalid) . ' monitoramento(s) Awin fora de aprovadas/BR foram removidos.', 'Awin');
    echo 'Removidos: ' . count($invalid) . PHP_EOL;
} elseif ($invalid) {
    echo "Rode com --apply para remover estes monitoramentos da Awin.\n";
}
