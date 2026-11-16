#!/usr/bin/env bash
# End-to-end test over HTTP: PHP built-in server + SQLite + Python 3 (standard library only).
#   bash tests/e2e.sh
set -euo pipefail
cd "$(dirname "$0")/.."
rm -rf tests/tmp && mkdir -p tests/tmp
cat > tests/tmp/config.php <<'PHP'
<?php
return ['db' => ['dsn' => 'sqlite:' . __DIR__ . '/e2e.sqlite'], 'debug' => true,
        'reset_hash' => password_hash('TEST-CODE-1234', PASSWORD_DEFAULT)];
PHP
export ANOUK_CONFIG="$PWD/tests/tmp/config.php"
php -r '
require "app/bootstrap.php";
Db::pdo()->exec(file_get_contents("tests/schema.sqlite.sql"));
Auth::createUser("Master", "admin@example.org", "admin", "adminadmin1", "de");
Auth::createUser("Lea Leiterin", "lea@example.org", "leader", "leaderlea1", "de");
Auth::createUser("Otto Other", "otto@example.org", "leader", "leaderotto1", "de");'
php -S 127.0.0.1:8099 -t . > tests/tmp/server.log 2>&1 &
SERVER=$!
trap 'kill $SERVER' EXIT
sleep 1
python3 tests/e2e.py
