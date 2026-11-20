<?php
declare(strict_types=1);

/**
 * Data exports of a study: long (one row per ID × task × alternative), wide (one row per ID)
 * and a codebook. Rows are produced by generators, so they can be tested without HTTP.
 * Timestamps are ISO 8601 in UTC (2026-11-19T08:30:00Z).
 */
final class Export
{
    public const FORMATS = ['long', 'wide', 'codebook'];

    /** Sends one export as a CSV download. */
    public static function send(array $study, string $format, bool $completeOnly, bool $includeTests, string $delimiter = ','): void
    {
        $format = in_array($format, self::FORMATS, true) ? $format : 'long';
        $rows = match ($format) {
            'wide' => self::wideRows($study, $completeOnly, $includeTests),
            'codebook' => self::codebookRows($study),
            default => self::longRows($study, $completeOnly, $includeTests),
        };
        $delimiter = $delimiter === ';' ? ';' : ',';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="anouk_' . $study['slug'] . '_' . $format . '_' . gmdate('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        self::write($out, $rows, $delimiter);
        fclose($out);
    }

    /**
     * Writes rows as CSV. The Excel style (semicolon) gets a BOM and neutralised formulas, so labels
     * or IDs such as =HYPERLINK(…) are shown, not run. The R style (comma) stays exact.
     *
     * @param resource $out
     */
    public static function write($out, iterable $rows, string $delimiter = ','): void
    {
        $excel = $delimiter === ';';
        if ($excel) {
            fwrite($out, "\xEF\xBB\xBF");
        }
        foreach ($rows as $row) {
            fputcsv($out, $excel ? array_map([self::class, 'safeCell'], $row) : $row, $excel ? ';' : ',', '"', '');
        }
    }

    /** Converts a stored UTC time (Y-m-d H:i:s) to ISO 8601 with Z; empty stays empty. */
    public static function iso(?string $t): string
    {
        if ($t === null || $t === '') {
            return '';
        }
        return str_replace(' ', 'T', substr($t, 0, 19)) . 'Z';
    }

    /** Respondents of a study for export. */
    private static function respondents(int $sid, bool $completeOnly, bool $includeTests): array
    {
        $where = 'study_id = ?';
        if ($completeOnly) {
            $where .= " AND status = 'complete'";
        }
        if (!$includeTests) {
            $where .= ' AND is_test = 0';
        }
        return Db::all("SELECT id, ext_id, version, status, is_test, started_at, completed_at FROM {respondents} WHERE $where ORDER BY id", [$sid]);
    }

    /**
     * Long format: one row per respondent × task × alternative, choice = 1 for the chosen one.
     * With a none option, every task gets an extra row (alt = J + 1, none = 1, empty attributes).
     */
    public static function longRows(array $study, bool $completeOnly, bool $includeTests): \Generator
    {
        $sid = (int)$study['id'];
        $meta = Study::meta($study);
        $J = (int)($meta['alts'] ?? 0);
        $none = !empty(Study::settings($study)['none_option']);
        $attrs = Study::attributes($sid);
        $labels = [];
        foreach ($attrs as $a) {
            foreach ($a['levels'] as $l) {
                $labels[$l['id']] = $l['label'];
            }
        }
        $head = array_merge(['id', 'obs_id', 'version', 'task', 'alt'], Study::varNames($attrs));
        if ($none) {
            $head[] = 'none';
        }
        yield array_merge($head, ['choice', 'rt_ms', 'screen_w', 'answered_at', 'status', 'is_test']);

        $designs = [];
        $obs = 0; // running number per respondent × task, as logitr/mlogit expect
        foreach (self::respondents($sid, $completeOnly, $includeTests) as $r) {
            $v = (int)$r['version'];
            $designs[$v] ??= Design::versionTasks($sid, $v);
            $answers = Db::all('SELECT task, choice, rt_ms, screen_w, answered_at FROM {answers} WHERE respondent_id = ? ORDER BY task', [$r['id']]);
            foreach ($answers as $ans) {
                $t = (int)$ans['task'];
                $obs++;
                $tail = [$ans['rt_ms'], $ans['screen_w'], self::iso($ans['answered_at']), $r['status'], (int)$r['is_test']];
                for ($a = 1; $a <= $J; $a++) {
                    $row = [$r['ext_id'], $obs, $v, $t, $a];
                    foreach ($designs[$v][$t][$a] ?? [] as $levelId) {
                        $row[] = $labels[$levelId] ?? '';
                    }
                    if ($none) {
                        $row[] = 0;
                    }
                    $row[] = (int)$ans['choice'] === $a ? 1 : 0;
                    yield array_merge($row, $tail);
                }
                if ($none) {
                    $row = array_merge([$r['ext_id'], $obs, $v, $t, $J + 1], array_fill(0, count($attrs), ''), [1, (int)$ans['choice'] === 0 ? 1 : 0]);
                    yield array_merge($row, $tail);
                }
            }
        }
    }

    /**
     * Wide format: one row per respondent, with the chosen alternative (choice_tNN; 0 = none,
     * empty = not answered) and the response time (rt_tNN) for every task.
     */
    public static function wideRows(array $study, bool $completeOnly, bool $includeTests): \Generator
    {
        $sid = (int)$study['id'];
        $T = (int)(Study::meta($study)['tasks'] ?? 0);
        $nums = $T > 0 ? array_map(fn($t) => sprintf('%02d', $t), range(1, $T)) : [];
        yield array_merge(
            ['id', 'version', 'status', 'is_test', 'n_answered', 'started_at', 'completed_at'],
            array_map(fn($n) => 'choice_t' . $n, $nums),
            array_map(fn($n) => 'rt_t' . $n, $nums)
        );
        foreach (self::respondents($sid, $completeOnly, $includeTests) as $r) {
            $choice = $T > 0 ? array_fill(1, $T, '') : [];
            $rt = $choice;
            $n = 0;
            foreach (Db::all('SELECT task, choice, rt_ms FROM {answers} WHERE respondent_id = ? ORDER BY task', [$r['id']]) as $ans) {
                $t = (int)$ans['task'];
                if ($t >= 1 && $t <= $T) {
                    $choice[$t] = (int)$ans['choice'];
                    $rt[$t] = $ans['rt_ms'] ?? '';
                    $n++;
                }
            }
            yield array_merge(
                [$r['ext_id'], (int)$r['version'], $r['status'], (int)$r['is_test'], $n, self::iso($r['started_at']), self::iso($r['completed_at'])],
                array_values($choice),
                array_values($rt)
            );
        }
    }

    /** Codebook: one row per column of the long and wide exports. */
    public static function codebookRows(array $study): \Generator
    {
        $sid = (int)$study['id'];
        $meta = Study::meta($study);
        $J = (int)($meta['alts'] ?? 0);
        $T = (int)($meta['tasks'] ?? 0);
        $none = !empty(Study::settings($study)['none_option']);
        $attrs = Study::attributes($sid);
        $status = 'active = started, not finished; complete = finished';

        yield ['variable', 'label', 'type', 'values'];
        yield ['id', 'Respondent ID passed in by the survey tool', 'text', ''];
        yield ['obs_id', 'Long format: running number of the choice situation (respondent × task)', 'integer', ''];
        yield ['version', 'Design version shown to the respondent', 'integer', '1–' . (int)($meta['versions'] ?? 0)];
        yield ['task', 'Long format: number of the choice task', 'integer', $T ? '1–' . $T : ''];
        yield ['alt', 'Long format: position of the alternative on the screen', 'integer', $J ? '1–' . $J . ($none ? '; ' . ($J + 1) . ' = none option' : '') : ''];
        foreach (Study::varNames($attrs) as $i => $v) {
            yield [$v, (string)$attrs[$i]['name'], 'categorical', implode(' | ', array_column($attrs[$i]['levels'], 'label'))];
        }
        if ($none) {
            yield ['none', 'Long format: 1 = row of the none option', 'integer', '0, 1'];
        }
        yield ['choice', 'Long format: 1 = this alternative was chosen', 'integer', '0, 1'];
        yield ['rt_ms', 'Long format: response time of the task in milliseconds', 'integer', ''];
        yield ['screen_w', 'Long format: screen width in pixels when answering', 'integer', ''];
        yield ['answered_at', 'Long format: time of the answer (UTC, ISO 8601)', 'datetime', ''];
        yield ['status', 'Respondent status', 'text', $status];
        yield ['is_test', '1 = test ID', 'integer', '0, 1'];
        yield ['n_answered', 'Wide format: number of answered tasks', 'integer', $T ? '0–' . $T : ''];
        yield ['started_at', 'Wide format: first visit (UTC, ISO 8601)', 'datetime', ''];
        yield ['completed_at', 'Wide format: completion (UTC, ISO 8601); empty = not completed', 'datetime', ''];
        for ($t = 1; $t <= $T; $t++) {
            yield [sprintf('choice_t%02d', $t), 'Wide format: chosen alternative in task ' . $t, 'integer', '1–' . $J . ($none ? '; 0 = none option' : '') . '; empty = not answered'];
        }
        for ($t = 1; $t <= $T; $t++) {
            yield [sprintf('rt_t%02d', $t), 'Wide format: response time in task ' . $t . ' (ms)', 'integer', ''];
        }
    }

    /** Neutralises spreadsheet formulas; plain numbers such as -5 stay as they are. */
    public static function safeCell(mixed $v): mixed
    {
        if (is_string($v) && $v !== '' && strpbrk($v[0], "=+-@\t\r") !== false && !is_numeric($v)) {
            return "'" . $v;
        }
        return $v;
    }
}
