<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$tokenFile = '/var/www/html/monitor.agendaae.com/.kuma-sso-token';
$monitorsFile = '/var/www/html/monitor.agendaae.com/monitors.json';

$token = is_readable($tokenFile) ? trim((string) file_get_contents($tokenFile)) : '';
$monitors = [];

if (is_readable($monitorsFile)) {
    $decoded = json_decode((string) file_get_contents($monitorsFile), true);
    if (is_array($decoded)) {
        $monitors = $decoded;
    }
}

if ($token === '') {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'sso não configurado']);
    exit;
}

echo json_encode([
    'ok' => true,
    'ssoUrl' => 'https://kuma.agendaae.com/sso/?t=' . rawurlencode($token),
    'monitors' => $monitors,
]);
