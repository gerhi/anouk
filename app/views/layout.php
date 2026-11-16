<?php defined('ANOUK_ROOT') or exit; ?>
<?php
$me = Auth::user();
$active ??= '';
$actions ??= '';
$groups = [
    t('Studies') => [
        'studies'     => [url('studies'), t('Studies')],
        'study.new'   => [url('study.new'), t('New study')],
        'respondents' => [url('respondents'), t('Respondents')],
    ],
];
if ($me && $me['role'] === 'admin') {
    $groups[t('Administration')] = [
        'users'  => [url('users'), t('Accounts')],
        'system' => [url('system'), t('System')],
    ];
}
$groups[t('Help')] = [
    'help-en' => [url('help', ['lang' => 'en']), 'English'],
    'help-de' => [url('help', ['lang' => 'de']), 'Deutsch'],
];
?><!doctype html>
<html lang="<?= h(i18n_lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= h(($pageTitle ?? '') !== '' ? $pageTitle . ' – ANOUK' : 'ANOUK') ?></title>
<link rel="stylesheet" href="<?= h(base_path()) ?>/assets/style.css?v=<?= h(ANOUK_VERSION) ?>">
<link rel="icon" href="<?= h(base_path()) ?>/assets/favicon.svg" type="image/svg+xml">
<script src="<?= h(base_path()) ?>/assets/admin.js?v=<?= h(ANOUK_VERSION) ?>" defer></script>
</head>
<body class="admin">
<aside class="sidebar">
  <a class="brand" href="<?= h(url('overview')) ?>"<?= $active === 'overview' ? ' aria-current="page"' : '' ?>><span class="bubble" aria-hidden="true">≻</span>ANOUK</a>
  <?php if ($me): ?>
  <?php foreach ($groups as $label => $links): ?>
    <nav class="nav-group" aria-label="<?= h($label) ?>">
      <span class="group-label"><?= h($label) ?></span>
      <?php foreach ($links as $key => [$href, $text]): ?>
        <a href="<?= h($href) ?>"<?= $key === $active ? ' aria-current="page"' : '' ?>><?= h($text) ?></a>
      <?php endforeach; ?>
    </nav>
  <?php endforeach; ?>
  <div class="bottom">
    <div class="who">
      <strong><?= h($me['name']) ?></strong>
      <span class="muted small"><?= h(role_label($me['role'])) ?></span>
    </div>
    <details class="langs">
      <summary><?= te('Language') ?>: <?= h(ANOUK_LANGS[i18n_lang()]) ?></summary>
      <ul>
        <?php foreach (ANOUK_LANGS as $code => $name): ?>
          <li><a href="<?= h(url('lang', ['set' => $code, 'back' => $_SERVER['REQUEST_URI'] ?? ''])) ?>" lang="<?= h($code) ?>"<?= $code === i18n_lang() ? ' aria-current="true"' : '' ?>><?= h($name) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </details>
    <nav class="nav-group" aria-label="<?= te('Account') ?>">
      <a href="<?= h(url('account')) ?>"<?= $active === 'account' ? ' aria-current="page"' : '' ?>><?= te('Change password') ?></a>
    </nav>
    <form method="post" action="<?= h(url('logout')) ?>" class="logout">
      <?= csrf_field() ?><button type="submit" class="linklike"><?= te('Log out') ?></button>
    </form>
  </div>
  <?php endif; ?>
</aside>
<div class="main"><div class="content">
<div class="page-head"><h1><?= h($pageTitle ?? 'ANOUK') ?></h1><?= $actions ?></div>
<?php foreach (take_flashes() as $f): ?>
  <div class="alert alert-<?= h($f['type']) ?>" role="status"><?= nl2br(h($f['message'])) ?></div>
<?php endforeach; ?>
<?= $content ?>
<p class="foot">ANOUK <?= h(ANOUK_VERSION) ?> – <?= te('Choice-based conjoint studies with LimeSurvey') ?> · MIT</p>
</div></div>
</body>
</html>
