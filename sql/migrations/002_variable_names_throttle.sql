-- ANOUK 1.1.0 (schema version 2)
-- {meta}: schema version and settings; {login_attempts}: login throttling;
-- {attributes}.var_name: R-friendly variable names (filled in by the migration code).

CREATE TABLE IF NOT EXISTS {meta} (
  name VARCHAR(64) NOT NULL,
  value TEXT NULL,
  PRIMARY KEY (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {login_attempts} (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email_hash CHAR(64) NOT NULL,
  ip_hash CHAR(64) NOT NULL,
  attempted_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY ix_login_attempts (email_hash, ip_hash, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE {attributes} ADD COLUMN var_name VARCHAR(64) NOT NULL DEFAULT '' AFTER position;
