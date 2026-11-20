<?php defined('ANOUK_ROOT') or exit; ?>
<?php $pageTitle = t('New study'); ?>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" action="<?= h(url('study.new')) ?>" class="panel form narrow-form">
  <?= csrf_field() ?>
  <label><?= te('Title') ?>
    <input name="title" value="<?= h($title) ?>" required maxlength="255">
  </label>
  <label><?= te('Link name') ?>
    <input name="slug" value="<?= h($slug) ?>" required pattern="[a-z0-9][a-z0-9\-]{1,62}" placeholder="<?= te('e.g. battery-study') ?>">
    <small><?= te('Lower-case letters, digits and hyphens. It appears in the link for respondents and cannot be changed later.') ?></small>
  </label>
  <label><?= te('Language of the respondent pages') ?>
    <select name="lang"><?php foreach (anouk_langs() as $code => $name): ?><option value="<?= h($code) ?>"<?= $code === $lang ? ' selected' : '' ?>><?= h($name) ?></option><?php endforeach; ?></select>
  </label>
  <button class="btn btn-primary" type="submit"><?= te('Create study') ?></button>
</form>
