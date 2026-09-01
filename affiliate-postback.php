<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/affiliation.php';

$payload = $_GET;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $rawBody = (string) file_get_contents('php://input');
    $jsonPayload = json_decode($rawBody, true);
    if (is_array($jsonPayload)) {
        $payload = array_merge($_POST, $jsonPayload);
    } else {
        $payload = $_POST;
    }
}

try {
    $result = affiliation_register_conversion($payload);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => true,
        'result' => $result,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $exception) {
    http_response_code($exception->getMessage() === 'Assinatura inválida.' ? 403 : 400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => false,
        'error' => $exception->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
