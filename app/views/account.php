<?php defined('ANOUK_ROOT') or exit; ?>
<?php $pageTitle = t('Change password'); ?>
<?php if ((int)($user['must_change_password'] ?? 0) === 1): ?>
  <div class="alert alert-warning"><?= te('You are using a starting password. Choose a password of your own to continue.') ?></div>
<?php endif; ?>
<p class="muted"><?= h($user['name']) ?> · <?= h($user['email']) ?> · <?= h(role_label($user['role'])) ?></p>
<form method="post" action="<?= h(url('account')) ?>" class="panel form narrow-form">
  <?= csrf_field() ?>
  <label><?= (int)($user['must_change_password'] ?? 0) === 1 ? te('Starting password') : te('Current password') ?> <input type="password" name="current" required autocomplete="current-password"></label>
  <label><?= te('New password') ?> <input type="password" name="new" required minlength="10" autocomplete="new-password"><small><?= te('At least 10 characters.') ?></small></label>
  <label><?= te('Repeat new password') ?> <input type="password" name="repeat" required minlength="10" autocomplete="new-password"></label>
  <button class="btn btn-primary" type="submit"><?= te('Change password') ?></button>
</form>
