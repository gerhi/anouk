<?php
declare(strict_types=1);

function h(mixed $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Current time in UTC, as stored in the database. */
function now(int $offsetSeconds = 0): string
{
    return gmdate('Y-m-d H:i:s', time() + $offsetSeconds);
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

/** Path of the ANOUK folder on the web server, '' for the document root. */
function base_path(): string
{
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/.');
    if (defined('ANOUK_ADMIN') && str_ends_with($dir, '/admin')) {
        $dir = substr($dir, 0, -6); // admin/index.php lives one level below the ANOUK folder
    }
    return $dir;
}

/** URL of a file in assets/, with a version suffix against stale caches. */
function asset(string $path): string
{
    return base_path() . '/assets/' . $path . '?v=' . ANOUK_VERSION;
}

/** Absolute base URL, e.g. https://example.org/anouk */
function base_url(): string
{
    $configured = anouk_config()['base_url'] ?? '';
    if ($configured !== '') {
        return rtrim($configured, '/');
    }
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return (is_https() ? 'https' : 'http') . '://' . $host . base_path();
}

/** Address of the admin front controller, for forms that send the route as a hidden field. */
function admin_path(): string
{
    return base_path() . '/admin/index.php';
}

/** Admin page, e.g. url('study', ['id' => 3]) → /anouk/admin/index.php?r=study&id=3 */
function url(string $route, array $params = []): string
{
    return admin_path() . '?' . http_build_query(['r' => $route] + $params);
}

function survey_url(string $slug, array $params = [], bool $absolute = false): string
{
    $prefix = $absolute ? base_url() : base_path();
    return $prefix . '/index.php?' . http_build_query(['s' => $slug] + $params);
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 303);
    exit;
}

function abort(int $code, string $message): never
{
    http_response_code($code);
    $title = match ($code) {
        403 => t('No access'),
        404 => t('Not found'),
        default => t('Error %d', $code),
    };
    render('message', ['title' => $title, 'message' => $message]);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function post(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

/** $type: success | error | warning */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        abort(400, t('The form has expired. Go back, reload the page and try again.'));
    }
}

/** Renders src/views/<view>.php inside a layout. */
function render(string $view, array $vars = [], string $layout = 'layout'): void
{
    extract($vars, EXTR_SKIP);
    ob_start();
    require ANOUK_ROOT . '/src/views/' . $view . '.php';
    $content = ob_get_clean();
    require ANOUK_ROOT . '/src/views/' . $layout . '.php';
}

function random_password(int $length = 14): string
{
    $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $out;
}

function json_dec(?string $json, mixed $default = []): mixed
{
    if ($json === null || $json === '') {
        return $default;
    }
    $v = json_decode($json, true);
    return $v ?? $default;
}

function json_enc(mixed $value): string
{
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
}

/** Letter for alternative number: 1 → A, 2 → B … */
function alt_letter(int $n): string
{
    return $n <= 26 ? chr(64 + $n) : (string)$n;
}

function fmt_dt(?string $utc): string
{
    if (!$utc) {
        return '–';
    }
    $d = new DateTime($utc, new DateTimeZone('UTC'));
    $d->setTimezone(new DateTimeZone(anouk_config()['timezone'] ?? 'Europe/Berlin'));
    return $d->format(i18n_lang() === 'de' ? 'd.m.Y H:i' : 'Y-m-d H:i');
}

/** Number with the decimal separator of the interface language. */
function fmt_num(float $x, int $dec = 1): string
{
    return i18n_lang() === 'de' ? number_format($x, $dec, ',', '.') : number_format($x, $dec, '.', ',');
}

function status_label(string $status): string
{
    return match ($status) {
        'draft'  => t('Draft'),
        'field'  => t('In the field'),
        'closed' => t('Closed'),
        default  => $status,
    };
}

function role_label(string $role): string
{
    return $role === 'admin' ? t('Administrator') : t('Project leader');
}

/** Links to switch the interface language; returns to the current page. */
function language_links(string $class = 'lang-row'): string
{
    $back = (string)($_SERVER['REQUEST_URI'] ?? url('overview'));
    $out = '<nav class="' . h($class) . '" aria-label="' . te('Language') . '">';
    foreach (anouk_langs() as $code => $name) {
        $href = url('lang', ['set' => $code, 'back' => $back]);
        $out .= '<a href="' . h($href) . '" lang="' . h($code) . '"' . ($code === i18n_lang() ? ' aria-current="true"' : '') . '>' . h($name) . '</a>';
    }
    return $out . '</nav>';
}

/** Page shown when the database in config.php cannot be reached. */
function anouk_db_error(DbConnectionError $e): never
{
    error_log('ANOUK: ' . $e->getMessage());
    i18n_init();
    http_response_code(503);
    render('message', [
        'title'   => t('Database not reachable'),
        'message' => t('ANOUK cannot reach the database configured in config.php. Check the access data. To set ANOUK up again, delete config.php by FTP or reset it in install.php with the emergency code.')
            . (!empty(anouk_config()['debug']) ? ' (' . $e->getMessage() . ')' : ''),
    ], 'layout_plain');
    exit;
}

/** Last resort for unexpected errors: no details unless debug is on. */
function anouk_fatal(Throwable $e): never
{
    error_log('ANOUK: ' . $e);
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo '<!doctype html><meta charset="utf-8"><title>Error</title><p>An internal error occurred. / Ein interner Fehler ist aufgetreten.</p>';
    if (!empty(anouk_config()['debug'])) {
        echo '<pre>' . htmlspecialchars((string)$e) . '</pre>';
    }
    exit;
}
