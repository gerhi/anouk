<?php defined('ANOUK_ROOT') or exit; ?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= h(Survey::t($lang, 'title')) ?></title>
<link rel="stylesheet" href="<?= h(base_path()) ?>/assets/style.css?v=<?= h(ANOUK_VERSION) ?>">
<link rel="icon" href="<?= h(base_path()) ?>/assets/favicon.svg" type="image/svg+xml">
<script src="<?= h(base_path()) ?>/assets/survey.js?v=<?= h(ANOUK_VERSION) ?>" defer></script>
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
