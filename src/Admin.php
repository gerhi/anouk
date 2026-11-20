<?php
declare(strict_types=1);

/** Admin area: one method per route. Every POST checks the CSRF token. */
final class Admin
{
    private const ROUTES = [
        'login'               => 'login',
        'logout'              => 'logout',
        'lang'                => 'language',
        'overview'            => 'overview',
        'dashboard'           => 'overview',
        'studies'             => 'studies',
        'study.new'           => 'studyNew',
        'study'               => 'study',
        'study.settings'      => 'studySettings',
        'study.attributes'    => 'studyAttributes',
        'study.varnames'      => 'studyVarNames',
        'study.design'        => 'studyDesign',
        'study.upload'        => 'studyUpload',
        'study.generate'      => 'studyGenerate',
        'study.design.delete' => 'studyDesignDelete',
        'study.design.csv'    => 'studyDesignCsv',
        'study.rscript'       => 'studyRScript',
        'study.status'        => 'studyStatus',
        'study.export'        => 'studyExport',
        'study.delete'        => 'studyDelete',
        'users'               => 'users',
        'user.new'            => 'userNew',
        'user.edit'           => 'userEdit',
        'user.delete'         => 'userDelete',
        'respondents'         => 'respondents',
        'respondent'          => 'respondent',
        'respondent.delete'   => 'respondentDelete',
        'study.reset'         => 'studyReset',
        'user.toggle'         => 'userToggle',
        'user.reset'          => 'userReset',
        'account'             => 'account',
        'system'              => 'system',
        'system.installer'    => 'systemDeleteInstaller',
        'system.migrate'      => 'systemMigrate',
        'system.reset'        => 'systemResetConfig',
        'help'                => 'help',
    ];

    public static function dispatch(string $route): void
    {
        i18n_init();
        $u = Auth::user(); // sets the account's language
        if (is_post()) {
            csrf_check();
        }
        if ($u && Migrator::pending() && !in_array($route, ['logout', 'lang', 'help', 'system', 'system.migrate'], true)) {
            if (Auth::isAdmin($u)) {
                redirect(url('system')); // the update is offered there
            }
            abort(503, t('A database update is pending. Please ask the administrator to log in and run it.'));
        }
        if ($u && (int)($u['must_change_password'] ?? 0) === 1 && !in_array($route, ['account', 'logout', 'lang', 'help'], true)) {
            redirect(url('account')); // a starting password has to be replaced first
        }
        if (!isset(self::ROUTES[$route])) {
            abort(404, t('This page does not exist.'));
        }
        $method = self::ROUTES[$route];
        self::$method();
    }

    private static function studyFromRequest(): array
    {
        return Auth::study((int)($_GET['id'] ?? 0));
    }

    private static function requireDraft(array $study): void
    {
        if ($study['status'] !== 'draft') {
            flash('error', t('Attributes and design can only be changed while the study is a draft.'));
            redirect(url('study', ['id' => $study['id']]));
        }
    }

    // ---------------------------------------------------------------- session and language

    private static function login(): void
    {
        if (Auth::user()) {
            redirect(url('overview'));
        }
        $error = null;
        if (is_post()) {
            $email = post('email');
            $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
            $wait = Throttle::wait($email, $ip);
            if ($wait > 0) {
                $error = t('Too many failed attempts. Please try again in %d minutes.', (int)ceil($wait / 60));
            } elseif (Auth::attempt($email, (string)($_POST['password'] ?? ''))) {
                Throttle::clear($email, $ip);
                redirect(url('overview'));
            } else {
                Throttle::fail($email, $ip);
                $wait = Throttle::wait($email, $ip);
                $error = $wait > 0
                    ? t('Too many failed attempts. Please try again in %d minutes.', (int)ceil($wait / 60))
                    : t('E-mail address or password is wrong.');
            }
        }
        render('login', ['error' => $error, 'email' => post('email')], 'layout_plain');
    }

    private static function logout(): void
    {
        if (is_post()) {
            Auth::logout();
        }
        redirect(url('login'));
    }

    /** Switches the interface language (stored with the account and in a cookie), then goes back. */
    private static function language(): void
    {
        $lang = (string)($_GET['set'] ?? '');
        if (lang_exists($lang)) {
            setcookie('anouk_lang', $lang, [
                'expires'  => time() + 365 * 24 * 3600,
                'path'     => (base_path() ?: '') . '/',
                'secure'   => is_https(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            if ($u = Auth::user()) {
                Db::exec('UPDATE {users} SET language = ? WHERE id = ?', [$lang, $u['id']]);
            }
        }
        redirect(self::safeBack((string)($_GET['back'] ?? '')));
    }

    /**
     * Only return to pages of this application: the path must be index.php in the ANOUK folder,
     * and the query may hold only URL-safe characters. A looser check let "/\\evil.example"
     * through, which browsers read as "//evil.example".
     */
    public static function safeBack(string $back): string
    {
        $pattern = '~^' . preg_quote(base_path(), '~') . '/admin/(index\.php(\?[A-Za-z0-9._\~%&=+\-]*)?)?$~D';
        return preg_match($pattern, $back) ? $back : url('overview');
    }

    private static function account(): void
    {
        $u = Auth::require();
        if (is_post()) {
            $current = (string)($_POST['current'] ?? '');
            $new = (string)($_POST['new'] ?? '');
            if (!password_verify($current, $u['password_hash'])) {
                flash('error', t('The current password is wrong.'));
            } elseif (mb_strlen($new) < 10) {
                flash('error', t('The new password needs at least 10 characters.'));
            } elseif ($new !== (string)($_POST['repeat'] ?? '')) {
                flash('error', t('The two new passwords do not match.'));
            } else {
                Auth::setPassword((int)$u['id'], $new);
                flash('success', t('Password changed.'));
            }
            redirect(url('account'));
        }
        render('account', ['user' => $u, 'active' => 'account']);
    }

    // ---------------------------------------------------------------- overview and studies

    private static function ownStudies(array $u): array
    {
        $sql = "SELECT s.*, u.name AS owner_name,
                  (SELECT COUNT(*) FROM {respondents} r WHERE r.study_id = s.id AND r.is_test = 0 AND r.status = 'complete') AS n_complete,
                  (SELECT COUNT(*) FROM {respondents} r WHERE r.study_id = s.id AND r.is_test = 0) AS n_started,
                  (SELECT MAX(r.last_seen_at) FROM {respondents} r WHERE r.study_id = s.id) AS last_activity
                FROM {studies} s JOIN {users} u ON u.id = s.owner_id"
            . (Auth::isAdmin($u) ? '' : ' WHERE s.owner_id = ?')
            . ' ORDER BY s.updated_at DESC';
        return Db::all($sql, Auth::isAdmin($u) ? [] : [$u['id']]);
    }

    private static function overview(): void
    {
        $u = Auth::require();
        $studies = self::ownStudies($u);
        $ids = array_map(fn($s) => (int)$s['id'], $studies);
        $week = 0;
        if ($ids) {
            $week = (int)Db::val(
                "SELECT COUNT(*) FROM {respondents} WHERE status = 'complete' AND is_test = 0 AND completed_at >= ? AND study_id IN ("
                . implode(',', $ids) . ')',
                [now(-7 * 86400)]
            );
        }
        $accounts = Auth::isAdmin($u)
            ? Db::all('SELECT u.*, (SELECT COUNT(*) FROM {studies} s WHERE s.owner_id = u.id) AS n_studies FROM {users} u ORDER BY u.active DESC, u.role, u.name')
            : [];
        render('overview', [
            'studies'  => $studies,
            'figures'  => [
                'studies'  => count($studies),
                'field'    => count(array_filter($studies, fn($s) => $s['status'] === 'field')),
                'complete' => array_sum(array_map(fn($s) => (int)$s['n_complete'], $studies)),
                'week'     => $week,
            ],
            'accounts' => $accounts,
            'isAdmin'  => Auth::isAdmin($u),
            'active'   => 'overview',
        ]);
    }

    private static function studies(): void
    {
        $u = Auth::require();
        render('studies', ['studies' => self::ownStudies($u), 'isAdmin' => Auth::isAdmin($u), 'active' => 'studies']);
    }

    private static function studyNew(): void
    {
        $u = Auth::require();
        $title = post('title');
        $slug = mb_strtolower(post('slug'));
        $lang = post('lang', i18n_lang());
        $errors = [];
        if (is_post()) {
            if ($title === '' || mb_strlen($title) > 255) {
                $errors[] = t('Enter a title (at most 255 characters).');
            }
            if (!Study::validSlug($slug)) {
                $errors[] = t('The link name may contain only lower-case letters, digits and hyphens (2 to 63 characters).');
            } elseif (Study::bySlug($slug)) {
                $errors[] = t('This link name is already taken.');
            }
            if (!$errors) {
                $id = Study::create((int)$u['id'], $title, $slug, $lang);
                flash('success', t('Study created. Next, define the attributes or upload a design straight away.'));
                redirect(url('study', ['id' => $id]));
            }
        }
        render('study_new', ['title' => $title, 'slug' => $slug, 'lang' => $lang, 'errors' => $errors, 'active' => 'study.new']);
    }

    private static function study(): void
    {
        $study = self::studyFromRequest();
        $sid = (int)$study['id'];
        $attrs = Study::attributes($sid);
        $meta = Study::meta($study);
        render('study', [
            'study'       => $study,
            'settings'    => Study::settings($study),
            'meta'        => $meta,
            'attrs'       => $attrs,
            'owner'       => Db::one('SELECT name, email FROM {users} WHERE id = ?', [$study['owner_id']]),
            'stats'       => Study::fieldStats($sid),
            'transitions' => Study::transitions($study),
            'diagnosis'   => !empty($meta['versions']) ? Design::diagnose($sid, $attrs) : [],
            'isAdmin'     => Auth::isAdmin(),
            'active'      => 'studies',
        ]);
    }

    private static function studySettings(): void
    {
        $study = self::studyFromRequest();
        $s = Study::settings($study);
        $errors = [];
        if (is_post()) {
            $title = post('title');
            $new = [
                'lang'                 => lang_exists(post('lang')) ? post('lang') : 'en',
                'question'             => post('question'),
                'intro'                => post('intro'),
                'none_option'          => isset($_POST['none_option']),
                'none_label'           => post('none_label'),
                'randomize_attributes' => isset($_POST['randomize_attributes']),
                'show_progress'        => isset($_POST['show_progress']),
                'return_url'           => post('return_url'),
                'error_url'            => post('error_url'),
                'release_minutes'      => (int)post('release_minutes', '60'),
                'test_prefix'          => post('test_prefix'),
            ];
            if ($title === '') {
                $errors[] = t('The title must not be empty.');
            }
            foreach (['return_url' => t('return URL'), 'error_url' => t('error URL')] as $k => $label) {
                if ($new[$k] !== '' && !preg_match('~^https?://~i', $new[$k])) {
                    $errors[] = t('The %s must start with http:// or https://.', $label);
                }
            }
            if ($new['release_minutes'] < 5 || $new['release_minutes'] > 10080) {
                $errors[] = t('The release time must be between 5 minutes and 7 days.');
            }
            if ($new['none_option'] !== (bool)$s['none_option'] && Study::respondentCount((int)$study['id']) > 0) {
                $errors[] = t('The none option can no longer be changed because data exist.');
            }
            if (!$errors) {
                Study::saveSettings((int)$study['id'], $new);
                Db::exec('UPDATE {studies} SET title = ? WHERE id = ?', [mb_substr($title, 0, 255), $study['id']]);
                flash('success', t('Settings saved.'));
                redirect(url('study', ['id' => $study['id']]));
            }
            $s = $new;
            $study['title'] = $title;
        }
        render('study_settings', ['study' => $study, 's' => $s, 'errors' => $errors, 'active' => 'studies']);
    }

    private static function studyAttributes(): void
    {
        $study = self::studyFromRequest();
        $sid = (int)$study['id'];
        $hasDesign = Study::hasDesign($sid);
        $text = Design::attributesToText(Study::attributes($sid));
        $errors = [];
        if (is_post()) {
            self::requireDraft($study);
            if ($hasDesign) {
                flash('error', t('A design exists already. Delete the design first, then change the attributes.'));
                redirect(url('study.attributes', ['id' => $sid]));
            }
            $text = (string)($_POST['attributes'] ?? '');
            [$attrs, $errors] = Design::parseAttributeText($text);
            if (!$errors) {
                $kept = array_column(Study::attributes($sid), 'var_name', 'name');
                foreach ($attrs as &$a) {
                    $a['var_name'] = $kept[$a['name']] ?? ''; // an attribute that keeps its label keeps its variable name
                }
                unset($a);
                Study::saveAttributes($sid, $attrs);
                flash('success', t('%d attributes saved.', count($attrs)));
                redirect(url('study', ['id' => $sid]));
            }
        }
        render('study_attributes', [
            'study' => $study, 'text' => $text, 'errors' => $errors, 'attrs' => Study::attributes($sid),
            'locked' => $hasDesign || $study['status'] !== 'draft', 'active' => 'studies',
        ]);
    }

    /** Variable names can be changed at any time: they only name the export columns. */
    private static function studyVarNames(): void
    {
        $study = self::studyFromRequest();
        $sid = (int)$study['id'];
        if (is_post()) {
            $names = is_array($_POST['var'] ?? null) ? array_map(fn($v) => is_string($v) ? $v : '', $_POST['var']) : [];
            $errors = Study::saveVarNames($sid, $names);
            foreach ($errors as $e) {
                flash('error', $e);
            }
            if (!$errors) {
                flash('success', t('Variable names saved.'));
            }
        }
        redirect(url('study.attributes', ['id' => $sid]));
    }

    private static function studyDesign(): void
    {
        $study = self::studyFromRequest();
        render('study_design', [
            'study'  => $study,
            'attrs'  => Study::attributes((int)$study['id']),
            'meta'   => Study::meta($study),
            'result' => $_SESSION['import_result'] ?? null,
            'active' => 'studies',
        ]);
        unset($_SESSION['import_result']);
    }

    private static function studyUpload(): void
    {
        $study = self::studyFromRequest();
        self::requireDraft($study);
        $file = $_FILES['design'] ?? null;
        $back = url('study.design', ['id' => $study['id']]);
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            $_SESSION['import_result'] = ['ok' => false, 'errors' => [t('Choose a CSV file.')], 'info' => []];
            redirect($back);
        }
        if ($file['size'] > 20 * 1024 * 1024) {
            $_SESSION['import_result'] = ['ok' => false, 'errors' => [t('The file is larger than 20 MB.')], 'info' => []];
            redirect($back);
        }
        $map = ['version' => post('col_version'), 'task' => post('col_task'), 'alt' => post('col_alt'), 'ignore' => post('col_ignore')];
        $result = Design::import($study, (string)file_get_contents($file['tmp_name']), $map, basename((string)$file['name']));
        if ($result['ok']) {
            foreach ($result['info'] as $msg) {
                flash('success', $msg);
            }
            redirect(url('study', ['id' => $study['id']]));
        }
        $_SESSION['import_result'] = $result;
        redirect($back);
    }

    private static function studyGenerate(): void
    {
        $study = self::studyFromRequest();
        self::requireDraft($study);
        $seedRaw = post('seed');
        $seed = preg_match('/^\d{1,9}$/', $seedRaw) ? (int)$seedRaw : random_int(1, 999999999);
        try {
            $r = Design::generateForStudy($study, (int)post('versions'), (int)post('tasks'), (int)post('alts'), post('method'), $seed);
            flash('success', t('Design generated: %1$d versions × %2$d tasks × %3$d alternatives (seed %4$d).', $r['versions'], $r['tasks'], $r['alts'], $seed));
            redirect(url('study', ['id' => $study['id']]));
        } catch (DesignError $e) {
            $_SESSION['import_result'] = ['ok' => false, 'errors' => [$e->getMessage()], 'info' => []];
            redirect(url('study.design', ['id' => $study['id']]));
        }
    }

    private static function studyDesignDelete(): void
    {
        $study = self::studyFromRequest();
        self::requireDraft($study);
        if (!is_post()) {
            redirect(url('study', ['id' => $study['id']]));
        }
        if (Study::respondentCount((int)$study['id']) > 0) {
            flash('error', t('Data exist already; the design cannot be deleted.'));
        } else {
            Design::delete((int)$study['id']);
            flash('success', t('Design deleted.'));
        }
        redirect(url('study', ['id' => $study['id']]));
    }

    private static function studyDesignCsv(): void
    {
        $study = self::studyFromRequest();
        Design::downloadCsv($study);
        exit;
    }

    private static function studyRScript(): void
    {
        $study = self::studyFromRequest();
        $attrs = Study::attributes((int)$study['id']);
        if (!$attrs) {
            flash('error', t('Define attributes and levels before downloading the R script.'));
            redirect(url('study.design', ['id' => $study['id']]));
        }
        $num = fn(string $k, int $d, int $min, int $max) => max($min, min($max, (int)($_GET[$k] ?? $d)));
        $method = (string)($_GET['method'] ?? 'minoverlap');
        if (!isset(RScript::METHODS[$method])) {
            $method = 'minoverlap';
        }
        $seedRaw = (string)($_GET['seed'] ?? '');
        $seed = preg_match('/^\d{1,9}$/', $seedRaw) ? (int)$seedRaw : random_int(1, 999999);
        $script = RScript::build($study, $attrs, $num('n_resp', 300, 1, 10000), $num('n_q', 12, 1, 50), $num('n_alts', 3, 2, 10), $method, $seed);
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="anouk_' . $study['slug'] . '_cbctools.R"');
        echo $script;
        exit;
    }

    private static function studyStatus(): void
    {
        $study = self::studyFromRequest();
        $to = post('to');
        if (!is_post() || !in_array($to, Study::transitions($study), true)) {
            flash('error', t('This status change is not possible.'));
            redirect(url('study', ['id' => $study['id']]));
        }
        if ($to === 'field' && !Study::hasDesign((int)$study['id'])) {
            flash('error', t('Without a design the study cannot go into the field.'));
            redirect(url('study', ['id' => $study['id']]));
        }
        Db::exec('UPDATE {studies} SET status = ?, updated_at = ? WHERE id = ?', [$to, now(), $study['id']]);
        flash('success', t('Status: %s.', status_label($to)));
        redirect(url('study', ['id' => $study['id']]));
    }

    private static function studyExport(): void
    {
        $study = self::studyFromRequest();
        $format = is_string($_GET['format'] ?? null) ? $_GET['format'] : 'long';
        Export::send($study, $format, ($_GET['scope'] ?? 'complete') === 'complete', isset($_GET['tests']), ($_GET['sep'] ?? ',') === ';' ? ';' : ',');
        exit;
    }

    private static function studyDelete(): void
    {
        $study = self::studyFromRequest();
        if (!is_post()) {
            redirect(url('study', ['id' => $study['id']]));
        }
        if (post('confirm') !== $study['slug']) {
            flash('error', t('To delete, type the link name of the study exactly.'));
            redirect(url('study', ['id' => $study['id']]));
        }
        Study::delete((int)$study['id']);
        flash('success', t('Study “%s” and all its data deleted.', $study['title']));
        redirect(url('studies'));
    }

    // ---------------------------------------------------------------- accounts (administrator only)

    private static function users(): void
    {
        $me = Auth::requireAdmin();
        $users = Db::all('SELECT u.*, (SELECT COUNT(*) FROM {studies} s WHERE s.owner_id = u.id) AS n_studies
                          FROM {users} u ORDER BY u.active DESC, u.role, u.name');
        render('users', ['users' => $users, 'me' => $me, 'newPassword' => $_SESSION['new_password'] ?? null, 'active' => 'users']);
        unset($_SESSION['new_password']);
    }

    private static function userNew(): void
    {
        Auth::requireAdmin();
        $name = post('name');
        $email = Auth::normEmail(post('email'));
        $role = post('role') === 'admin' ? 'admin' : 'leader';
        $lang = lang_exists(post('language')) ? post('language') : 'en';
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', t('Enter a name and a valid e-mail address.'));
        } elseif (Db::val('SELECT COUNT(*) FROM {users} WHERE email = ?', [$email])) {
            flash('error', t('This e-mail address is already registered.'));
        } else {
            $pw = random_password();
            Auth::createUser($name, $email, $role, $pw, $lang, true);
            $_SESSION['new_password'] = ['email' => $email, 'password' => $pw];
            flash('success', t('Account for %s created.', $name));
        }
        redirect(url('users'));
    }

    private static function userToggle(): void
    {
        $me = Auth::requireAdmin();
        $u = Db::one('SELECT * FROM {users} WHERE id = ?', [(int)post('user_id')]);
        if (!$u) {
            redirect(url('users'));
        }
        if ((int)$u['id'] === (int)$me['id']) {
            flash('error', t('You cannot deactivate your own account.'));
        } elseif ((int)$u['active'] === 1 && $u['role'] === 'admin' && Auth::activeAdmins() <= 1) {
            flash('error', t('The last active administrator cannot be deactivated.'));
        } else {
            Db::exec('UPDATE {users} SET active = 1 - active WHERE id = ?', [$u['id']]);
            flash('success', (int)$u['active'] === 1 ? t('Account deactivated.') : t('Account activated.'));
        }
        redirect(url('user.edit', ['id' => $u['id']]));
    }

    private static function userReset(): void
    {
        Auth::requireAdmin();
        $u = Db::one('SELECT id, email FROM {users} WHERE id = ?', [(int)post('user_id')]);
        if (!$u) {
            redirect(url('users'));
        }
        $pw = random_password();
        Auth::setStartPassword((int)$u['id'], $pw);
        $_SESSION['new_password'] = ['email' => $u['email'], 'password' => $pw];
        flash('success', t('New starting password created. It has to be changed at the next login.'));
        redirect(url('user.edit', ['id' => $u['id']]));
    }

    private static function userEdit(): void
    {
        $me = Auth::requireAdmin();
        $u = Db::one('SELECT * FROM {users} WHERE id = ?', [(int)($_GET['id'] ?? 0)]);
        if (!$u) {
            abort(404, t('This account was not found.'));
        }
        $errors = [];
        if (is_post()) {
            $name = post('name');
            $email = Auth::normEmail(post('email'));
            $role = post('role') === 'admin' ? 'admin' : 'leader';
            $lang = lang_exists(post('language')) ? post('language') : 'en';
            $self = (int)$u['id'] === (int)$me['id'];
            if ($name === '' || mb_strlen($name) > 190) {
                $errors[] = t('Enter a name.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = t('Enter a valid e-mail address.');
            } elseif (Db::val('SELECT COUNT(*) FROM {users} WHERE email = ? AND id <> ?', [$email, $u['id']])) {
                $errors[] = t('This e-mail address is already registered.');
            }
            if ($u['role'] === 'admin' && $role !== 'admin') {
                if ($self) {
                    $errors[] = t('You cannot take away your own administrator role.');
                } elseif ((int)$u['active'] === 1 && Auth::activeAdmins() <= 1) {
                    $errors[] = t('The last active administrator must keep this role.');
                }
            }
            if (!$errors) {
                Db::exec('UPDATE {users} SET name = ?, email = ?, role = ?, language = ? WHERE id = ?', [$name, $email, $role, $lang, $u['id']]);
                flash('success', t('Account updated.'));
                redirect(url('user.edit', ['id' => $u['id']]));
            }
            $u = array_merge($u, ['name' => $name, 'email' => $email, 'role' => $role, 'language' => $lang]);
        }
        render('user_edit', [
            'u'           => $u,
            'me'          => $me,
            'errors'      => $errors,
            'nStudies'    => (int)Db::val('SELECT COUNT(*) FROM {studies} WHERE owner_id = ?', [$u['id']]),
            'others'      => Db::all('SELECT id, name, email FROM {users} WHERE active = 1 AND id <> ? ORDER BY name', [$u['id']]),
            'newPassword' => $_SESSION['new_password'] ?? null,
            'active'      => 'users',
        ]);
        unset($_SESSION['new_password']);
    }

    private static function userDelete(): void
    {
        $me = Auth::requireAdmin();
        $u = Db::one('SELECT * FROM {users} WHERE id = ?', [(int)post('user_id')]);
        if (!$u || !is_post()) {
            redirect(url('users'));
        }
        $back = url('user.edit', ['id' => $u['id']]);
        $n = (int)Db::val('SELECT COUNT(*) FROM {studies} WHERE owner_id = ?', [$u['id']]);
        $to = (int)post('transfer_to');
        if ((int)$u['id'] === (int)$me['id']) {
            flash('error', t('You cannot delete your own account.'));
            redirect($back);
        }
        if ($u['role'] === 'admin' && (int)$u['active'] === 1 && Auth::activeAdmins() <= 1) {
            flash('error', t('The last active administrator cannot be deleted.'));
            redirect($back);
        }
        if ($n > 0 && ($to === (int)$u['id'] || !Db::val('SELECT id FROM {users} WHERE id = ? AND active = 1', [$to]))) {
            flash('error', t('Choose the account that takes over the studies.'));
            redirect($back);
        }
        if (post('confirm') !== '1') {
            flash('error', t('Tick the confirmation box to delete the account.'));
            redirect($back);
        }
        Db::tx(function () use ($u, $n, $to) {
            if ($n > 0) {
                Db::exec('UPDATE {studies} SET owner_id = ? WHERE owner_id = ?', [$to, $u['id']]);
            }
            Db::exec('DELETE FROM {users} WHERE id = ?', [$u['id']]);
        });
        flash('success', $n > 0 ? t('Account “%1$s” deleted; %2$d studies were transferred.', $u['name'], $n) : t('Account “%s” deleted.', $u['name']));
        redirect(url('users'));
    }

    // ---------------------------------------------------------------- respondents

    /** Respondents of the studies this account may see, with filters and pages. */
    private static function respondents(): void
    {
        $u = Auth::require();
        $studies = Db::all('SELECT id, title, slug, settings, design_meta FROM {studies}' . (Auth::isAdmin($u) ? '' : ' WHERE owner_id = ?') . ' ORDER BY title', Auth::isAdmin($u) ? [] : [$u['id']]);
        $byId = [];
        foreach ($studies as $s) {
            $byId[(int)$s['id']] = $s;
        }
        $f = [
            'study'  => (int)($_GET['study'] ?? 0),
            'status' => in_array($_GET['status'] ?? '', ['complete', 'active', 'dropped', 'test'], true) ? $_GET['status'] : '',
            'q'      => trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : ''),
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = 50;
        $rows = [];
        $total = 0;
        $ids = $f['study'] && isset($byId[$f['study']]) ? [$f['study']] : array_keys($byId);
        if ($ids) {
            $where = 'r.study_id IN (' . implode(',', array_map('intval', $ids)) . ')';
            $params = [];
            if ($f['q'] !== '') {
                $where .= ' AND INSTR(r.ext_id, ?) > 0'; // plain substring search, same in MySQL and SQLite
                $params[] = $f['q'];
            }
            if ($f['status'] === 'complete') {
                $where .= " AND r.status = 'complete' AND r.is_test = 0";
            } elseif ($f['status'] === 'active' || $f['status'] === 'dropped') {
                $where .= " AND r.status = 'active' AND r.is_test = 0";
            } elseif ($f['status'] === 'test') {
                $where .= ' AND r.is_test = 1';
            }
            $all = Db::all(
                "SELECT r.*, (SELECT COUNT(*) FROM {answers} a WHERE a.respondent_id = r.id) AS n_answers
                 FROM {respondents} r WHERE $where ORDER BY r.last_seen_at DESC, r.id DESC",
                $params
            );
            foreach ($all as $r) {
                $r['state'] = self::respondentState($r, $byId[(int)$r['study_id']]);
                if (($f['status'] === 'active' || $f['status'] === 'dropped') && $r['state'] !== $f['status']) {
                    continue;
                }
                $rows[] = $r;
            }
            $total = count($rows);
            $rows = array_slice($rows, ($page - 1) * $per, $per);
        }
        render('respondents', [
            'rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => max(1, (int)ceil($total / $per)),
            'f' => $f, 'studies' => $byId, 'isAdmin' => Auth::isAdmin($u), 'active' => 'respondents',
        ]);
    }

    /** complete | active | dropped (no activity within the study's release time) */
    public static function respondentState(array $r, array $study): string
    {
        if ($r['status'] === 'complete') {
            return 'complete';
        }
        $minutes = (int)(Study::settings($study)['release_minutes'] ?? 60);
        return strtotime($r['last_seen_at'] . ' UTC') < time() - 60 * $minutes ? 'dropped' : 'active';
    }

    private static function loadRespondent(int $id): array
    {
        $r = Db::one('SELECT * FROM {respondents} WHERE id = ?', [$id]);
        if (!$r) {
            abort(404, t('This respondent was not found.'));
        }
        $study = Auth::study((int)$r['study_id']); // 404 for studies of other accounts
        return [$r, $study];
    }

    private static function respondent(): void
    {
        [$r, $study] = self::loadRespondent((int)($_GET['id'] ?? 0));
        $answers = Db::all('SELECT * FROM {answers} WHERE respondent_id = ? ORDER BY task', [$r['id']]);
        $tasks = Design::versionTasks((int)$study['id'], (int)$r['version']);
        $attrs = Study::attributes((int)$study['id']);
        $labels = [];
        foreach ($attrs as $a) {
            foreach ($a['levels'] as $l) {
                $labels[$l['id']] = $l['label'];
            }
        }
        $r['state'] = self::respondentState($r, $study);
        render('respondent', [
            'r' => $r, 'study' => $study, 'answers' => $answers, 'tasks' => $tasks, 'attrs' => $attrs,
            'labels' => $labels, 'meta' => Study::meta($study), 'active' => 'respondents',
        ]);
    }

    private static function respondentDelete(): void
    {
        [$r, $study] = self::loadRespondent((int)post('respondent_id'));
        if (is_post()) {
            Db::tx(function () use ($r) {
                Db::exec('DELETE FROM {answers} WHERE respondent_id = ?', [$r['id']]);
                Db::exec('DELETE FROM {respondents} WHERE id = ?', [$r['id']]);
            });
            flash('success', t('Respondent “%s” and their answers deleted.', $r['ext_id']));
        }
        redirect(url('respondents', ['study' => $study['id']]));
    }

    private static function studyReset(): void
    {
        $study = self::studyFromRequest();
        if (!is_post()) {
            redirect(url('study', ['id' => $study['id']]));
        }
        if (post('confirm') !== $study['slug']) {
            flash('error', t('To delete, type the link name of the study exactly.'));
            redirect(url('study', ['id' => $study['id']]));
        }
        $n = Study::respondentCount((int)$study['id']);
        Db::tx(function () use ($study) {
            Db::exec('DELETE FROM {answers} WHERE respondent_id IN (SELECT id FROM {respondents} WHERE study_id = ?)', [$study['id']]);
            Db::exec('DELETE FROM {respondents} WHERE study_id = ?', [$study['id']]);
        });
        flash('success', t('%d respondents and their answers deleted. The study and its design are kept.', $n));
        redirect(url('study', ['id' => $study['id']]));
    }

    // ---------------------------------------------------------------- system (administrator only)

    private static function system(): void
    {
        Auth::requireAdmin();
        render('system', [
            'schema'    => Migrator::dbVersion(),
            'schemaCode' => Migrator::codeVersion(),
            'pendingMigrations' => Migrator::pendingFiles(),
            'prefix'    => Db::prefix(),
            'dbVersion' => Db::serverVersion(),
            'installer' => is_file(Installer::installerFile()),
            'counts'    => [
                t('Accounts')    => (int)Db::val('SELECT COUNT(*) FROM {users}'),
                t('Studies')     => (int)Db::val('SELECT COUNT(*) FROM {studies}'),
                t('Respondents') => (int)Db::val('SELECT COUNT(*) FROM {respondents}'),
                t('Answers')     => (int)Db::val('SELECT COUNT(*) FROM {answers}'),
            ],
            'active'    => 'system',
        ]);
    }

    private static function systemMigrate(): void
    {
        Auth::requireAdmin();
        if (is_post() && Migrator::pending()) {
            try {
                $done = Migrator::migrate();
                flash('success', t('Database updated to version %d (%s).', Migrator::dbVersion(true), implode(', ', $done)));
            } catch (Throwable $e) {
                error_log('ANOUK migration: ' . $e);
                flash('error', t('The database update failed: %s', $e->getMessage()));
            }
        }
        redirect(url('system'));
    }

    private static function systemDeleteInstaller(): void
    {
        Auth::requireAdmin();
        if (is_post()) {
            Installer::deleteInstaller()
                ? flash('success', t('install.php has been deleted.'))
                : flash('error', t('install.php could not be deleted. Delete it by FTP.'));
        }
        redirect(url('system'));
    }

    private static function systemResetConfig(): void
    {
        Auth::requireAdmin();
        if (!is_post()) {
            redirect(url('system'));
        }
        if (!is_file(Installer::installerFile())) {
            flash('error', t('install.php is missing. Upload install.php again first, otherwise ANOUK cannot be set up afterwards.'));
            redirect(url('system'));
        }
        if (post('confirm') !== 'DELETE') {
            flash('error', t('To confirm, type DELETE.'));
            redirect(url('system'));
        }
        if (!Installer::deleteConfig()) {
            flash('error', t('config.php could not be deleted. Delete it by FTP.'));
            redirect(url('system'));
        }
        Installer::deleteLock();
        Auth::logout();
        redirect(base_path() . '/install.php');
    }

    // ---------------------------------------------------------------- help

    /** Shows README.md (English) or README.de.md (German). */
    private static function help(): void
    {
        Auth::require();
        $lang = ($_GET['lang'] ?? i18n_lang()) === 'de' ? 'de' : 'en';
        $file = ANOUK_ROOT . '/' . ($lang === 'de' ? 'README.de.md' : 'README.md');
        $md = is_file($file) ? (string)file_get_contents($file) : '';
        $md = (string)preg_replace('/^!\[.*$\R?/m', '', $md); // badges and screenshots belong to GitHub, not the help page
        [$html, $toc] = md_render($md);
        $html = (string)preg_replace('#^<h1[^>]*>.*?</h1>\s*#s', '', $html, 1);
        render('help', ['html' => $html, 'toc' => $toc, 'lang' => $lang, 'active' => 'help-' . $lang]);
    }
}
