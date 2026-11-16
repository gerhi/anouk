<?php defined('ANOUK_ROOT') or exit; ?>
<?php
$pageTitle = t('Account') . ': ' . $u['name'];
$self = (int)$u['id'] === (int)$me['id'];
?>
<p class="muted small"><a href="<?= h(url('users')) ?>">← <?= te('Accounts') ?></a></p>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e) ?></div><?php endforeach; ?>
<?php if ($newPassword): ?>
  <div class="alert alert-success" role="status">
    <?= tm('Starting password for <b>%s</b>:', $newPassword['email']) ?>
    <div class="copyrow mt">
      <input class="copy-src" readonly value="<?= h($newPassword['password']) ?>" aria-label="<?= te('Password') ?>">
      <button type="button" class="btn btn-sm" data-copy data-copied="<?= te('Copied') ?>"><?= te('Copy') ?></button>
    </div>
    <p class="small mt"><?= te('It is shown only now. Pass it on safely; the person has to choose a password of their own at the first login.') ?></p>
  </div>
<?php endif; ?>

<div class="grid2">
<form method="post" action="<?= h(url('user.edit', ['id' => $u['id']])) ?>" class="panel form">
  <?= csrf_field() ?>
  <h2><?= te('Details') ?></h2>
  <label><?= te('Name') ?> <input name="name" value="<?= h($u['name']) ?>" required maxlength="190"></label>
  <label><?= te('E-mail address') ?> <input type="email" name="email" value="<?= h($u['email']) ?>" required maxlength="190"></label>
  <div class="row2">
    <label><?= te('Role') ?>
      <?php if ($self): ?>
        <input type="hidden" name="role" value="admin"><input value="<?= te('Administrator') ?>" readonly>
      <?php else: ?>
        <select name="role">
          <option value="leader"<?= $u['role'] === 'leader' ? ' selected' : '' ?>><?= te('Project leader') ?></option>
          <option value="admin"<?= $u['role'] === 'admin' ? ' selected' : '' ?>><?= te('Administrator') ?></option>
        </select>
      <?php endif; ?>
    </label>
    <label><?= te('Language') ?>
      <select name="language"><?php foreach (ANOUK_LANGS as $code => $name): ?><option value="<?= h($code) ?>"<?= ($u['language'] ?? 'en') === $code ? ' selected' : '' ?>><?= h($name) ?></option><?php endforeach; ?></select>
    </label>
  </div>
  <button class="btn btn-primary" type="submit"><?= te('Save') ?></button>
</form>

<section class="panel">
  <h2><?= te('Access') ?></h2>
  <dl class="facts">
    <dt><?= te('Status') ?></dt><dd><?= (int)$u['active'] ? te('Active') : '<span class="pill off">' . te('Inactive') . '</span>' ?></dd>
    <dt><?= te('Password') ?></dt><dd><?= (int)($u['must_change_password'] ?? 0) ? te('Starting password, not yet changed') : te('Chosen by the person') ?></dd>
    <dt><?= te('Last login') ?></dt><dd><?= $u['last_login_at'] ? h(fmt_dt($u['last_login_at'])) : te('Never') ?></dd>
    <dt><?= te('Studies') ?></dt><dd><?= (int)$nStudies ?></dd>
  </dl>
  <?php if (!$self): ?>
  <div class="actions mt">
    <form method="post" action="<?= h(url('user.toggle')) ?>"><?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
      <button class="btn" type="submit"><?= (int)$u['active'] ? te('Deactivate') : te('Activate') ?></button></form>
    <form method="post" action="<?= h(url('user.reset')) ?>" data-confirm="<?= te('Create a new starting password?') ?>"><?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
      <button class="btn" type="submit"><?= te('New starting password') ?></button></form>
  </div>
  <p class="muted small mt"><?= te('A deactivated account can no longer log in; its studies stay as they are.') ?></p>
  <?php endif; ?>
</section>
</div>

<?php if (!$self): ?>
<section class="panel danger-zone section">
  <h2><?= te('Delete account') ?></h2>
  <form method="post" action="<?= h(url('user.delete')) ?>" class="form">
    <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
    <?php if ($nStudies > 0): ?>
      <p><?= te('This account owns %d studies. Choose who takes them over; results stay with the studies.', $nStudies) ?></p>
      <label><?= te('Transfer the studies to') ?>
        <select name="transfer_to" required>
          <?php foreach ($others as $o): ?>
            <option value="<?= (int)$o['id'] ?>"<?= (int)$o['id'] === (int)$me['id'] ? ' selected' : '' ?>><?= h($o['name']) ?> (<?= h($o['email']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </label>
    <?php else: ?>
      <p><?= te('This account owns no studies.') ?></p>
    <?php endif; ?>
    <label class="check"><input type="checkbox" name="confirm" value="1" required> <?= te('Delete this account for good') ?></label>
    <button class="btn btn-danger" type="submit"><?= te('Delete account') ?></button>
  </form>
</section>
<?php endif; ?>
