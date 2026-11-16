<?php defined('ANOUK_ROOT') or exit; ?>
<?php $pageTitle = t('System'); ?>
<?php if ($installer): ?>
  <div class="alert alert-warning"><?= tm('The installer <code>install.php</code> is still on the server. Without the emergency code it cannot be misused, but it should be deleted.') ?></div>
<?php endif; ?>
<div class="grid2">
<section class="panel">
  <h2><?= te('Installation') ?></h2>
  <dl class="facts">
    <dt>ANOUK</dt><dd><?= h(ANOUK_VERSION) ?></dd>
    <dt>PHP</dt><dd><?= h(PHP_VERSION) ?></dd>
    <dt><?= te('Database server') ?></dt><dd><?= h($dbVersion) ?></dd>
    <dt>install.php</dt><dd><?= $installer ? '<span class="warn-text">' . te('present') . '</span>' : te('deleted') ?></dd>
    <?php foreach ($counts as $label => $n): ?><dt><?= h($label) ?></dt><dd><?= (int)$n ?></dd><?php endforeach; ?>
  </dl>
  <?php if ($installer): ?>
    <form method="post" action="<?= h(url('system.installer')) ?>" class="mt"><?= csrf_field() ?><button class="btn btn-primary" type="submit"><?= te('Delete install.php') ?></button></form>
  <?php endif; ?>
</section>

<section class="panel danger-zone">
  <h2><?= te('Reset the configuration') ?></h2>
  <p><?= tm('Deletes <code>config.php</code>. ANOUK then leads to <code>install.php</code>, where the database connection can be set up again. Studies, accounts and answers in the database are kept.') ?></p>
  <?php if (!$installer): ?>
    <p class="warn-text"><?= tm('This needs <code>install.php</code> on the server. Upload the file from the ANOUK package again.') ?></p>
  <?php else: ?>
  <form method="post" action="<?= h(url('system.reset')) ?>" class="inline-form">
    <?= csrf_field() ?>
    <label><?= tm('Type <code>DELETE</code> to confirm') ?> <input name="confirm" required autocomplete="off"></label>
    <button class="btn btn-danger" type="submit"><?= te('Delete config.php') ?></button>
  </form>
  <?php endif; ?>
</section>
</div>
