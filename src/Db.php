<?php
declare(strict_types=1);

/**
 * Thin PDO wrapper. MySQL/MariaDB in production; SQLite is used only for the test suite.
 *
 * SQL names tables as {users}, {studies} … and other prefixed objects as {prefix}name.
 * Db::sql() replaces them with the configured table prefix (config.php: db.prefix, default anouk_),
 * so several tools or installations can share one database.
 */
final class Db
{
    public const TABLES = ['meta', 'users', 'studies', 'attributes', 'levels', 'design', 'respondents', 'answers', 'login_attempts'];
    public const DEFAULT_PREFIX = 'anouk_';

    private static ?PDO $pdo = null;
    private static ?string $prefix = null;

    public static function prefix(): string
    {
        return self::$prefix ??= (string)(anouk_config()['db']['prefix'] ?? self::DEFAULT_PREFIX);
    }

    public static function setPrefix(string $prefix): void
    {
        self::$prefix = $prefix;
    }

    public static function validPrefix(string $prefix): bool
    {
        return (bool)preg_match('/^[a-z][a-z0-9_]{0,19}$/', $prefix);
    }

    /** Replaces {table} and {prefix} placeholders. */
    public static function sql(string $sql, ?string $prefix = null): string
    {
        $prefix ??= self::prefix();
        return (string)preg_replace_callback('/\{([a-z_]+)\}/', function ($m) use ($prefix) {
            if ($m[1] === 'prefix') {
                return $prefix;
            }
            return in_array($m[1], self::TABLES, true) ? $prefix . $m[1] : $m[0];
        }, $sql);
    }

    /** Prefixed table name, e.g. Db::table('users') → anouk_users. */
    public static function table(string $name): string
    {
        return self::prefix() . $name;
    }

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $c = anouk_config()['db'] ?? [];
            try {
                self::$pdo = self::connect($c['dsn'] ?? '', $c['user'] ?? null, $c['pass'] ?? null);
            } catch (PDOException $e) {
                throw new DbConnectionError($e->getMessage(), 0, $e);
            }
        }
        return self::$pdo;
    }

    /** Server version string, e.g. "8.0.36" or "10.11.6-MariaDB". */
    public static function serverVersion(): string
    {
        return (string)self::pdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    public static function connect(string $dsn, ?string $user, ?string $pass): PDO
    {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $pdo->exec('PRAGMA foreign_keys = ON');
            $pdo->exec('PRAGMA busy_timeout = 5000');
        }
        return $pdo;
    }

    public static function setPdo(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    public static function isMysql(): bool
    {
        return self::pdo()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
    }

    /** Prepared and executed statement, for reading large results row by row. */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $st = self::pdo()->prepare(self::sql($sql));
        $st->execute($params);
        return $st;
    }

    public static function all(string $sql, array $params = []): array
    {
        $st = self::pdo()->prepare(self::sql($sql));
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $st = self::pdo()->prepare(self::sql($sql));
        $st->execute($params);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    public static function val(string $sql, array $params = []): mixed
    {
        $st = self::pdo()->prepare(self::sql($sql));
        $st->execute($params);
        $v = $st->fetchColumn();
        return $v === false ? null : $v;
    }

    public static function exec(string $sql, array $params = []): int
    {
        $st = self::pdo()->prepare(self::sql($sql));
        $st->execute($params);
        return $st->rowCount();
    }

    public static function insert(string $table, array $row): int
    {
        $cols = array_keys($row);
        $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $cols) . ') VALUES ('
            . implode(', ', array_fill(0, count($cols), '?')) . ')';
        self::exec($sql, array_values($row));
        return (int)self::pdo()->lastInsertId();
    }

    /** Inserts many rows with multi-row INSERT statements. */
    public static function insertMany(string $table, array $cols, array $rows, int $chunk = 400): void
    {
        foreach (array_chunk($rows, $chunk) as $part) {
            $ph = '(' . implode(', ', array_fill(0, count($cols), '?')) . ')';
            $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $cols) . ') VALUES '
                . implode(', ', array_fill(0, count($part), $ph));
            $params = [];
            foreach ($part as $r) {
                foreach ($r as $v) {
                    $params[] = $v;
                }
            }
            self::exec($sql, $params);
        }
    }

    private static int $txDepth = 0;

    /** Runs $fn in a transaction. Nested calls join the outer transaction. */
    public static function tx(callable $fn): mixed
    {
        if (self::$txDepth > 0) {
            self::$txDepth++;
            try {
                return $fn();
            } finally {
                self::$txDepth--;
            }
        }
        $pdo = self::pdo();
        $pdo->beginTransaction();
        self::$txDepth = 1;
        try {
            $result = $fn();
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        } finally {
            self::$txDepth = 0;
        }
    }

    public static function isDuplicate(PDOException $e): bool
    {
        return ($e->errorInfo[0] ?? $e->getCode()) === '23000';
    }
}

/** The database configured in config.php cannot be reached. */
final class DbConnectionError extends RuntimeException
{
}
