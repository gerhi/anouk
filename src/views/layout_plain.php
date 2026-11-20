<?php defined('ANOUK_ROOT') or exit; ?>
<!doctype html>
<html lang="<?= h(i18n_lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= h(($pageTitle ?? '') !== '' ? $pageTitle . ' – ANOUK' : 'ANOUK') ?></title>
<link rel="stylesheet" href="<?= h(asset('css/style.css')) ?>">
<link rel="icon" href="<?= h(asset('img/favicon.svg')) ?>" type="image/svg+xml">
</head>
<body class="plain">
<main class="wrap narrow">
<?= $content ?>
<?= language_links() ?>
</main>
</body>
</html>
