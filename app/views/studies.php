<?php defined('ANOUK_ROOT') or exit; ?>
<?php
$pageTitle = t('Studies');
$actions = '<a class="btn btn-primary" href="' . h(url('study.new')) . '">' . te('New study') . '</a>';
?>
<?php if (!$studies): ?>
  <div class="empty">
    <p><?= te('No studies yet. Create a study to get a link for LimeSurvey.') ?></p>
    <p><a class="btn btn-primary" href="<?= h(url('study.new')) ?>"><?= te('New study') ?></a></p>
  </div>
<?php else: ?>
<div class="tests">
  <?php foreach ($studies as $s): $meta = Study::meta($s); $set = Study::settings($s); ?>
  <article class="test-row">
    <div>
      <h2><a href="<?= h(url('study', ['id' => $s['id']])) ?>"><?= h($s['title']) ?></a></h2>
      <div class="meta">
        <?php if ($isAdmin): ?><span><?= te('Owner') ?> <b><?= h($s['owner_name']) ?></b></span><?php endif; ?>
        <span><?= te('Design') ?> <b><?= !empty($meta['versions'])
            ? h(t('%1$d versions × %2$d tasks × %3$d alternatives', $meta['versions'], $meta['tasks'], $meta['alts']))
            : te('none yet') ?></b></span>
        <span><?= te('Language') ?> <b><?= h(Survey::LANGS[$set['lang']] ?? $set['lang']) ?></b></span>
      </div>
      <div class="copyrow">
        <input class="copy-src" readonly value="<?= h(survey_url($s['slug'], [], true) . '&id={TOKEN}') ?>" aria-label="<?= te('Entry link for LimeSurvey') ?>">
        <button type="button" class="btn btn-sm" data-copy data-copied="<?= te('Copied') ?>"><?= te('Copy link') ?></button>
      </div>
      <?php if ($set['return_url'] !== ''): ?><span class="tag"><?= te('Returns to the survey') ?></span><?php endif; ?>
      <?php if (!empty($set['none_option'])): ?><span class="tag"><?= te('None option') ?></span><?php endif; ?>
    </div>
    <div class="side">
      <span class="pill <?= h($s['status']) ?>"><?= h(status_label($s['status'])) ?></span>
      <div class="counts"><strong><?= (int)$s['n_complete'] ?></strong><?= te('completed') ?></div>
    </div>
  </article>
  <?php endforeach; ?>
</div>
<?php endif; ?>
