-- SQLite variant of sql/migrations/002_variable_names_throttle.sql, used only by the tests.
CREATE TABLE IF NOT EXISTS {meta} (
  name TEXT PRIMARY KEY,
  value TEXT NULL
);
CREATE TABLE IF NOT EXISTS {login_attempts} (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email_hash TEXT NOT NULL,
  ip_hash TEXT NOT NULL,
  attempted_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS {prefix}ix_login_attempts ON {login_attempts} (email_hash, ip_hash, attempted_at);
ALTER TABLE {attributes} ADD COLUMN var_name TEXT NOT NULL DEFAULT '';
