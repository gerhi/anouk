<?php defined('ANOUK_ROOT') or exit; ?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= h(Survey::t($lang, 'title')) ?></title>
<link rel="stylesheet" href="<?= h(asset('css/style.css')) ?>">
<link rel="icon" href="<?= h(asset('img/favicon.svg')) ?>" type="image/svg+xml">
<script src="<?= h(asset('js/survey.js')) ?>" defer></script>
</head>
<body class="participant">
<?php if (!empty($preview)): ?>
  <div class="preview-bar"><?= h(Survey::t($lang, 'preview')) ?><?= isset($version) ? ' · Version ' . (int)$version : '' ?></div>
<?php endif; ?>
<main class="wrap<?= !empty($wide) ? ' wide' : '' ?>">
<?= $content ?>
</main>
</body>
</html>
