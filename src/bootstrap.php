<?php
declare(strict_types=1);

const ANOUK_VERSION = '1.1.0';
define('ANOUK_ROOT', dirname(__DIR__));

require __DIR__ . '/Lang.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/Db.php';
require __DIR__ . '/Auth.php';
require __DIR__ . '/Study.php';
require __DIR__ . '/Design.php';
require __DIR__ . '/Survey.php';
require __DIR__ . '/Export.php';
require __DIR__ . '/RScript.php';
require __DIR__ . '/Installer.php';
require __DIR__ . '/Migrator.php';
require __DIR__ . '/Throttle.php';
require __DIR__ . '/Markdown.php';

/** Returns the configuration array, or [] if ANOUK is not installed yet. */
function anouk_config(): array
{
    static $config = null;
    if ($config === null) {
        $file = getenv('ANOUK_CONFIG') ?: ANOUK_ROOT . '/config.php';
        $config = is_file($file) ? (require $file) : [];
    }
    return $config;
}

function anouk_start_session(): void
{
    if (PHP_SAPI === 'cli' || session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('ANOUKSESS');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => (base_path() ?: '') . '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Security headers for every page. No inline scripts or styles are used, so the
 * Content-Security-Policy can forbid them. Pages are not cached (shared devices,
 * back button after answering).
 */
function anouk_security_headers(bool $allowFraming = false): void
{
    if (PHP_SAPI === 'cli' || headers_sent()) {
        return;
    }
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    header('Cache-Control: no-store, max-age=0');
    $frame = $allowFraming ? "'self'" : "'none'";
    header("Content-Security-Policy: default-src 'self'; frame-ancestors $frame; base-uri 'self'; form-action 'self' http: https:");
    if (!$allowFraming) {
        header('X-Frame-Options: DENY');
    }
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}

date_default_timezone_set('UTC');
if (PHP_SAPI !== 'cli') {
    // never show PHP errors to visitors unless debugging is switched on in config.php
    ini_set('display_errors', !empty(anouk_config()['debug']) ? '1' : '0');
}
