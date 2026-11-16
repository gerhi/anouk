<?php defined('ANOUK_ROOT') or exit; ?>
<?php $pageTitle = t('Design') . ': ' . $study['title']; $draft = $study['status'] === 'draft'; ?>
<p class="muted small"><a href="<?= h(url('study', ['id' => $study['id']])) ?>">← <?= h($study['title']) ?></a></p>
<?php if (!empty($meta['versions'])): ?>
  <div class="alert alert-warning"><?= te('A design exists already (%d versions). A new upload replaces it.', $meta['versions']) ?></div>
<?php endif; ?>
<?php if ($result && !$result['ok']): ?>
  <div class="alert alert-error" role="alert">
    <strong><?= te('The design was not saved.') ?></strong>
    <ul><?php foreach ($result['errors'] as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>
<?php if (!$draft): ?>
  <div class="alert alert-warning"><?= te('The design can only be changed while the study is a draft.') ?></div>
<?php else: ?>
<div class="grid2">
<section class="panel">
  <h2><?= te('1. Create choice sets with cbcTools') ?></h2>
  <?php if (!$attrs): ?>
    <p><?= tm('ANOUK writes an R script with the attributes of this study. <a href="%s">Define the attributes</a> first.', url('study.attributes', ['id' => $study['id']])) ?></p>
    <p class="small muted"><?= te('Or upload a cbcTools design of your own right away; the attributes are then taken from the file.') ?></p>
  <?php else: ?>
  <p class="small"><?= tm('ANOUK writes an R script that builds the choice sets with <code>cbcTools</code> and saves them as CSV. Run it in R, then upload the CSV.') ?></p>
  <form method="get" action="<?= h(base_path()) ?>/index.php" class="form">
    <input type="hidden" name="r" value="study.rscript"><input type="hidden" name="id" value="<?= (int)$study['id'] ?>">
    <label><?= te('Method') ?>
      <select name="method"><?php foreach (RScript::METHODS as $k => $label): ?><option value="<?= h($k) ?>"><?= te($label) ?></option><?php endforeach; ?></select>
    </label>
    <div class="row3">
      <label><?= te('Versions') ?> <input type="number" name="n_resp" value="300" min="1" max="10000" required></label>
      <label><?= te('Sets per version') ?> <input type="number" name="n_q" value="12" min="1" max="50" required></label>
      <label><?= te('Alternatives per set') ?> <input type="number" name="n_alts" value="3" min="2" max="10" required></label>
    </div>
    <label><?= te('Seed (empty = random)') ?> <input name="seed" inputmode="numeric" pattern="\d{1,9}"></label>
    <button class="btn btn-primary" type="submit"><?= te('Download R script') ?></button>
  </form>
  <p class="small muted mt"><?= te('Versions are different questionnaires. ANOUK spreads them evenly; with more respondents than versions, each version is used several times.') ?></p>
  <?php endif; ?>
</section>

<section class="panel">
  <h2><?= te('2. Upload the CSV') ?></h2>
  <p class="small"><?= tm('Output of <code>cbcTools</code> (columns <code>respID</code>, <code>qID</code>, <code>altID</code> and one column per attribute) or any other design in long format:') ?></p>
<pre class="sample">version,task,alt,Price,Brand
1,1,1,299 €,A
1,1,2,349 €,B
…</pre>
  <p class="small muted"><?= tm('<code>profileID</code>, <code>obsID</code>, <code>blockID</code> and <code>choice</code> are ignored, no-choice rows removed.') ?>
    <?= $attrs ? te('Column names and levels must match the defined attributes.') : te('The attributes are taken from the file.') ?></p>
  <form method="post" enctype="multipart/form-data" action="<?= h(url('study.upload', ['id' => $study['id']])) ?>" class="form">
    <?= csrf_field() ?>
    <label><?= te('File (CSV with comma, semicolon or tab)') ?> <input type="file" name="design" accept=".csv,.txt,text/csv" required></label>
    <details class="help">
      <summary><?= te('Assign columns yourself') ?></summary>
      <label><?= te('Column for the version') ?> <input name="col_version" placeholder="<?= te('automatic') ?>"></label>
      <label><?= te('Column for the task') ?> <input name="col_task" placeholder="<?= te('automatic') ?>"></label>
      <label><?= te('Column for the alternative') ?> <input name="col_alt" placeholder="<?= te('automatic') ?>"></label>
      <label><?= te('Ignore columns (comma-separated)') ?> <input name="col_ignore" placeholder="D_eff"></label>
    </details>
    <button class="btn btn-primary mt" type="submit"><?= te('Upload and check') ?></button>
  </form>
</section>
</div>

<?php if ($attrs): ?>
<details class="help panel section">
  <summary><?= te('Without R: generate a simple design in ANOUK') ?></summary>
  <p class="small"><?= te('For tests, or when R is not at hand. For the actual study, cbcTools is preferable.') ?></p>
  <form method="post" action="<?= h(url('study.generate', ['id' => $study['id']])) ?>" class="form">
    <?= csrf_field() ?>
    <label><?= te('Method') ?>
      <select name="method"><?php foreach (Design::METHODS as $k => $label): ?><option value="<?= h($k) ?>"><?= te($label) ?></option><?php endforeach; ?></select>
    </label>
    <div class="row3">
      <label><?= te('Versions') ?> <input type="number" name="versions" value="300" min="1" max="10000" required></label>
      <label><?= te('Tasks per version') ?> <input type="number" name="tasks" value="12" min="1" max="50" required></label>
      <label><?= te('Alternatives per task') ?> <input type="number" name="alts" value="3" min="2" max="10" required></label>
    </div>
    <label><?= te('Seed (empty = random)') ?> <input name="seed" inputmode="numeric" pattern="\d{1,9}"></label>
    <button class="btn" type="submit"><?= te('Generate in ANOUK') ?></button>
  </form>
</details>
<?php endif; ?>
<?php endif; ?>
