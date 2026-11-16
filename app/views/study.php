<?php defined('ANOUK_ROOT') or exit; ?>
<?php
$pageTitle = $study['title'];
$sid = (int)$study['id'];
$draft = $study['status'] === 'draft';
$hasDesign = !empty($meta['versions']);
$entry = survey_url($study['slug'], [], true) . '&id={TOKEN}';
$actions = '<div class="actions">'
    . '<a class="btn" href="' . h(url('study.settings', ['id' => $sid])) . '">' . te('Settings') . '</a>'
    . ($hasDesign ? '<a class="btn" href="' . h(survey_url($study['slug'], ['preview' => 1, 'restart' => 1])) . '" target="_blank" rel="noopener">' . te('Preview') . '</a>' : '')
    . '</div>';
$transitionLabels = ['field' => t('Send into the field'), 'closed' => t('Close'), 'draft' => t('Back to draft')];
?>
<dl class="facts panel">
  <dt><?= te('Status') ?></dt><dd><span class="pill <?= h($study['status']) ?>"><?= h(status_label($study['status'])) ?></span></dd>
  <dt><?= te('Link name') ?></dt><dd><code><?= h($study['slug']) ?></code></dd>
  <dt><?= te('Language of the respondent pages') ?></dt><dd><?= h(Survey::LANGS[$settings['lang']] ?? $settings['lang']) ?></dd>
  <?php if ($isAdmin): ?><dt><?= te('Owner') ?></dt><dd><?= h($owner['name'] ?? '?') ?></dd><?php endif; ?>
</dl>

<div class="grid2">
<section class="panel">
  <h2><?= te('Status') ?></h2>
  <p><?php if ($study['status'] === 'draft'): ?><?= te('While the study is a draft, attributes and design can be changed. Respondents see nothing yet.') ?>
     <?php elseif ($study['status'] === 'field'): ?><?= te('The study accepts respondents. Attributes and design are frozen.') ?>
     <?php else: ?><?= te('The study no longer accepts respondents.') ?><?php endif; ?></p>
  <div class="actions">
  <?php foreach ($transitions as $to): ?>
    <form method="post" action="<?= h(url('study.status', ['id' => $sid])) ?>">
      <?= csrf_field() ?><input type="hidden" name="to" value="<?= h($to) ?>">
      <button class="btn<?= $to === 'field' ? ' btn-primary' : '' ?>" type="submit"<?= ($to === 'field' && !$hasDesign) ? ' disabled title="' . te('Create a design first') . '"' : '' ?>><?= h($transitionLabels[$to]) ?></button>
    </form>
  <?php endforeach; ?>
  </div>
</section>

<section class="panel">
  <h2><?= te('Link with LimeSurvey') ?></h2>
  <p class="small"><?= te('LimeSurvey sends respondents to this link; it replaces {TOKEN} with the participant’s token.') ?></p>
  <div class="copyrow">
    <input class="copy-src" readonly value="<?= h($entry) ?>" aria-label="<?= te('Entry link for LimeSurvey') ?>">
    <button type="button" class="btn btn-sm" data-copy data-copied="<?= te('Copied') ?>"><?= te('Copy link') ?></button>
  </div>
  <p class="small mt"><?= te('Return after completion:') ?>
    <?php if ($settings['return_url'] !== ''): ?><code><?= h($settings['return_url']) ?></code>
    <?php else: ?><span class="warn-text"><?= te('not set – respondents only see a thank-you page.') ?></span> <a href="<?= h(url('study.settings', ['id' => $sid])) ?>"><?= te('Set it') ?></a><?php endif; ?>
  </p>
  <p class="muted small"><?= tm('IDs that start with <code>%s</code> are test IDs.', $settings['test_prefix'] ?: '–') ?></p>
</section>
</div>

<section class="section">
  <div class="section-head">
    <h2><?= te('Attributes and levels') ?></h2>
    <?php if ($draft && !$hasDesign): ?><a href="<?= h(url('study.attributes', ['id' => $sid])) ?>"><?= $attrs ? te('Edit') : te('Define') ?></a><?php endif; ?>
  </div>
  <?php if (!$attrs): ?>
    <div class="empty"><p><?= te('No attributes yet. Define them here, or take them from the file when you upload a design.') ?></p></div>
  <?php else: ?>
    <div class="table-wrap">
    <table>
      <thead><tr><th><?= te('Attribute') ?></th><th><?= te('Levels') ?></th></tr></thead>
      <tbody>
      <?php foreach ($attrs as $a): ?>
        <tr><td><?= h($a['name']) ?></td><td><?= h(implode(' · ', array_column($a['levels'], 'label'))) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php if ($hasDesign && $draft): ?><p class="muted small"><?= te('To change the attributes, delete the design first.') ?></p><?php endif; ?>
  <?php endif; ?>
</section>

<section class="section">
  <div class="section-head">
    <h2><?= te('Design') ?></h2>
    <div class="actions">
      <?php if ($hasDesign): ?><a class="btn btn-sm" href="<?= h(url('study.design.csv', ['id' => $sid])) ?>"><?= te('Download as CSV') ?></a><?php endif; ?>
      <?php if ($draft): ?><a class="btn btn-sm<?= $hasDesign ? '' : ' btn-primary' ?>" href="<?= h(url('study.design', ['id' => $sid])) ?>"><?= $hasDesign ? te('Replace') : te('Create or upload') ?></a><?php endif; ?>
    </div>
  </div>
  <?php if (!$hasDesign): ?>
    <div class="empty"><p><?= te('No design yet.') ?></p></div>
  <?php else: ?>
  <div class="panel">
    <p><?= tm('<b>%1$d</b> versions × <b>%2$d</b> tasks × <b>%3$d</b> alternatives', $meta['versions'], $meta['tasks'], $meta['alts']) ?> ·
      <?= ($meta['source'] ?? '') === 'upload'
          ? (!empty($meta['filename']) ? tm('uploaded from <code>%s</code>', $meta['filename']) : te('uploaded'))
          : te('generated with “%1$s”, seed %2$s', t(Design::METHODS[$meta['method'] ?? ''] ?? '?'), (string)($meta['seed'] ?? '')) ?>
      · <?= h(fmt_dt($meta['created_at'] ?? null)) ?></p>
    <details class="help">
      <summary><?= te('Design diagnostics: level frequencies and overlap') ?></summary>
      <div class="diag">
      <?php foreach ($diagnosis as $d): ?>
        <div class="table-wrap">
        <table class="compact">
          <thead><tr><th><?= h($d['name']) ?></th><th class="num">n</th><th class="num"><?= te('Share') ?></th></tr></thead>
          <tbody>
          <?php foreach ($d['levels'] as $l): ?>
            <tr><td><?= h($l['label']) ?></td><td class="num"><?= (int)$l['n'] ?></td><td class="num"><?= h(fmt_num($l['share'] * 100)) ?> %</td></tr>
          <?php endforeach; ?>
          <tr class="sum"><td><?= te('Tasks with a repeated level') ?></td><td></td><td class="num"><?= h(fmt_num($d['overlap'] * 100)) ?> %</td></tr>
          </tbody>
        </table>
        </div>
      <?php endforeach; ?>
      </div>
    </details>
    <?php if ($draft && $stats['active'] + $stats['complete'] + $stats['test'] === 0): ?>
      <form method="post" action="<?= h(url('study.design.delete', ['id' => $sid])) ?>" class="mt" data-confirm="<?= te('Really delete the design?') ?>">
        <?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit"><?= te('Delete design') ?></button>
      </form>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</section>

<div class="grid2 section">
<section class="panel">
  <h2><?= te('Field status') ?></h2>
  <table class="compact">
    <tr><td><?= te('Completed') ?></td><td class="num"><?= (int)$stats['complete'] ?></td></tr>
    <tr><td><?= te('Started, not completed') ?></td><td class="num"><?= (int)$stats['active'] ?></td></tr>
    <tr><td><?= te('Test IDs') ?></td><td class="num"><?= (int)$stats['test'] ?></td></tr>
    <?php if ($hasDesign): ?>
    <tr><td><?= te('Versions with completions') ?></td><td class="num"><?= h(t('%1$d of %2$d', $stats['versions_used'], $meta['versions'])) ?></td></tr>
    <tr><td><?= te('Completions per used version') ?></td><td class="num"><?= (int)$stats['per_version_min'] ?>–<?= (int)$stats['per_version_max'] ?></td></tr>
    <?php endif; ?>
  </table>
  <p class="mt"><a href="<?= h(url('respondents', ['study' => $sid])) ?>"><?= te('Show respondents') ?></a></p>
</section>

<section class="panel">
  <h2><?= te('Export (long format)') ?></h2>
  <p class="small muted"><?= tm('One row per ID × task × alternative, with <code>choice</code> = 1 for the chosen alternative. Ready for <code>logitr</code>, <code>mlogit</code> and <code>bayesm</code>.') ?></p>
  <form method="get" action="<?= h(base_path()) ?>/index.php" class="form">
    <input type="hidden" name="r" value="study.export"><input type="hidden" name="id" value="<?= $sid ?>">
    <label><?= te('Respondents') ?>
      <select name="scope"><option value="complete"><?= te('completed only') ?></option><option value="all"><?= te('all, including drop-outs') ?></option></select>
    </label>
    <label class="check"><input type="checkbox" name="tests" value="1"> <?= te('Include test IDs') ?></label>
    <label><?= te('File style') ?>
      <select name="sep"><option value=","><?= te('R, SPSS, Python (comma)') ?></option><option value=";"><?= te('German Excel (semicolon)') ?></option></select>
    </label>
    <button class="btn btn-primary" type="submit"><?= te('Download CSV') ?></button>
  </form>
</section>
</div>

<?php if ($stats['active'] + $stats['complete'] + $stats['test'] > 0): ?>
<section class="panel danger-zone section">
  <h2><?= te('Delete all answers') ?></h2>
  <p><?= te('Deletes all respondents and answers of this study, for example after a test run. The study, its settings and its design are kept.') ?></p>
  <form method="post" action="<?= h(url('study.reset', ['id' => $sid])) ?>" class="inline-form">
    <?= csrf_field() ?>
    <label><?= te('Type the link name to confirm') ?> <input name="confirm" placeholder="<?= h($study['slug']) ?>" required autocomplete="off"></label>
    <button class="btn btn-danger" type="submit"><?= te('Delete all answers') ?></button>
  </form>
</section>
<?php endif; ?>

<section class="panel danger-zone section">
  <h2><?= te('Delete study') ?></h2>
  <p><?= te('Deletes the study with its design and all answers for good.') ?></p>
  <form method="post" action="<?= h(url('study.delete', ['id' => $sid])) ?>" class="inline-form">
    <?= csrf_field() ?>
    <label><?= te('Type the link name to confirm') ?> <input name="confirm" placeholder="<?= h($study['slug']) ?>" required autocomplete="off"></label>
    <button class="btn btn-danger" type="submit"><?= te('Delete for good') ?></button>
  </form>
</section>
