<?php
/**
 * ANOUK – Auswahlbasierte Nutzenmessung: Online-Umsetzung & Kalkulation
 * Front controller.
 *   index.php?s=<slug>&id=<id>   respondent pages (from LimeSurvey)
 *   index.php?r=<route>          admin area
 */
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/Admin.php';

if (!anouk_config()) {
    header('Location: install.php');
    exit;
}

if (!empty(anouk_config()['force_https']) && !is_https() && isset($_SERVER['HTTP_HOST'])) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
    exit;
}

anouk_start_session();
anouk_security_headers();

try {
    Schema::ensure();
    if (isset($_GET['s'])) {
        Survey::handle();
    } else {
        Admin::dispatch(is_string($_GET['r'] ?? null) ? $_GET['r'] : 'overview');
    }
} catch (DbConnectionError $e) {
    error_log('ANOUK: ' . $e->getMessage());
    i18n_init();
    http_response_code(503);
    render('message', [
        'title'   => t('Database not reachable'),
        'message' => t('ANOUK cannot reach the database configured in config.php. Check the access data. To set ANOUK up again, delete config.php by FTP or reset it in install.php with the emergency code.')
            . (!empty(anouk_config()['debug']) ? ' (' . $e->getMessage() . ')' : ''),
    ], 'layout_plain');
} catch (Throwable $e) {
    error_log('ANOUK: ' . $e);
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><title>Error</title><p>An internal error occurred. / Ein interner Fehler ist aufgetreten.</p>';
    if (!empty(anouk_config()['debug'])) {
        echo '<pre>' . htmlspecialchars((string)$e) . '</pre>';
    }
}
