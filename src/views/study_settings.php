<?php defined('ANOUK_ROOT') or exit; ?>
<?php $pageTitle = t('Settings') . ': ' . $study['title']; ?>
<p class="muted small"><a href="<?= h(url('study', ['id' => $study['id']])) ?>">← <?= h($study['title']) ?></a></p>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" action="<?= h(url('study.settings', ['id' => $study['id']])) ?>" class="panel form">
  <?= csrf_field() ?>
  <fieldset class="first">
    <legend><?= te('Study') ?></legend>
    <label><?= te('Title') ?> <input name="title" value="<?= h($study['title']) ?>" required maxlength="255"></label>
  </fieldset>

  <fieldset>
    <legend><?= te('Respondent pages') ?></legend>
    <label><?= te('Language') ?>
      <select name="lang"><?php foreach (anouk_langs() as $code => $name): ?><option value="<?= h($code) ?>"<?= $s['lang'] === $code ? ' selected' : '' ?>><?= h($name) ?></option><?php endforeach; ?></select>
      <small><?= te('Fixed texts such as buttons and messages follow this language.') ?></small>
    </label>
    <label><?= te('Question above each task') ?>
      <input name="question" value="<?= h($s['question']) ?>" maxlength="500" placeholder="<?= h(Survey::t($s['lang'], 'question')) ?>">
      <small><?= te('Leave empty for the default text of the chosen language.') ?></small>
    </label>
    <label><?= te('Introduction before the first task (optional)') ?>
      <textarea name="intro" rows="5"><?= h($s['intro']) ?></textarea>
      <small><?= te('Plain text; blank lines separate paragraphs. Leave empty to start with the first task.') ?></small>
    </label>
    <label class="check"><input type="checkbox" name="none_option" value="1"<?= $s['none_option'] ? ' checked' : '' ?>> <?= te('Offer a none option') ?></label>
    <label><?= te('Label of the none option') ?>
      <input name="none_label" value="<?= h($s['none_label']) ?>" maxlength="190" placeholder="<?= h(Survey::t($s['lang'], 'none')) ?>">
    </label>
    <label class="check"><input type="checkbox" name="randomize_attributes" value="1"<?= $s['randomize_attributes'] ? ' checked' : '' ?>> <?= te('Random attribute order per respondent (the same for all of their tasks)') ?></label>
    <label class="check"><input type="checkbox" name="show_progress" value="1"<?= $s['show_progress'] ? ' checked' : '' ?>> <?= te('Show progress') ?></label>
  </fieldset>

  <fieldset>
    <legend><?= te('Return to LimeSurvey') ?></legend>
    <label><?= te('Return URL after completion') ?>
      <input name="return_url" value="<?= h($s['return_url']) ?>" placeholder="https://survey.example.org/index.php/123456?token={ID}&amp;lang=de">
      <small><?= tm('Placeholders: <code>{ID}</code> = the respondent’s ID, <code>{STATUS}</code> = <code>complete</code>. To return into the same survey, LimeSurvey needs a participant list with token-based response persistence.') ?></small>
    </label>
    <label><?= te('Error URL (optional)') ?>
      <input name="error_url" value="<?= h($s['error_url']) ?>" placeholder="https://survey.example.org/index.php/123456?token={ID}&amp;anouk={STATUS}">
      <small><?= tm('Used when the study is not in the field (<code>{STATUS}</code> = <code>closed</code>) or the ID is invalid (<code>invalid_id</code>). Empty: a message page.') ?></small>
    </label>
  </fieldset>

  <fieldset>
    <legend><?= te('Versions and tests') ?></legend>
    <div class="row2">
      <label><?= te('Release the version of a drop-out after (minutes)') ?>
        <input type="number" name="release_minutes" value="<?= (int)$s['release_minutes'] ?>" min="5" max="10080">
      </label>
      <label><?= te('Prefix of test IDs') ?>
        <input name="test_prefix" value="<?= h($s['test_prefix']) ?>" maxlength="20">
        <small><?= te('Test IDs do not count for the version assignment and are left out of the export unless chosen.') ?></small>
      </label>
    </div>
  </fieldset>

  <div class="actions"><button class="btn btn-primary" type="submit"><?= te('Save') ?></button> <a class="btn" href="<?= h(url('study', ['id' => $study['id']])) ?>"><?= te('Cancel') ?></a></div>
</form>
