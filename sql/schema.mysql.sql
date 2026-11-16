-- ANOUK database schema for MySQL >= 5.7 / MariaDB >= 10.3
-- All times are stored in UTC.

CREATE TABLE IF NOT EXISTS anouk_users (
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

CREATE TABLE IF NOT EXISTS anouk_studies (
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
  CONSTRAINT fk_studies_owner FOREIGN KEY (owner_id) REFERENCES anouk_users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS anouk_attributes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  study_id INT UNSIGNED NOT NULL,
  position INT NOT NULL,
  name VARCHAR(190) NOT NULL,
  PRIMARY KEY (id),
  KEY ix_attributes_study (study_id, position),
  CONSTRAINT fk_attributes_study FOREIGN KEY (study_id) REFERENCES anouk_studies (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS anouk_levels (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  attribute_id INT UNSIGNED NOT NULL,
  position INT NOT NULL,
  label VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  KEY ix_levels_attribute (attribute_id, position),
  CONSTRAINT fk_levels_attribute FOREIGN KEY (attribute_id) REFERENCES anouk_attributes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One row per version x task x alternative. level_ids: JSON array of level ids in attribute order.
CREATE TABLE IF NOT EXISTS anouk_design (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  study_id INT UNSIGNED NOT NULL,
  version INT NOT NULL,
  task INT NOT NULL,
  alt INT NOT NULL,
  level_ids TEXT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_design_cell (study_id, version, task, alt),
  CONSTRAINT fk_design_study FOREIGN KEY (study_id) REFERENCES anouk_studies (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS anouk_respondents (
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
  CONSTRAINT fk_respondents_study FOREIGN KEY (study_id) REFERENCES anouk_studies (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- choice: number of the chosen alternative, 0 = none option
CREATE TABLE IF NOT EXISTS anouk_answers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  respondent_id INT UNSIGNED NOT NULL,
  task INT NOT NULL,
  choice INT NOT NULL,
  rt_ms INT NULL,
  screen_w INT NULL,
  answered_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_answers_task (respondent_id, task),
  CONSTRAINT fk_answers_respondent FOREIGN KEY (respondent_id) REFERENCES anouk_respondents (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
