<?php
declare(strict_types=1);

/** Shared logic for install.php and the system page. */
final class Installer
{
    public static function configFile(): string
    {
        return getenv('ANOUK_CONFIG') ?: ANOUK_ROOT . '/config.php';
    }

    public static function installerFile(): string
    {
        return ANOUK_ROOT . '/install.php';
    }

    public static function dsn(string $host, string $port, string $name): string
    {
        return "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
    }

    /**
     * Connects and checks what the installation needs.
     * @return array{ok:bool, lines:list<array{0:bool,1:string}>, users:int, pdo:?PDO}
     */
    public static function testConnection(string $dsn, string $user, string $pass): array
    {
        $lines = [];
        try {
            $pdo = Db::connect($dsn, $user, $pass);
        } catch (PDOException $e) {
            return ['ok' => false, 'lines' => [[false, t('No connection: %s', self::explain($e))]], 'users' => 0, 'pdo' => null];
        }
        $lines[] = [true, t('Connected, server version %s.', (string)$pdo->getAttribute(PDO::ATTR_SERVER_VERSION))];
        $ok = true;
        try {
            $pdo->exec('CREATE TABLE IF NOT EXISTS anouk_install_check (id INT)');
            $pdo->exec('DROP TABLE anouk_install_check');
            $lines[] = [true, t('The user may create tables.')];
        } catch (PDOException $e) {
            $ok = false;
            $lines[] = [false, t('The user may not create tables (the CREATE privilege is missing).')];
        }
        $users = 0;
        if ($pdo->query("SHOW TABLES LIKE 'anouk_users'")->fetchColumn()) {
            $users = (int)$pdo->query('SELECT COUNT(*) FROM anouk_users')->fetchColumn();
            $lines[] = [true, t('Existing ANOUK database found (%d accounts). It will be used again; no new administrator is needed.', $users)];
        } else {
            $n = (int)$pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()')->fetchColumn();
            $lines[] = [true, $n === 0 ? t('The database is empty.') : t('The database contains %d other tables; ANOUK uses its own tables with the prefix anouk_.', $n)];
        }
        return ['ok' => $ok, 'lines' => $lines, 'users' => $users, 'pdo' => $pdo];
    }

    /** Plain-language hints for the most common connection errors. */
    private static function explain(PDOException $e): string
    {
        $msg = $e->getMessage();
        $hint = match (true) {
            str_contains($msg, '[1045]') => t('User name or password is wrong.'),
            str_contains($msg, '[1049]') => t('This database does not exist. Create it in your hosting control panel first.'),
            str_contains($msg, '[1044]') => t('This user has no rights on this database.'),
            str_contains($msg, '[2002]'), str_contains($msg, '[2005]') => t('The database server was not reached. Check host and port; sometimes the host is not “localhost”.'),
            default => '',
        };
        return $hint !== '' ? "$hint ($msg)" : $msg;
    }

    public static function runSchema(PDO $pdo): void
    {
        $sql = (string)file_get_contents(ANOUK_ROOT . '/sql/schema.mysql.sql');
        $sql = (string)preg_replace('/^\s*--.*$/m', '', $sql);
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
            $pdo->exec($stmt);
        }
    }

    /**
     * Writes config.php. Returns null on success, otherwise the file content for manual upload.
     * @return array{0: ?string, 1: string} [manual content or null, emergency reset code]
     */
    public static function writeConfig(string $dsn, string $user, string $pass, bool $forceHttps = false): array
    {
        $resetCode = implode('-', str_split(random_password(16), 4));
        $config = "<?php\n// ANOUK configuration – created by install.php on " . gmdate('Y-m-d H:i') . " UTC\nreturn " . var_export([
            'db'         => ['dsn' => $dsn, 'user' => $user, 'pass' => $pass],
            'base_url'   => '',
            'timezone'   => 'Europe/Berlin',
            'debug'      => false,
            'force_https' => $forceHttps,
            'admin_idle_minutes' => 240,
            'reset_hash' => password_hash($resetCode, PASSWORD_DEFAULT),
        ], true) . ";\n";
        $written = @file_put_contents(self::configFile(), $config) !== false;
        return [$written ? null : $config, $resetCode];
    }

    public static function checkResetCode(string $code): bool
    {
        $file = self::configFile();
        if (!is_file($file)) {
            return false;
        }
        $cfg = require $file;
        $hash = is_array($cfg) ? ($cfg['reset_hash'] ?? '') : '';
        return $hash !== '' && password_verify(trim($code), $hash);
    }

    public static function deleteConfig(): bool
    {
        $file = self::configFile();
        return is_file($file) && @unlink($file);
    }

    public static function deleteInstaller(): bool
    {
        $file = self::installerFile();
        return is_file($file) && @unlink($file);
    }
}
