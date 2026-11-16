<?php defined('ANOUK_ROOT') or exit; ?>
<?php $preview = true; ?>
<div class="sheet done">
  <div class="bubble" aria-hidden="true">✓</div>
  <?php if ($target): ?>
    <p><?= h(Survey::t($lang, 'preview_end')) ?></p>
    <p><code><?= h($target) ?></code></p>
  <?php else: ?>
    <p><?= h(Survey::t($lang, 'no_return')) ?></p>
  <?php endif; ?>
  <div class="actions">
    <a class="btn btn-primary" href="<?= h($restart) ?>"><?= h(Survey::t($lang, 'restart')) ?></a>
    <a class="btn" href="<?= h($back) ?>"><?= h(Survey::t($lang, 'back')) ?></a>
  </div>
</div>
