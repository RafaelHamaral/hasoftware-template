<?php
/**
 * Teste local do formulário de contato. Não dispara e-mail real.
 * Uso: php bin/test-contato-http.php
 */

$base = 'http://[::1]:5000';
$cookieFile = sys_get_temp_dir() . '/hasoftware-template-contato-http.cookie';
@unlink($cookieFile);

function http_request(string $url, string $cookieFile, string $method = 'GET', array $fields = []): array
{
    $ch = curl_init($url);
    $headers = [];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_HEADERFUNCTION => static function ($ch, string $header) use (&$headers): int {
            $headers[] = $header;
            return strlen($header);
        },
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    }
    $body = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false) {
        fwrite(STDERR, "HTTP error: {$err}\n");
        exit(1);
    }
    return ['code' => $code, 'body' => $body, 'headers' => implode('', $headers)];
}

function csrf_from(string $html): string
{
    if (!preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $html, $m)) {
        fwrite(STDERR, "CSRF ausente\n");
        exit(1);
    }
    return $m[1];
}

$home = http_request($base . '/', $cookieFile);
if ($home['code'] !== 200) {
    fwrite(STDERR, "GET / esperado 200, veio {$home['code']}\n");
    exit(1);
}
$token = csrf_from($home['body']);

$invalid = http_request($base . '/contato/enviar', $cookieFile, 'POST', [
    'csrf_token' => $token,
    'name' => 'Visitante',
    'email' => 'nao-e-email',
    'message' => 'teste validacao',
]);
if ($invalid['code'] !== 302) {
    fwrite(STDERR, "POST inválido esperado 302, veio {$invalid['code']}\n");
    exit(1);
}
$afterInvalid = http_request($base . '/', $cookieFile);
if (!str_contains($afterInvalid['body'], 'Informe um e-mail válido')) {
    fwrite(STDERR, "Flash de e-mail inválido não apareceu\n");
    exit(1);
}
echo "OK validacao_visitante\n";

$token2 = csrf_from($afterInvalid['body']);
$valid = http_request($base . '/contato/enviar', $cookieFile, 'POST', [
    'csrf_token' => $token2,
    'name' => 'Visitante Log',
    'email' => 'visitante-log@example.com',
    'message' => 'Envio valido em desenvolvimento (MAIL_TRANSPORT=log).',
]);
if ($valid['code'] !== 302) {
    fwrite(STDERR, "POST válido esperado 302, veio {$valid['code']}\n");
    exit(1);
}
$afterValid = http_request($base . '/', $cookieFile);
if (!str_contains($afterValid['body'], 'Mensagem enviada com sucesso')) {
    fwrite(STDERR, "Flash de sucesso não apareceu\n");
    exit(1);
}
echo "OK envio_valido_http\n";
echo "OK\n";
