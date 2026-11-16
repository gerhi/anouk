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
        return Db::one('SELECT * FROM anouk_studies WHERE slug = ?', [$slug]);
    }

    public static function validSlug(string $slug): bool
    {
        return (bool)preg_match('/^[a-z0-9][a-z0-9-]{1,62}$/', $slug);
    }

    public static function create(int $ownerId, string $title, string $slug, string $lang = 'en'): int
    {
        $settings = self::DEFAULT_SETTINGS;
        $settings['lang'] = isset(Survey::STRINGS[$lang]) ? $lang : 'en';
        return Db::insert('anouk_studies', [
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
        Db::exec('UPDATE anouk_studies SET settings = ?, updated_at = ? WHERE id = ?', [json_enc($settings), now(), $studyId]);
    }

    /**
     * Attributes with their levels, in display order.
     * @return list<array{id:int,name:string,position:int,levels:list<array{id:int,label:string,position:int}>}>
     */
    public static function attributes(int $studyId): array
    {
        $attrs = Db::all('SELECT id, name, position FROM anouk_attributes WHERE study_id = ? ORDER BY position', [$studyId]);
        if (!$attrs) {
            return [];
        }
        $levels = Db::all(
            'SELECT l.id, l.attribute_id, l.label, l.position FROM anouk_levels l
             JOIN anouk_attributes a ON a.id = l.attribute_id
             WHERE a.study_id = ? ORDER BY l.attribute_id, l.position',
            [$studyId]
        );
        $byAttr = [];
        foreach ($levels as $l) {
            $byAttr[(int)$l['attribute_id']][] = ['id' => (int)$l['id'], 'label' => $l['label'], 'position' => (int)$l['position']];
        }
        $out = [];
        foreach ($attrs as $a) {
            $out[] = ['id' => (int)$a['id'], 'name' => $a['name'], 'position' => (int)$a['position'], 'levels' => $byAttr[(int)$a['id']] ?? []];
        }
        return $out;
    }

    /** Replaces all attributes and levels. Only allowed while no design exists. */
    public static function saveAttributes(int $studyId, array $attributes): void
    {
        Db::tx(function () use ($studyId, $attributes) {
            Db::exec('DELETE FROM anouk_attributes WHERE study_id = ?', [$studyId]);
            foreach (array_values($attributes) as $i => $a) {
                $aid = Db::insert('anouk_attributes', ['study_id' => $studyId, 'position' => $i + 1, 'name' => $a['name']]);
                foreach (array_values($a['levels']) as $j => $label) {
                    Db::insert('anouk_levels', ['attribute_id' => $aid, 'position' => $j + 1, 'label' => $label]);
                }
            }
            Db::exec('UPDATE anouk_studies SET updated_at = ? WHERE id = ?', [now(), $studyId]);
        });
    }

    public static function hasDesign(int $studyId): bool
    {
        return (bool)Db::val('SELECT COUNT(*) FROM anouk_design WHERE study_id = ?', [$studyId]);
    }

    public static function respondentCount(int $studyId, bool $includeTests = true): int
    {
        $sql = 'SELECT COUNT(*) FROM anouk_respondents WHERE study_id = ?' . ($includeTests ? '' : ' AND is_test = 0');
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
            'SELECT status, is_test, COUNT(*) AS n FROM anouk_respondents WHERE study_id = ? GROUP BY status, is_test',
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
            "SELECT version, COUNT(*) AS n FROM anouk_respondents
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
            Db::exec('DELETE FROM anouk_answers WHERE respondent_id IN (SELECT id FROM anouk_respondents WHERE study_id = ?)', [$studyId]);
            Db::exec('DELETE FROM anouk_respondents WHERE study_id = ?', [$studyId]);
            Db::exec('DELETE FROM anouk_design WHERE study_id = ?', [$studyId]);
            Db::exec('DELETE FROM anouk_levels WHERE attribute_id IN (SELECT id FROM anouk_attributes WHERE study_id = ?)', [$studyId]);
            Db::exec('DELETE FROM anouk_attributes WHERE study_id = ?', [$studyId]);
            Db::exec('DELETE FROM anouk_studies WHERE id = ?', [$studyId]);
        });
    }
}
