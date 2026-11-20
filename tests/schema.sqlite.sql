-- SQLite schema, used only by the test suite. Mirrors sql/schema.sql; placeholders are replaced by Db::sql().

CREATE TABLE {meta} (
  name TEXT PRIMARY KEY,
  value TEXT NULL
);

CREATE TABLE {users} (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'leader',
  language TEXT NOT NULL DEFAULT 'en',
  active INTEGER NOT NULL DEFAULT 1,
  must_change_password INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL,
  last_login_at TEXT NULL
);

CREATE TABLE {studies} (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  owner_id INTEGER NOT NULL REFERENCES {users} (id),
  slug TEXT NOT NULL UNIQUE,
  title TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'draft',
  settings TEXT NULL,
  design_meta TEXT NULL,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL
);

CREATE TABLE {attributes} (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  study_id INTEGER NOT NULL REFERENCES {studies} (id) ON DELETE CASCADE,
  position INTEGER NOT NULL,
  var_name TEXT NOT NULL DEFAULT '',
  name TEXT NOT NULL
);

CREATE TABLE {levels} (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  attribute_id INTEGER NOT NULL REFERENCES {attributes} (id) ON DELETE CASCADE,
  position INTEGER NOT NULL,
  label TEXT NOT NULL
);

CREATE TABLE {design} (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  study_id INTEGER NOT NULL REFERENCES {studies} (id) ON DELETE CASCADE,
  version INTEGER NOT NULL,
  task INTEGER NOT NULL,
  alt INTEGER NOT NULL,
  level_ids TEXT NOT NULL,
  UNIQUE (study_id, version, task, alt)
);

CREATE TABLE {respondents} (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  study_id INTEGER NOT NULL REFERENCES {studies} (id) ON DELETE CASCADE,
  ext_id TEXT NOT NULL,
  version INTEGER NOT NULL,
  status TEXT NOT NULL DEFAULT 'active',
  is_test INTEGER NOT NULL DEFAULT 0,
  attr_order TEXT NULL,
  started_at TEXT NOT NULL,
  last_seen_at TEXT NOT NULL,
  completed_at TEXT NULL,
  token_hash TEXT NULL,
  UNIQUE (study_id, ext_id)
);
CREATE INDEX {prefix}ix_respondents_token ON {respondents} (token_hash);

CREATE TABLE {answers} (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  respondent_id INTEGER NOT NULL REFERENCES {respondents} (id) ON DELETE CASCADE,
  task INTEGER NOT NULL,
  choice INTEGER NOT NULL,
  rt_ms INTEGER NULL,
  screen_w INTEGER NULL,
  answered_at TEXT NOT NULL,
  UNIQUE (respondent_id, task)
);

CREATE TABLE {login_attempts} (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email_hash TEXT NOT NULL,
  ip_hash TEXT NOT NULL,
  attempted_at TEXT NOT NULL
);
CREATE INDEX {prefix}ix_login_attempts ON {login_attempts} (email_hash, ip_hash, attempted_at);
