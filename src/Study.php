<?php
declare(strict_types=1);

/** Study settings, attributes and status rules. */
final class Study
{
    public const DEFAULT_SETTINGS = [
        'lang'                 => 'en',
        'question'             => '', // empty: default text of the participant language
        'intro'                => '',
        'none_option'          => false,
        'none_label'           => '', // empty: default text of the participant language
        'randomize_attributes' => false,
        'show_progress'        => true,
        'return_url'           => '',
        'error_url'            => '',
        'release_minutes'      => 60,
        'test_prefix'          => 'test_',
    ];

    public static function settings(array $study): array
    {
        return array_merge(self::DEFAULT_SETTINGS, json_dec($study['settings'] ?? null, []));
    }

    public static function meta(array $study): array
    {
        return json_dec($study['design_meta'] ?? null, []);
    }

    public static function bySlug(string $slug): ?array
    {
        return Db::one('SELECT * FROM {studies} WHERE slug = ?', [$slug]);
    }

    public static function validSlug(string $slug): bool
    {
        return (bool)preg_match('/^[a-z0-9][a-z0-9-]{1,62}$/', $slug);
    }

    public static function create(int $ownerId, string $title, string $slug, string $lang = 'en'): int
    {
        $settings = self::DEFAULT_SETTINGS;
        $settings['lang'] = lang_exists($lang) ? $lang : 'en';
        return Db::insert('{studies}', [
            'owner_id'    => $ownerId,
            'slug'        => $slug,
            'title'       => $title,
            'status'      => 'draft',
            'settings'    => json_enc($settings),
            'design_meta' => null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public static function saveSettings(int $studyId, array $settings): void
    {
        Db::exec('UPDATE {studies} SET settings = ?, updated_at = ? WHERE id = ?', [json_enc($settings), now(), $studyId]);
    }

    /**
     * Attributes with their levels, in display order.
     * @return list<array{id:int,name:string,position:int,levels:list<array{id:int,label:string,position:int}>}>
     */
    public static function attributes(int $studyId): array
    {
        $attrs = Db::all('SELECT id, name, var_name, position FROM {attributes} WHERE study_id = ? ORDER BY position', [$studyId]);
        if (!$attrs) {
            return [];
        }
        $levels = Db::all(
            'SELECT l.id, l.attribute_id, l.label, l.position FROM {levels} l
             JOIN {attributes} a ON a.id = l.attribute_id
             WHERE a.study_id = ? ORDER BY l.attribute_id, l.position',
            [$studyId]
        );
        $byAttr = [];
        foreach ($levels as $l) {
            $byAttr[(int)$l['attribute_id']][] = ['id' => (int)$l['id'], 'label' => $l['label'], 'position' => (int)$l['position']];
        }
        $out = [];
        foreach ($attrs as $a) {
            $out[] = ['id' => (int)$a['id'], 'name' => $a['name'], 'var_name' => (string)($a['var_name'] ?? ''), 'position' => (int)$a['position'], 'levels' => $byAttr[(int)$a['id']] ?? []];
        }
        return $out;
    }

    /** Replaces all attributes and levels. Only allowed while no design exists. */
    public static function saveAttributes(int $studyId, array $attributes): void
    {
        Db::tx(function () use ($studyId, $attributes) {
            Db::exec('DELETE FROM {attributes} WHERE study_id = ?', [$studyId]);
            $used = [];
            foreach (array_values($attributes) as $i => $a) {
                $var = (string)($a['var_name'] ?? '');
                if (self::validVarName($var) && !isset($used[$var])) {
                    $used[$var] = true; // a name chosen by the user stays as it is
                } else {
                    $var = self::varName($var !== '' ? $var : (string)$a['name'], $used);
                }
                $aid = Db::insert('{attributes}', ['study_id' => $studyId, 'position' => $i + 1, 'var_name' => $var, 'name' => $a['name']]);
                foreach (array_values($a['levels']) as $j => $label) {
                    Db::insert('{levels}', ['attribute_id' => $aid, 'position' => $j + 1, 'label' => $label]);
                }
            }
            Db::exec('UPDATE {studies} SET updated_at = ? WHERE id = ?', [now(), $studyId]);
        });
    }

    /** Column names of the long export and R words that an attribute must not take. */
    public const RESERVED_NAMES = [
        'id', 'obs_id', 'version', 'task', 'alt', 'none', 'choice', 'rt_ms', 'screen_w', 'answered_at', 'status', 'is_test',
        'if', 'else', 'repeat', 'while', 'function', 'for', 'next', 'break', 'true', 'false', 'null', 'inf', 'nan', 'na', 'in',
    ];

    /** Column names of the attributes: stored variable names, or derived ones for incomplete data. */
    public static function varNames(array $attrs): array
    {
        $used = [];
        $names = [];
        foreach ($attrs as $a) {
            $v = (string)($a['var_name'] ?? '');
            if (self::validVarName($v) && !isset($used[$v])) {
                $used[$v] = true;
            } else {
                $v = self::varName((string)$a['name'], $used);
            }
            $names[] = $v;
        }
        return $names;
    }

    /**
     * Sets the variable names of a study's attributes (editable at any time; they only name export columns).
     * @param array<int,string> $names attribute id => name
     * @return string[] errors
     */
    public static function saveVarNames(int $studyId, array $names): array
    {
        $attrs = self::attributes($studyId);
        $errors = [];
        $seen = [];
        foreach ($attrs as $a) {
            $v = strtolower(trim((string)($names[$a['id']] ?? $a['var_name'])));
            if (!self::validVarName($v)) {
                $errors[] = t('“%1$s”: the variable name “%2$s” is not valid. Use lower-case letters, digits and underscores, starting with a letter (at most 64 characters); export column names such as id, task or choice are taken.', $a['name'], $v);
            } elseif (isset($seen[$v])) {
                $errors[] = t('The variable name “%s” is used twice.', $v);
            }
            $seen[$v] = $a['id'];
        }
        if ($errors) {
            return $errors;
        }
        Db::tx(function () use ($seen, $studyId) {
            foreach ($seen as $v => $aid) {
                Db::exec('UPDATE {attributes} SET var_name = ? WHERE id = ? AND study_id = ?', [$v, $aid, $studyId]);
            }
            Db::exec('UPDATE {studies} SET updated_at = ? WHERE id = ?', [now(), $studyId]);
        });
        return [];
    }

    public static function validVarName(string $name): bool
    {
        return (bool)preg_match('/^[a-z][a-z0-9_]{0,63}$/', $name) && !in_array($name, self::RESERVED_NAMES, true);
    }

    /**
     * R-friendly variable name in snake_case, derived from a label: "Eigenanteil pro Monat" → eigenanteil_pro_monat.
     * $used collects the names already taken in the study; duplicates get _2, _3 …
     */
    public static function varName(string $label, array &$used): string
    {
        $s = strtr(mb_strtolower(trim($label)), ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'à' => 'a', 'á' => 'a', 'ç' => 'c', 'ñ' => 'n', 'ø' => 'o', 'å' => 'a']);
        $s = trim((string)preg_replace('/[^a-z0-9]+/', '_', $s), '_');
        if ($s === '' || ctype_digit($s[0])) {
            $s = 'a_' . $s;
        }
        $s = rtrim(substr($s, 0, 30), '_');
        if (in_array($s, self::RESERVED_NAMES, true)) {
            $s .= '_attr';
        }
        $base = $s;
        for ($i = 2; isset($used[$s]); $i++) {
            $s = $base . '_' . $i;
        }
        $used[$s] = true;
        return $s;
    }

    public static function hasDesign(int $studyId): bool
    {
        return (bool)Db::val('SELECT COUNT(*) FROM {design} WHERE study_id = ?', [$studyId]);
    }

    public static function respondentCount(int $studyId, bool $includeTests = true): int
    {
        $sql = 'SELECT COUNT(*) FROM {respondents} WHERE study_id = ?' . ($includeTests ? '' : ' AND is_test = 0');
        return (int)Db::val($sql, [$studyId]);
    }

    /** Allowed status changes from the current status. */
    public static function transitions(array $study): array
    {
        $hasRespondents = self::respondentCount((int)$study['id']) > 0;
        return match ($study['status']) {
            'draft'  => ['field'],
            'field'  => $hasRespondents ? ['closed'] : ['closed', 'draft'],
            'closed' => $hasRespondents ? ['field'] : ['field', 'draft'],
            default  => [],
        };
    }

    public static function fieldStats(int $studyId): array
    {
        $rows = Db::all(
            'SELECT status, is_test, COUNT(*) AS n FROM {respondents} WHERE study_id = ? GROUP BY status, is_test',
            [$studyId]
        );
        $s = ['active' => 0, 'complete' => 0, 'test' => 0];
        foreach ($rows as $r) {
            if ((int)$r['is_test'] === 1) {
                $s['test'] += (int)$r['n'];
            } else {
                $s[$r['status']] = ($s[$r['status']] ?? 0) + (int)$r['n'];
            }
        }
        $perVersion = Db::all(
            "SELECT version, COUNT(*) AS n FROM {respondents}
             WHERE study_id = ? AND is_test = 0 AND status = 'complete' GROUP BY version",
            [$studyId]
        );
        $counts = array_map(fn($r) => (int)$r['n'], $perVersion);
        $s['versions_used'] = count($counts);
        $s['per_version_min'] = $counts ? min($counts) : 0;
        $s['per_version_max'] = $counts ? max($counts) : 0;
        return $s;
    }

    public static function delete(int $studyId): void
    {
        Db::tx(function () use ($studyId) {
            // explicit order, so it also works where foreign keys are not enforced
            Db::exec('DELETE FROM {answers} WHERE respondent_id IN (SELECT id FROM {respondents} WHERE study_id = ?)', [$studyId]);
            Db::exec('DELETE FROM {respondents} WHERE study_id = ?', [$studyId]);
            Db::exec('DELETE FROM {design} WHERE study_id = ?', [$studyId]);
            Db::exec('DELETE FROM {levels} WHERE attribute_id IN (SELECT id FROM {attributes} WHERE study_id = ?)', [$studyId]);
            Db::exec('DELETE FROM {attributes} WHERE study_id = ?', [$studyId]);
            Db::exec('DELETE FROM {studies} WHERE id = ?', [$studyId]);
        });
    }
}
