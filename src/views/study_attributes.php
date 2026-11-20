<?php defined('ANOUK_ROOT') or exit; ?>
<?php $pageTitle = t('Attributes and levels') . ': ' . $study['title']; ?>
<p class="muted small"><a href="<?= h(url('study', ['id' => $study['id']])) ?>">← <?= h($study['title']) ?></a></p>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e) ?></div><?php endforeach; ?>
<?php if ($locked): ?>
  <div class="alert alert-warning"><?= te('The attributes are locked because a design exists or the study is not a draft.') ?> <?= te('The variable names below can still be changed.') ?></div>
<?php endif; ?>
<form method="post" action="<?= h(url('study.attributes', ['id' => $study['id']])) ?>" class="panel form">
  <?= csrf_field() ?>
  <label><?= tm('One attribute per line, levels separated by <code>|</code>') ?>
    <textarea name="attributes" rows="10" class="code"<?= $locked ? ' readonly' : '' ?> placeholder="<?= h("Price: 249 € | 299 € | 349 €\nBrand: A | B | C\nBattery life: 8 h | 10 h | 14 h") ?>"><?= h($text) ?></textarea>
    <small><?= te('The levels must match an uploaded design exactly. The order of the lines is the display order.') ?></small>
  </label>
  <?php if (!$locked): ?><div class="actions"><button class="btn btn-primary" type="submit"><?= te('Save') ?></button></div><?php endif; ?>
</form>

<?php if ($attrs): ?>
<section class="panel section">
  <h2><?= te('Variable names') ?></h2>
  <p class="small muted"><?= tm('Column names of the attributes in exports and in the cbcTools script, for example <code>price</code> instead of “Price per month”. Lower-case letters, digits and underscores, starting with a letter. You can change them at any time, also while the study is in the field.') ?></p>
  <form method="post" action="<?= h(url('study.varnames', ['id' => $study['id']])) ?>" class="form">
    <?= csrf_field() ?>
    <div class="table-wrap"><table>
      <thead><tr><th><?= te('Attribute') ?></th><th><?= te('Variable name') ?></th></tr></thead>
      <tbody>
      <?php foreach ($attrs as $a): ?>
        <tr><td><label for="var<?= (int)$a['id'] ?>"><?= h($a['name']) ?></label></td>
            <td><input id="var<?= (int)$a['id'] ?>" name="var[<?= (int)$a['id'] ?>]" value="<?= h($a['var_name']) ?>" required maxlength="64" pattern="[a-z][a-z0-9_]{0,63}" class="code" spellcheck="false" autocomplete="off"></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <div class="actions"><button class="btn btn-primary" type="submit"><?= te('Save variable names') ?></button></div>
  </form>
</section>
<?php endif; ?>
