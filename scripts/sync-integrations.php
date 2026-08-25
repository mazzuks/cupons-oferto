<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/integrations.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Use pela cron do servidor.');
}

$started = date('Y-m-d H:i:s');
$results = sync_all_integrations();

echo "Sincronizacao Oferto iniciada em {$started}\n";
foreach ($results as $result) {
    if (!empty($result['error'])) {
        echo $result['partner'] . ': erro - ' . $result['error'] . "\n";
        continue;
    }

    echo $result['partner']
        . ': lidas=' . (int) $result['read']
        . ' atualizadas=' . (int) $result['updated']
        . ' sumiram=' . (int) $result['missing']
        . "\n";
}
echo "Fim em " . date('Y-m-d H:i:s') . "\n";
