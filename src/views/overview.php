<?php defined('ANOUK_ROOT') or exit; ?>
<?php $pageTitle = t('Overview'); ?>
<div class="panel">
<dl class="figures">
  <div><dt><?= te('Studies') ?></dt><dd><?= (int)$figures['studies'] ?></dd></div>
  <div><dt><?= te('In the field') ?></dt><dd><?= (int)$figures['field'] ?></dd></div>
  <div><dt><?= te('Completed respondents') ?></dt><dd><?= (int)$figures['complete'] ?></dd></div>
  <div><dt><?= te('Completed in the last 7 days') ?></dt><dd><?= (int)$figures['week'] ?></dd></div>
  <?php if ($isAdmin): ?><div><dt><?= te('Accounts') ?></dt><dd><?= count($accounts) ?></dd></div><?php endif; ?>
</dl>
</div>

<?php if (!$studies): ?>
<section class="section">
  <h2><?= te('Getting started') ?></h2>
  <ol class="steps">
    <li><?= tm('Create a study under <a href="%s">New study</a> and define its attributes and levels.', url('study.new')) ?></li>
    <li><?= te('Under “Design”, download the R script, create the choice sets with cbcTools and upload the CSV file.') ?></li>
    <li><?= te('Enter the return URL to LimeSurvey in the settings, check the preview and send the study into the field.') ?></li>
    <li><?= te('Link LimeSurvey to the entry link of the study and download the data in long format afterwards.') ?></li>
  </ol>
</section>
<?php else: ?>
<section class="section">
  <div class="section-head">
    <h2><?= te('Studies') ?></h2>
    <a href="<?= h(url('studies')) ?>"><?= te('All studies') ?></a>
  </div>
  <div class="table-wrap">
  <table>
    <thead><tr>
      <th><?= te('Study') ?></th><?php if ($isAdmin): ?><th><?= te('Owner') ?></th><?php endif; ?>
      <th><?= te('Status') ?></th><th class="num"><?= te('Completed') ?></th><th class="num"><?= te('Started') ?></th><th><?= te('Last activity') ?></th>
    </tr></thead>
    <tbody>
    <?php foreach ($studies as $s): ?>
      <tr>
        <td><a class="rowlink" href="<?= h(url('study', ['id' => $s['id']])) ?>"><?= h($s['title']) ?></a>
          <div class="muted small"><code><?= h($s['slug']) ?></code></div></td>
        <?php if ($isAdmin): ?><td><?= h($s['owner_name']) ?></td><?php endif; ?>
        <td><span class="pill <?= h($s['status']) ?>"><?= h(status_label($s['status'])) ?></span></td>
        <td class="num"><?= (int)$s['n_complete'] ?></td>
        <td class="num"><?= (int)$s['n_started'] ?></td>
        <td class="small nowrap"><?= $s['last_activity'] ? h(fmt_dt($s['last_activity'])) : '<span class="muted">' . te('None yet') . '</span>' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</section>
<?php endif; ?>

<?php if ($isAdmin): ?>
<section class="section">
  <div class="section-head">
    <h2><?= te('Accounts') ?></h2>
    <a href="<?= h(url('users')) ?>"><?= te('Manage accounts') ?></a>
  </div>
  <div class="table-wrap">
  <table>
    <thead><tr><th><?= te('Name') ?></th><th><?= te('Role') ?></th><th class="num"><?= te('Studies') ?></th><th><?= te('Last login') ?></th></tr></thead>
    <tbody>
    <?php foreach ($accounts as $a): ?>
      <tr<?= (int)$a['active'] ? '' : ' class="inactive"' ?>>
        <td><?= h($a['name']) ?> <span class="muted small"><?= h($a['email']) ?></span></td>
        <td><?= h(role_label($a['role'])) ?></td>
        <td class="num"><?= (int)$a['n_studies'] ?></td>
        <td class="small nowrap"><?= $a['last_login_at'] ? h(fmt_dt($a['last_login_at'])) : '<span class="muted">' . te('Never') . '</span>' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</section>
<?php endif; ?>
