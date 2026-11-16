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
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    return rtrim($dir, '/.');
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

function url(string $route, array $params = []): string
{
    return base_path() . '/index.php?' . http_build_query(['r' => $route] + $params);
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

/** Renders app/views/<view>.php inside a layout. */
function render(string $view, array $vars = [], string $layout = 'layout'): void
{
    extract($vars, EXTR_SKIP);
    ob_start();
    require ANOUK_ROOT . '/app/views/' . $view . '.php';
    $content = ob_get_clean();
    require ANOUK_ROOT . '/app/views/' . $layout . '.php';
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
    foreach (ANOUK_LANGS as $code => $name) {
        $href = url('lang', ['set' => $code, 'back' => $back]);
        $out .= '<a href="' . h($href) . '" lang="' . h($code) . '"' . ($code === i18n_lang() ? ' aria-current="true"' : '') . '>' . h($name) . '</a>';
    }
    return $out . '</nav>';
}
