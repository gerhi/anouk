<?php
/**
 * Checks the translations: every string passed to t(), te() or tm() (and the method
 * labels) must have an entry in each app/lang/*.json, and no entry may be unused.
 *   php tests/i18n.php          report
 *   php tests/i18n.php --keys   print all keys as JSON (for adding a language)
 */
declare(strict_types=1);

PHP_SAPI === 'cli' or exit; // never run over the web

$root = dirname(__DIR__);
require $root . '/app/bootstrap.php';

$files = array_merge(
    glob($root . '/app/*.php'),
    glob($root . '/app/views/*.php'),
    glob($root . '/app/views/survey/*.php'),
    [$root . '/index.php', $root . '/install.php']
);
$keys = [];
foreach ($files as $file) {
    $tokens = token_get_all((string)file_get_contents($file));
    $n = count($tokens);
    for ($i = 0; $i < $n; $i++) {
        $tok = $tokens[$i];
        if (!is_array($tok) || $tok[0] !== T_STRING || !in_array($tok[1], ['t', 'te', 'tm'], true)) {
            continue;
        }
        $prev = $tokens[$i - 1] ?? null;
        if (is_array($prev) && in_array($prev[0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION], true)) {
            continue; // method calls such as Survey::t()
        }
        $j = $i + 1;
        while (is_array($tokens[$j] ?? null) && $tokens[$j][0] === T_WHITESPACE) {
            $j++;
        }
        if (($tokens[$j] ?? null) !== '(') {
            continue;
        }
        $j++;
        while (is_array($tokens[$j] ?? null) && $tokens[$j][0] === T_WHITESPACE) {
            $j++;
        }
        $arg = $tokens[$j] ?? null;
        if (is_array($arg) && $arg[0] === T_CONSTANT_ENCAPSED_STRING) {
            $keys[eval('return ' . $arg[1] . ';')] = basename($file);
        }
    }
}
foreach (array_merge(array_values(Design::METHODS), array_values(RScript::METHODS)) as $label) {
    $keys[$label] = 'METHODS';
}
ksort($keys);

if (in_array('--keys', $argv, true)) {
    echo json_encode(array_fill_keys(array_keys($keys), ''), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), "\n";
    exit(0);
}

$fail = 0;
foreach (array_keys(ANOUK_LANGS) as $lang) {
    if ($lang === 'en') {
        continue;
    }
    $dict = json_decode((string)file_get_contents($root . "/app/lang/$lang.json"), true);
    if (!is_array($dict)) {
        echo "app/lang/$lang.json is not valid JSON\n";
        exit(1);
    }
    $missing = array_diff_key($keys, array_filter($dict, fn($v) => $v !== ''));
    $unused = array_diff_key($dict, $keys);
    foreach ($missing as $k => $f) {
        echo "[$lang] missing: $k   ($f)\n";
    }
    foreach ($unused as $k => $_) {
        echo "[$lang] unused: $k\n";
    }
    // placeholders must survive the translation
    foreach ($dict as $k => $v) {
        preg_match_all('/%(\d+\$)?[sd]/', (string)$k, $a);
        preg_match_all('/%(\d+\$)?[sd]/', (string)$v, $b);
        $pa = $a[0]; $pb = $b[0];
        sort($pa); sort($pb);
        if ($v !== '' && $pa !== $pb) {
            echo "[$lang] placeholders differ: $k\n";
            $fail++;
        }
    }
    $fail += count($missing) + count($unused);
    echo "[$lang] " . count($keys) . ' keys, ' . count($missing) . ' missing, ' . count($unused) . " unused\n";
}
exit($fail ? 1 : 0);
