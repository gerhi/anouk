-- SQLite schema, used only by the test suite. Mirrors sql/schema.mysql.sql.

CREATE TABLE anouk_users (
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

CREATE TABLE anouk_studies (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  owner_id INTEGER NOT NULL REFERENCES anouk_users (id),
  slug TEXT NOT NULL UNIQUE,
  title TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'draft',
  settings TEXT NULL,
  design_meta TEXT NULL,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL
);

CREATE TABLE anouk_attributes (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  study_id INTEGER NOT NULL REFERENCES anouk_studies (id) ON DELETE CASCADE,
  position INTEGER NOT NULL,
  name TEXT NOT NULL
);

CREATE TABLE anouk_levels (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  attribute_id INTEGER NOT NULL REFERENCES anouk_attributes (id) ON DELETE CASCADE,
  position INTEGER NOT NULL,
  label TEXT NOT NULL
);

CREATE TABLE anouk_design (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  study_id INTEGER NOT NULL REFERENCES anouk_studies (id) ON DELETE CASCADE,
  version INTEGER NOT NULL,
  task INTEGER NOT NULL,
  alt INTEGER NOT NULL,
  level_ids TEXT NOT NULL,
  UNIQUE (study_id, version, task, alt)
);

CREATE TABLE anouk_respondents (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  study_id INTEGER NOT NULL REFERENCES anouk_studies (id) ON DELETE CASCADE,
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
CREATE INDEX ix_respondents_token ON anouk_respondents (token_hash);

CREATE TABLE anouk_answers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  respondent_id INTEGER NOT NULL REFERENCES anouk_respondents (id) ON DELETE CASCADE,
  task INTEGER NOT NULL,
  choice INTEGER NOT NULL,
  rt_ms INTEGER NULL,
  screen_w INTEGER NULL,
  answered_at TEXT NOT NULL,
  UNIQUE (respondent_id, task)
);
