# ANOUK

**A** **N**neat: **O**nline-**U**tility & **K**it (choice-based utility measurement: online presentation and calculation). A web application that shows respondents the choice tasks of a choice-based conjoint (CBC) study, built as a companion to LimeSurvey. The choice sets are created in R with `cbcTools`. It needs PHP and a MySQL/MariaDB database, so it runs on ordinary shared web hosting that also runs LimeSurvey.

Deutsche Fassung: `README.de.md`

- **Choice sets:** ANOUK writes an R script for `cbcTools` from the attributes of a study; the resulting CSV is uploaded and checked
- **Respondents:** come from LimeSurvey with their ID, get a version of the design and return to the same survey with the same ID
- **Versions:** each new ID gets the least-used version; versions of drop-outs become free again after a set time
- **Export:** long format, one row per respondent × task × alternative, ready for `logitr`, `mlogit` and `bayesm`
- **Accounts:** an administrator plus any number of project leaders, who each see only their own studies
- **Languages:** the admin area and the respondent pages are available in English and German

Version 1.0.1 · MIT licence

## 1. Installation

1. Create a MySQL or MariaDB database in your hosting control panel and note the host, database name, user and password. The database may contain other tables; ANOUK uses its own tables with the prefix `anouk_`.
2. Download the files (on GitHub: **Code → Download ZIP**) and unpack them. Upload the whole folder by FTP, for example to `https://example.org/anouk/`. Upload the `.htaccess` files too (they are hidden files).
3. Open `https://example.org/anouk/install.php`.
4. Enter the database details and click **Test connection**. The page reports whether the connection works, whether the user may create tables and whether an ANOUK database exists already.
5. Enter name, e-mail address and password (at least 10 characters) for the administrator account and click **Install**.
6. Write down the **emergency code** shown on the success page, then click **Delete install.php**.
7. Log in at `https://example.org/anouk/`.

If the installer cannot write `config.php`, it shows you the file's content; create the file by FTP and reload the page.

**Requirements:** PHP 8.1 or newer with `pdo_mysql` and `mbstring`, and MySQL 5.7+ or MariaDB 10.3+. HTTPS is strongly recommended. No Composer, Node or shell access is needed.

**Updating:** upload the new files over the old ones, but keep `config.php` and do not upload `install.php` again. The database is updated automatically at the next login.

### Resetting the configuration

To set ANOUK up again, for example after the database password changed, `config.php` is deleted. Studies, accounts and answers stay in the database; the installer recognises the existing database.

- **As administrator:** *System → Delete config.php* (this needs `install.php` on the server).
- **Without logging in**, for example when the database can no longer be reached: open `install.php` and enter the emergency code.
- Or delete the file by FTP.

## 2. Studies

**Studies → New study**

- **Title** is shown in the admin area only.
- **Link name** gives the entry link `index.php?s=link-name`. It cannot be changed later.
- **Language of the respondent pages:** English or German. Buttons and messages follow this language.

A study is a **draft** until you send it into the field. Only drafts can change attributes and design. **In the field** it accepts respondents; **closed** it does not. A study can go back to draft as long as nobody has answered.

**Settings** of a study:

- **Question above each task**, for example “Which of these apps would you use?” Empty: the default text of the language.
- **Introduction:** plain text shown before the first task.
- **None option:** an extra choice “None of these” below the alternatives.
- **Random attribute order** per respondent (the same for all of their tasks).
- **Return URL** and **error URL:** see section 5.
- **Release time:** after how many minutes without activity the version of a drop-out can be given to someone else.
- **Prefix of test IDs** (default `test_`): see section 5.

## 3. Attributes and choice sets

### Attributes and levels

Under *Attributes and levels*, enter one attribute per line, the levels separated by `|`:

```
Price: 249 € | 299 € | 349 €
Brand: A | B | C
Battery life: 8 h | 10 h | 14 h
```

The order of the lines is the order on the respondent pages. The labels appear exactly as written.

### Creating choice sets with cbcTools

Under *Design*, ANOUK writes an R script with the attributes and levels of the study. You choose:

- the **method** of `cbcTools` that needs no prior assumptions: `minoverlap` (recommended), `balanced`, `shortcut` or `random`,
- the number of **versions** (different questionnaires), **sets per version** and **alternatives per set**,
- optionally a **seed**, so the design can be reproduced.

The script builds the choice sets with `cbc_profiles()` and `cbc_design()`, shows level balance and overlap with `cbc_inspect()`, and saves a CSV file with the attribute names and level labels of the study. Run it in R and upload the CSV under *Design*. It needs `cbcTools` 0.7 or newer (`install.packages("cbcTools")`).

For D-efficient designs with priors, change the script to `method = "cea"` or `"modfed"` with `cbc_priors()`. Do not add a “no choice” option in `cbcTools`; switch on the none option in ANOUK instead. No-choice rows in an uploaded file are removed.

**Example:** the folder `examples/diga` contains a complete study on digital health applications (DiGA) with an R script and a ready-made design.

### Uploading a design

The CSV file has one row per version × task × alternative and one column per attribute with the level label:

```
version,task,alt,Price,Brand
1,1,1,299 €,A
1,1,2,349 €,B
1,2,1,249 €,C
```

- Column names such as `respID`, `qID` and `altID` from `cbcTools` are recognised; `profileID`, `obsID`, `blockID` and `choice` are ignored. Other columns can be assigned or ignored in the upload form.
- Comma, semicolon and tab are recognised as separators; files saved as Windows-1252 are converted.
- Numbering may start anywhere; ANOUK renumbers versions, tasks and alternatives from 1.
- If no attributes are defined yet, they are taken from the file.

A file is stored only if every row is valid. Otherwise the page lists the problems with their line numbers: unknown levels, missing values, different numbers of tasks or alternatives, identical alternatives within a task, repeated tasks within a version.

The study page shows **design diagnostics** (level frequencies and the share of tasks with a repeated level) and lets you download the stored design as CSV.

**Without R:** for tests, ANOUK can also generate a simple design itself (random or minimal overlap).

## 4. Respondents

Each respondent gets one version of the design: the version with the fewest completed respondents (and respondents active within the release time). Test IDs do not count. All answers are stored immediately; a respondent who leaves and comes back with the same ID continues at the next open task. A completed ID is sent straight back to LimeSurvey.

**The ID leaves the address bar:** the first request with `?id=…` sets a session cookie with a random token and redirects to a clean address without the ID; all further pages work with the cookie. The ID therefore appears neither in the address bar nor in the browser history. If the browser refuses the cookie, the page explains that cookies are needed.

**Respondents page:** *Studies → Respondents* lists the respondents of your studies with study, version, status (completed, in progress, dropped out), number of answers and times. You can filter by study and status and search for an ID. The details page shows each task with the alternatives and the choice. **Delete respondent** removes one respondent with all answers, for example on a deletion request; their version becomes free again.

**Delete all answers:** on the study page, *Delete all answers* removes all respondents and answers of the study, for example after a test run. The study, its settings and its design are kept.

The pages show one task per page with the alternatives side by side (one below the other on phones), a progress ruler and, if set, the none option. ANOUK stores the response time in milliseconds and the window width for each answer.

**Preview:** the *Preview* button on the study page runs through the study without storing anything.

## 5. Linking with LimeSurvey

### From LimeSurvey to ANOUK

The study page shows the entry link, for example

```
https://example.org/anouk/index.php?s=battery-study&id={TOKEN}
```

LimeSurvey replaces `{TOKEN}` with the participant's token. The respondent should leave LimeSurvey on a question page (not through the end URL, which completes the response), for example with a link or an automatic redirect in a question of type *Text display*:

```
<script>window.location.href = 'https://example.org/anouk/index.php?s=battery-study&id={TOKEN}';</script>
```

Depending on its settings, LimeSurvey's XSS filter removes scripts from question texts; then use an ordinary link.

### From ANOUK back to LimeSurvey

Enter a **return URL** in the study settings, for example

```
https://survey.example.org/index.php/123456?token={id}&lang=de
```

`{id}` is replaced by the respondent's ID and `{status}` by `complete`. To return into the same survey, run it with a participant list (tokens) and switch on token-based response persistence, so LimeSurvey continues the started response. **Please test this with your LimeSurvey version before the field phase.**

An optional **error URL** is used when the study is not in the field (`{status}` = `closed`) or the ID is invalid (`invalid_id`).

**Test IDs:** IDs that start with the test prefix (default `test_`) run through normally, but do not count for the version assignment and are left out of the export unless chosen.

## 6. Export

The study page offers the **long format**, one row per respondent × task × alternative:

| Column | Content |
|---|---|
| `id` | ID from LimeSurvey |
| `obs_id` | running number per respondent × task |
| `version`, `task`, `alt` | position in the design |
| attribute columns | the level shown |
| `none` | with a none option only: 1 in the extra row for “none of these” (`alt` = J + 1, attributes empty) |
| `choice` | 1 = chosen, else 0 |
| `rt_ms` | response time of the task in milliseconds (measured in the browser) |
| `screen_w` | window width in pixels |
| `answered_at_utc` | time of the answer (UTC) |
| `status`, `is_test` | status of the respondent, test ID yes/no |

File styles: **R/SPSS/Python** (comma) or **German Excel** (semicolon, UTF-8 BOM). Example with `logitr`:

```
library(logitr)
d <- read.csv("anouk_battery-study_long_20261116.csv", fileEncoding = "UTF-8")
m <- logitr(data = d, outcome = "choice", obsID = "obs_id",
            pars = c("Price", "Brand", "Battery.life"))
summary(m)
```

With a none option, code the empty attribute values of the `none` rows before estimating, for example with `none` as a parameter of its own.

Estimating individual part-worths (hierarchical Bayes) and a wide export with one row per respondent follow in the next version, with established R packages whose results ANOUK reads back.

## 7. Accounts, roles and languages

| | Administrator | Project leader |
|---|---|---|
| Create studies, create and upload designs, export | yes | yes |
| Studies of other accounts | all | none |
| Create, edit, deactivate and delete accounts, set starting passwords | yes | no |
| System page (installer, configuration) | yes | no |

**Creating a project leader:** under *Accounts*, the administrator enters name, e-mail address, role and language. ANOUK creates a starting password that is shown once; pass it on to the person. At the first login the person has to choose a password of their own. The application sends no e-mails; if someone forgets their password, the administrator creates a new starting password.

**Editing and leaving:** clicking an account opens its page, where the administrator changes name, e-mail address, role and language, deactivates the account (the person can no longer log in, everything else stays) or deletes it. If the account owns studies, they are first transferred to another account; results stay with the studies. The last active administrator can neither be deleted, deactivated nor made a project leader.

**Separation:** a project leader who opens a study of another account by its address gets “not found”.

**Languages:** the admin area follows the browser language at first; everyone can switch the language in the menu at the bottom left, and the choice is stored with the account. The language of the respondent pages is set per study. The texts are in `app/lang/de.json` (admin area, key = English text) and `app/Survey.php` (respondent pages). A further language needs a new file `app/lang/xx.json` and an entry in `app/I18n.php`; `php tests/i18n.php --keys` lists all texts.

## 8. Data protection

**What ANOUK stores:** the ID passed by LimeSurvey, the assigned version, the answers with response time and window width, and timestamps.

**What it does not store:** IP addresses, browser details, names or e-mail addresses of respondents.

**Cookies:** one session cookie per study (`anouk_s{id}`) with a random token, and the PHP session cookie (both HttpOnly, SameSite=Lax, deleted when the browser closes). They are technically necessary (§ 25 Abs. 2 Nr. 2 TDDDG). The database stores only a SHA-256 hash of the token.

**ID in the address:** after the first page the ID is no longer in the address bar or the browser history.

**No third parties:** fonts and scripts are part of the application. Nothing is loaded from other servers, and `Referrer-Policy: no-referrer` stops addresses from being passed on.

**Hosting:** the provider's access logs contain the entry link including the ID. Record the processing agreement with the host and its log retention in your data protection documentation.

**Deletion:** find a respondent by ID under *Respondents* and use *Delete respondent*. *Delete all answers* empties a study, *Delete study* removes it with design and all answers for good.

## 9. Security

- Delete `install.php` after installation.
- `app/`, `sql/`, `tests/` and `config.php` are blocked by `.htaccess` files. On servers without `.htaccess` support (nginx), block these paths in the server configuration, for example `location ~ ^/anouk/(app|sql|tests)/ { deny all; }`. Even without the block, `config.php`, the PHP files in `app/` and the tests print nothing when requested.
- Passwords are stored as bcrypt hashes. All admin forms are protected against cross-site requests, and admin sessions end after four hours without activity (`admin_idle_minutes` in `config.php`). Changing or resetting a password ends all other sessions of that account.
- There is no lockout after failed logins. Use long passwords; a lockout can be added in front of ANOUK with the web server or the host's tools.
- Anyone who knows the entry link of a study in the field can start it with an invented ID. Such rows are recognisable because their ID is not in the LimeSurvey participant list; remove them when merging.
- The Excel-style export puts an apostrophe in front of cells that start with `=`, `+`, `-` or `@`, so a spreadsheet shows them instead of running them as formulas. The R-style export is left exact.
- A Content-Security-Policy forbids inline scripts and anything from other servers; pages are not cached.
- Tick “Always use HTTPS” during installation (`force_https` in `config.php`).

## 10. Troubleshooting

| Problem | Solution |
|---|---|
| “Database not reachable” | Check the access data in `config.php`, or reset the configuration (section 1) |
| “The form has expired” | Cookies are blocked, or the page was open too long: reload it |
| Upload fails without a message | The file exceeds the host's PHP `upload_max_filesize` |
| Umlauts are garbled | Save the CSV as “CSV UTF-8” |
| Show PHP errors | Set `'debug' => true` in `config.php`, temporarily |

## 11. Development and tests

The tests use SQLite and need no MySQL server:

```
php tests/run.php      # logic: parser, generator, validation, version assignment
php tests/i18n.php     # every text has a German translation
bash tests/e2e.sh      # complete run over HTTP (PHP built-in server + Python 3)
```

## Files

```
index.php            entry point (admin area and respondent pages)
install.php          installer (delete after use)
app/                 logic and templates (blocked by .htaccess)
app/lang/de.json     German texts of the admin area
assets/              stylesheet, fonts, scripts, icon
examples/diga/       example study with cbcTools script and design
sql/                 MySQL schema
tests/               tests with SQLite
README.md            this text; README.de.md in German
```

The design of the interface comes from the sister project JUST-CAT (adaptive tests for LimeSurvey).

## Licence

MIT, see `LICENSE`. The bundled fonts (Instrument Sans, IBM Plex Serif) are under the SIL Open Font License 1.1 (`assets/fonts`).
