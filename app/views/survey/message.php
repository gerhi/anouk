<?php defined('ANOUK_ROOT') or exit; ?>
<?php if (!empty($title)): ?>
<div class="sheet done">
  <div class="bubble" aria-hidden="true">✓</div>
  <h1 class="test-title"><?= h($title) ?></h1>
  <p><?= h($text) ?></p>
</div>
<?php else: ?>
<div class="sheet message">
  <p><?= h($text) ?></p>
</div>
<?php endif; ?>
