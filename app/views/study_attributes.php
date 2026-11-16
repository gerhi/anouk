<?php defined('ANOUK_ROOT') or exit; ?>
<?php $pageTitle = t('Attributes and levels') . ': ' . $study['title']; ?>
<p class="muted small"><a href="<?= h(url('study', ['id' => $study['id']])) ?>">← <?= h($study['title']) ?></a></p>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e) ?></div><?php endforeach; ?>
<?php if ($locked): ?>
  <div class="alert alert-warning"><?= te('The attributes are locked because a design exists or the study is not a draft.') ?></div>
<?php endif; ?>
<form method="post" action="<?= h(url('study.attributes', ['id' => $study['id']])) ?>" class="panel form">
  <?= csrf_field() ?>
  <label><?= tm('One attribute per line, levels separated by <code>|</code>') ?>
    <textarea name="attributes" rows="10" class="code"<?= $locked ? ' readonly' : '' ?> placeholder="<?= h("Price: 249 € | 299 € | 349 €\nBrand: A | B | C\nBattery life: 8 h | 10 h | 14 h") ?>"><?= h($text) ?></textarea>
    <small><?= te('The levels must match an uploaded design exactly. The order of the lines is the display order.') ?></small>
  </label>
  <?php if (!$locked): ?><div class="actions"><button class="btn btn-primary" type="submit"><?= te('Save') ?></button></div><?php endif; ?>
</form>
