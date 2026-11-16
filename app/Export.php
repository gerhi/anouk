<?php
declare(strict_types=1);

final class Export
{
    /**
     * Long format: one row per respondent × task × alternative.
     * With a none option, every task gets an extra row (alt = J + 1, none = 1, empty attributes).
     */
    public static function long(array $study, bool $completeOnly, bool $includeTests, string $delimiter = ','): void
    {
        $sid = (int)$study['id'];
        $settings = Study::settings($study);
        $meta = Study::meta($study);
        $J = (int)($meta['alts'] ?? 0);
        $none = !empty($settings['none_option']);
        $attrs = Study::attributes($sid);
        $labels = [];
        foreach ($attrs as $a) {
            foreach ($a['levels'] as $l) {
                $labels[$l['id']] = $l['label'];
            }
        }
        $delimiter = $delimiter === ';' ? ';' : ',';
        // The Excel style is opened in a spreadsheet: cells that start like a formula get a leading
        // apostrophe, so labels or IDs such as =HYPERLINK(…) are shown, not run. The R style stays exact.
        $put = $delimiter === ';'
            ? fn($out, array $row) => fputcsv($out, array_map([self::class, 'safeCell'], $row), ';', '"', '')
            : fn($out, array $row) => fputcsv($out, $row, ',', '"', '');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="anouk_' . $study['slug'] . '_long_' . gmdate('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        if ($delimiter === ';') {
            fwrite($out, "\xEF\xBB\xBF"); // Excel recognizes UTF-8 only with a BOM
        }
        $head = array_merge(['id', 'obs_id', 'version', 'task', 'alt'], array_column($attrs, 'name'));
        if ($none) {
            $head[] = 'none';
        }
        $head = array_merge($head, ['choice', 'rt_ms', 'screen_w', 'answered_at_utc', 'status', 'is_test']);
        $put($out, $head);

        $where = 'study_id = ?';
        if ($completeOnly) {
            $where .= " AND status = 'complete'";
        }
        if (!$includeTests) {
            $where .= ' AND is_test = 0';
        }
        $respondents = Db::all("SELECT id, ext_id, version, status, is_test FROM anouk_respondents WHERE $where ORDER BY id", [$sid]);
        $designs = [];
        $obs = 0; // running number per respondent × task, as logitr/mlogit expect
        foreach ($respondents as $r) {
            $v = (int)$r['version'];
            $designs[$v] ??= Design::versionTasks($sid, $v);
            $answers = Db::all('SELECT task, choice, rt_ms, screen_w, answered_at FROM anouk_answers WHERE respondent_id = ? ORDER BY task', [$r['id']]);
            foreach ($answers as $ans) {
                $t = (int)$ans['task'];
                $obs++;
                $tail = [$ans['rt_ms'], $ans['screen_w'], $ans['answered_at'], $r['status'], $r['is_test']];
                for ($a = 1; $a <= $J; $a++) {
                    $row = [$r['ext_id'], $obs, $v, $t, $a];
                    foreach ($designs[$v][$t][$a] ?? [] as $levelId) {
                        $row[] = $labels[$levelId] ?? '';
                    }
                    if ($none) {
                        $row[] = 0;
                    }
                    $row[] = (int)$ans['choice'] === $a ? 1 : 0;
                    $put($out, array_merge($row, $tail));
                }
                if ($none) {
                    $row = array_merge([$r['ext_id'], $obs, $v, $t, $J + 1], array_fill(0, count($attrs), ''), [1, (int)$ans['choice'] === 0 ? 1 : 0]);
                    $put($out, array_merge($row, $tail));
                }
            }
        }
        fclose($out);
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
