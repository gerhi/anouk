<?php
/**
 * ANOUK – Auswahlbasierte Nutzenmessung: Online-Umsetzung & Kalkulation
 * Entry point for respondents:  index.php?s=<slug>&id=<id>
 * The admin area lives in admin/; requests without a study are sent there.
 */
declare(strict_types=1);

require __DIR__ . '/src/bootstrap.php';

if (!anouk_config()) {
    header('Location: install.php');
    exit;
}

if (!isset($_GET['s'])) {
    // Admin addresses of version 1.0 (index.php?r=…) keep working.
    $query = isset($_GET['r']) ? '?' . http_build_query($_GET) : '';
    header('Location: ' . admin_path() . $query, true, 302);
    exit;
}

if (!empty(anouk_config()['force_https']) && !is_https() && isset($_SERVER['HTTP_HOST'])) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
    exit;
}

anouk_start_session();
anouk_security_headers();

try {
    Survey::handle();
} catch (DbConnectionError $e) {
    anouk_db_error($e);
} catch (Throwable $e) {
    anouk_fatal($e);
}
