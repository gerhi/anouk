<?php defined('ANOUK_ROOT') or exit; ?>
<?php $pageTitle = $title; ?>
<div class="panel">
  <?php if (!Auth::user()): ?><h1><?= h($title) ?></h1><?php endif; ?>
  <p><?= h($message) ?></p>
  <p class="actions"><a class="btn" href="<?= h(url('overview')) ?>"><?= te('Back to the overview') ?></a></p>
</div>
