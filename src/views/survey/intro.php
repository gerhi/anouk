<?php defined('ANOUK_ROOT') or exit; ?>
<div class="sheet">
  <div class="intro">
    <?php foreach (preg_split('/\R{2,}/u', trim($intro)) as $para): ?>
      <p><?= nl2br(h($para)) ?></p>
    <?php endforeach; ?>
  </div>
  <form method="post" action="<?= h($action) ?>">
    <input type="hidden" name="action" value="intro">
    <button type="submit" class="btn btn-primary btn-lg"><?= h(Survey::t($lang, 'start')) ?></button>
  </form>
</div>
