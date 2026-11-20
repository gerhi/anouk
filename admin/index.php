<?php
/**
 * ANOUK admin area. Front controller: admin/index.php?r=<route>
 */
declare(strict_types=1);

const ANOUK_ADMIN = true;

require dirname(__DIR__) . '/src/bootstrap.php';
require dirname(__DIR__) . '/src/Admin.php';

if (!anouk_config()) {
    header('Location: ../install.php');
    exit;
}

if (!empty(anouk_config()['force_https']) && !is_https() && isset($_SERVER['HTTP_HOST'])) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
    exit;
}

anouk_start_session();
anouk_security_headers();

try {
    Admin::dispatch(is_string($_GET['r'] ?? null) ? $_GET['r'] : 'overview');
} catch (DbConnectionError $e) {
    anouk_db_error($e);
} catch (Throwable $e) {
    anouk_fatal($e);
}
