<?php
header('Cache-Control: no-store');

$tokenFile = '/var/www/html/kuma-sso/.sso-token';
$credFile = '/var/www/html/kuma-sso/.panel-secret';
$given = $_GET['t'] ?? '';

$expected = is_readable($tokenFile) ? trim((string) file_get_contents($tokenFile)) : '';
$user = 'admin';
$pass = '';

if (is_readable($credFile)) {
    foreach (file($credFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with($line, 'senha: ')) {
            $pass = trim(substr($line, 7));
        }
        if (str_starts_with($line, 'usuario: ')) {
            $user = trim(substr($line, 9));
        }
    }
}

if (!$expected || !hash_equals($expected, $given) || $pass === '') {
    http_response_code(403);
    echo 'SSO negado';
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <title>Kuma SSO</title>
  <style>
    body { margin:0; background:#0b0f14; color:#c7d2df; font:14px/1.4 IBM Plex Sans, system-ui, sans-serif; display:grid; place-items:center; height:100vh; }
  </style>
  <script src="https://cdn.socket.io/4.8.1/socket.io.min.js"></script>
</head>
<body>
  <div>Entrando no Uptime Kuma…</div>
  <script>
    const username = <?php echo json_encode($user, JSON_UNESCAPED_UNICODE); ?>;
    const password = <?php echo json_encode($pass, JSON_UNESCAPED_UNICODE); ?>;

    const socket = io("/", { transports: ["websocket", "polling"] });
    const timer = setTimeout(() => { document.body.textContent = "Timeout no login do Kuma"; }, 15000);

    socket.on("connect", () => {
      socket.emit("login", { username, password, token: "" }, (res) => {
        clearTimeout(timer);
        if (!res || !res.ok) {
          document.body.textContent = "Falha no login Kuma: " + JSON.stringify(res);
          return;
        }
        if (res.token) {
          localStorage.setItem("token", res.token);
        }
        location.replace("/dashboard");
      });
    });

    socket.on("connect_error", (err) => {
      document.body.textContent = "Erro socket: " + err.message;
    });
  </script>
</body>
</html>
