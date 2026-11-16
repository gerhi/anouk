<?php defined('ANOUK_ROOT') or exit; ?>
<?php $pageTitle = t('Accounts'); ?>
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

<div class="table-wrap">
<table>
  <thead><tr><th><?= te('Name') ?></th><th><?= te('Role') ?></th><th><?= te('Language') ?></th><th class="num"><?= te('Studies') ?></th><th><?= te('Last login') ?></th><th></th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
    <tr<?= (int)$u['active'] ? '' : ' class="inactive"' ?>>
      <td><a class="rowlink" href="<?= h(url('user.edit', ['id' => $u['id']])) ?>"><?= h($u['name']) ?></a>
        <div class="muted small"><?= h($u['email']) ?></div></td>
      <td><?= h(role_label($u['role'])) ?>
        <?php if (!(int)$u['active']): ?><div><span class="pill off"><?= te('Inactive') ?></span></div><?php endif; ?>
        <?php if ((int)($u['must_change_password'] ?? 0)): ?><div class="muted small"><?= te('Starting password') ?></div><?php endif; ?></td>
      <td><?= h(ANOUK_LANGS[$u['language'] ?? 'en'] ?? '') ?></td>
      <td class="num"><?= (int)$u['n_studies'] ?></td>
      <td class="small nowrap"><?= $u['last_login_at'] ? h(fmt_dt($u['last_login_at'])) : '<span class="muted">' . te('Never') . '</span>' ?></td>
      <td><a class="btn btn-sm" href="<?= h(url('user.edit', ['id' => $u['id']])) ?>"><?= te('Edit') ?></a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<section class="section">
  <h2><?= te('New account') ?></h2>
  <form method="post" action="<?= h(url('user.new')) ?>" class="panel form narrow-form">
    <?= csrf_field() ?>
    <label><?= te('Name') ?> <input name="name" required maxlength="190"></label>
    <label><?= te('E-mail address') ?> <input type="email" name="email" required maxlength="190"></label>
    <div class="row2">
      <label><?= te('Role') ?>
        <select name="role"><option value="leader"><?= te('Project leader') ?></option><option value="admin"><?= te('Administrator') ?></option></select>
      </label>
      <label><?= te('Language') ?>
        <select name="language"><?php foreach (ANOUK_LANGS as $code => $name): ?><option value="<?= h($code) ?>"<?= $code === i18n_lang() ? ' selected' : '' ?>><?= h($name) ?></option><?php endforeach; ?></select>
      </label>
    </div>
    <p class="muted small"><?= te('Project leaders see and edit only their own studies. ANOUK creates a starting password, which has to be changed at the first login.') ?></p>
    <button class="btn btn-primary" type="submit"><?= te('Create account') ?></button>
  </form>
</section>
