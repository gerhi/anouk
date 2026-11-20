<?php
declare(strict_types=1);

final class DesignError extends RuntimeException
{
}

/**
 * Choice designs: parsing attribute definitions, CSV import from R,
 * validation, generation and diagnostics.
 *
 * Internal row format: ['v' => version, 't' => task, 'a' => alt, 'lv' => [level index per attribute], 'line' => csv line]
 */
final class Design
{
    public const VERSION_COLS = ['version', 'respid', 'resp_id', 'respondent', 'respondentid', 'block', 'ver'];
    public const TASK_COLS    = ['task', 'qid', 'q_id', 'question', 'set', 'choiceset', 'choice_set', 'cs'];
    public const ALT_COLS     = ['alt', 'altid', 'alt_id', 'alternative', 'option'];
    public const IGNORE_COLS  = ['profileid', 'profile_id', 'obsid', 'obs_id', 'choice', 'chosen', 'blockid', 'block_id', 'no_choice', 'prob', 'probs'];

    public const MAX_ERRORS = 50;

    // ---------------------------------------------------------------- attributes

    /**
     * Parses the attribute editor text. One attribute per line: "Name: Level 1 | Level 2 | Level 3".
     * @return array{0: list<array{name:string,levels:list<string>}>, 1: list<string>}
     */
    public static function parseAttributeText(string $text): array
    {
        $attrs = [];
        $errors = [];
        $names = [];
        foreach (preg_split('/\R/u', $text) as $i => $line) {
            $line = trim($line);
            $n = $i + 1;
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $pos = mb_strpos($line, ':');
            if ($pos === false) {
                $errors[] = t('Line %d: the colon is missing. Format: Name: Level 1 | Level 2', $n);
                continue;
            }
            $name = trim(mb_substr($line, 0, $pos));
            $levels = array_values(array_filter(array_map('trim', explode('|', mb_substr($line, $pos + 1))), fn($l) => $l !== ''));
            if ($name === '') {
                $errors[] = t('Line %d: the attribute name is missing.', $n);
                continue;
            }
            if (isset($names[mb_strtolower($name)])) {
                $errors[] = t('Line %1$d: the attribute “%2$s” exists already.', $n, $name);
            }
            $names[mb_strtolower($name)] = true;
            if (count($levels) < 2) {
                $errors[] = t('Line %1$d: “%2$s” needs at least two levels.', $n, $name);
            }
            if (count(array_unique($levels)) !== count($levels)) {
                $errors[] = t('Line %1$d: “%2$s” contains a level twice.', $n, $name);
            }
            if (mb_strlen($name) > 190) {
                $errors[] = t('Line %d: the attribute name is too long (at most 190 characters).', $n);
            }
            foreach ($levels as $l) {
                if (mb_strlen($l) > 255) {
                    $errors[] = t('Line %d: a level is too long (at most 255 characters).', $n);
                    break;
                }
            }
            $attrs[] = ['name' => $name, 'levels' => $levels];
        }
        if (!$attrs && !$errors) {
            $errors[] = t('Enter at least one attribute.');
        }
        return [$attrs, $errors];
    }

    public static function attributesToText(array $attrs): string
    {
        $lines = [];
        foreach ($attrs as $a) {
            $lines[] = $a['name'] . ': ' . implode(' | ', array_map(fn($l) => $l['label'], $a['levels']));
        }
        return implode("\n", $lines);
    }

    // ---------------------------------------------------------------- CSV import

    /** @return array{header: list<string>, rows: list<array{line:int, cells:list<string>}>} */
    public static function readCsv(string $content): array
    {
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
        }
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $firstLine = strtok($content, "\n") ?: '';
        $counts = [',' => substr_count($firstLine, ','), ';' => substr_count($firstLine, ';'), "\t" => substr_count($firstLine, "\t")];
        arsort($counts);
        $delim = (string)array_key_first($counts);
        if ($counts[$delim] === 0) {
            $delim = ',';
        }

        $fh = fopen('php://temp', 'r+');
        fwrite($fh, $content);
        rewind($fh);
        $header = null;
        $rows = [];
        $line = 0;
        while (($cells = fgetcsv($fh, 0, $delim, '"', '')) !== false) {
            $line++;
            if ($cells === [null] || (count($cells) === 1 && trim((string)$cells[0]) === '')) {
                continue;
            }
            $cells = array_map(fn($c) => trim((string)$c), $cells);
            if ($header === null) {
                $header = $cells;
                continue;
            }
            $rows[] = ['line' => $line, 'cells' => $cells];
        }
        fclose($fh);
        return ['header' => $header ?? [], 'rows' => $rows];
    }

    /**
     * Imports a design CSV into a study. If the study has no attributes yet, they are taken from the file.
     * @param array{version?:string,task?:string,alt?:string,ignore?:string} $map optional column names
     * @return array{ok:bool, errors:list<string>, info:list<string>}
     */
    public static function import(array $study, string $content, array $map = [], string $filename = ''): array
    {
        $sid = (int)$study['id'];
        $errors = [];
        $info = [];
        $csv = self::readCsv($content);
        $header = $csv['header'];
        if (!$header || !$csv['rows']) {
            return ['ok' => false, 'errors' => [t('The file is empty or contains only a header row.')], 'info' => []];
        }
        $lower = array_map(fn($h) => mb_strtolower($h), $header);

        $find = function (string $key, array $candidates) use ($map, $lower, &$errors): ?int {
            $wanted = trim($map[$key] ?? '');
            if ($wanted !== '') {
                $idx = array_search(mb_strtolower($wanted), $lower, true);
                if ($idx === false) {
                    $errors[] = t('The file has no column “%s”.', $wanted);
                    return null;
                }
                return (int)$idx;
            }
            foreach ($candidates as $c) {
                $idx = array_search($c, $lower, true);
                if ($idx !== false) {
                    return (int)$idx;
                }
            }
            $errors[] = t('No column found for “%1$s”. Expected for example %2$s, or enter the column name in the form.', $key, implode(', ', array_slice($candidates, 0, 3)));
            return null;
        };
        $cV = $find('version', self::VERSION_COLS);
        $cT = $find('task', self::TASK_COLS);
        $cA = $find('alt', self::ALT_COLS);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors, 'info' => []];
        }

        // cbcTools with no_choice = TRUE adds an extra alternative per set; ANOUK has its own none option.
        $cNo = array_search('no_choice', $lower, true);
        if ($cNo !== false) {
            $before = count($csv['rows']);
            $csv['rows'] = array_values(array_filter($csv['rows'], fn($r) => ($r['cells'][$cNo] ?? '0') !== '1'));
            if (count($csv['rows']) < $before) {
                $info[] = t('The no-choice rows from cbcTools were removed. For a “none of these” option, switch on the none option in the settings.');
            }
        }

        $ignore = self::IGNORE_COLS;
        foreach (explode(',', $map['ignore'] ?? '') as $col) {
            if (trim($col) !== '') {
                $ignore[] = mb_strtolower(trim($col));
            }
        }
        $attrCols = [];
        foreach ($header as $i => $name) {
            if (in_array($i, [$cV, $cT, $cA], true) || in_array($lower[$i], $ignore, true) || $name === '') {
                continue;
            }
            $attrCols[$i] = $name;
        }
        if (!$attrCols) {
            return ['ok' => false, 'errors' => [t('The file contains no attribute columns.')], 'info' => []];
        }

        // Attribute definitions: existing ones, or taken from the file.
        $attrs = Study::attributes($sid);
        $fromFile = !$attrs;
        $colToAttr = [];
        if ($fromFile) {
            $values = [];
            foreach ($csv['rows'] as $r) {
                foreach ($attrCols as $i => $_) {
                    $v = $r['cells'][$i] ?? '';
                    if ($v !== '') {
                        $values[$i][$v] = true;
                    }
                }
            }
            $k = 0;
            $attrs = [];
            foreach ($attrCols as $i => $name) {
                $labels = array_keys($values[$i] ?? []);
                $labels = array_map('strval', $labels);
                usort($labels, 'strnatcasecmp');
                $attrs[] = ['name' => $name, 'levels' => array_map(fn($l, $p) => ['id' => 0, 'label' => $l, 'position' => $p + 1], $labels, array_keys($labels))];
                $colToAttr[$i] = $k++;
            }
            $info[] = t('%d attributes and their levels were taken from the file.', count($attrs));
        } else {
            $byName = [];
            foreach ($attrs as $k => $a) {
                $byName[$a['name']] = $k;
            }
            foreach (Study::varNames($attrs) as $k => $v) {
                $byName[$v] ??= $k; // columns may carry the label or the variable name
            }
            foreach ($attrCols as $i => $name) {
                if (!isset($byName[$name])) {
                    $errors[] = t('The column “%s” matches no defined attribute. If it is not an attribute, enter it under “Ignore columns”.', $name);
                } else {
                    $colToAttr[$i] = $byName[$name];
                }
            }
            foreach ($attrs as $k => $a) {
                if (!in_array($k, $colToAttr, true)) {
                    $errors[] = t('The file has no column for the attribute “%s”.', $a['name']);
                }
            }
            if ($errors) {
                return ['ok' => false, 'errors' => $errors, 'info' => []];
            }
        }

        $labelIndex = [];
        foreach ($attrs as $k => $a) {
            foreach ($a['levels'] as $idx => $l) {
                $labelIndex[$k][$l['label']] = $idx;
            }
        }

        $rows = [];
        foreach ($csv['rows'] as $r) {
            if (count($errors) >= self::MAX_ERRORS) {
                $errors[] = t('Further errors are not shown.');
                break;
            }
            $line = $r['line'];
            $nums = [];
            foreach ([t('version') => $cV, t('task') => $cT, t('alternative') => $cA] as $label => $c) {
                $raw = $r['cells'][$c] ?? '';
                if (!preg_match('/^\d+(\.0+)?$/', $raw) || (int)$raw < 1) {
                    $errors[] = t('Line %1$d: “%2$s” is not a valid number for the %3$s.', $line, $raw, $label);
                    continue 2;
                }
                $nums[] = (int)$raw;
            }
            $lv = array_fill(0, count($attrs), null);
            foreach ($colToAttr as $i => $k) {
                $raw = $r['cells'][$i] ?? '';
                if (!isset($labelIndex[$k][$raw])) {
                    $errors[] = $raw === ''
                        ? t('Line %1$d: the value for the attribute “%2$s” is missing.', $line, $attrs[$k]['name'])
                        : t('Line %1$d: the attribute “%3$s” has no level “%2$s”.', $line, $raw, $attrs[$k]['name']);
                    continue 2;
                }
                $lv[$k] = $labelIndex[$k][$raw];
            }
            $rows[] = ['v' => $nums[0], 't' => $nums[1], 'a' => $nums[2], 'lv' => $lv, 'line' => $line];
        }
        if ($errors) {
            return ['ok' => false, 'errors' => $errors, 'info' => $info];
        }

        $norm = self::normalize($rows, $errors);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors, 'info' => $info];
        }
        [$rows, $V, $T, $J, $renumbered] = $norm;
        if ($renumbered) {
            $info[] = t('Versions, tasks or alternatives were renumbered consecutively from 1.');
        }

        Db::tx(function () use ($sid, $fromFile, &$attrs, $rows, $V, $T, $J, $filename) {
            if ($fromFile) {
                Study::saveAttributes($sid, array_map(fn($a) => ['name' => $a['name'], 'levels' => array_map(fn($l) => $l['label'], $a['levels'])], $attrs));
                $attrs = Study::attributes($sid);
            }
            self::store($sid, $attrs, $rows, [
                'source' => 'upload', 'filename' => $filename,
                'versions' => $V, 'tasks' => $T, 'alts' => $J,
            ]);
        });
        $info[] = t('Design saved: %1$d versions × %2$d tasks × %3$d alternatives.', $V, $T, $J);
        return ['ok' => true, 'errors' => [], 'info' => $info];
    }

    /**
     * Checks structure and renumbers versions, tasks and alternatives to 1..n.
     * @return array{0: list<array>, 1:int, 2:int, 3:int, 4:bool}
     */
    public static function normalize(array $rows, array &$errors): array
    {
        $tree = [];
        foreach ($rows as $r) {
            if (isset($tree[$r['v']][$r['t']][$r['a']])) {
                $errors[] = t('Line %1$d: version %2$d, task %3$d, alternative %4$d occurs twice.', $r['line'], $r['v'], $r['t'], $r['a']);
                continue;
            }
            $tree[$r['v']][$r['t']][$r['a']] = $r;
        }
        if ($errors) {
            return [[], 0, 0, 0, false];
        }
        ksort($tree);
        $firstV = reset($tree);
        $T = count($firstV);
        $firstT = reset($firstV);
        $J = count($firstT);
        if ($J < 2) {
            $errors[] = t('Every task needs at least two alternatives.');
        }

        $out = [];
        $renumbered = false;
        $vNew = 0;
        foreach ($tree as $v => $tasks) {
            $vNew++;
            $renumbered = $renumbered || $v !== $vNew;
            if (count($tasks) !== $T) {
                $errors[] = t('Version %1$d has %2$d tasks; expected are %3$d (as in the first version).', $v, count($tasks), $T);
                continue;
            }
            ksort($tasks);
            $signatures = [];
            $tNew = 0;
            foreach ($tasks as $t => $alts) {
                $tNew++;
                $renumbered = $renumbered || $t !== $tNew;
                if (count($alts) !== $J) {
                    $errors[] = t('Version %1$d, task %2$d has %3$d alternatives; expected are %4$d.', $v, $t, count($alts), $J);
                    continue;
                }
                ksort($alts);
                $altSigs = [];
                $aNew = 0;
                foreach ($alts as $a => $r) {
                    $aNew++;
                    $renumbered = $renumbered || $a !== $aNew;
                    $sig = implode(',', $r['lv']);
                    if (isset($altSigs[$sig])) {
                        $errors[] = t('Version %1$d, task %2$d: alternative %3$d is identical to alternative %4$d.', $v, $t, $a, $altSigs[$sig]);
                    }
                    $altSigs[$sig] = $a;
                    $out[] = ['v' => $vNew, 't' => $tNew, 'a' => $aNew, 'lv' => $r['lv'], 'line' => $r['line']];
                }
                $taskSig = array_keys($altSigs);
                sort($taskSig);
                $taskSig = implode('|', $taskSig);
                if (isset($signatures[$taskSig])) {
                    $errors[] = t('Version %1$d: task %2$d repeats task %3$d.', $v, $t, $signatures[$taskSig]);
                }
                $signatures[$taskSig] = $t;
                if (count($errors) >= self::MAX_ERRORS) {
                    $errors[] = t('Further errors are not shown.');
                    return [[], 0, 0, 0, false];
                }
            }
        }
        return [$out, count($tree), $T, $J, $renumbered];
    }

    /** Stores normalized rows (level indices) as level ids. */
    public static function store(int $studyId, array $attrs, array $rows, array $meta): void
    {
        $data = [];
        foreach ($rows as $r) {
            $ids = [];
            foreach ($r['lv'] as $k => $idx) {
                $ids[] = $attrs[$k]['levels'][$idx]['id'];
            }
            $data[] = [$studyId, $r['v'], $r['t'], $r['a'], json_enc($ids)];
        }
        $meta['created_at'] = now();
        Db::tx(function () use ($studyId, $data, $meta) {
            Db::exec('DELETE FROM {design} WHERE study_id = ?', [$studyId]);
            Db::insertMany('{design}', ['study_id', 'version', 'task', 'alt', 'level_ids'], $data);
            Db::exec('UPDATE {studies} SET design_meta = ?, updated_at = ? WHERE id = ?', [json_enc($meta), now(), $studyId]);
        });
    }

    public static function delete(int $studyId): void
    {
        Db::tx(function () use ($studyId) {
            Db::exec('DELETE FROM {design} WHERE study_id = ?', [$studyId]);
            Db::exec('UPDATE {studies} SET design_meta = NULL, updated_at = ? WHERE id = ?', [now(), $studyId]);
        });
    }

    // ---------------------------------------------------------------- generation

    public const METHODS = [
        'minoverlap' => 'Minimal overlap (recommended)', // labels are translated with t()
        'random'     => 'Random',
    ];

    public static function generateForStudy(array $study, int $V, int $T, int $J, string $method, int $seed): array
    {
        $sid = (int)$study['id'];
        $attrs = Study::attributes($sid);
        if (!$attrs) {
            throw new DesignError(t('Define attributes and levels first.'));
        }
        if ($V < 1 || $V > 10000) {
            throw new DesignError(t('Number of versions: 1 to 10,000.'));
        }
        if ($T < 1 || $T > 50) {
            throw new DesignError(t('Tasks per version: 1 to 50.'));
        }
        if ($J < 2 || $J > 10) {
            throw new DesignError(t('Alternatives per task: 2 to 10.'));
        }
        $rows = self::generate(array_map(fn($a) => count($a['levels']), $attrs), $V, $T, $J, $method, $seed);
        self::store($sid, $attrs, $rows, [
            'source' => 'generated', 'method' => $method, 'seed' => $seed,
            'versions' => $V, 'tasks' => $T, 'alts' => $J,
        ]);
        return ['versions' => $V, 'tasks' => $T, 'alts' => $J];
    }

    /**
     * Generates a design. Deterministic for a given seed.
     * - random:     every level drawn independently
     * - minoverlap: levels within a task repeat as little as possible; least-used levels are preferred,
     *               which balances level frequencies across the whole design
     * Identical alternatives within a task and repeated tasks within a version are never produced.
     * @param list<int> $levelCounts number of levels per attribute
     */
    public static function generate(array $levelCounts, int $V, int $T, int $J, string $method, int $seed): array
    {
        if (!isset(self::METHODS[$method])) {
            throw new DesignError(t('Unknown method.'));
        }
        $profiles = array_product($levelCounts);
        if ($profiles < $J) {
            throw new DesignError(t('These attributes give only %1$d different profiles, too few for %2$d alternatives.', $profiles, $J));
        }
        mt_srand($seed);
        $usage = array_map(fn($L) => array_fill(0, $L, 0), $levelCounts);
        $rows = [];
        for ($v = 1; $v <= $V; $v++) {
            $seen = [];
            for ($t = 1; $t <= $T; $t++) {
                $task = null;
                for ($attempt = 0; $attempt < 500; $attempt++) {
                    $candidate = $method === 'minoverlap'
                        ? self::taskMinOverlap($levelCounts, $usage, $J)
                        : self::taskRandom($levelCounts, $J);
                    $sigs = array_map(fn($alt) => implode(',', $alt), $candidate);
                    if (count(array_unique($sigs)) < $J) {
                        continue;
                    }
                    sort($sigs);
                    $key = implode('|', $sigs);
                    if (isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;
                    $task = $candidate;
                    break;
                }
                if ($task === null) {
                    throw new DesignError(t('No new choice task found for version %1$d, task %2$d. Choose fewer tasks or alternatives.', $v, $t));
                }
                foreach ($task as $j => $alt) {
                    foreach ($alt as $k => $lv) {
                        $usage[$k][$lv]++;
                    }
                    $rows[] = ['v' => $v, 't' => $t, 'a' => $j + 1, 'lv' => $alt, 'line' => 0];
                }
            }
        }
        return $rows;
    }

    private static function taskRandom(array $levelCounts, int $J): array
    {
        $task = [];
        for ($j = 0; $j < $J; $j++) {
            foreach ($levelCounts as $k => $L) {
                $task[$j][$k] = mt_rand(0, $L - 1);
            }
        }
        return $task;
    }

    private static function taskMinOverlap(array $levelCounts, array $usage, int $J): array
    {
        $task = array_fill(0, $J, []);
        foreach ($levelCounts as $k => $L) {
            $tiebreak = [];
            for ($l = 0; $l < $L; $l++) {
                $tiebreak[$l] = mt_rand();
            }
            $order = range(0, $L - 1);
            usort($order, fn($a, $b) => [$usage[$k][$a], $tiebreak[$a]] <=> [$usage[$k][$b], $tiebreak[$b]]);
            $seq = [];
            while (count($seq) < $J) {
                foreach ($order as $l) {
                    $seq[] = $l;
                }
            }
            $seq = self::shuffle(array_slice($seq, 0, $J));
            foreach ($seq as $j => $l) {
                $task[$j][$k] = $l;
            }
        }
        return $task;
    }

    /** Fisher-Yates with mt_rand, so results follow the seed. */
    private static function shuffle(array $a): array
    {
        for ($i = count($a) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$a[$i], $a[$j]] = [$a[$j], $a[$i]];
        }
        return $a;
    }

    // ---------------------------------------------------------------- reading and diagnostics

    /** @return array<int, array<int, list<int>>> task => alt => level ids (attribute order) */
    public static function versionTasks(int $studyId, int $version): array
    {
        $rows = Db::all('SELECT task, alt, level_ids FROM {design} WHERE study_id = ? AND version = ? ORDER BY task, alt', [$studyId, $version]);
        $out = [];
        foreach ($rows as $r) {
            $out[(int)$r['task']][(int)$r['alt']] = array_map('intval', json_dec($r['level_ids']));
        }
        return $out;
    }

    /** Level frequencies and within-task overlap per attribute. */
    public static function diagnose(int $studyId, array $attrs): array
    {
        $counts = [];
        $tasks = [];
        $total = 0;
        $st = Db::query('SELECT version, task, level_ids FROM {design} WHERE study_id = ?', [$studyId]);
        while ($r = $st->fetch()) {
            $ids = json_dec($r['level_ids']);
            $total++;
            foreach ($ids as $k => $id) {
                $counts[$id] = ($counts[$id] ?? 0) + 1;
                $tasks[$r['version'] . '-' . $r['task']][$k][] = $id;
            }
        }
        $out = [];
        foreach ($attrs as $k => $a) {
            $overlap = 0;
            foreach ($tasks as $t) {
                if (count(array_unique($t[$k])) < count($t[$k])) {
                    $overlap++;
                }
            }
            $levels = [];
            foreach ($a['levels'] as $l) {
                $n = $counts[$l['id']] ?? 0;
                $levels[] = ['label' => $l['label'], 'n' => $n, 'share' => $total ? $n / $total : 0];
            }
            $out[] = ['name' => $a['name'], 'levels' => $levels, 'overlap' => $tasks ? $overlap / count($tasks) : 0];
        }
        return $out;
    }

    /** Streams the stored design as CSV (version, task, alt, attribute labels). */
    public static function downloadCsv(array $study): void
    {
        $sid = (int)$study['id'];
        $attrs = Study::attributes($sid);
        $labels = [];
        foreach ($attrs as $a) {
            foreach ($a['levels'] as $l) {
                $labels[$l['id']] = $l['label'];
            }
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="anouk_' . $study['slug'] . '_design.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, array_merge(['version', 'task', 'alt'], array_column($attrs, 'name')), ',', '"', '');
        $st = Db::query('SELECT version, task, alt, level_ids FROM {design} WHERE study_id = ? ORDER BY version, task, alt', [$sid]);
        while ($r = $st->fetch()) {
            $row = [$r['version'], $r['task'], $r['alt']];
            foreach (json_dec($r['level_ids']) as $id) {
                $row[] = $labels[$id] ?? '';
            }
            fputcsv($out, $row, ',', '"', '');
        }
        fclose($out);
    }
}
