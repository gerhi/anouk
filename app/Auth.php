<?php
declare(strict_types=1);

/**
 * Login and permissions.
 * Roles: 'admin' (administrator, sees everything, manages accounts)
 *        'leader' (project leader, sees only own studies).
 */
final class Auth
{
    private static ?array $user = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $uid = $_SESSION['uid'] ?? null;
            if ($uid) {
                $idle = (int)(anouk_config()['admin_idle_minutes'] ?? 240) * 60;
                if (($_SESSION['last_seen'] ?? 0) < time() - $idle) {
                    unset($_SESSION['uid']); // signed out after a long pause
                    return null;
                }
                self::$user = Db::one('SELECT * FROM anouk_users WHERE id = ? AND active = 1', [(int)$uid]);
                // A session ends when its account is gone, deactivated, or its password has changed.
                if (self::$user && !hash_equals(self::mark(self::$user['password_hash']), (string)($_SESSION['pw'] ?? ''))) {
                    self::$user = null;
                }
                if (!self::$user) {
                    unset($_SESSION['uid'], $_SESSION['pw']);
                } else {
                    $_SESSION['last_seen'] = time();
                    i18n_set((string)(self::$user['language'] ?? 'en'));
                }
            }
        }
        return self::$user;
    }

    public static function attempt(string $email, string $password): bool
    {
        $u = Db::one('SELECT * FROM anouk_users WHERE email = ?', [self::normEmail($email)]);
        // Always verify against some hash, so the response time does not reveal whether an account exists.
        $hash = ($u && (int)$u['active'] === 1) ? $u['password_hash'] : self::DUMMY_HASH;
        $ok = password_verify($password, $hash);
        if ($u && (int)$u['active'] === 1 && $ok) {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$u['id'];
            $_SESSION['pw'] = self::mark($u['password_hash']);
            $_SESSION['last_seen'] = time();
            Db::exec('UPDATE anouk_users SET last_login_at = ? WHERE id = ?', [now(), $u['id']]);
            if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
                $new = password_hash($password, PASSWORD_DEFAULT);
                Db::exec('UPDATE anouk_users SET password_hash = ? WHERE id = ?', [$new, $u['id']]);
                $_SESSION['pw'] = self::mark($new);
            }
            self::$loaded = false;
            return true;
        }
        usleep(400000); // slows down guessing
        return false;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        self::$user = null;
    }

    public static function require(): array
    {
        $u = self::user();
        if (!$u) {
            redirect(url('login'));
        }
        return $u;
    }

    public static function requireAdmin(): array
    {
        $u = self::require();
        if (!self::isAdmin($u)) {
            abort(403, t('This page is for administrators only.'));
        }
        return $u;
    }

    public static function isAdmin(?array $u = null): bool
    {
        $u ??= self::user();
        return $u !== null && $u['role'] === 'admin';
    }

    public static function canAccess(array $study, ?array $u = null): bool
    {
        $u ??= self::user();
        return $u !== null && (self::isAdmin($u) || (int)$study['owner_id'] === (int)$u['id']);
    }

    /** Loads a study the current user may access. Answers 404 otherwise, so foreign studies stay invisible. */
    public static function study(int $id): array
    {
        $u = self::require();
        $s = Db::one('SELECT * FROM anouk_studies WHERE id = ?', [$id]);
        if (!$s || !self::canAccess($s, $u)) {
            abort(404, t('This study was not found.'));
        }
        return $s;
    }

    public static function normEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    /** bcrypt hash of a random string nobody knows; used when the account does not exist. */
    private const DUMMY_HASH = '$2y$10$2JeBxhlNaOISB4vM8xBvuu7gJf.Ezxu9hIPm9B3O1QqvPKrdNRvPO';

    /** Ties a session to the current password without putting the hash into the session. */
    private static function mark(string $passwordHash): string
    {
        return substr(hash('sha256', $passwordHash), 0, 32);
    }

    /** The person's own password: clears a pending change and ends their other sessions. */
    public static function setPassword(int $userId, string $password): void
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        Db::exec('UPDATE anouk_users SET password_hash = ?, must_change_password = 0 WHERE id = ?', [$hash, $userId]);
        if ((int)($_SESSION['uid'] ?? 0) === $userId) {
            $_SESSION['pw'] = self::mark($hash); // this session stays, all others end
        }
    }

    /** A starting password set by the administrator: must be changed at the next login. */
    public static function setStartPassword(int $userId, string $password): void
    {
        Db::exec('UPDATE anouk_users SET password_hash = ?, must_change_password = 1 WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $userId]);
    }

    public static function activeAdmins(): int
    {
        return (int)Db::val("SELECT COUNT(*) FROM anouk_users WHERE role = 'admin' AND active = 1");
    }

    public static function createUser(string $name, string $email, string $role, string $password, string $language = 'en', bool $mustChange = false): int
    {
        return Db::insert('anouk_users', [
            'must_change_password' => $mustChange ? 1 : 0,
            'language'      => isset(ANOUK_LANGS[$language]) ? $language : 'en',
            'email'         => self::normEmail($email),
            'name'          => trim($name),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role'          => $role === 'admin' ? 'admin' : 'leader',
            'active'        => 1,
            'created_at'    => now(),
        ]);
    }
}
