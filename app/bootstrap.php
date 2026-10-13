<?php
declare(strict_types=1);

const APP_ROOT = __DIR__ . '/..';

// Everything in app/ and storage/ is outside the PUBLIC document root.
$localConfig = APP_ROOT . '/config.local.php';
$settings = is_file($localConfig) ? require $localConfig : [];
if (!is_array($settings)) {
    throw new RuntimeException('Invalid local configuration.');
}
$GLOBALS['app_config'] = array_replace([
    'BRAND_NAME' => 'Forma Advisory', // Concept identity; replace only with client approval.
    'SITE_MODE' => 'preview',          // Change to 'production' only after approvals.
    'SITE_URL' => '',                  // https://example.com (no ending slash)
    'MAIL_MODE' => 'disabled',         // disabled | log (local only) | mail | resend
    'CONTACT_TO_EMAIL' => '',
    'CONTACT_FROM_EMAIL' => '',
    'RESEND_API_KEY' => '',
    'RATE_LIMIT_MAX' => 3,
    'RATE_LIMIT_WINDOW' => 900,
], $settings);

function config(string $key): mixed
{
    $v = getenv($key);
    return $v !== false && $v !== '' ? $v : ($GLOBALS['app_config'][$key] ?? null);
}
function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function site_url(string $path): string
{
    $base = rtrim((string) config('SITE_URL'), '/');
    return $base ? $base . $path : '';
}
function headers_security(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; script-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; connect-src 'self'; form-action 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'none'");
}
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('forma_session');
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'secure' => $https,
        'httponly' => true, 'samesite' => 'Lax'
    ]);
    session_start();
}
function csrf_token(): string
{
    start_secure_session();
    if (empty($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf'];
}
function check_csrf(?string $token): bool
{
    start_secure_session();
    return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}
function arrow_icon(): string
{
    return '<svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4.5 12H19m-6-6 6 6-6 6"/></svg>';
}
function image_for_service(string $slug): string
{
    return in_array($slug, ['business-advisory','cfo-support'], true) ? 'meeting-detail.webp' : 'working-session.webp';
}
function render(string $template, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    require APP_ROOT . '/app/layout.php';
}
function json_reply(int $status, array $data): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
