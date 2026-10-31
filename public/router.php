<?php
/**
 * Roteador do servidor embutido: php -S localhost:8000 -t public public/router.php
 * Arquivos estáticos existentes são servidos; o restante vai ao front controller.
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = is_string($uri) && $uri !== '' ? $uri : '/';

$publicRoot = realpath(__DIR__);
$candidate  = realpath(__DIR__ . $uri);

if (
    $uri !== '/'
    && $publicRoot !== false
    && $candidate !== false
    && str_starts_with($candidate, $publicRoot)
    && is_file($candidate)
) {
    return false;
}

require __DIR__ . '/index.php';
