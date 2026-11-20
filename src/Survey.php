<?php
declare(strict_types=1);

/**
 * Respondent flow: LimeSurvey → index.php?s=<slug>&id=<id> → choice tasks → back to LimeSurvey.
 * Preview mode (index.php?s=<slug>&preview=1, logged in) keeps everything in the session.
 */
final class Survey
{
    /** Question and none label: the study's own text, else the default of its language. */
    public static function question(array $settings): string
    {
        return trim((string)$settings['question']) !== '' ? (string)$settings['question'] : self::t($settings['lang'], 'question');
    }

    public static function noneLabel(array $settings): string
    {
        return trim((string)$settings['none_label']) !== '' ? (string)$settings['none_label'] : self::t($settings['lang'], 'none');
    }

    /** Text for the respondent pages from lang/<study language>.php (key survey.<key>). */
    public static function t(string $lang, string $key, mixed ...$args): string
    {
        $s = i18n_lookup($lang, 'survey.' . $key) ?? $key;
        return $args ? vsprintf($s, $args) : $s;
    }

    public static function validId(string $id): bool
    {
        return (bool)preg_match('/^[A-Za-z0-9_.~@\-]{1,190}$/', $id);
    }

    public static function fillUrl(string $url, string $id, string $status): string
    {
        // {ID} and {STATUS}; the lower-case forms of ANOUK 1.0 still work.
        $id = rawurlencode($id);
        $status = rawurlencode($status);
        return strtr($url, ['{ID}' => $id, '{STATUS}' => $status, '{id}' => $id, '{status}' => $status]);
    }

    public static function handle(): void
    {
        $slug = is_string($_GET['s'] ?? null) ? $_GET['s'] : '';
        $study = Study::bySlug($slug);
        if (!$study) {
            self::message('en', self::t('en', 'notfound'), 404);
        }
        $settings = Study::settings($study);
        $lang = $settings['lang'];
        $meta = Study::meta($study);
        if (Migrator::pending()) {
            self::message($lang, self::t($lang, 'maintenance'), 503); // database update not yet run
        }

        if (isset($_GET['preview'])) {
            if (!Auth::user() || !Auth::canAccess($study)) {
                redirect(url('login'));
            }
            self::preview($study, $settings, $meta);
            return;
        }

        // id; tic as fallback, because the "external survey start" of Unipark/EFS passes its ticket as tic.
        $raw = $_GET['id'] ?? $_GET['tic'] ?? null;
        $ext = is_string($raw) ? trim($raw) : '';
        if ($study['status'] !== 'field' || empty($meta['versions'])) {
            if ($settings['error_url'] !== '') {
                redirect(self::fillUrl($settings['error_url'], self::validId($ext) ? $ext : '', 'closed'));
            }
            self::message($lang, self::t($lang, 'closed'), 200);
        }

        // Entry from LimeSurvey with ?id=…: identify, hand out a cookie and continue on a clean URL,
        // so the ID leaves the address bar and the browser history.
        $cookie = 'anouk_s' . (int)$study['id'];
        $clean = survey_url($slug);
        if ($ext !== '') {
            if (!self::validId($ext)) {
                if ($settings['error_url'] !== '') {
                    redirect(self::fillUrl($settings['error_url'], '', 'invalid_id'));
                }
                self::message($lang, self::t($lang, 'badid'), 400);
            }
            $resp = self::respondent($study, $settings, $meta, $ext);
            if ($resp['status'] === 'complete') {
                self::finish($settings, $resp);
            }
            $token = bin2hex(random_bytes(32));
            Db::exec('UPDATE {respondents} SET token_hash = ? WHERE id = ?', [hash('sha256', $token), $resp['id']]);
            self::setCookie($cookie, $token);
            if (!is_post()) {
                redirect(survey_url($slug, ['c' => 1]));
            }
        } else {
            $token = is_string($_COOKIE[$cookie] ?? null) ? $_COOKIE[$cookie] : '';
            $resp = $token !== ''
                ? Db::one('SELECT * FROM {respondents} WHERE study_id = ? AND token_hash = ?', [$study['id'], hash('sha256', $token)])
                : null;
            if (!$resp) {
                // ?c=1 marks the first page after the entry redirect: no cookie there means cookies are blocked
                $key = isset($_GET['c']) ? 'cookies' : 'badid';
                if ($settings['error_url'] !== '' && $key === 'badid') {
                    redirect(self::fillUrl($settings['error_url'], '', 'invalid_id'));
                }
                self::message($lang, self::t($lang, $key), 400);
            }
            if ($resp['status'] === 'complete') {
                self::finish($settings, $resp);
            }
        }
        Db::exec('UPDATE {respondents} SET last_seen_at = ? WHERE id = ?', [now(), $resp['id']]);

        $T = (int)$meta['tasks'];
        $J = (int)$meta['alts'];
        $answered = (int)Db::val('SELECT COUNT(*) FROM {answers} WHERE respondent_id = ?', [$resp['id']]);
        $next = $answered + 1; // tasks are answered strictly in order
        $self = $clean;
        $error = null;

        if (is_post()) {
            $action = post('action');
            if ($action === 'intro') {
                $_SESSION['anouk_intro'][(int)$resp['id']] = true;
                redirect($self);
            }
            if ($action === 'answer' && (int)post('task') === $next && $next <= $T) {
                $choice = self::parseChoice(post('choice', '-1'), $J, $settings);
                if ($choice === null) {
                    $error = self::t($lang, 'choose');
                } else {
                    try {
                        Db::insert('{answers}', [
                            'respondent_id' => $resp['id'],
                            'task'          => $next,
                            'choice'        => $choice,
                            'rt_ms'         => self::intOrNull(post('rt'), 0, 3600000),
                            'screen_w'      => self::intOrNull(post('sw'), 1, 20000),
                            'answered_at'   => now(),
                        ]);
                    } catch (PDOException $e) {
                        if (!Db::isDuplicate($e)) {
                            throw $e;
                        }
                    }
                    if ($next >= $T) {
                        self::complete($resp);
                        self::finish($settings, $resp);
                    }
                    redirect($self);
                }
            } elseif ($action !== '') {
                redirect($self); // stale form (reload, back button): show the current task
            }
        }

        if ($next > $T) {
            self::complete($resp);
            self::finish($settings, $resp);
        }
        if ($answered === 0 && trim($settings['intro']) !== '' && empty($_SESSION['anouk_intro'][(int)$resp['id']])) {
            self::renderIntro($settings, $self, false);
            return;
        }
        $order = json_dec($resp['attr_order'] ?? null, null);
        self::renderTask($study, $settings, $meta, (int)$resp['version'], $next, $order, $self, $error, false);
    }

    // ---------------------------------------------------------------- respondents

    private static function respondent(array $study, array $settings, array $meta, string $ext): array
    {
        $sid = (int)$study['id'];
        $find = fn() => Db::one('SELECT * FROM {respondents} WHERE study_id = ? AND ext_id = ?', [$sid, $ext]);
        $r = $find();
        if ($r) {
            return $r;
        }
        try {
            return Db::tx(function () use ($sid, $settings, $meta, $ext, $find) {
                if (Db::isMysql()) {
                    // serializes version assignment per study, so a panel start stays balanced
                    Db::one('SELECT id FROM {studies} WHERE id = ? FOR UPDATE', [$sid]);
                }
                $r = $find();
                if ($r) {
                    return $r;
                }
                $prefix = (string)$settings['test_prefix'];
                $isTest = $prefix !== '' && str_starts_with($ext, $prefix);
                $order = null;
                if (!empty($settings['randomize_attributes'])) {
                    $n = (int)Db::val('SELECT COUNT(*) FROM {attributes} WHERE study_id = ?', [$sid]);
                    $order = range(0, $n - 1);
                    for ($i = $n - 1; $i > 0; $i--) {
                        $j = random_int(0, $i);
                        [$order[$i], $order[$j]] = [$order[$j], $order[$i]];
                    }
                }
                $id = Db::insert('{respondents}', [
                    'study_id'     => $sid,
                    'ext_id'       => $ext,
                    'version'      => self::pickVersion($sid, (int)$meta['versions'], (int)$settings['release_minutes']),
                    'status'       => 'active',
                    'is_test'      => $isTest ? 1 : 0,
                    'attr_order'   => $order === null ? null : json_enc($order),
                    'started_at'   => now(),
                    'last_seen_at' => now(),
                    'completed_at' => null,
                ]);
                return Db::one('SELECT * FROM {respondents} WHERE id = ?', [$id]);
            });
        } catch (PDOException $e) {
            if (Db::isDuplicate($e)) {
                return $find(); // a parallel request created it first
            }
            throw $e;
        }
    }

    /**
     * Least-used version. Counted are completed respondents and active ones seen within the release window;
     * versions of drop-outs become free again after release_minutes. Test IDs do not count.
     */
    public static function pickVersion(int $studyId, int $versions, int $releaseMinutes): int
    {
        $cut = now(-60 * max(1, $releaseMinutes));
        $rows = Db::all(
            "SELECT version, COUNT(*) AS n FROM {respondents}
             WHERE study_id = ? AND is_test = 0 AND (status = 'complete' OR last_seen_at >= ?)
             GROUP BY version",
            [$studyId, $cut]
        );
        $count = array_fill(1, $versions, 0);
        foreach ($rows as $r) {
            if (isset($count[(int)$r['version']])) {
                $count[(int)$r['version']] = (int)$r['n'];
            }
        }
        $min = min($count);
        foreach ($count as $v => $n) {
            if ($n === $min) {
                return $v; // lowest free version number: keeps versions filled in order
            }
        }
        return 1;
    }

    private static function setCookie(string $name, string $token): void
    {
        if (headers_sent()) {
            return;
        }
        setcookie($name, $token, [
            'expires'  => 0,
            'path'     => (base_path() ?: '') . '/',
            'secure'   => is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[$name] = $token;
    }

    private static function complete(array $resp): void
    {
        Db::exec("UPDATE {respondents} SET status = 'complete', completed_at = ? WHERE id = ? AND status = 'active'", [now(), $resp['id']]);
    }

    private static function finish(array $settings, array $resp): never
    {
        if ($settings['return_url'] !== '') {
            redirect(self::fillUrl($settings['return_url'], $resp['ext_id'], 'complete'));
        }
        self::message($settings['lang'], self::t($settings['lang'], 'thanks'), 200, false, self::t($settings['lang'], 'thanks_title'));
    }

    private static function parseChoice(string $raw, int $J, array $settings): ?int
    {
        if (!preg_match('/^\d+$/', $raw)) {
            return null;
        }
        $c = (int)$raw;
        if ($c >= 1 && $c <= $J) {
            return $c;
        }
        if ($c === 0 && !empty($settings['none_option'])) {
            return 0;
        }
        return null;
    }

    private static function intOrNull(string $raw, int $min, int $max): ?int
    {
        if (!preg_match('/^\d+$/', $raw)) {
            return null;
        }
        $v = (int)$raw;
        return ($v >= $min && $v <= $max) ? $v : null;
    }

    // ---------------------------------------------------------------- preview

    private static function preview(array $study, array $settings, array $meta): void
    {
        $lang = $settings['lang'];
        if (empty($meta['versions'])) {
            self::message($lang, self::t($lang, 'nodesign'), 200, true);
        }
        $key = 'anouk_preview_' . $study['id'];
        $V = (int)$meta['versions'];
        $T = (int)$meta['tasks'];
        $J = (int)$meta['alts'];
        if (isset($_GET['restart']) || !isset($_SESSION[$key])) {
            $order = null;
            if (!empty($settings['randomize_attributes'])) {
                $order = range(0, count(Study::attributes((int)$study['id'])) - 1);
                shuffle($order);
            }
            $_SESSION[$key] = [
                'version' => max(1, min($V, (int)($_GET['v'] ?? 1))),
                'answers' => [],
                'intro'   => false,
                'order'   => $order,
            ];
            redirect(survey_url($study['slug'], ['preview' => 1]));
        }
        $state = &$_SESSION[$key];
        $self = survey_url($study['slug'], ['preview' => 1]);
        $next = count($state['answers']) + 1;
        $error = null;

        if (is_post()) {
            $action = post('action');
            if ($action === 'intro') {
                $state['intro'] = true;
                redirect($self);
            }
            if ($action === 'answer' && (int)post('task') === $next && $next <= $T) {
                $choice = self::parseChoice(post('choice', '-1'), $J, $settings);
                if ($choice === null) {
                    $error = self::t($lang, 'choose');
                } else {
                    $state['answers'][$next] = $choice;
                    redirect($self);
                }
            } elseif ($action !== '') {
                redirect($self);
            }
        }

        if ($next > $T) {
            $target = $settings['return_url'] !== '' ? self::fillUrl($settings['return_url'], 'VORSCHAU', 'complete') : null;
            render('survey/preview_end', [
                'lang' => $lang, 'target' => $target,
                'restart' => survey_url($study['slug'], ['preview' => 1, 'restart' => 1, 'v' => $state['version']]),
                'back' => url('study', ['id' => $study['id']]),
            ], 'survey/layout');
            return;
        }
        if ($next === 1 && trim($settings['intro']) !== '' && !$state['intro']) {
            self::renderIntro($settings, $self, true);
            return;
        }
        self::renderTask($study, $settings, $meta, (int)$state['version'], $next, $state['order'], $self, $error, true);
    }

    // ---------------------------------------------------------------- rendering

    private static function renderIntro(array $settings, string $action, bool $preview): void
    {
        render('survey/intro', [
            'lang' => $settings['lang'], 'intro' => $settings['intro'], 'action' => $action, 'preview' => $preview,
        ], 'survey/layout');
    }

    private static function renderTask(array $study, array $settings, array $meta, int $version, int $task, ?array $order, string $action, ?string $error, bool $preview): void
    {
        $attrs = Study::attributes((int)$study['id']);
        $labels = [];
        foreach ($attrs as $a) {
            foreach ($a['levels'] as $l) {
                $labels[$l['id']] = $l['label'];
            }
        }
        $tasks = Design::versionTasks((int)$study['id'], $version);
        if (!isset($tasks[$task])) {
            self::message($settings['lang'], self::t($settings['lang'], 'nodesign'), 500, $preview);
        }
        $positions = ($order && count($order) === count($attrs)) ? $order : array_keys($attrs);
        $cards = [];
        foreach ($tasks[$task] as $alt => $levelIds) {
            $rows = [];
            foreach ($positions as $k) {
                $rows[] = ['name' => $attrs[$k]['name'], 'label' => $labels[$levelIds[$k]] ?? '?'];
            }
            $cards[$alt] = $rows;
        }
        if ($error !== null) {
            http_response_code(422);
        }
        render('survey/task', [
            'lang' => $settings['lang'], 'settings' => $settings, 'cards' => $cards, 'task' => $task,
            'tasks' => (int)$meta['tasks'], 'action' => $action, 'error' => $error, 'preview' => $preview,
            'version' => $version,
        ], 'survey/layout');
    }

    public static function message(string $lang, string $text, int $code = 200, bool $preview = false, string $title = ''): never
    {
        http_response_code($code);
        render('survey/message', ['lang' => $lang, 'text' => $text, 'title' => $title, 'preview' => $preview], 'survey/layout');
        exit;
    }
}
