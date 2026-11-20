<?php defined('ANOUK_ROOT') or exit; ?>
<?php
$pageTitle = t('Respondents');
$stateLabels = ['complete' => t('Completed'), 'active' => t('In progress'), 'dropped' => t('Dropped out')];
$link = fn(array $over) => url('respondents', array_filter(array_merge(['study' => $f['study'] ?: null, 'status' => $f['status'] ?: null, 'q' => $f['q'] !== '' ? $f['q'] : null], $over), fn($v) => $v !== null && $v !== ''));
?>
<form method="get" action="<?= h(admin_path()) ?>" class="filters">
  <input type="hidden" name="r" value="respondents">
  <select name="study" aria-label="<?= te('Study') ?>">
    <option value=""><?= te('All studies') ?></option>
    <?php foreach ($studies as $id => $s): ?><option value="<?= (int)$id ?>"<?= $f['study'] === (int)$id ? ' selected' : '' ?>><?= h($s['title']) ?></option><?php endforeach; ?>
  </select>
  <select name="status" aria-label="<?= te('Status') ?>">
    <option value=""><?= te('All') ?></option>
    <?php foreach (['complete' => t('Completed'), 'active' => t('In progress'), 'dropped' => t('Dropped out'), 'test' => t('Test IDs')] as $k => $label): ?>
      <option value="<?= h($k) ?>"<?= $f['status'] === $k ? ' selected' : '' ?>><?= h($label) ?></option>
    <?php endforeach; ?>
  </select>
  <input name="q" value="<?= h($f['q']) ?>" placeholder="<?= te('Search ID') ?>" aria-label="<?= te('Search ID') ?>">
  <button class="btn" type="submit"><?= te('Filter') ?></button>
  <span class="muted small"><?= te('%d respondents', $total) ?></span>
</form>

<?php if (!$rows): ?>
  <div class="empty"><p><?= te('No respondents found.') ?></p></div>
<?php else: ?>
<div class="table-wrap">
<table>
  <thead><tr><th><?= te('ID') ?></th><th><?= te('Study') ?></th><th class="num"><?= te('Version') ?></th><th><?= te('Status') ?></th><th class="num"><?= te('Answers') ?></th><th><?= te('Started') ?></th><th><?= te('Last activity') ?></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): $meta = Study::meta($studies[(int)$r['study_id']]); ?>
    <tr>
      <td><a class="rowlink" href="<?= h(url('respondent', ['id' => $r['id']])) ?>"><code><?= h($r['ext_id']) ?></code></a>
        <?php if ((int)$r['is_test']): ?><span class="tag"><?= te('Test') ?></span><?php endif; ?></td>
      <td><?= h($studies[(int)$r['study_id']]['title']) ?></td>
      <td class="num"><?= (int)$r['version'] ?></td>
      <td><span class="pill <?= h($r['state']) ?>"><?= h($stateLabels[$r['state']]) ?></span></td>
      <td class="num"><?= (int)$r['n_answers'] ?> / <?= (int)($meta['tasks'] ?? 0) ?></td>
      <td class="small nowrap"><?= h(fmt_dt($r['started_at'])) ?></td>
      <td class="small nowrap"><?= h(fmt_dt($r['last_seen_at'])) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php if ($pages > 1): ?>
<div class="pager">
  <?php if ($page > 1): ?><a class="btn btn-sm" href="<?= h($link(['page' => $page - 1])) ?>">← <?= te('Previous') ?></a><?php endif; ?>
  <span class="muted small"><?= te('Page %1$d of %2$d', $page, $pages) ?></span>
  <?php if ($page < $pages): ?><a class="btn btn-sm" href="<?= h($link(['page' => $page + 1])) ?>"><?= te('Next') ?> →</a><?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>
<p class="muted small mt"><?= te('“Dropped out” means no activity within the release time of the study; the version is then free for others.') ?></p>
