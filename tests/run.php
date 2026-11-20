<?php
/**
 * ANOUK test suite (no dependencies). Uses an in-memory SQLite database.
 *   php tests/run.php
 */
declare(strict_types=1);

PHP_SAPI === 'cli' or exit; // never run over the web

putenv('ANOUK_CONFIG=' . __DIR__ . '/config.test.php');
putenv('ANOUK_MIGRATIONS=' . __DIR__ . '/migrations'); // SQLite variants of sql/migrations
require dirname(__DIR__) . '/src/bootstrap.php';

$pdo = Db::connect('sqlite::memory:', null, null);
$pdo->exec(Db::sql((string)file_get_contents(__DIR__ . '/schema.sqlite.sql')));
Db::setPdo($pdo);
Migrator::markInstalled();
i18n_set('de'); // messages are checked in German; this also exercises the translation path

$failed = 0;
$passed = 0;
function check(string $name, bool $ok, string $detail = ''): void
{
    global $failed, $passed;
    if ($ok) {
        $passed++;
        echo "  ok   $name\n";
    } else {
        $failed++;
        echo "  FAIL $name" . ($detail !== '' ? " – $detail" : '') . "\n";
    }
}

echo "Attribute editor\n";
[$attrs, $errors] = Design::parseAttributeText("Preis: 249 € | 299 € | 349 €\n# comment\nMarke: A | B\n");
check('parses two attributes', count($attrs) === 2 && !$errors);
check('keeps level order', $attrs[0]['levels'] === ['249 €', '299 €', '349 €']);
[, $errors] = Design::parseAttributeText("Preis 249 | 299\nMarke: A\nMarke: A | A");
check('reports missing colon, single level, duplicates', count($errors) >= 3, implode(' / ', $errors));

echo "CSV reading\n";
$csv = Design::readCsv("\xEF\xBB\xBFversion;task;alt;Preis\r\n1;1;1;\"299 €\"\r\n\r\n1;1;2;349 €\r\n");
check('detects semicolon and strips BOM', $csv['header'] === ['version', 'task', 'alt', 'Preis']);
check('skips blank lines, keeps line numbers', count($csv['rows']) === 2 && $csv['rows'][1]['line'] === 4);

echo "Generator\n";
$counts = [3, 3, 4];
$a = Design::generate($counts, 20, 10, 3, 'minoverlap', 42);
$b = Design::generate($counts, 20, 10, 3, 'minoverlap', 42);
check('same seed gives same design', $a === $b);
check('row count V×T×J', count($a) === 20 * 10 * 3);
$dupAlt = false;
$dupTask = false;
$overlap = 0;
$byTask = [];
foreach ($a as $r) {
    $byTask[$r['v']][$r['t']][] = $r['lv'];
}
foreach ($byTask as $tasks) {
    $sigs = [];
    foreach ($tasks as $alts) {
        $s = array_map(fn($x) => implode(',', $x), $alts);
        if (count(array_unique($s)) < count($s)) {
            $dupAlt = true;
        }
        sort($s);
        $k = implode('|', $s);
        if (isset($sigs[$k])) {
            $dupTask = true;
        }
        $sigs[$k] = 1;
        foreach (array_keys($counts) as $k2) {
            $col = array_column($alts, $k2);
            if (count(array_unique($col)) < count($col)) {
                $overlap++;
            }
        }
    }
}
check('no identical alternatives in a task', !$dupAlt);
check('no repeated task within a version', !$dupTask);
check('minimal overlap: no repeated level when J ≤ levels', $overlap === 0, "$overlap overlaps");
$freq = array_fill(0, 4, 0);
foreach ($a as $r) {
    $freq[$r['lv'][2]]++;
}
check('level frequencies balanced (±1)', max($freq) - min($freq) <= 1, implode(',', $freq));
$rnd = Design::generate([2, 2], 5, 3, 2, 'random', 7);
check('random method works on small designs', count($rnd) === 30);
try {
    Design::generate([2], 1, 3, 2, 'random', 1);
    check('impossible design is rejected', false);
} catch (DesignError $e) {
    check('impossible design is rejected', true);
}

echo "Import and validation\n";
$uid = Auth::createUser('Admin', 'admin@example.org', 'admin', 'secretsecret');
$sid = Study::create($uid, 'Test', 'test-study');
$study = Db::one('SELECT * FROM anouk_studies WHERE id = ?', [$sid]);
$good = "respID,qID,altID,obsID,Preis,Marke\n";
foreach ([[1, 1], [1, 2], [2, 1], [2, 2]] as [$v, $t]) {
    $good .= "$v,$t,1,0,299 €,A\n$v,$t,2,0," . ($t === 1 ? '349 €' : '249 €') . ",B\n";
}
$r = Design::import($study, $good, [], 'good.csv');
check('imports cbcTools-style columns and creates attributes', $r['ok'], implode(' / ', $r['errors']));
$meta = Study::meta(Db::one('SELECT * FROM anouk_studies WHERE id = ?', [$sid]));
check('stores dimensions 2×2×2', ($meta['versions'] ?? 0) === 2 && $meta['tasks'] === 2 && $meta['alts'] === 2);
$at = Study::attributes($sid);
check('levels sorted naturally', array_column($at[0]['levels'], 'label') === ['249 €', '299 €', '349 €']);

$bad = "version,task,alt,Preis,Marke\n1,1,1,299 €,A\n1,1,2,299 €,A\n1,2,1,999 €,B\n1,2,2,249 €,B\n2,1,1,299 €,A\n";
$r = Design::import($study, $bad);
check('unknown level is reported with line', !$r['ok'] && str_contains(implode(' ', $r['errors']), 'Zeile 4'), implode(' / ', $r['errors']));
$bad2 = "version,task,alt,Preis,Marke\n1,1,1,299 €,A\n1,1,2,299 €,A\n";
$r = Design::import($study, $bad2);
check('identical alternatives are rejected', !$r['ok'] && str_contains(implode(' ', $r['errors']), 'identisch'));
$bad3 = "version,task,alt,Preis,Marke\n1,1,1,299 €,A\n1,1,2,249 €,B\n2,1,1,299 €,A\n";
$r = Design::import($study, $bad3);
check('unequal alternatives per task are rejected', !$r['ok']);
$bad4 = "version,task,alt,Preis\n1,1,1,299 €\n1,1,2,249 €\n";
$r = Design::import($study, $bad4);
check('missing attribute column is reported', !$r['ok'] && str_contains(implode(' ', $r['errors']), 'Marke'));
$renum = "version,task,alt,Preis,Marke\n5,3,1,299 €,A\n5,3,2,249 €,B\n";
$r = Design::import($study, $renum);
check('renumbers versions and tasks from 1', $r['ok'] && str_contains(implode(' ', $r['info']), 'neu nummeriert'));

echo "Version assignment\n";
Design::generateForStudy(Db::one('SELECT * FROM anouk_studies WHERE id = ?', [$sid]), 3, 2, 2, 'minoverlap', 1);
$assigned = [];
for ($i = 0; $i < 6; $i++) {
    $v = Survey::pickVersion($sid, 3, 60);
    $assigned[] = $v;
    Db::insert('anouk_respondents', ['study_id' => $sid, 'ext_id' => "p$i", 'version' => $v, 'status' => 'active', 'is_test' => 0,
        'attr_order' => null, 'started_at' => now(), 'last_seen_at' => now(), 'completed_at' => null]);
}
check('versions are filled evenly', $assigned === [1, 2, 3, 1, 2, 3], implode(',', $assigned));
Db::exec("UPDATE anouk_respondents SET last_seen_at = ? WHERE ext_id = 'p0'", [now(-7200)]);
check('version of a drop-out is released after the window', Survey::pickVersion($sid, 3, 60) === 1);
Db::insert('anouk_respondents', ['study_id' => $sid, 'ext_id' => 'test_1', 'version' => 1, 'status' => 'complete', 'is_test' => 1,
    'attr_order' => null, 'started_at' => now(), 'last_seen_at' => now(), 'completed_at' => now()]);
check('test IDs do not count', Survey::pickVersion($sid, 3, 60) === 1);

echo "Return URL\n";
check('placeholders {ID} and {STATUS}', Survey::fillUrl('https://x.org/?t={ID}&s={STATUS}', 'R1', 'closed') === 'https://x.org/?t=R1&s=closed');
check('placeholders are filled and encoded', Survey::fillUrl('https://x.org/i.php/1?token={id}&s={status}', 'a b', 'complete') === 'https://x.org/i.php/1?token=a%20b&s=complete');
check('ID validation', Survey::validId('Ab_12-x.y') && !Survey::validId('') && !Survey::validId('a b') && !Survey::validId('<x>'));


echo "cbcTools script\n";
$script = RScript::build(['slug' => 'akku', 'title' => 'Akku'], [
    ['name' => 'Preis', 'levels' => [['label' => '249 €'], ['label' => '299 €']]],
    ['name' => '2. Größe', 'levels' => [['label' => 'sagt "hallo"'], ['label' => 'a\\b']]],
    ['name' => 'preis', 'levels' => [['label' => 'x'], ['label' => 'y']]],
], 300, 12, 3, 'minoverlap', 42);
check('non-ASCII as \u escapes', str_contains($script, '"249 ' . '\\' . 'u20ac"') && !str_contains($script, '€'));
check('quotes and backslashes escaped', str_contains($script, '"sagt \"hallo\""') && str_contains($script, '"a\\\\b"'));
check('R names are syntactic and unique', str_contains($script, 'a_2_groesse') && str_contains($script, 'preis_2'));
$script2 = RScript::build(['slug' => 'akku', 'title' => 'Akku'], [['name' => 'Preis pro Monat', 'var_name' => 'price', 'levels' => [['label' => '1'], ['label' => '2']]]], 10, 2, 2, 'random', 1);
check('script uses stored variable names as columns', str_contains($script2, '  price = c(') && !str_contains($script2, 'names(d)'));
check('design parameters in script', str_contains($script, 'method   = "minoverlap"') && str_contains($script, 'n_resp   = 300') && str_contains($script, 'set.seed(42)'));

echo "cbcTools no_choice rows\n";
$sid3 = Study::create($uid, 'NoChoice', 'no-choice');
$nc = "profileID,respID,qID,altID,obsID,price,no_choice\n1,1,1,1,1,1,0\n2,1,1,2,1,2,0\n0,1,1,3,1,0,1\n3,1,2,1,2,3,0\n1,1,2,2,2,1,0\n0,1,2,3,2,0,1\n";
$r = Design::import(Db::one('SELECT * FROM anouk_studies WHERE id = ?', [$sid3]), $nc);
$m3 = Study::meta(Db::one('SELECT * FROM anouk_studies WHERE id = ?', [$sid3]));
check('no-choice rows removed, two alternatives remain', $r['ok'] && ($m3['alts'] ?? 0) === 2 && str_contains(implode(' ', $r['info']), 'No-Choice'), implode(' / ', $r['errors']));

echo "DiGA example\n";
$sid4 = Study::create($uid, 'DiGA', 'diga');
$r = Design::import(Db::one('SELECT * FROM anouk_studies WHERE id = ?', [$sid4]), (string)file_get_contents(dirname(__DIR__) . '/docs/examples/diga/diga_design.csv'), [], 'diga_design.csv');
$m4 = Study::meta(Db::one('SELECT * FROM anouk_studies WHERE id = ?', [$sid4]));
check('example CSV imports: 200 × 8 × 3', $r['ok'] && $m4['versions'] === 200 && $m4['tasks'] === 8 && $m4['alts'] === 3, implode(' / ', $r['errors']));
check('example has five attributes', count(Study::attributes($sid4)) === 5);

echo "Installer\n";
// Installer is loaded by bootstrap
$tmpCfg = sys_get_temp_dir() . '/anouk_cfg_' . bin2hex(random_bytes(4)) . '.php';
$old = getenv('ANOUK_CONFIG');
putenv('ANOUK_CONFIG=' . $tmpCfg);
[$manual, $code] = Installer::writeConfig('mysql:host=localhost;port=3306;dbname=x;charset=utf8mb4', 'u', "p'w\\");
$cfg = require $tmpCfg;
check('config written and readable', $manual === null && $cfg['db']['pass'] === "p'w\\" && $cfg['db']['user'] === 'u');
check('emergency code verifies', Installer::checkResetCode($code) && !Installer::checkResetCode('falsch') && !Installer::checkResetCode(''));
check('config can be deleted', Installer::deleteConfig() && !is_file($tmpCfg));
check('missing config: code check fails safely', !Installer::checkResetCode($code));
putenv('ANOUK_CONFIG=' . $old);
$t = Installer::testConnection('mysql:host=127.0.0.1;port=1;dbname=x', 'u', 'p');
check('unreachable server reported in plain language', !$t['ok'] && str_contains($t['lines'][0][1], 'Keine Verbindung'), $t['lines'][0][1] ?? '');

echo "CSV cells in the Excel export\n";
check('formulas are neutralised', Export::safeCell('=HYPERLINK("x")') === "'=HYPERLINK(\"x\")" && Export::safeCell('@SUM(1)') === "'@SUM(1)" && Export::safeCell('+1+1') === "'+1+1");
check('numbers and ordinary text stay', Export::safeCell('-5') === '-5' && Export::safeCell('299 €') === '299 €' && Export::safeCell(3) === 3 && Export::safeCell('') === '');

echo "Exports\n";
$sidE = Study::create($uid, 'Export', 'export-study');
$csvE = "respID,qID,altID,Preis pro Monat,Marke\n1,1,1,10 €,A\n1,1,2,20 €,B\n1,2,1,20 €,A\n1,2,2,10 €,B\n";
$rE = Design::import(Db::one('SELECT * FROM {studies} WHERE id = ?', [$sidE]), $csvE);
check('design for export imported', $rE['ok'], implode(' / ', $rE['errors']));
Db::exec("UPDATE {studies} SET settings = ? WHERE id = ?", [json_enc(array_merge(Study::settings(Db::one('SELECT * FROM {studies} WHERE id = ?', [$sidE])), ['none_option' => true])), $sidE]);
$stE = fn() => Db::one('SELECT * FROM {studies} WHERE id = ?', [$sidE]);
$attrsE = Study::attributes($sidE);
check('variable names derived from the file columns', array_column($attrsE, 'var_name') === ['preis_pro_monat', 'marke']);
check('invalid variable names are refused', (bool)Study::saveVarNames($sidE, [$attrsE[0]['id'] => 'preis-eur']) && (bool)Study::saveVarNames($sidE, [$attrsE[0]['id'] => 'choice']) && (bool)Study::saveVarNames($sidE, [$attrsE[0]['id'] => 'marke']));
check('variable names can be changed', Study::saveVarNames($sidE, [$attrsE[0]['id'] => 'price', $attrsE[1]['id'] => 'brand']) === [] && array_column(Study::attributes($sidE), 'var_name') === ['price', 'brand']);
$rid1 = Db::insert('{respondents}', ['study_id' => $sidE, 'ext_id' => 'P1', 'version' => 1, 'status' => 'complete', 'is_test' => 0, 'started_at' => '2026-11-19 08:00:00', 'last_seen_at' => '2026-11-19 08:05:00', 'completed_at' => '2026-11-19 08:05:00']);
Db::insert('{answers}', ['respondent_id' => $rid1, 'task' => 1, 'choice' => 2, 'rt_ms' => 1500, 'screen_w' => 1280, 'answered_at' => '2026-11-19 08:01:00']);
Db::insert('{answers}', ['respondent_id' => $rid1, 'task' => 2, 'choice' => 0, 'rt_ms' => 900, 'screen_w' => 1280, 'answered_at' => '2026-11-19 08:02:00']);
$rid2 = Db::insert('{respondents}', ['study_id' => $sidE, 'ext_id' => 'P2', 'version' => 1, 'status' => 'active', 'is_test' => 0, 'started_at' => '2026-11-19 09:00:00', 'last_seen_at' => '2026-11-19 09:00:00']);
Db::insert('{answers}', ['respondent_id' => $rid2, 'task' => 1, 'choice' => 1, 'rt_ms' => 700, 'screen_w' => 400, 'answered_at' => '2026-11-19 09:01:00']);
$long = iterator_to_array(Export::longRows($stE(), true, false), false);
check('long: header with variable names and none column', $long[0] === ['id', 'obs_id', 'version', 'task', 'alt', 'price', 'brand', 'none', 'choice', 'rt_ms', 'screen_w', 'answered_at', 'status', 'is_test'], json_encode($long[0]));
check('long: 2 tasks × (2 alternatives + none) rows', count($long) === 7);
check('long: chosen alternative and ISO time', $long[2] === ['P1', 1, 1, 1, 2, '20 €', 'B', 0, 1, 1500, 1280, '2026-11-19T08:01:00Z', 'complete', 0], json_encode($long[2]));
check('long: none chosen in task 2', $long[6][7] === 1 && $long[6][8] === 1 && $long[4][8] === 0);
$wide = iterator_to_array(Export::wideRows($stE(), false, false), false);
check('wide: header', $wide[0] === ['id', 'version', 'status', 'is_test', 'n_answered', 'started_at', 'completed_at', 'choice_t01', 'choice_t02', 'rt_t01', 'rt_t02'], json_encode($wide[0]));
check('wide: completed respondent', $wide[1] === ['P1', 1, 'complete', 0, 2, '2026-11-19T08:00:00Z', '2026-11-19T08:05:00Z', 2, 0, 1500, 900], json_encode($wide[1]));
check('wide: drop-out has empty cells', $wide[2] === ['P2', 1, 'active', 0, 1, '2026-11-19T09:00:00Z', '', 1, '', 700, ''], json_encode($wide[2]));
check('wide: completed only', count(iterator_to_array(Export::wideRows($stE(), true, false), false)) === 2);
$cb = iterator_to_array(Export::codebookRows($stE()), false);
$cbVars = array_column($cb, 0);
check('codebook covers every long and wide column', !array_diff(array_merge($long[0], $wide[0]), $cbVars) && $cb[0] === ['variable', 'label', 'type', 'values'], json_encode(array_diff(array_merge($long[0], $wide[0]), $cbVars)));
$cbPrice = $cb[array_search('price', $cbVars, true)];
check('codebook lists label and levels', $cbPrice[1] === 'Preis pro Monat' && $cbPrice[3] === '10 € | 20 €', json_encode($cbPrice));
$fh = fopen('php://memory', 'w+');
Export::write($fh, [['a', '=1+1'], ['ü', '-5']], ';');
rewind($fh);
check('Excel style: BOM, semicolons, formulas neutralised', stream_get_contents($fh) === "\xEF\xBB\xBFa;'=1+1\nü;-5\n");
check('ISO time helper', Export::iso('2026-11-19 08:01:00') === '2026-11-19T08:01:00Z' && Export::iso(null) === '' && Export::iso('') === '');

echo "Table prefix\n";
check('tables get the prefix', Db::sql('SELECT * FROM {users} JOIN {studies}', 'x_') === 'SELECT * FROM x_users JOIN x_studies');
check('{prefix} for index names, unknown braces untouched', Db::sql('CREATE INDEX {prefix}ix ON {answers} ({foo})', 'p_') === 'CREATE INDEX p_ix ON p_answers ({foo})');
check('prefix validation', Db::validPrefix('anouk_') && Db::validPrefix('a2_') && !Db::validPrefix('2a') && !Db::validPrefix('A_') && !Db::validPrefix('a-b') && !Db::validPrefix(str_repeat('a', 21)));

echo "Variable names\n";
$used = [];
check('derived in snake_case', Study::varName('Eigenanteil pro Monat', $used) === 'eigenanteil_pro_monat');
check('umlauts and digits', Study::varName('2. Größe', $used) === 'a_2_groesse');
check('reserved names get a suffix', Study::varName('Version', $used) === 'version_attr' && Study::varName('choice', $used) === 'choice_attr');
check('duplicates get a number', Study::varName('Eigenanteil pro Monat', $used) === 'eigenanteil_pro_monat_2');
check('validation', Study::validVarName('preis_eur') && !Study::validVarName('Preis') && !Study::validVarName('1x') && !Study::validVarName('task'));

echo "Login throttling\n";
$t0 = 1_800_000_000;
check('delays 0,0,0,0,1,2,4,8,15,15', array_map([Throttle::class, 'delayMinutes'], range(1, 10)) === [0, 0, 0, 0, 1, 2, 4, 8, 15, 15]);
for ($i = 0; $i < 4; $i++) { Throttle::fail('a@example.org', '1.2.3.4', $t0); }
check('4 failures: no wait', Throttle::wait('a@example.org', '1.2.3.4', $t0) === 0);
Throttle::fail('A@Example.org', '1.2.3.4', $t0);
check('5th failure: 1 minute', Throttle::wait('a@example.org', '1.2.3.4', $t0) === 60 && Throttle::wait('a@example.org', '1.2.3.4', $t0 + 61) === 0);
Throttle::fail('a@example.org', '1.2.3.4', $t0 + 61);
check('6th failure: 2 minutes', Throttle::wait('a@example.org', '1.2.3.4', $t0 + 61) === 120);
check('other client is not blocked', Throttle::wait('a@example.org', '5.6.7.8', $t0 + 61) === 0);
check('other account is not blocked', Throttle::wait('b@example.org', '1.2.3.4', $t0 + 61) === 0);
check('only hashes are stored', !Db::val("SELECT COUNT(*) FROM {login_attempts} WHERE email_hash LIKE '%example%' OR ip_hash LIKE '%1.2.3%'"));
Throttle::clear('a@example.org', '1.2.3.4');
check('successful login clears', Throttle::wait('a@example.org', '1.2.3.4', $t0 + 61) === 0);
Throttle::fail('c@example.org', '9.9.9.9', $t0 - 3 * 86400);
Throttle::fail('c@example.org', '9.9.9.9', $t0 + 100);
check('entries older than 24 hours are removed', (int)Db::val('SELECT COUNT(*) FROM {login_attempts}') === 1);

echo "Migration from ANOUK 1.0.1\n";
$legacy = Db::connect('sqlite::memory:', null, null);
$old = (string)shell_exec('git -C ' . escapeshellarg(dirname(__DIR__)) . ' show v1.0.1:tests/schema.sqlite.sql 2>/dev/null');
if ($old === '') {
    echo "  skip (tag v1.0.1 not available)\n";
} else {
    $keepPdo = Db::pdo();
    $legacy->exec($old);
    Db::setPdo($legacy);
    Db::setPrefix('anouk_');
    Db::exec("INSERT INTO anouk_users (email, name, password_hash, role, language, active, must_change_password, created_at) VALUES ('x@example.org', 'X', 'h', 'admin', 'de', 1, 0, '2026-01-01 00:00:00')");
    Db::exec("INSERT INTO anouk_studies (owner_id, slug, title, status, created_at, updated_at) VALUES (1, 'alt', 'Alt', 'field', '2026-01-01 00:00:00', '2026-01-01 00:00:00')");
    Db::exec("INSERT INTO anouk_attributes (study_id, position, name) VALUES (1, 1, 'Preis pro Monat'), (1, 2, 'Status')");
    Db::exec("INSERT INTO anouk_respondents (study_id, ext_id, version, status, is_test, started_at, last_seen_at) VALUES (1, 'R1', 1, 'complete', 0, '2026-01-01 00:00:00', '2026-01-01 00:00:00')");
    check('1.0.1 database is version 1 with a pending update', Migrator::dbVersion(true) === 1 && Migrator::pending() && Migrator::pendingFiles() === ['002_variable_names_throttle.sql']);
    $ran = Migrator::migrate();
    check('migration runs and records version 2', $ran === ['002_variable_names_throttle.sql'] && Migrator::dbVersion(true) === 2 && !Migrator::pending());
    check('variable names filled in', array_column(Study::attributes(1), 'var_name') === ['preis_pro_monat', 'status_attr']);
    check('data kept', Db::val("SELECT ext_id FROM {respondents}") === 'R1' && Db::val('SELECT title FROM {studies}') === 'Alt');
    check('secret created', strlen(Migrator::secret()) === 64);
    Db::exec("UPDATE {meta} SET value = '1' WHERE name = 'schema_version'");
    check('migration can run again (already applied parts are skipped)', Migrator::migrate() === ['002_variable_names_throttle.sql'] && Migrator::dbVersion(true) === 2);
    Db::setPdo($keepPdo);
    Db::setPrefix('anouk_');
    Migrator::dbVersion(true);
}

echo "\n$passed passed, $failed failed\n";
exit($failed ? 1 : 0);
