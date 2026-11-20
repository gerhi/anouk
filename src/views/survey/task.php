<?php defined('ANOUK_ROOT') or exit; ?>
<?php $wide = true; $n = count($cards); ?>
<?php if (!empty($settings['show_progress'])): ?>
<div class="test-head">
  <span class="test-name"></span>
  <span><?= h(Survey::t($lang, 'progress', $task, $tasks)) ?></span>
</div>
<?php if ($tasks <= 40): ?>
  <div class="ruler" aria-hidden="true"><?php for ($i = 1; $i <= $tasks; $i++): ?><span class="tick<?= $i < $task ? ' done' : ($i === $task ? ' now' : '') ?>"></span><?php endfor; ?></div>
<?php else: ?>
  <progress class="bar" value="<?= $task - 1 ?>" max="<?= (int)$tasks ?>" aria-hidden="true"></progress>
<?php endif; ?>
<?php endif; ?>

<form method="post" action="<?= h($action) ?>" class="sheet question" id="task-form" data-choose="<?= h(Survey::t($lang, 'choose')) ?>" novalidate>
  <input type="hidden" name="action" value="answer">
  <input type="hidden" name="task" value="<?= (int)$task ?>">
  <input type="hidden" name="rt" id="anouk-rt" value="">
  <input type="hidden" name="sw" id="anouk-sw" value="">
  <fieldset>
    <legend class="qtext"><?= h(Survey::question($settings)) ?></legend>
    <p class="task-error" id="task-error" role="alert"><?= $error ? h($error) : '' ?></p>
    <div class="alts n<?= $n ?>">
      <?php foreach ($cards as $alt => $rows): $letter = alt_letter((int)$alt); ?>
      <label class="alt">
        <input type="radio" name="choice" value="<?= (int)$alt ?>">
        <span class="alt-head"><span class="bubble" aria-hidden="true"><?= h($letter) ?></span><span class="name"><?= h(Survey::t($lang, 'option', $letter)) ?></span></span>
        <dl>
          <?php foreach ($rows as $r): ?>
          <div><dt><?= h($r['name']) ?></dt><dd><?= h($r['label']) ?></dd></div>
          <?php endforeach; ?>
        </dl>
      </label>
      <?php endforeach; ?>
    </div>
    <?php if (!empty($settings['none_option'])): ?>
      <label class="none-option">
        <input type="radio" name="choice" value="0">
        <span class="bubble" aria-hidden="true">–</span>
        <span class="label"><?= h(Survey::noneLabel($settings)) ?></span>
      </label>
    <?php endif; ?>
  </fieldset>
  <div class="submit-row">
    <p class="note"><?= h(Survey::t($lang, 'saved_note')) ?></p>
    <button type="submit" class="btn btn-primary btn-lg"><?= h(Survey::t($lang, 'next')) ?></button>
  </div>
</form>
