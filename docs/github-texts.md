# Texts for publishing ANOUK on GitHub

## 1. Repository description (About)

ANOUK is a lightweight PHP/MySQL web app for choice-based conjoint (CBC) studies in psychological research: it shows choice sets designed in R with cbcTools and exports the choices for R. Runs on any shared hosting, integrates with LimeSurvey.

Website field: leave empty (or the URL of a demo installation).

## 2. Topics

`psychology`, `research-tool`, `survey`, `limesurvey`, `php`, `mysql`, `open-science`, `conjoint-analysis`, `discrete-choice`, `choice-experiment`, `cbctools`, `r`

## 3. Release notes

```markdown
## ANOUK v1.1.0

### Highlights
- Database updates with one click on the System page – no shell, no SQL by hand
- Variable names per attribute (e.g. `price`), editable at any time; exports and the cbcTools script use them
- New exports: wide format (one row per ID, to merge with the survey data) and a codebook; all times in ISO 8601 UTC
- Login throttling after repeated failed logins
- Table prefix and lock file in the installer
- Instructions for LimeSurvey, SoSci Survey, Unipark and Qualtrics; R function `docs/r/read_anouk.R`

### Installation / Update notes
- New installation: unzip, upload by FTP, open `install.php` – see README.
- Update from 1.0.x: back up the database, upload the new files (keep `config.php`), log in as administrator and click **Update database** under *System*. Then delete the old folders `app/` and `examples/` and the old files in `assets/` (list in the README).
- The admin area is now at `admin/`; old bookmarks are forwarded.
- Long export: attribute columns are now named by their variable names, and `answered_at_utc` is now `answered_at` (ISO 8601). Adapt existing R scripts.

### Changes
See [CHANGELOG.md](CHANGELOG.md#110--2026-11-19).
```

```markdown
## ANOUK v1.0.1

### Highlights
- Security fixes after a review (open redirect in the language switch, sessions after password change, formula cells in the Excel export).

### Installation / Update notes
- Upload the new files over the old ones; keep `config.php`.

### Changes
See [CHANGELOG.md](CHANGELOG.md#101--2026-11-16).
```

## 4. CITATION.cff

Included in the repository root. Add your ORCID in `CITATION.cff` (line `orcid:`). With the Zenodo–GitHub integration, every release gets a citable DOI automatically; switch it on at zenodo.org → GitHub before publishing the release.

## 5. Kurzankündigung (Deutsch)

Choice-based-Conjoint-Studien lassen sich in R mit cbcTools gut planen – aber wie kommen die Choice-Sets zu den Befragten, ohne teure Spezialsoftware? ANOUK ist eine kleine Web-App, die die Choice-Tasks direkt aus der R-Planung anzeigt, Befragte aus LimeSurvey (oder SoSci Survey, Unipark, Qualtrics) übernimmt und mit derselben ID zurückschickt. Die Daten kommen im Lang- und Breitformat samt Codebuch zurück nach R. ANOUK läuft auf normalem Webspace mit PHP und MySQL und ist frei unter MIT-Lizenz: https://github.com/gerhi/anouk

## 6. Social-media post

ANOUK 1.1: run choice-based conjoint studies designed in R (cbcTools) on your own web space, linked with LimeSurvey & co. Long/wide exports + codebook for R. PHP/MySQL, no shell needed, MIT. https://github.com/gerhi/anouk #conjoint #rstats #openscience
