<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$path = $argv[1] ?? dirname(__DIR__) . '/data/ofertas_95_classificadas.csv';
$pdo = db();

if (!$pdo) {
    fwrite(STDERR, "Banco de dados indisponivel.\n");
    exit(1);
}

if (!is_file($path)) {
    fwrite(STDERR, "Arquivo nao encontrado: {$path}\n");
    exit(1);
}

$result = import_offer_classifications($pdo, $path);

echo 'Linhas lidas: ' . (int) ($result['rows'] ?? 0) . PHP_EOL;
echo 'Cupons atualizados: ' . (int) ($result['updated'] ?? 0) . PHP_EOL;
echo 'Lojas mapeadas: ' . (int) ($result['mapped_stores'] ?? 0) . PHP_EOL;
