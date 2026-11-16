<?php defined('ANOUK_ROOT') or exit; ?>
<?php
$pageTitle = t('Respondent') . ' ' . $r['ext_id'];
$stateLabels = ['complete' => t('Completed'), 'active' => t('In progress'), 'dropped' => t('Dropped out')];
$order = json_dec($r['attr_order'] ?? null, null);
$positions = ($order && count($order) === count($attrs)) ? $order : array_keys($attrs);
$byTask = [];
foreach ($answers as $a) {
    $byTask[(int)$a['task']] = $a;
}
?>
<p class="muted small"><a href="<?= h(url('respondents', ['study' => $study['id']])) ?>">← <?= te('Respondents') ?></a></p>
<dl class="facts panel">
  <dt><?= te('ID') ?></dt><dd><code><?= h($r['ext_id']) ?></code><?= (int)$r['is_test'] ? ' <span class="tag">' . te('Test') . '</span>' : '' ?></dd>
  <dt><?= te('Study') ?></dt><dd><a href="<?= h(url('study', ['id' => $study['id']])) ?>"><?= h($study['title']) ?></a></dd>
  <dt><?= te('Status') ?></dt><dd><span class="pill <?= h($r['state']) ?>"><?= h($stateLabels[$r['state']]) ?></span></dd>
  <dt><?= te('Version') ?></dt><dd><?= (int)$r['version'] ?></dd>
  <dt><?= te('Answers') ?></dt><dd><?= count($answers) ?> / <?= (int)($meta['tasks'] ?? 0) ?></dd>
  <dt><?= te('Started') ?></dt><dd><?= h(fmt_dt($r['started_at'])) ?></dd>
  <dt><?= te('Last activity') ?></dt><dd><?= h(fmt_dt($r['last_seen_at'])) ?></dd>
  <?php if ($r['completed_at']): ?><dt><?= te('Completed') ?></dt><dd><?= h(fmt_dt($r['completed_at'])) ?></dd><?php endif; ?>
</dl>

<?php if ($answers): ?>
<section class="section">
  <h2><?= te('Answers') ?></h2>
  <div class="answer-grid">
  <?php foreach ($byTask as $t => $a): $alts = $tasks[$t] ?? []; ?>
    <div class="panel">
      <h3><?= te('Task %d', $t) ?> · <?= (int)$a['choice'] === 0 ? te('none chosen') : te('chose %s', alt_letter((int)$a['choice'])) ?>
        <span class="muted small"> · <?= $a['rt_ms'] !== null ? h(fmt_num((int)$a['rt_ms'] / 1000)) . ' s' : '–' ?></span></h3>
      <table class="compact">
        <thead><tr><th></th><?php foreach ($alts as $alt => $_): ?><th<?= (int)$a['choice'] === $alt ? ' class="chosen"' : '' ?>><?= h(alt_letter($alt)) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
        <?php foreach ($positions as $k): ?>
          <tr><td class="muted"><?= h($attrs[$k]['name']) ?></td>
            <?php foreach ($alts as $alt => $ids): ?><td<?= (int)$a['choice'] === $alt ? ' class="chosen"' : '' ?>><?= h($labels[$ids[$k]] ?? '?') ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="panel danger-zone section">
  <h2><?= te('Delete respondent') ?></h2>
  <p><?= te('Deletes this respondent and all their answers, for example on a deletion request. Their version becomes free again.') ?></p>
  <form method="post" action="<?= h(url('respondent.delete')) ?>" data-confirm="<?= te('Really delete this respondent and all their answers?') ?>">
    <?= csrf_field() ?><input type="hidden" name="respondent_id" value="<?= (int)$r['id'] ?>">
    <button class="btn btn-danger" type="submit"><?= te('Delete respondent') ?></button>
  </form>
</section>
