<?php
/**
 * ANOUK test suite (no dependencies). Uses an in-memory SQLite database.
 *   php tests/run.php
 */
declare(strict_types=1);

PHP_SAPI === 'cli' or exit; // never run over the web

putenv('ANOUK_CONFIG=' . __DIR__ . '/config.test.php');
require dirname(__DIR__) . '/app/bootstrap.php';

$pdo = Db::connect('sqlite::memory:', null, null);
$pdo->exec((string)file_get_contents(__DIR__ . '/schema.sqlite.sql'));
Db::setPdo($pdo);
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
check('placeholders are filled and encoded', Survey::fillUrl('https://x.org/i.php/1?token={id}&s={status}', 'a b', 'complete') === 'https://x.org/i.php/1?token=a%20b&s=complete');
check('ID validation', Survey::validId('Ab_12-x.y') && !Survey::validId('') && !Survey::validId('a b') && !Survey::validId('<x>'));


echo "cbcTools script\n";
$script = RScript::build(['slug' => 'akku', 'title' => 'Akku'], [
    ['name' => 'Preis', 'levels' => [['label' => '249 €'], ['label' => '299 €']]],
    ['name' => '2. Größe', 'levels' => [['label' => 'sagt "hallo"'], ['label' => 'a\\b']]],
    ['name' => 'preis', 'levels' => [['label' => 'x'], ['label' => 'y']]],
], 300, 12, 3, 'minoverlap', 42);
check('non-ASCII as \u escapes', str_contains($script, '"249 ' . '\\' . 'u20ac"') && str_contains($script, '"2. Gr' . '\\' . 'u00f6' . '\\' . 'u00dfe"') && !str_contains($script, '€'));
check('quotes and backslashes escaped', str_contains($script, '"sagt \"hallo\""') && str_contains($script, '"a\\\\b"'));
check('R names are syntactic and unique', str_contains($script, 'a_2_groesse') && str_contains($script, 'preis_2'));
check('design parameters in script', str_contains($script, 'method   = "minoverlap"') && str_contains($script, 'n_resp   = 300') && str_contains($script, 'set.seed(42)'));

echo "cbcTools no_choice rows\n";
$sid3 = Study::create($uid, 'NoChoice', 'no-choice');
$nc = "profileID,respID,qID,altID,obsID,price,no_choice\n1,1,1,1,1,1,0\n2,1,1,2,1,2,0\n0,1,1,3,1,0,1\n3,1,2,1,2,3,0\n1,1,2,2,2,1,0\n0,1,2,3,2,0,1\n";
$r = Design::import(Db::one('SELECT * FROM anouk_studies WHERE id = ?', [$sid3]), $nc);
$m3 = Study::meta(Db::one('SELECT * FROM anouk_studies WHERE id = ?', [$sid3]));
check('no-choice rows removed, two alternatives remain', $r['ok'] && ($m3['alts'] ?? 0) === 2 && str_contains(implode(' ', $r['info']), 'No-Choice'), implode(' / ', $r['errors']));

echo "DiGA example\n";
$sid4 = Study::create($uid, 'DiGA', 'diga');
$r = Design::import(Db::one('SELECT * FROM anouk_studies WHERE id = ?', [$sid4]), (string)file_get_contents(dirname(__DIR__) . '/examples/diga/diga_design.csv'), [], 'diga_design.csv');
$m4 = Study::meta(Db::one('SELECT * FROM anouk_studies WHERE id = ?', [$sid4]));
check('example CSV imports: 200 × 8 × 3', $r['ok'] && $m4['versions'] === 200 && $m4['tasks'] === 8 && $m4['alts'] === 3, implode(' / ', $r['errors']));
check('example has five attributes', count(Study::attributes($sid4)) === 5);

echo "Installer\n";
require_once dirname(__DIR__) . '/app/Installer.php';
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

echo "\n$passed passed, $failed failed\n";
exit($failed ? 1 : 0);
