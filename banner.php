<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/coupons.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$coupon = $id > 0 ? coupon_by_id($id) : null;

if (!$coupon) {
    http_response_code(404);
    exit('Banner nao encontrado.');
}

$url = trim((string) $coupon['banner_url']);
if (!is_remote_banner_url($url) || !is_proxyable_banner_url($url)) {
    http_response_code(400);
    exit('Banner invalido.');
}

$cacheDir = __DIR__ . '/uploads/banner-cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

$cacheBase = $cacheDir . '/coupon-' . $id . '-' . sha1($url);
$cached = cached_banner_file($cacheBase);

if (!$cached) {
    $download = download_banner($url);
    if (!$download) {
        http_response_code(502);
        exit('Nao foi possivel carregar o banner.');
    }

    [$body, $contentType] = $download;
    $extension = banner_extension($contentType);
    if (!$extension) {
        http_response_code(415);
        exit('Formato de banner indisponivel.');
    }

    $cached = $cacheBase . '.' . $extension;
    file_put_contents($cached, $body, LOCK_EX);
}

$contentType = banner_content_type($cached);
header('Content-Type: ' . $contentType);
header('Cache-Control: public, max-age=86400');
header('X-Content-Type-Options: nosniff');
readfile($cached);

function is_proxyable_banner_url(string $url): bool
{
    $parts = parse_url($url);
    $host = strtolower((string) ($parts['host'] ?? ''));
    $scheme = strtolower((string) ($parts['scheme'] ?? ''));

    if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
        return false;
    }

    if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
        return false;
    }

    $ip = gethostbyname($host);
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }

    return true;
}

function cached_banner_file(string $cacheBase): ?string
{
    foreach (['jpg', 'png', 'webp', 'gif'] as $extension) {
        $path = $cacheBase . '.' . $extension;
        if (is_file($path)) {
            return $path;
        }
    }

    return null;
}

function download_banner(string $url): ?array
{
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_USERAGENT => 'OfertoCupons/1.0',
            CURLOPT_HEADER => true,
        ]);
        $response = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $headerSize = (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $contentType = (string) curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        curl_close($curl);

        if (!is_string($response) || $status < 200 || $status >= 300) {
            return null;
        }

        return [substr($response, $headerSize), strtolower(strtok($contentType, ';') ?: '')];
    }

    $context = stream_context_create([
        'http' => [
            'timeout' => 20,
            'follow_location' => 1,
            'max_redirects' => 3,
            'user_agent' => 'OfertoCupons/1.0',
        ],
    ]);
    $body = @file_get_contents($url, false, $context);
    if (!is_string($body)) {
        return null;
    }

    $contentType = '';
    foreach ($http_response_header ?? [] as $header) {
        if (stripos($header, 'Content-Type:') === 0) {
            $contentType = strtolower(trim(substr($header, 13)));
            $contentType = strtok($contentType, ';') ?: '';
            break;
        }
    }

    return [$body, $contentType];
}

function banner_extension(string $contentType): ?string
{
    return match ($contentType) {
        'image/jpeg', 'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        default => null,
    };
}

function banner_content_type(string $path): string
{
    return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        default => 'application/octet-stream',
    };
}
