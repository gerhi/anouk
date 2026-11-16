<?php
// Copy to config.php if you do not use install.php.
return [
    'db' => [
        'dsn'  => 'mysql:host=localhost;port=3306;dbname=anouk;charset=utf8mb4',
        'user' => 'anouk',
        'pass' => 'secret',
    ],
    // Leave empty to detect automatically, or set e.g. 'https://example.org/anouk'
    'base_url' => '',
    // Time zone for dates shown in the admin area (data is stored in UTC)
    'timezone' => 'Europe/Berlin',
    // Shows error details in the browser. Never enable in production.
    'debug' => false,
];
