<?php
declare(strict_types=1);

/**
 * Database schema version and updates.
 *
 * The schema version is stored in {meta} (name = schema_version). sql/schema.sql always holds
 * the newest schema; sql/migrations/NNN_name.sql bring older databases up to version NNN.
 * Pending migrations are offered to the administrator in the admin area (System page);
 * they never run on their own.
 *
 * Version 1 is the state of ANOUK 1.0.x, which had no {meta} table.
 */
final class Migrator
{
    private static ?int $dbVersion = null;

    /** Folder with the migration files; tests point it to SQLite variants. */
    public static function dir(): string
    {
        return getenv('ANOUK_MIGRATIONS') ?: ANOUK_ROOT . '/sql/migrations';
    }

    /** @return array<int, string> version => file path, ascending */
    public static function files(): array
    {
        $out = [];
        foreach (glob(self::dir() . '/*.sql') ?: [] as $file) {
            if (preg_match('/^(\d{3})_[a-z0-9_]+\.sql$/', basename($file), $m)) {
                $out[(int)$m[1]] = $file;
            }
        }
        ksort($out);
        return $out;
    }

    /** Schema version this code expects. */
    public static function codeVersion(): int
    {
        $files = self::files();
        return $files ? max(1, (int)array_key_last($files)) : 1;
    }

    /** Schema version of the database: 0 = not installed, 1 = ANOUK 1.0.x. */
    public static function dbVersion(bool $fresh = false): int
    {
        if (self::$dbVersion !== null && !$fresh) {
            return self::$dbVersion;
        }
        if (self::hasTable('meta')) {
            $v = Db::val("SELECT value FROM {meta} WHERE name = 'schema_version'");
            return self::$dbVersion = $v === null ? 1 : (int)$v;
        }
        if (!self::hasTable('users')) {
            return self::$dbVersion = 0;
        }
        self::legacyColumns();
        return self::$dbVersion = 1;
    }

    public static function pending(): bool
    {
        $v = self::dbVersion();
        return $v > 0 && $v < self::codeVersion();
    }

    /** @return list<string> file names of migrations still to run */
    public static function pendingFiles(): array
    {
        $v = self::dbVersion();
        $out = [];
        foreach (self::files() as $n => $file) {
            if ($n > $v) {
                $out[] = basename($file);
            }
        }
        return $out;
    }

    /**
     * Runs all pending migrations in order. Each one is recorded right after it ran,
     * so a failure leaves the database at the last successful version.
     * @return list<string> names of the migrations that ran
     */
    public static function migrate(): array
    {
        $done = [];
        foreach (self::files() as $n => $file) {
            if ($n <= self::dbVersion(true)) {
                continue;
            }
            foreach (self::statements((string)file_get_contents($file)) as $stmt) {
                try {
                    Db::exec($stmt);
                } catch (PDOException $e) {
                    // A migration that stopped halfway may be run again: skip what already exists.
                    if (!self::alreadyApplied($e)) {
                        throw $e;
                    }
                }
            }
            $after = 'after' . $n;
            if (method_exists(self::class, $after)) {
                self::$after();
            }
            self::setVersion($n);
            $done[] = basename($file);
        }
        self::$dbVersion = null;
        return $done;
    }

    /** After a fresh installation from sql/schema.sql. */
    public static function markInstalled(): void
    {
        self::setVersion(self::codeVersion());
        self::secret();
    }

    /** Random value for hashing (login throttling); created on first use. */
    public static function secret(): string
    {
        $s = Db::val("SELECT value FROM {meta} WHERE name = 'secret'");
        if (!is_string($s) || strlen($s) < 32) {
            $s = bin2hex(random_bytes(32));
            Db::exec("REPLACE INTO {meta} (name, value) VALUES ('secret', ?)", [$s]);
        }
        return $s;
    }

    // ---------------------------------------------------------------- steps that need PHP

    /** 002: variable names for existing attributes, secret for login throttling. */
    private static function after2(): void
    {
        $rows = Db::all("SELECT id, study_id, name FROM {attributes} WHERE var_name = '' ORDER BY study_id, position");
        $used = [];
        foreach (Db::all("SELECT study_id, var_name FROM {attributes} WHERE var_name <> ''") as $r) {
            $used[(int)$r['study_id']][$r['var_name']] = true;
        }
        foreach ($rows as $r) {
            $sid = (int)$r['study_id'];
            $used[$sid] ??= [];
            Db::exec('UPDATE {attributes} SET var_name = ? WHERE id = ?', [Study::varName($r['name'], $used[$sid]), $r['id']]);
        }
        self::secret();
    }

    // ---------------------------------------------------------------- helpers

    private static function setVersion(int $v): void
    {
        Db::exec("REPLACE INTO {meta} (name, value) VALUES ('schema_version', ?)", [(string)$v]);
        self::$dbVersion = $v;
    }

    /** @return list<string> */
    public static function statements(string $sql): array
    {
        $sql = (string)preg_replace('/^\s*--.*$/m', '', $sql);
        return array_values(array_filter(array_map('trim', explode(';', $sql))));
    }

    /** Duplicate column, table or index: the statement ran before. */
    private static function alreadyApplied(PDOException $e): bool
    {
        $code = (int)($e->errorInfo[1] ?? 0);
        $msg = $e->getMessage();
        return in_array($code, [1050, 1060, 1061], true)
            || str_contains($msg, 'duplicate column') || str_contains($msg, 'already exists');
    }

    public static function hasTable(string $name): bool
    {
        $table = Db::table($name);
        if (Db::isMysql()) {
            return (bool)Db::val('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?', [$table]);
        }
        return (bool)Db::val("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = ?", [$table]);
    }

    public static function hasColumn(string $name, string $column): bool
    {
        $table = Db::table($name);
        if (Db::isMysql()) {
            return (bool)Db::val('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?', [$table, $column]);
        }
        foreach (Db::all('PRAGMA table_info(' . $table . ')') as $c) {
            if ($c['name'] === $column) {
                return true;
            }
        }
        return false;
    }

    /** Columns added by versions 0.2 and 0.3, for databases that skipped them. */
    private static function legacyColumns(): void
    {
        if (!self::hasColumn('users', 'language')) {
            Db::exec("ALTER TABLE {users} ADD COLUMN language VARCHAR(5) NOT NULL DEFAULT 'en'");
        }
        if (!self::hasColumn('users', 'must_change_password')) {
            Db::exec('ALTER TABLE {users} ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0');
        }
        if (!self::hasColumn('respondents', 'token_hash')) {
            Db::exec('ALTER TABLE {respondents} ADD COLUMN token_hash VARCHAR(64) NULL');
            Db::exec('CREATE INDEX {prefix}ix_respondents_token ON {respondents} (token_hash)');
        }
    }
}
