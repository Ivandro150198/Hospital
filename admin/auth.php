<?php

function admin_boot(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
        ]);
    }
}

function admin_auth_config(): array
{
    $path = dirname(__DIR__) . '/config/auth.php';
    if (!is_file($path)) {
        return ['username' => 'admin', 'password_hash' => ''];
    }
    $cfg = require $path;
    return is_array($cfg) ? $cfg : ['username' => 'admin', 'password_hash' => ''];
}

function admin_is_logged_in(): bool
{
    admin_boot();
    return !empty($_SESSION['hmh_admin']);
}

function admin_require_login(): void
{
    if (!admin_is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function admin_csrf_token(): string
{
    admin_boot();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function admin_verify_csrf(?string $token): bool
{
    admin_boot();
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function admin_login_rate_ok(): bool
{
    admin_boot();
    $now = time();
    $attempts = $_SESSION['login_attempts'] ?? [];
    $attempts = array_values(array_filter($attempts, static fn($t) => is_int($t) && ($now - $t) < 900));
    $_SESSION['login_attempts'] = $attempts;
    return count($attempts) < 8;
}

function admin_login_fail(): void
{
    admin_boot();
    $_SESSION['login_attempts'][] = time();
}

function admin_try_login(string $user, string $pass): bool
{
    if (!admin_login_rate_ok()) {
        return false;
    }

    $cfg = admin_auth_config();
    $okUser = hash_equals((string) ($cfg['username'] ?? ''), $user);
    $hash = (string) ($cfg['password_hash'] ?? '');
    $okPass = $hash !== '' && password_verify($pass, $hash);

    if ($okUser && $okPass) {
        session_regenerate_id(true);
        $_SESSION['hmh_admin'] = true;
        $_SESSION['login_attempts'] = [];
        return true;
    }

    admin_login_fail();
    return false;
}

function admin_logout(): void
{
    admin_boot();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
