<?php defined('ANOUK_ROOT') or exit; ?>
<?php $pageTitle = t('Log in'); ?>
<form method="post" action="<?= h(url('login')) ?>" class="panel form">
  <h1 class="brand"><span class="bubble" aria-hidden="true">≻</span>ANOUK</h1>
  <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <label><?= te('E-mail address') ?>
    <input type="email" name="email" value="<?= h($email) ?>" required autofocus autocomplete="username">
  </label>
  <label><?= te('Password') ?>
    <input type="password" name="password" required autocomplete="current-password">
  </label>
  <button class="btn btn-primary btn-block" type="submit"><?= te('Log in') ?></button>
</form>
