<?php defined('ANOUK_ROOT') or exit; ?>
<?php $pageTitle = t('Help'); ?>
<div class="help-layout">
  <nav class="toc" aria-label="<?= te('Contents') ?>">
    <div class="lang-switch">
      <a href="<?= h(url('help', ['lang' => 'en'])) ?>"<?= $lang === 'en' ? ' aria-current="page"' : '' ?>>English</a>
      <a href="<?= h(url('help', ['lang' => 'de'])) ?>"<?= $lang === 'de' ? ' aria-current="page"' : '' ?>>Deutsch</a>
    </div>
    <ol>
      <?php foreach ($toc as [$id, $title]): ?>
        <li><a href="#<?= h($id) ?>"><?= h(preg_replace('/^\d+[a-z]?\.\s*/', '', strip_tags($title))) ?></a></li>
      <?php endforeach; ?>
    </ol>
  </nav>
  <article class="doc" lang="<?= h($lang) ?>">
    <?= $html !== '' ? $html : '<p class="muted">' . te('The help file was not found.') . '</p>' ?>
  </article>
</div>
