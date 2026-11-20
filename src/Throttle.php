<?php
declare(strict_types=1);

/**
 * Login throttling, stored in {login_attempts}.
 *
 * Failed logins are counted per account and per client, both only as salted SHA-256 hashes.
 * From the 5th failure within 24 hours, the next attempt for that pair has to wait
 * 1, 2, 4, 8, then 15 minutes after the last failure. Counting per pair means that someone
 * guessing from elsewhere cannot lock the real user out. Entries older than 24 hours are
 * deleted whenever a login is attempted; a successful login clears the pair.
 */
final class Throttle
{
    public const FREE_ATTEMPTS = 5;
    public const MAX_WAIT_MINUTES = 15;
    public const WINDOW_HOURS = 24;

    /** Seconds to wait before the next attempt is allowed; 0 = allowed now. */
    public static function wait(string $email, string $ip, ?int $now = null): int
    {
        if (!self::ready()) {
            return 0;
        }
        $now ??= time();
        [$e, $i] = self::keys($email, $ip);
        $row = Db::one(
            'SELECT COUNT(*) AS n, MAX(attempted_at) AS last FROM {login_attempts}
             WHERE email_hash = ? AND ip_hash = ? AND attempted_at >= ?',
            [$e, $i, gmdate('Y-m-d H:i:s', $now - self::WINDOW_HOURS * 3600)]
        );
        $n = (int)($row['n'] ?? 0);
        if ($n < self::FREE_ATTEMPTS || empty($row['last'])) {
            return 0;
        }
        $until = strtotime($row['last'] . ' UTC') + 60 * self::delayMinutes($n);
        return max(0, $until - $now);
    }

    /** Waiting time after n failures: 1, 2, 4, 8, 15, 15 … minutes from the 5th failure on. */
    public static function delayMinutes(int $failures): int
    {
        if ($failures < self::FREE_ATTEMPTS) {
            return 0;
        }
        return min(self::MAX_WAIT_MINUTES, 2 ** min(10, $failures - self::FREE_ATTEMPTS));
    }

    public static function fail(string $email, string $ip, ?int $now = null): void
    {
        if (!self::ready()) {
            return;
        }
        $now ??= time();
        [$e, $i] = self::keys($email, $ip);
        Db::exec('DELETE FROM {login_attempts} WHERE attempted_at < ?', [gmdate('Y-m-d H:i:s', $now - self::WINDOW_HOURS * 3600)]);
        Db::insert('{login_attempts}', ['email_hash' => $e, 'ip_hash' => $i, 'attempted_at' => gmdate('Y-m-d H:i:s', $now)]);
    }

    public static function clear(string $email, string $ip): void
    {
        if (self::ready()) {
            [$e, $i] = self::keys($email, $ip);
            Db::exec('DELETE FROM {login_attempts} WHERE email_hash = ? AND ip_hash = ?', [$e, $i]);
        }
    }

    /** Throttling needs schema version 2; before the update it is off. */
    private static function ready(): bool
    {
        return Migrator::dbVersion() >= 2;
    }

    /** @return array{0: string, 1: string} */
    private static function keys(string $email, string $ip): array
    {
        $secret = Migrator::secret();
        return [
            hash('sha256', $secret . '|email|' . Auth::normEmail($email)),
            hash('sha256', $secret . '|ip|' . $ip),
        ];
    }
}
