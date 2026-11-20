-- ANOUK database schema for MySQL >= 5.7 / MariaDB >= 10.3
-- {users} etc. and {prefix} are replaced by the table prefix (default anouk_) when the installer runs.
-- All times are stored in UTC.

CREATE TABLE IF NOT EXISTS {meta} (
  name VARCHAR(64) NOT NULL,
  value TEXT NULL,
  PRIMARY KEY (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {users} (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  name VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(16) NOT NULL DEFAULT 'leader',
  language VARCHAR(5) NOT NULL DEFAULT 'en',
  active TINYINT(1) NOT NULL DEFAULT 1,
  must_change_password TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  last_login_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {studies} (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  owner_id INT UNSIGNED NOT NULL,
  slug VARCHAR(64) NOT NULL,
  title VARCHAR(255) NOT NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'draft',
  settings MEDIUMTEXT NULL,
  design_meta TEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_studies_slug (slug),
  KEY ix_studies_owner (owner_id),
  CONSTRAINT {prefix}fk_studies_owner FOREIGN KEY (owner_id) REFERENCES {users} (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {attributes} (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  study_id INT UNSIGNED NOT NULL,
  position INT NOT NULL,
  var_name VARCHAR(64) NOT NULL DEFAULT '',
  name VARCHAR(190) NOT NULL,
  PRIMARY KEY (id),
  KEY ix_attributes_study (study_id, position),
  CONSTRAINT {prefix}fk_attributes_study FOREIGN KEY (study_id) REFERENCES {studies} (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {levels} (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  attribute_id INT UNSIGNED NOT NULL,
  position INT NOT NULL,
  label VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  KEY ix_levels_attribute (attribute_id, position),
  CONSTRAINT {prefix}fk_levels_attribute FOREIGN KEY (attribute_id) REFERENCES {attributes} (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One row per version x task x alternative. level_ids: JSON array of level ids in attribute order.
CREATE TABLE IF NOT EXISTS {design} (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  study_id INT UNSIGNED NOT NULL,
  version INT NOT NULL,
  task INT NOT NULL,
  alt INT NOT NULL,
  level_ids TEXT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_design_cell (study_id, version, task, alt),
  CONSTRAINT {prefix}fk_design_study FOREIGN KEY (study_id) REFERENCES {studies} (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {respondents} (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  study_id INT UNSIGNED NOT NULL,
  ext_id VARCHAR(190) NOT NULL,
  version INT NOT NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'active',
  is_test TINYINT(1) NOT NULL DEFAULT 0,
  attr_order TEXT NULL,
  started_at DATETIME NOT NULL,
  last_seen_at DATETIME NOT NULL,
  completed_at DATETIME NULL,
  token_hash VARCHAR(64) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_respondents_ext (study_id, ext_id),
  KEY ix_respondents_token (token_hash),
  KEY ix_respondents_version (study_id, version, status),
  CONSTRAINT {prefix}fk_respondents_study FOREIGN KEY (study_id) REFERENCES {studies} (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- choice: number of the chosen alternative, 0 = none option
CREATE TABLE IF NOT EXISTS {answers} (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  respondent_id INT UNSIGNED NOT NULL,
  task INT NOT NULL,
  choice INT NOT NULL,
  rt_ms INT NULL,
  screen_w INT NULL,
  answered_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_answers_task (respondent_id, task),
  CONSTRAINT {prefix}fk_answers_respondent FOREIGN KEY (respondent_id) REFERENCES {respondents} (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {login_attempts} (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email_hash CHAR(64) NOT NULL,
  ip_hash CHAR(64) NOT NULL,
  attempted_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY ix_login_attempts (email_hash, ip_hash, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
