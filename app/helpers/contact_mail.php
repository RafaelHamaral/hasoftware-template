<?php
/**
 * Notificação por e-mail das mensagens do formulário público.
 * Destino: dados_contato.email. Remetente: MAIL_FROM no .env (não versionado).
 */

/**
 * @param array{nome:string,email:string,mensagem:string} $payload
 * @param array<string, mixed> $contato
 * @param callable|null $mailFn function(string $to, string $subject, string $body, string $headers): bool
 * @return array{attempted:bool,sent:bool,reason:string}
 */
function contact_notification_send(array $payload, array $contato, ?callable $mailFn = null): array
{
    $destino = contact_mail_sanitize_address((string) ($contato['email'] ?? ''));
    if ($destino === '' || !filter_var($destino, FILTER_VALIDATE_EMAIL)) {
        error_log('Notificação de contato ignorada: destinatário ausente ou inválido em Dados de contato.');
        return ['attempted' => false, 'sent' => false, 'reason' => 'destinatario_invalido'];
    }

    $replyTo = contact_mail_sanitize_address((string) ($payload['email'] ?? ''));
    if ($replyTo === '' || !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        error_log('Notificação de contato ignorada: e-mail do visitante inválido para Reply-To.');
        return ['attempted' => false, 'sent' => false, 'reason' => 'reply_to_invalido'];
    }

    $from = contact_mail_sanitize_address((string) (getenv('MAIL_FROM') ?: ($_ENV['MAIL_FROM'] ?? '')));
    if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
        error_log(
            'Notificação de contato não enviada: defina MAIL_FROM no .env com um remetente autorizado '
            . 'pelo servidor (na InfinityFree, um e-mail criado no hPanel do domínio). '
            . 'A mensagem foi gravada. Destinatário configurado: ' . $destino
        );
        return ['attempted' => false, 'sent' => false, 'reason' => 'remetente_ausente'];
    }

    $fromName = contact_mail_sanitize_text((string) (getenv('MAIL_FROM_NAME') ?: ($_ENV['MAIL_FROM_NAME'] ?? 'H. A Consultoria')));
    if ($fromName === '') {
        $fromName = 'H. A Consultoria';
    }

    $nome = contact_mail_sanitize_text((string) ($payload['nome'] ?? 'Visitante'));
    $assunto = 'Nova mensagem do site — ' . $nome;
    if (function_exists('mb_encode_mimeheader')) {
        $assunto = mb_encode_mimeheader($assunto, 'UTF-8', 'B', "\r\n");
    }

    $corpo = "Nome: {$nome}\n"
        . "E-mail: {$replyTo}\n\n"
        . "Mensagem:\n" . (string) ($payload['mensagem'] ?? '') . "\n";

    $fromHeader = $fromName !== ''
        ? sprintf('"%s" <%s>', str_replace('"', '', $fromName), $from)
        : $from;

    $headers = implode("\r\n", [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $fromHeader,
        'Reply-To: ' . $replyTo,
        'X-Mailer: hasoftware-template-contato',
    ]);

    $transport = strtolower(trim((string) (getenv('MAIL_TRANSPORT') ?: ($_ENV['MAIL_TRANSPORT'] ?? 'mail'))));
    if ($transport === 'log') {
        error_log('MAIL_TRANSPORT=log; notificação não disparada. Para=' . $destino . ' From=' . $from . ' Reply-To=' . $replyTo);
        return ['attempted' => true, 'sent' => true, 'reason' => 'log'];
    }
    if ($transport === 'fail') {
        error_log('MAIL_TRANSPORT=fail; simulação de falha de envio. Destino=' . $destino);
        return ['attempted' => true, 'sent' => false, 'reason' => 'falha_simulada'];
    }

    $sender = $mailFn ?? static function (string $to, string $subject, string $body, string $headerList): bool {
        return @mail($to, $subject, $body, $headerList);
    };

    $enviado = (bool) $sender($destino, $assunto, $corpo, $headers);
    if (!$enviado) {
        error_log(
            'Falha ao enviar e-mail de notificação de contato. Destino=' . $destino
            . ' From=' . $from . ' (a mensagem já estava gravada).'
        );
        return ['attempted' => true, 'sent' => false, 'reason' => 'falha_envio'];
    }

    return ['attempted' => true, 'sent' => true, 'reason' => 'enviado'];
}

function contact_mail_sanitize_address(string $value): string
{
    $value = trim(str_replace(["\r", "\n", "\0"], '', $value));
    return $value;
}

function contact_mail_sanitize_text(string $value): string
{
    return trim(str_replace(["\r", "\n", "\0"], '', $value));
}
