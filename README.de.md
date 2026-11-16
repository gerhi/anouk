# ANOUK

**A**uswahlbasierte **N**utzenmessung: **O**nline-**U**msetzung & **K**alkulation. Eine Webanwendung, die Probanden die Choice-Tasks einer Choice-Based-Conjoint-Studie (CBC) zeigt, gebaut als Ergänzung zu LimeSurvey. Die Choice-Sets entstehen in R mit `cbcTools`. Sie braucht PHP und eine MySQL/MariaDB-Datenbank und läuft deshalb auf gewöhnlichem Webhosting, auf dem auch LimeSurvey läuft.

English version: `README.md`

- **Choice-Sets:** ANOUK schreibt aus den Attributen einer Studie ein R-Skript für `cbcTools`; die erzeugte CSV wird hochgeladen und geprüft
- **Probanden:** kommen mit ihrer ID aus LimeSurvey, erhalten eine Version des Designs und kehren mit derselben ID in dieselbe Umfrage zurück
- **Versionen:** jede neue ID erhält die am wenigsten genutzte Version; Versionen von Abbrechern werden nach einer einstellbaren Frist wieder frei
- **Export:** Langformat mit einer Zeile je Proband × Task × Alternative, direkt nutzbar mit `logitr`, `mlogit` und `bayesm`
- **Konten:** ein Administrator und beliebig viele Projektleitungen, die jeweils nur ihre eigenen Studien sehen
- **Sprachen:** Verwaltung und Probandenseiten gibt es auf Englisch und Deutsch

Version 1.0.1 · MIT-Lizenz

## 1. Installation

1. Im Kundenmenü des Providers eine MySQL- oder MariaDB-Datenbank anlegen und Host, Datenbankname, Benutzer und Passwort notieren. Die Datenbank darf andere Tabellen enthalten; ANOUK nutzt eigene Tabellen mit dem Präfix `anouk_`.
2. Die Dateien herunterladen (auf GitHub: **Code → Download ZIP**) und entpacken. Den ganzen Ordner per FTP hochladen, z. B. nach `https://example.org/anouk/`. Die `.htaccess`-Dateien mit hochladen (es sind versteckte Dateien).
3. `https://example.org/anouk/install.php` aufrufen.
4. Die Datenbankangaben eintragen und auf **Verbindung testen** klicken. Die Seite meldet, ob die Verbindung klappt, ob der Benutzer Tabellen anlegen darf und ob schon eine ANOUK-Datenbank existiert.
5. Name, E-Mail-Adresse und Passwort (mindestens 10 Zeichen) für das Administrator-Konto eintragen und auf **Installieren** klicken.
6. Den angezeigten **Notfall-Code** notieren, dann auf **install.php löschen** klicken.
7. Unter `https://example.org/anouk/` anmelden.

Kann der Installer `config.php` nicht schreiben, zeigt er den Inhalt der Datei an; diese dann per FTP anlegen und die Seite neu laden.

**Voraussetzungen:** PHP 8.1 oder neuer mit `pdo_mysql` und `mbstring` sowie MySQL 5.7+ oder MariaDB 10.3+. HTTPS ist dringend empfohlen. Composer, Node oder Shell-Zugriff sind nicht nötig.

**Aktualisieren:** die neuen Dateien über die alten hochladen, dabei `config.php` behalten und `install.php` nicht erneut hochladen. Die Datenbank wird bei der nächsten Anmeldung automatisch angepasst.

### Konfiguration zurücksetzen

Um ANOUK neu einzurichten, z. B. nach einem geänderten Datenbankpasswort, wird `config.php` gelöscht. Studien, Konten und Antworten bleiben in der Datenbank; der Installer erkennt die bestehende Datenbank.

- **Als Administrator:** *System → config.php löschen* (dafür muss `install.php` auf dem Server liegen).
- **Ohne Anmeldung**, etwa wenn die Datenbank nicht mehr erreichbar ist: `install.php` aufrufen und den Notfall-Code eingeben.
- Oder die Datei per FTP löschen.

## 2. Studien

**Studien → Neue Studie**

- **Titel** erscheint nur in der Verwaltung.
- **Kürzel für den Link** ergibt den Einstiegslink `index.php?s=kuerzel`. Es lässt sich später nicht ändern.
- **Sprache der Probandenseiten:** Englisch oder Deutsch. Schaltflächen und Hinweise folgen dieser Sprache.

Eine Studie ist ein **Entwurf**, bis sie ins Feld geht. Nur im Entwurf lassen sich Attribute und Design ändern. **Im Feld** nimmt sie Probanden an, **geschlossen** nicht. Zurück in den Entwurf geht es, solange niemand geantwortet hat.

**Einstellungen** einer Studie:

- **Frage über jedem Task**, z. B. „Welche dieser Apps würden Sie nutzen?“. Leer: der Standardtext der Sprache.
- **Instruktion:** reiner Text vor dem ersten Task.
- **None-Option:** eine zusätzliche Wahl „Keine dieser Optionen“ unter den Alternativen.
- **Zufällige Attributreihenfolge** je Proband (gleich für alle seine Tasks).
- **Rücksprung-URL** und **Fehler-URL:** siehe Abschnitt 5.
- **Freigabefrist:** nach wie vielen Minuten ohne Aktivität die Version eines Abbrechers an jemand anderen gehen kann.
- **Präfix für Test-IDs** (Standard `test_`): siehe Abschnitt 5.

## 3. Attribute und Choice-Sets

### Attribute und Level

Unter *Attribute und Level* ein Attribut pro Zeile eintragen, die Level durch `|` getrennt:

```
Preis: 249 € | 299 € | 349 €
Marke: A | B | C
Akkulaufzeit: 8 h | 10 h | 14 h
```

Die Reihenfolge der Zeilen ist die Reihenfolge auf den Probandenseiten. Die Beschriftungen erscheinen genau so, wie sie hier stehen.

### Choice-Sets mit cbcTools erstellen

Unter *Design* erzeugt ANOUK ein R-Skript mit den Attributen und Leveln der Studie. Wählbar sind:

- das **Verfahren** von `cbcTools`, das ohne Vorannahmen auskommt: `minoverlap` (empfohlen), `balanced`, `shortcut` oder `random`,
- die Zahl der **Versionen** (verschiedene Fragebögen), **Sets je Version** und **Alternativen je Set**,
- optional ein **Seed**, damit sich das Design reproduzieren lässt.

Das Skript baut die Choice-Sets mit `cbc_profiles()` und `cbc_design()`, zeigt mit `cbc_inspect()` Levelbalance und Overlap und speichert eine CSV mit den Attributnamen und Level-Beschriftungen der Studie. Das Skript in R ausführen und die CSV unter *Design* hochladen. Voraussetzung ist `cbcTools` 0.7 oder neuer (`install.packages("cbcTools")`).

Für D-effiziente Designs mit Vorannahmen das Skript auf `method = "cea"` oder `"modfed"` mit `cbc_priors()` umstellen. Ein „no choice“ bitte nicht in `cbcTools`, sondern als None-Option in ANOUK aktivieren. No-Choice-Zeilen in einer hochgeladenen Datei werden entfernt.

**Beispiel:** der Ordner `examples/diga` enthält eine vollständige Studie zu Digitalen Gesundheitsanwendungen (DiGA) mit R-Skript und fertigem Design.

### Design hochladen

Die CSV-Datei hat eine Zeile je Version × Task × Alternative und eine Spalte je Attribut mit dem Level-Label:

```
version,task,alt,Preis,Marke
1,1,1,299 €,A
1,1,2,349 €,B
1,2,1,249 €,C
```

- Spaltennamen wie `respID`, `qID` und `altID` aus `cbcTools` werden erkannt; `profileID`, `obsID`, `blockID` und `choice` werden ignoriert. Weitere Spalten lassen sich im Upload-Formular zuordnen oder ausschließen.
- Komma, Semikolon und Tabulator werden als Trennzeichen erkannt; als Windows-1252 gespeicherte Dateien werden umgewandelt.
- Die Nummerierung darf beliebig beginnen; ANOUK nummeriert Versionen, Tasks und Alternativen ab 1.
- Sind noch keine Attribute definiert, werden sie aus der Datei übernommen.

Gespeichert wird eine Datei nur, wenn alle Zeilen gültig sind. Sonst listet die Seite die Probleme mit Zeilennummer auf: unbekannte Level, fehlende Werte, unterschiedliche Zahl von Tasks oder Alternativen, identische Alternativen in einem Task, wiederholte Tasks in einer Version.

Die Studienseite zeigt eine **Design-Diagnose** (Levelhäufigkeiten und Anteil der Tasks mit wiederholtem Level) und bietet das gespeicherte Design als CSV zum Herunterladen an.

**Ohne R:** für Tests kann ANOUK auch selbst ein einfaches Design erzeugen (zufällig oder mit minimalem Overlap).

## 4. Probanden

Jeder Proband erhält eine Version des Designs: die Version mit den wenigsten abgeschlossenen (und innerhalb der Freigabefrist aktiven) Probanden. Test-IDs zählen nicht mit. Jede Antwort wird sofort gespeichert; wer abbricht und mit derselben ID zurückkommt, macht beim nächsten offenen Task weiter. Eine abgeschlossene ID wird direkt zu LimeSurvey zurückgeschickt.

**Die ID verschwindet aus der Adresszeile:** der erste Aufruf mit `?id=…` setzt ein Sitzungscookie mit einem zufälligen Token und leitet auf eine Adresse ohne ID um; alle weiteren Seiten arbeiten mit dem Cookie. Die ID steht damit weder in der Adresszeile noch im Browserverlauf. Nimmt der Browser das Cookie nicht an, erklärt die Seite, dass Cookies nötig sind.

**Probandenseite:** *Studien → Probanden* listet die Probanden der eigenen Studien mit Studie, Version, Status (abgeschlossen, läuft, abgebrochen), Zahl der Antworten und Zeitpunkten. Gefiltert wird nach Studie und Status, gesucht nach der ID. Die Detailseite zeigt jeden Task mit den Alternativen und der Wahl. **Proband löschen** entfernt einen Probanden mit allen Antworten, z. B. auf eine Löschanfrage hin; seine Version wird wieder frei.

**Alle Antworten löschen:** auf der Studienseite entfernt *Alle Antworten löschen* alle Probanden und Antworten der Studie, z. B. nach einem Testlauf. Studie, Einstellungen und Design bleiben erhalten.

Die Seiten zeigen einen Task pro Seite mit den Alternativen nebeneinander (auf dem Smartphone untereinander), einem Fortschrittslineal und, falls eingestellt, der None-Option. ANOUK speichert zu jeder Antwort die Antwortzeit in Millisekunden und die Fensterbreite.

**Vorschau:** die Schaltfläche *Vorschau* auf der Studienseite spielt die Studie durch, ohne etwas zu speichern.

## 5. Anbindung an LimeSurvey

### Von LimeSurvey zu ANOUK

Die Studienseite zeigt den Einstiegslink, z. B.

```
https://example.org/anouk/index.php?s=akku-studie&id={TOKEN}
```

LimeSurvey ersetzt `{TOKEN}` durch den Token des Teilnehmers. Die Probanden sollten LimeSurvey auf einer Fragenseite verlassen (nicht über die End-URL, die die Antwort abschließt), z. B. mit einem Link oder einer automatischen Weiterleitung in einer Frage vom Typ *Textanzeige*:

```
<script>window.location.href = 'https://example.org/anouk/index.php?s=akku-studie&id={TOKEN}';</script>
```

Je nach Einstellung entfernt der XSS-Filter von LimeSurvey Skripte aus Fragetexten; dann einen gewöhnlichen Link verwenden.

### Von ANOUK zurück zu LimeSurvey

In den Einstellungen der Studie eine **Rücksprung-URL** eintragen, z. B.

```
https://umfrage.example.org/index.php/123456?token={id}&lang=de
```

`{id}` wird durch die ID des Probanden ersetzt, `{status}` durch `complete`. Für die Rückkehr in dieselbe Umfrage diese mit einer Teilnehmertabelle (Tokens) betreiben und die token-basierte Antwortpersistenz einschalten, damit LimeSurvey die angefangene Antwort fortsetzt. **Bitte vor der Feldphase mit der eigenen LimeSurvey-Version testen.**

Eine optionale **Fehler-URL** wird genutzt, wenn die Studie nicht im Feld ist (`{status}` = `closed`) oder die ID ungültig ist (`invalid_id`).

**Test-IDs:** IDs, die mit dem Test-Präfix beginnen (Standard `test_`), laufen normal durch, zählen aber nicht für die Versionsvergabe und fehlen im Export, wenn sie nicht ausdrücklich gewählt werden.

## 6. Export

Die Studienseite bietet das **Langformat** mit einer Zeile je Proband × Task × Alternative:

| Spalte | Inhalt |
|---|---|
| `id` | ID aus LimeSurvey |
| `obs_id` | laufende Nummer je Proband × Task |
| `version`, `task`, `alt` | Position im Design |
| Attributspalten | angezeigtes Level |
| `none` | nur mit None-Option: 1 in der zusätzlichen Zeile für „keine davon“ (`alt` = J + 1, Attribute leer) |
| `choice` | 1 = gewählt, sonst 0 |
| `rt_ms` | Antwortzeit des Tasks in Millisekunden (im Browser gemessen) |
| `screen_w` | Fensterbreite in Pixeln |
| `answered_at_utc` | Zeitpunkt der Antwort (UTC) |
| `status`, `is_test` | Status des Probanden, Test-ID ja/nein |

Dateiformate: **R/SPSS/Python** (Komma) oder **deutsches Excel** (Semikolon, UTF-8 mit BOM). Beispiel mit `logitr`:

```
library(logitr)
d <- read.csv("anouk_akku-studie_long_20261116.csv", fileEncoding = "UTF-8")
m <- logitr(data = d, outcome = "choice", obsID = "obs_id",
            pars = c("Preis", "Marke", "Akkulaufzeit"))
summary(m)
```

Mit None-Option die leeren Attributwerte der `none`-Zeilen vor der Schätzung passend kodieren, z. B. mit `none` als eigenem Parameter.

Die Schätzung individueller Teilnutzenwerte (Hierarchical Bayes) und ein Weitexport mit einer Zeile je Proband folgen in der nächsten Version – mit etablierten R-Paketen, deren Ergebnisse ANOUK wieder einliest.

## 7. Konten, Rollen und Sprachen

| | Administrator | Projektleitung |
|---|---|---|
| Studien anlegen, Designs erstellen und hochladen, exportieren | ja | ja |
| Studien anderer Konten | alle | keine |
| Konten anlegen, bearbeiten, deaktivieren und löschen, Startpasswörter setzen | ja | nein |
| Systemseite (Installer, Konfiguration) | ja | nein |

**Projektleitung anlegen:** unter *Konten* trägt der Administrator Name, E-Mail-Adresse, Rolle und Sprache ein. ANOUK erzeugt ein Startpasswort, das einmal angezeigt wird; dieses an die Person weitergeben. Bei der ersten Anmeldung muss die Person ein eigenes Passwort wählen. Die Anwendung verschickt keine E-Mails; wer sein Passwort vergisst, erhält vom Administrator ein neues Startpasswort.

**Bearbeiten und Ausscheiden:** ein Klick auf ein Konto öffnet dessen Seite. Dort ändert der Administrator Name, E-Mail-Adresse, Rolle und Sprache, deaktiviert das Konto (die Person kann sich nicht mehr anmelden, alles andere bleibt) oder löscht es. Gehören dem Konto Studien, werden sie vorher an ein anderes Konto übertragen; die Ergebnisse bleiben bei den Studien. Der letzte aktive Administrator kann weder gelöscht noch deaktiviert noch zur Projektleitung gemacht werden.

**Trennung:** öffnet eine Projektleitung die Studie eines anderen Kontos über deren Adresse, erhält sie „nicht gefunden“.

**Sprachen:** die Verwaltung folgt zunächst der Browsersprache; im Menü unten links lässt sich die Sprache umstellen, die Wahl wird mit dem Konto gespeichert. Die Sprache der Probandenseiten wird je Studie eingestellt. Die Texte stehen in `app/lang/de.json` (Verwaltung, Schlüssel = englischer Text) und `app/Survey.php` (Probandenseiten). Eine weitere Sprache braucht eine Datei `app/lang/xx.json` und einen Eintrag in `app/I18n.php`; `php tests/i18n.php --keys` listet alle Texte auf.

## 8. Datenschutz

**Was ANOUK speichert:** die von LimeSurvey übergebene ID, die zugewiesene Version, die Antworten mit Antwortzeit und Fensterbreite sowie Zeitstempel.

**Was nicht gespeichert wird:** IP-Adressen, Browserdaten, Namen oder E-Mail-Adressen der Probanden.

**Cookies:** je Studie ein Sitzungscookie (`anouk_s{id}`) mit einem zufälligen Token sowie das PHP-Sitzungscookie (beide HttpOnly, SameSite=Lax, werden beim Schließen des Browsers gelöscht). Sie sind technisch notwendig (§ 25 Abs. 2 Nr. 2 TDDDG). Die Datenbank speichert nur einen SHA-256-Hash des Tokens.

**ID in der Adresse:** nach der ersten Seite steht die ID weder in der Adresszeile noch im Browserverlauf.

**Keine Dritten:** Schriften und Skripte sind Teil der Anwendung. Nichts wird von anderen Servern geladen, und `Referrer-Policy: no-referrer` verhindert, dass Adressen weitergegeben werden.

**Hosting:** die Zugriffsprotokolle des Providers enthalten den Einstiegslink samt ID. In der Datenschutzdokumentation den Auftragsverarbeitungsvertrag mit dem Provider und dessen Speicherdauer für Protokolle festhalten.

**Löschen:** unter *Probanden* einen Probanden über seine ID finden und *Proband löschen* wählen. *Alle Antworten löschen* leert eine Studie, *Studie löschen* entfernt sie mit Design und allen Antworten endgültig.

## 9. Sicherheit

- `install.php` nach der Installation löschen.
- `app/`, `sql/`, `tests/` und `config.php` werden durch `.htaccess`-Dateien gesperrt. Auf Servern ohne `.htaccess` (nginx) diese Pfade in der Serverkonfiguration sperren, z. B. `location ~ ^/anouk/(app|sql|tests)/ { deny all; }`. Auch ohne Sperre geben `config.php`, die PHP-Dateien in `app/` und die Tests beim direkten Aufruf nichts aus.
- Passwörter werden als bcrypt-Hashes gespeichert. Alle Formulare der Verwaltung sind gegen Cross-Site-Requests geschützt, und Sitzungen der Verwaltung enden nach vier Stunden ohne Aktivität (`admin_idle_minutes` in `config.php`). Wird ein Passwort geändert oder zurückgesetzt, enden alle anderen Sitzungen dieses Kontos.
- Nach fehlgeschlagenen Anmeldungen gibt es keine Sperre. Bitte lange Passwörter verwenden; eine Sperre lässt sich mit dem Webserver oder den Werkzeugen des Providers vor ANOUK schalten.
- Wer den Einstiegslink einer Studie im Feld kennt, kann sie mit einer erfundenen ID starten. Solche Zeilen sind erkennbar, weil ihre ID nicht in der LimeSurvey-Teilnehmerliste steht; beim Mergen entfernen.
- Der Export für Excel setzt vor Zellen, die mit `=`, `+`, `-` oder `@` beginnen, einen Apostroph, damit eine Tabellenkalkulation sie anzeigt und nicht als Formel ausführt. Der Export für R bleibt unverändert.
- Eine Content-Security-Policy verbietet eingebettete Skripte und alles von fremden Servern; Seiten werden nicht zwischengespeichert.
- Bei der Installation „Immer HTTPS verwenden“ ankreuzen (`force_https` in `config.php`).

## 10. Probleme

| Problem | Lösung |
|---|---|
| „Datenbank nicht erreichbar“ | Zugangsdaten in `config.php` prüfen oder die Konfiguration zurücksetzen (Abschnitt 1) |
| „Das Formular ist abgelaufen“ | Cookies sind blockiert oder die Seite war zu lange offen: neu laden |
| Upload scheitert ohne Meldung | Die Datei überschreitet `upload_max_filesize` des Providers |
| Umlaute sind verstümmelt | Die CSV als „CSV UTF-8“ speichern |
| PHP-Fehler anzeigen | Vorübergehend `'debug' => true` in `config.php` setzen |

## 11. Entwicklung und Tests

Die Tests laufen mit SQLite ohne MySQL-Server:

```
php tests/run.php      # Logik: Parser, Generator, Prüfung, Versionsvergabe
php tests/i18n.php     # jeder Text hat eine deutsche Übersetzung
bash tests/e2e.sh      # kompletter Ablauf über HTTP (PHP-Server + Python 3)
```

## Dateien

```
index.php            Einstieg (Verwaltung und Probandenseiten)
install.php          Installer (nach Gebrauch löschen)
app/                 Logik und Templates (per .htaccess gesperrt)
app/lang/de.json     deutsche Texte der Verwaltung
assets/              Stylesheet, Schriften, Skripte, Icon
examples/diga/       Beispielstudie mit cbcTools-Skript und Design
sql/                 MySQL-Schema
tests/               Tests mit SQLite
README.de.md         dieser Text; README.md auf Englisch
```

Die Gestaltung der Oberfläche stammt aus dem Schwesterprojekt JUST-CAT (adaptive Tests für LimeSurvey).

## Lizenz

MIT, siehe `LICENSE`. Die mitgelieferten Schriften (Instrument Sans, IBM Plex Serif) stehen unter der SIL Open Font License 1.1 (`assets/fonts`).
