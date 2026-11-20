<?php
/**
 * ANOUK web installer: connection test, table prefix, tables, administrator, config.php,
 * lock file data/install.lock. Once installed, the installer only offers to delete itself
 * or – with the emergency code shown at installation – to delete config.php for a fresh setup.
 */
declare(strict_types=1);

require __DIR__ . '/src/bootstrap.php';

anouk_security_headers();

// Language: ?lang=xx or the hidden form field, else cookie or browser.
$lang = (string)($_POST['lang'] ?? $_GET['lang'] ?? '');
lang_exists($lang) ? i18n_set($lang) : i18n_init();

$action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
$errors = [];
$notice = null;
$test = null;
$done = false;
$manualConfig = null;
$resetCode = null;
$existingUsers = 0;
$lockWritten = true;

$f = fn(string $k, string $d = ''): string => trim(is_string($_POST[$k] ?? null) ? $_POST[$k] : $d);

// ------------------------------------------------------------------ installed: delete installer / reset config
if (is_file(Installer::configFile())) {
    if ($action === 'delete_installer') {
        if (Installer::deleteInstaller()) {
            header('Location: admin/index.php', true, 303);
            exit;
        }
        $errors[] = t('install.php could not be deleted. Delete it by FTP.');
    } elseif ($action === 'reset_config') {
        if (Installer::checkResetCode($f('reset_code'))) {
            if (Installer::deleteConfig()) {
                Installer::deleteLock();
                $notice = t('config.php has been deleted. ANOUK can now be set up again; the data in the database are kept.');
            } else {
                $errors[] = t('config.php could not be deleted. Delete it by FTP.');
            }
        } else {
            usleep(800000);
            $errors[] = t('The emergency code is wrong.');
        }
    }
}
$locked = Installer::locked();
$lockOnly = $locked && !is_file(Installer::configFile());

// ------------------------------------------------------------------ not installed: test / install
$dbHost = $f('db_host', 'localhost');
$dbPort = $f('db_port', '3306');
$dbName = $f('db_name');
$dbUser = $f('db_user');
$dbPass = is_string($_POST['db_pass'] ?? null) ? $_POST['db_pass'] : '';
$dbPrefix = $f('db_prefix', Db::DEFAULT_PREFIX);
$forceHttps = $action ? isset($_POST['force_https']) : is_https();

if (!$locked && in_array($action, ['test', 'install'], true)) {
    if ($dbName === '' || $dbUser === '') {
        $errors[] = t('Enter the database name and user.');
    } elseif (!preg_match('/^[A-Za-z0-9._-]+$/', $dbHost) || !preg_match('/^\d{1,5}$/', $dbPort) || !preg_match('/^[A-Za-z0-9_$-]+$/', $dbName)) {
        $errors[] = t('Host, port or database name contain invalid characters.');
    } elseif (!Db::validPrefix($dbPrefix)) {
        $errors[] = t('The table prefix may contain lower-case letters, digits and underscores, starting with a letter (at most 20 characters).');
    } else {
        $dsn = Installer::dsn($dbHost, $dbPort, $dbName);
        $test = Installer::testConnection($dsn, $dbUser, $dbPass, $dbPrefix);
        $existingUsers = $test['users'];

        if ($action === 'install' && $test['ok']) {
            $name = $f('admin_name');
            $email = Auth::normEmail($f('admin_email'));
            $pw = is_string($_POST['admin_password'] ?? null) ? $_POST['admin_password'] : '';
            if ($existingUsers === 0) {
                if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = t('Enter a name and a valid e-mail address for the administrator.');
                }
                if (mb_strlen($pw) < 10) {
                    $errors[] = t('The administrator password needs at least 10 characters.');
                } elseif ($pw !== (string)($_POST['admin_password2'] ?? '')) {
                    $errors[] = t('The two passwords do not match.');
                }
            }
            if (!$errors) {
                try {
                    Db::setPdo($test['pdo']);
                    if ($existingUsers === 0) {
                        Installer::runSchema($test['pdo']);
                        Migrator::markInstalled();
                        Auth::createUser($name, $email, 'admin', $pw, i18n_lang());
                    }
                    // An existing database keeps its data; pending updates are offered after login.
                    [$manualConfig, $resetCode] = Installer::writeConfig($dsn, $dbUser, $dbPass, $forceHttps, $dbPrefix);
                    $lockWritten = Installer::writeLock();
                    $done = true;
                } catch (PDOException $e) {
                    $errors[] = t('Database error: %s', $e->getMessage());
                }
            }
        }
    }
}

$langField = '<input type="hidden" name="lang" value="' . h(i18n_lang()) . '">';
$checks = [
    [t('PHP 8.1 or newer (found: %s)', PHP_VERSION), version_compare(PHP_VERSION, '8.1.0', '>='), true],
    [t('PHP extension pdo_mysql'), extension_loaded('pdo_mysql'), true],
    [t('PHP extension mbstring'), extension_loaded('mbstring'), true],
    [t('Folder writable (for config.php)'), is_writable(ANOUK_ROOT), false],
    [t('Folder data/ writable (for the lock file)'), is_writable(Installer::dataDir()), false],
    [t('HTTPS'), is_https(), false],
];
$blocked = (bool)array_filter($checks, fn($c) => $c[2] && !$c[1]);
?>
<!doctype html>
<html lang="<?= h(i18n_lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= te('ANOUK: installation') ?></title>
<link rel="stylesheet" href="assets/css/style.css?v=<?= h(ANOUK_VERSION) ?>">
<link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
</head>
<body class="plain">
<header class="topbar">
  <span class="brand"><span class="bubble" aria-hidden="true">≻</span><?= te('ANOUK: installation') ?></span>
  <nav class="lang-row" aria-label="<?= te('Language') ?>">
    <?php foreach (anouk_langs() as $code => $name): ?>
      <a href="?lang=<?= h($code) ?>" lang="<?= h($code) ?>"<?= $code === i18n_lang() ? ' aria-current="true"' : '' ?>><?= h($name) ?></a>
    <?php endforeach; ?>
  </nav>
</header>
<main class="wrap">
<?php foreach ($errors as $e): ?><div class="alert alert-error" role="alert"><?= h($e) ?></div><?php endforeach; ?>
<?php if ($notice): ?><div class="alert alert-success" role="status"><?= h($notice) ?></div><?php endif; ?>

<?php if ($done): ?>
  <h1><?= te('ANOUK is installed') ?></h1>
  <div class="alert alert-success"><?= $existingUsers ? te('ANOUK uses the existing database with %d accounts.', $existingUsers) : te('Tables and administrator account have been created.') ?></div>
  <?php if ($existingUsers): ?><p><?= te('If this database comes from an older ANOUK version, the administrator is offered a database update after logging in.') ?></p><?php endif; ?>
  <?php if (!$lockWritten): ?>
    <div class="alert alert-warning"><?= tm('The lock file <code>data/install.lock</code> could not be written. Delete <code>install.php</code> now, or make the folder <code>data/</code> writable.') ?></div>
  <?php endif; ?>
  <?php if ($manualConfig !== null): ?>
    <p><?= tm('The folder is not writable. Create a file <code>config.php</code> next to <code>index.php</code> with this content:') ?></p>
    <pre><?= h($manualConfig) ?></pre>
  <?php endif; ?>
  <div class="panel">
    <h2><?= te('Emergency code') ?></h2>
    <p><?= tm('With this code, <code>config.php</code> can later be deleted on this page, even when the database can no longer be reached. <b>Write it down now</b>; it is not shown again.') ?></p>
    <p><code class="big"><?= h($resetCode) ?></code></p>
  </div>
  <div class="alert alert-warning"><?= tm('For security, delete <code>install.php</code> now.') ?></div>
  <form method="post" class="actions">
    <?= $langField ?><input type="hidden" name="action" value="delete_installer">
    <button class="btn btn-primary" type="submit"><?= te('Delete install.php and log in') ?></button>
    <a class="btn" href="admin/index.php"><?= te('Log in without deleting') ?></a>
  </form>

<?php elseif ($lockOnly): ?>
  <h1><?= te('Installer locked') ?></h1>
  <p><?= tm('The installer is locked by <code>data/install.lock</code>, but <code>config.php</code> is missing. To install again, delete <code>data/install.lock</code> by FTP and reload this page. The data in the database are kept.') ?></p>

<?php elseif ($locked): ?>
  <h1><?= te('Already installed') ?></h1>
  <p><?= tm('ANOUK is installed (<code>config.php</code> exists).') ?></p>
  <div class="alert alert-warning"><?= tm('For security, delete <code>install.php</code> now.') ?></div>
  <form method="post" class="actions">
    <?= $langField ?><input type="hidden" name="action" value="delete_installer">
    <button class="btn btn-primary" type="submit"><?= te('Delete install.php') ?></button>
    <a class="btn" href="admin/index.php"><?= te('Log in') ?></a>
  </form>
  <details class="help panel section">
    <summary><?= te('Reset the configuration (delete config.php)') ?></summary>
    <p class="small"><?= te('Afterwards ANOUK can be set up again here, for example with new database credentials. Studies and accounts in the database are kept. Administrators can also do this without the code under “System”.') ?></p>
    <form method="post" class="form">
      <?= $langField ?><input type="hidden" name="action" value="reset_config">
      <label><?= te('Emergency code from the installation') ?> <input name="reset_code" autocomplete="off" required></label>
      <button class="btn btn-danger" type="submit"><?= te('Delete config.php') ?></button>
    </form>
  </details>

<?php else: ?>
  <h1><?= te('Install') ?></h1>
  <p class="lead"><?= tm('Fill in the database details from your hosting provider. Use <em>Test connection</em> first; nothing is saved until you click <em>Install</em>.') ?></p>

  <h2><?= te('Requirements') ?></h2>
  <ul class="checks">
  <?php foreach ($checks as [$label, $ok, $required]): ?>
    <li class="<?= $ok ? 'ok' : ($required ? 'bad' : 'warn') ?>"><span class="mark"><?= $ok ? '✓' : ($required ? '✗' : '!') ?></span><?= h($label) ?></li>
  <?php endforeach; ?>
  </ul>
  <?php if ($blocked): ?>
    <div class="alert alert-error"><?= te('A required component is missing. Ask your provider to enable it (often a PHP version setting in the control panel).') ?></div>
  <?php endif; ?>

  <?php if ($test): ?>
    <div class="alert <?= $test['ok'] ? 'alert-success' : 'alert-error' ?>" role="status">
      <strong><?= $test['ok'] ? te('Connection test passed') : te('Connection test failed') ?></strong>
      <ul><?php foreach ($test['lines'] as [$ok, $line]): ?><li><?= $ok ? '' : '✗ ' ?><?= h($line) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" class="form panel" autocomplete="off">
    <?= $langField ?>
    <fieldset class="first">
      <legend><?= te('Database (MySQL or MariaDB)') ?></legend>
      <div class="row2">
        <label><?= te('Host') ?>
          <input name="db_host" value="<?= h($dbHost) ?>" required>
          <small><?= tm('Often <code>localhost</code>; some providers use a separate name.') ?></small>
        </label>
        <label><?= te('Port') ?> <input name="db_port" value="<?= h($dbPort) ?>" inputmode="numeric" required></label>
      </div>
      <label><?= te('Database name') ?> <input name="db_name" value="<?= h($dbName) ?>" required></label>
      <div class="row2">
        <label><?= te('Database user') ?> <input name="db_user" value="<?= h($dbUser) ?>" required autocomplete="off"></label>
        <label><?= te('Database password') ?> <input type="password" name="db_pass" value="<?= h($dbPass) ?>" autocomplete="new-password"></label>
      </div>
      <label><?= te('Table prefix') ?> <input name="db_prefix" value="<?= h($dbPrefix) ?>" required pattern="[a-z][a-z0-9_]{0,19}">
        <small><?= tm('All ANOUK tables start with this prefix, so ANOUK can share a database with other tools. Keep <code>anouk_</code> unless you run two ANOUK installations in one database. To reuse an existing ANOUK database, enter its prefix.') ?></small></label>
      <button class="btn" type="submit" name="action" value="test" formnovalidate><?= te('Test connection') ?></button>
    </fieldset>

    <?php if ($existingUsers === 0): ?>
    <fieldset>
      <legend><?= te('Administrator account') ?></legend>
      <div class="row2">
        <label><?= te('Name') ?> <input name="admin_name" value="<?= h($f('admin_name')) ?>" maxlength="190"></label>
        <label><?= te('E-mail address') ?> <input type="email" name="admin_email" value="<?= h($f('admin_email')) ?>" maxlength="190"></label>
      </div>
      <div class="row2">
        <label><?= te('Password') ?> <input type="password" name="admin_password" minlength="10" autocomplete="new-password"><small><?= te('At least 10 characters.') ?></small></label>
        <label><?= te('Repeat password') ?> <input type="password" name="admin_password2" minlength="10" autocomplete="new-password"></label>
      </div>
      <p class="muted small"><?= te('The administrator can create further accounts for project leaders after logging in.') ?></p>
    </fieldset>
    <?php endif; ?>

    <fieldset>
      <legend><?= te('Settings') ?></legend>
      <label class="check"><input type="checkbox" name="force_https" value="1"<?= $forceHttps ? ' checked' : '' ?>> <?= te('Always use HTTPS (redirect http:// to https://)') ?></label>
    </fieldset>

    <div class="actions"><button class="btn btn-primary" type="submit" name="action" value="install"<?= $blocked ? ' disabled' : '' ?>><?= te('Install') ?></button></div>
  </form>
<?php endif; ?>
</main>
</body>
</html>
