# Changelog

All notable changes to ANOUK. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/); versions follow [Semantic Versioning](https://semver.org/).

## [1.1.0] – 2026-11-19

Revision along the common guidelines of the research web tools (structure, updates, exports, documentation).

### Added
- **Database updates with one click:** ANOUK records its database version; after an update of the files, the administrator sees *Update database* on the *System* page. Until then respondents see a maintenance notice and project leaders cannot work. Updating from 1.0.x keeps all studies, accounts and answers.
- **Variable names** per attribute (e.g. `price` for “Price per month”), derived automatically and editable at any time, also in the field. They name the export columns and the columns of the `cbcTools` script; uploads match columns by label or variable name.
- **Wide export:** one row per ID with `choice_tNN` and `rt_tNN` per task, to merge with the survey data.
- **Codebook export:** label, type and values of every export column.
- **Login throttling:** after five failed logins (same e-mail address and connection) waits of 1, 2, 4, 8 and 15 minutes. Only salted hashes are stored, deleted after 24 hours.
- **Table prefix** chosen in the installer (default `anouk_`), so several installations can share one database.
- **Lock file** `data/install.lock` locks the installer after installation.
- Placeholders `{ID}` and `{STATUS}` in return and error URLs.
- Unipark/EFS: the ticket parameter `tic` is accepted as the respondent ID.
- `docs/r/read_anouk.R`: reads all exports in R, with factors in the defined level order.
- README sections for SoSci Survey, Unipark and Qualtrics, nginx rules, update instructions; `CITATION.cff`; this changelog.

### Changed
- **The admin area moved to `admin/`**; `index.php` serves the respondent pages only. Old addresses `index.php?r=…` are forwarded.
- New folder layout: `src/` (was `app/`), `lang/`, `sql/migrations/`, `data/`, `assets/css|js|img/`, `docs/examples/` (was `examples/`).
- Language files are now `lang/<code>.php` (one file per language for admin area and respondent pages); a new language only needs a new file.
- Long export: attribute columns are named by their variable names; `answered_at_utc` is now `answered_at` in ISO 8601 (`…Z`).
- The `cbcTools` script writes the variable names as column names instead of renaming them to the labels.

### Deprecated
- Lower-case placeholders `{id}` and `{status}`; they still work.

## [1.0.1] – 2026-11-16

### Security
- Language switch: strict check of the return target (open redirect via `/\host` when installed in the web root).
- Changing or resetting a password ends all other sessions of the account.
- Login takes the same time for unknown accounts.
- Excel-style export neutralises cells that start like a formula.
- Views and tests do nothing when requested directly (servers without `.htaccess`).
- PHP error display switched off earlier; old `assets/anouk.css` removed.

## [1.0.0] – 2026-11-16

First stable release, functionally equal to 0.3.0.

## [0.3.0] – 2026-11-16

### Added
- Respondents page with filter, search, paging, details and deletion.
- *Delete all answers* of a study; study and design are kept.
- Starting passwords must be changed at the first login.
- Edit, deactivate and delete accounts, with transfer of their studies; the last active administrator is protected.
- Entry with `?id=` sets a cookie and redirects to an address without the ID; notice when cookies are blocked.

## [0.2.0] – 2026-11-16

### Added
- Layout of the sister project JUST-CAT: sidebar, sheet layout for respondents, bundled fonts, status pills, copy buttons.
- Admin area in English and German, language per account; respondent pages in English or German per study.
- Overview with key figures, study list, help page from the README (EN/DE).
- Content-Security-Policy without inline scripts, `no-referrer`, `no-store`, HSTS, optional `force_https`, session end after inactivity.

## [0.1.1] – 2026-11-16

### Added
- The design page writes an R script for `cbcTools` from the attributes of a study.
- Upload removes no-choice rows of `cbcTools` and ignores `blockID`.
- Installer with connection test and permission check, reuse of an existing database, emergency code, button to delete `install.php`.
- System page for the administrator: delete `config.php`.
- Example study on digital health applications (DiGA).

## [0.1.0] – 2026-11-16

### Added
- Roles administrator and project leader; each project leader sees only their own studies.
- Upload and generation of designs, version assignment, return to LimeSurvey, preview, long export.

[1.1.0]: https://github.com/gerhi/anouk/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/gerhi/anouk/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/gerhi/anouk/releases/tag/v1.0.0
