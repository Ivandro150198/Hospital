<?php
require_once __DIR__ . '/auth.php';
admin_boot();

if (admin_is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_verify_csrf($_POST['csrf'] ?? null)) {
        $error = 'Sessão inválida. Tente novamente.';
    } elseif (!admin_login_rate_ok()) {
        $error = 'Demasiadas tentativas. Aguarde alguns minutos.';
    } else {
        $user = trim((string) ($_POST['username'] ?? ''));
        $pass = (string) ($_POST['password'] ?? '');
        if (admin_try_login($user, $pass)) {
            header('Location: index.php');
            exit;
        }
        $error = 'Utilizador ou password incorrectos.';
    }
}

$csrf = admin_csrf_token();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">
    <title>Admin · HMH Cumura</title>
    <link rel="icon" href="../favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="admin.css?v=20260921m">
</head>
<body class="admin-login">
    <main class="login-card">
        <p class="eyebrow">Painel institucional</p>
        <h1>HMH Cumura</h1>
        <p class="muted">Aceda para gerir SEO, serviços e notícias.</p>
        <?php if ($error): ?>
            <p class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <label for="username">Utilizador</label>
            <input type="text" id="username" name="username" required autofocus>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Entrar</button>
        </form>
        <p class="hint"><a href="../index.php">← Voltar ao site</a></p>
    </main>
</body>
</html>
