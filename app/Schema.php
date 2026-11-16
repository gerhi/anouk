<?php
declare(strict_types=1);

/**
 * Brings an older database up to date. Runs once per session; each step checks
 * first, so it is safe to run repeatedly.
 */
final class Schema
{
    public const VERSION = 3;

    public static function ensure(): void
    {
        if (($_SESSION['anouk_schema'] ?? 0) >= self::VERSION) {
            return;
        }
        $pdo = Db::pdo();
        // 0.2.0: interface language per account
        if (!self::hasColumn('anouk_users', 'language')) {
            $pdo->exec("ALTER TABLE anouk_users ADD COLUMN language VARCHAR(5) NOT NULL DEFAULT 'en'");
        }
        // 0.3.0: starting passwords must be changed; respondents continue by cookie
        if (!self::hasColumn('anouk_users', 'must_change_password')) {
            $pdo->exec('ALTER TABLE anouk_users ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0');
        }
        if (!self::hasColumn('anouk_respondents', 'token_hash')) {
            $pdo->exec('ALTER TABLE anouk_respondents ADD COLUMN token_hash VARCHAR(64) NULL');
            $pdo->exec('CREATE INDEX ix_respondents_token ON anouk_respondents (token_hash)');
        }
        $_SESSION['anouk_schema'] = self::VERSION;
    }

    public static function hasColumn(string $table, string $column): bool
    {
        if (Db::isMysql()) {
            return (bool)Db::one('SHOW COLUMNS FROM ' . $table . ' LIKE ?', [$column]);
        }
        foreach (Db::all('PRAGMA table_info(' . $table . ')') as $c) {
            if ($c['name'] === $column) {
                return true;
            }
        }
        return false;
    }
}
