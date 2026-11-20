<?php
/**
 * ANOUK interface texts: German.
 *
 * To add a language, copy lang/en.php to lang/<code>.php (two lower-case letters,
 * e.g. fr.php), set '_name' and translate the values. The keys stay as they are.
 * Missing or empty values fall back to English. Placeholders: %s, %d, %1$s …
 */
defined('ANOUK_ROOT') or exit;

return [
    '_name' => 'Deutsch',

    // ---- respondent pages (survey.*) -------------------------------------
    'survey.title' => 'Befragung',
    'survey.question' => 'Welche dieser Optionen würden Sie wählen?',
    'survey.none' => 'Keine dieser Optionen',
    'survey.progress' => 'Auswahl %d von %d',
    'survey.next' => 'Weiter',
    'survey.start' => 'Los geht’s',
    'survey.choose' => 'Bitte wählen Sie eine der Optionen aus.',
    'survey.option' => 'Option %s',
    'survey.saved_note' => 'Ihre Antwort wird nach jeder Auswahl gespeichert.',
    'survey.closed' => 'Diese Befragung ist derzeit nicht aktiv.',
    'survey.notfound' => 'Diese Befragung wurde nicht gefunden.',
    'survey.badid' => 'Der Link enthält keine gültige Teilnahme-ID. Bitte starten Sie über den Link in Ihrer Einladung.',
    'survey.thanks' => 'Vielen Dank! Dieser Teil der Befragung ist abgeschlossen. Sie können das Fenster jetzt schließen.',
    'survey.thanks_title' => 'Vielen Dank!',
    'survey.preview' => 'Vorschau – Antworten werden nicht gespeichert.',
    'survey.preview_end' => 'Ende der Vorschau. Im Feld würde ANOUK jetzt hierhin weiterleiten:',
    'survey.no_return' => 'Es ist keine Rücksprung-URL eingetragen; Probanden sähen eine Dankeseite.',
    'survey.restart' => 'Vorschau neu starten',
    'survey.back' => 'Zurück zu ANOUK',
    'survey.nodesign' => 'Für diese Studie ist noch kein Design hinterlegt.',
    'survey.cookies' => 'Ihr Browser nimmt das Cookie nicht an, das diese Befragung zum Speichern Ihrer Antworten braucht. Bitte erlauben Sie Cookies für diese Seite und starten Sie erneut über den Link in der Befragung.',
    'survey.maintenance' => 'Diese Befragung wird gerade aktualisiert. Bitte versuchen Sie es in einigen Minuten erneut.',

    // ---- admin area and installer: key = English text -----------------------
    '%1$d of %2$d'
        => '%1$d von %2$d',
    '%1$d versions × %2$d tasks × %3$d alternatives'
        => '%1$d Versionen × %2$d Tasks × %3$d Alternativen',
    '%d attributes and their levels were taken from the file.'
        => '%d Attribute mit ihren Leveln wurden aus der Datei übernommen.',
    '%d attributes saved.'
        => '%d Attribute gespeichert.',
    '%d respondents'
        => '%d Probanden',
    '%d respondents and their answers deleted. The study and its design are kept.'
        => '%d Probanden und ihre Antworten gelöscht. Studie und Design bleiben erhalten.',
    '1. Create choice sets with cbcTools'
        => '1. Choice-Sets mit cbcTools erstellen',
    '1. Run this script in R (written for cbcTools 0.7 or newer: install.packages("cbcTools")).'
        => '1. Dieses Skript in R ausführen (geschrieben für cbcTools 0.7 oder neuer: install.packages("cbcTools")).',
    '2. Upload the CSV'
        => '2. CSV hochladen',
    '2. Upload the file %s in ANOUK under “Design”.'
        => '2. Die Datei %s in ANOUK unter „Design“ hochladen.',
    '<b>%1$d</b> versions × <b>%2$d</b> tasks × <b>%3$d</b> alternatives'
        => '<b>%1$d</b> Versionen × <b>%2$d</b> Tasks × <b>%3$d</b> Alternativen',
    '<b>Codebook</b>: all columns with label, type and values.'
        => '<b>Codebuch</b>: alle Spalten mit Beschreibung, Typ und Werten.',
    '<b>Long</b>: one row per ID × task × alternative, <code>choice</code> = 1 for the chosen alternative. Ready for <code>logitr</code>, <code>mlogit</code> and <code>bayesm</code>.'
        => '<b>Lang</b>: eine Zeile je ID × Aufgabe × Alternative, <code>choice</code> = 1 für die gewählte Alternative. Direkt nutzbar mit <code>logitr</code>, <code>mlogit</code> und <code>bayesm</code>.',
    '<b>Wide</b>: one row per ID with the chosen alternative (<code>choice_t01</code> …) and the response time (<code>rt_t01</code> …) per task; to merge with the survey data.'
        => '<b>Breit</b>: eine Zeile je ID mit gewählter Alternative (<code>choice_t01</code> …) und Antwortzeit (<code>rt_t01</code> …) je Aufgabe; zum Zusammenführen mit den Umfragedaten.',
    '<code>profileID</code>, <code>obsID</code>, <code>blockID</code> and <code>choice</code> are ignored, no-choice rows removed.'
        => '<code>profileID</code>, <code>obsID</code>, <code>blockID</code> und <code>choice</code> werden ignoriert, No-Choice-Zeilen entfernt.',
    'A database update is pending. Please ask the administrator to log in and run it.'
        => 'Ein Datenbank-Update steht aus. Bitte den Master-Admin bitten, sich anzumelden und es auszuführen.',
    'A deactivated account can no longer log in; its studies stay as they are.'
        => 'Ein deaktiviertes Konto kann sich nicht mehr anmelden; seine Studien bleiben unverändert.',
    'A design exists already (%d versions). A new upload replaces it.'
        => 'Es gibt schon ein Design (%d Versionen). Ein neuer Upload ersetzt es.',
    'A design exists already. Delete the design first, then change the attributes.'
        => 'Es gibt schon ein Design. Bitte zuerst das Design löschen, dann die Attribute ändern.',
    'A required component is missing. Ask your provider to enable it (often a PHP version setting in the control panel).'
        => 'Eine benötigte Komponente fehlt. Bitten Sie Ihren Provider, sie zu aktivieren (oft eine Einstellung der PHP-Version im Kundenmenü).',
    'Access'
        => 'Zugang',
    'Account'
        => 'Konto',
    'Account activated.'
        => 'Konto aktiviert.',
    'Account deactivated.'
        => 'Konto deaktiviert.',
    'Account for %s created.'
        => 'Konto für %s angelegt.',
    'Account updated.'
        => 'Konto aktualisiert.',
    'Account “%1$s” deleted; %2$d studies were transferred.'
        => 'Konto „%1$s“ gelöscht; %2$d Studien wurden übertragen.',
    'Account “%s” deleted.'
        => 'Konto „%s“ gelöscht.',
    'Accounts'
        => 'Konten',
    'Activate'
        => 'Aktivieren',
    'Active'
        => 'Aktiv',
    'Administration'
        => 'Verwaltung',
    'Administrator'
        => 'Administrator',
    'Administrator account'
        => 'Administrator-Konto',
    'Afterwards ANOUK can be set up again here, for example with new database credentials. Studies and accounts in the database are kept. Administrators can also do this without the code under “System”.'
        => 'Danach lässt sich ANOUK hier neu einrichten, z. B. mit neuen Datenbankzugangsdaten. Studien und Konten in der Datenbank bleiben erhalten. Administratoren können das auch ohne Code unter „System“ tun.',
    'All'
        => 'Alle',
    'All ANOUK tables start with this prefix, so ANOUK can share a database with other tools. Keep <code>anouk_</code> unless you run two ANOUK installations in one database. To reuse an existing ANOUK database, enter its prefix.'
        => 'Alle ANOUK-Tabellen beginnen mit diesem Präfix; so kann ANOUK eine Datenbank mit anderen Tools teilen. <code>anouk_</code> beibehalten, außer es laufen zwei ANOUK-Installationen in einer Datenbank. Um eine bestehende ANOUK-Datenbank weiterzuverwenden, deren Präfix eintragen.',
    'All studies'
        => 'Alle Studien',
    'all, including drop-outs'
        => 'alle, auch Abbrecher',
    'Already installed'
        => 'Bereits installiert',
    'alternative'
        => 'die Alternative',
    'alternatives per choice set'
        => 'Alternativen je Choice-Set',
    'Alternatives per set'
        => 'Alternativen je Set',
    'Alternatives per task'
        => 'Alternativen je Task',
    'Alternatives per task: 2 to 10.'
        => 'Alternativen je Task: 2 bis 10.',
    'Always use HTTPS (redirect http:// to https://)'
        => 'Immer HTTPS verwenden (http:// auf https:// umleiten)',
    'ANOUK cannot reach the database configured in config.php. Check the access data. To set ANOUK up again, delete config.php by FTP or reset it in install.php with the emergency code.'
        => 'ANOUK erreicht die Datenbank aus config.php nicht. Bitte die Zugangsdaten prüfen. Zum Neueinrichten config.php per FTP löschen oder in install.php mit dem Notfall-Code zurücksetzen.',
    'ANOUK is installed'
        => 'ANOUK ist installiert',
    'ANOUK is installed (<code>config.php</code> exists).'
        => 'ANOUK ist installiert (<code>config.php</code> existiert).',
    'ANOUK uses the existing database with %d accounts.'
        => 'ANOUK nutzt die bestehende Datenbank mit %d Konten.',
    'ANOUK writes an R script that builds the choice sets with <code>cbcTools</code> and saves them as CSV. Run it in R, then upload the CSV.'
        => 'ANOUK erzeugt ein R-Skript, das mit <code>cbcTools</code> die Choice-Sets baut und als CSV speichert. Skript in R ausführen, dann die CSV hochladen.',
    'ANOUK writes an R script with the attributes of this study. <a href="%s">Define the attributes</a> first.'
        => 'ANOUK erzeugt ein R-Skript mit den Attributen dieser Studie. Bitte zuerst <a href="%s">die Attribute definieren</a>.',
    'ANOUK – create choice sets with cbcTools'
        => 'ANOUK – Choice-Sets mit cbcTools erstellen',
    'ANOUK: installation'
        => 'ANOUK: Installation',
    'Answers'
        => 'Antworten',
    'Assign columns yourself'
        => 'Spalten selbst zuordnen',
    'At least 10 characters.'
        => 'Mindestens 10 Zeichen.',
    'Attribute'
        => 'Attribut',
    'Attributes and design can only be changed while the study is a draft.'
        => 'Attribute und Design lassen sich nur im Entwurf ändern.',
    'Attributes and levels'
        => 'Attribute und Level',
    'automatic'
        => 'automatisch',
    'Back to draft'
        => 'Zurück in den Entwurf',
    'Back to the overview'
        => 'Zurück zur Übersicht',
    'Back up the database first if you can (for example with phpMyAdmin of your hosting provider). The update keeps all studies, accounts and answers.'
        => 'Wenn möglich vorher die Datenbank sichern (z. B. mit phpMyAdmin des Providers). Das Update behält alle Studien, Konten und Antworten.',
    'balanced – equal level frequencies'
        => 'balanced – gleiche Levelhäufigkeiten',
    'Cancel'
        => 'Abbrechen',
    'Change password'
        => 'Passwort ändern',
    'Check'
        => 'Prüfen',
    'choice sets per version'
        => 'Choice-Sets je Version',
    'Choice-based conjoint studies with LimeSurvey'
        => 'Choice-Based-Conjoint-Studien mit LimeSurvey',
    'Choose a CSV file.'
        => 'Bitte eine CSV-Datei auswählen.',
    'Choose the account that takes over the studies.'
        => 'Bitte das Konto wählen, das die Studien übernimmt.',
    'chose %s'
        => 'Wahl: %s',
    'Chosen by the person'
        => 'Von der Person selbst gewählt',
    'Close'
        => 'Schließen',
    'Closed'
        => 'Geschlossen',
    'Column for the alternative'
        => 'Spalte für die Alternative',
    'Column for the task'
        => 'Spalte für den Task',
    'Column for the version'
        => 'Spalte für die Version',
    'Column names and levels must match the defined attributes.'
        => 'Spaltennamen und Level müssen den definierten Attributen entsprechen.',
    'Column names of the attributes in exports and in the cbcTools script, for example <code>price</code> instead of “Price per month”. Lower-case letters, digits and underscores, starting with a letter. You can change them at any time, also while the study is in the field.'
        => 'Spaltennamen der Attribute in Exporten und im cbcTools-Skript, z. B. <code>preis</code> statt „Preis pro Monat“. Kleinbuchstaben, Ziffern und Unterstriche, beginnend mit einem Buchstaben. Sie lassen sich jederzeit ändern, auch während der Feldphase.',
    'Completed'
        => 'Abgeschlossen',
    'completed'
        => 'abgeschlossen',
    'Completed in the last 7 days'
        => 'Abgeschlossen in den letzten 7 Tagen',
    'completed only'
        => 'nur abgeschlossene',
    'Completed respondents'
        => 'Abgeschlossene Probanden',
    'Completions per used version'
        => 'Abschlüsse je genutzter Version',
    'config.php could not be deleted. Delete it by FTP.'
        => 'config.php konnte nicht gelöscht werden. Bitte per FTP entfernen.',
    'config.php has been deleted. ANOUK can now be set up again; the data in the database are kept.'
        => 'config.php wurde gelöscht. ANOUK kann jetzt neu eingerichtet werden; die Daten in der Datenbank bleiben erhalten.',
    'Connected, server version %s.'
        => 'Verbindung hergestellt, Serverversion %s.',
    'Connection test failed'
        => 'Verbindungstest fehlgeschlagen',
    'Connection test passed'
        => 'Verbindungstest erfolgreich',
    'Contents'
        => 'Inhalt',
    'Copied'
        => 'Kopiert',
    'Copy'
        => 'Kopieren',
    'Copy link'
        => 'Link kopieren',
    'Create a design first'
        => 'Erst ein Design anlegen',
    'Create a new starting password?'
        => 'Neues Startpasswort erzeugen?',
    'Create a study under <a href="%s">New study</a> and define its attributes and levels.'
        => 'Unter <a href="%s">Neue Studie</a> eine Studie anlegen und ihre Attribute und Level definieren.',
    'Create account'
        => 'Konto anlegen',
    'Create or upload'
        => 'Erstellen oder hochladen',
    'Create study'
        => 'Studie anlegen',
    'Current password'
        => 'Aktuelles Passwort',
    'Data exist already; the design cannot be deleted.'
        => 'Es liegen schon Daten vor; das Design kann nicht gelöscht werden.',
    'Database (MySQL or MariaDB)'
        => 'Datenbank (MySQL oder MariaDB)',
    'Database error: %s'
        => 'Datenbankfehler: %s',
    'Database name'
        => 'Datenbankname',
    'Database not reachable'
        => 'Datenbank nicht erreichbar',
    'Database password'
        => 'Datenbank-Passwort',
    'Database server'
        => 'Datenbankserver',
    'Database update'
        => 'Datenbank-Update',
    'Database updated to version %d (%s).'
        => 'Datenbank auf Version %d aktualisiert (%s).',
    'Database user'
        => 'Datenbank-Benutzer',
    'Database version'
        => 'Datenbankversion',
    'Deactivate'
        => 'Deaktivieren',
    'Define'
        => 'Definieren',
    'Define attributes and levels before downloading the R script.'
        => 'Für das R-Skript bitte zuerst Attribute und Level definieren.',
    'Define attributes and levels first.'
        => 'Bitte zuerst Attribute und Level definieren.',
    'Delete account'
        => 'Konto löschen',
    'Delete all answers'
        => 'Alle Antworten löschen',
    'Delete config.php'
        => 'config.php löschen',
    'Delete design'
        => 'Design löschen',
    'Delete for good'
        => 'Endgültig löschen',
    'Delete install.php'
        => 'install.php löschen',
    'Delete install.php and log in'
        => 'install.php löschen und anmelden',
    'Delete respondent'
        => 'Proband löschen',
    'Delete study'
        => 'Studie löschen',
    'Delete this account for good'
        => 'Dieses Konto endgültig löschen',
    'deleted'
        => 'gelöscht',
    'Deletes <code>config.php</code> and the lock file <code>data/install.lock</code>. ANOUK then leads to <code>install.php</code>, where the database connection can be set up again. Studies, accounts and answers in the database are kept.'
        => 'Löscht <code>config.php</code> und die Sperrdatei <code>data/install.lock</code>. Danach führt ANOUK zu <code>install.php</code>, wo sich die Datenbankverbindung neu einrichten lässt. Studien, Konten und Antworten in der Datenbank bleiben erhalten.',
    'Deletes all respondents and answers of this study, for example after a test run. The study, its settings and its design are kept.'
        => 'Löscht alle Probanden und Antworten dieser Studie, z. B. nach einem Testlauf. Studie, Einstellungen und Design bleiben erhalten.',
    'Deletes the study with its design and all answers for good.'
        => 'Löscht die Studie mit Design und allen Antworten endgültig.',
    'Deletes this respondent and all their answers, for example on a deletion request. Their version becomes free again.'
        => 'Löscht diesen Probanden mit allen Antworten, z. B. auf eine Löschanfrage hin. Seine Version wird wieder frei.',
    'Design'
        => 'Design',
    'Design deleted.'
        => 'Design gelöscht.',
    'Design diagnostics: level frequencies and overlap'
        => 'Design-Diagnose: Levelhäufigkeiten und Overlap',
    'Design generated: %1$d versions × %2$d tasks × %3$d alternatives (seed %4$d).'
        => 'Design erzeugt: %1$d Versionen × %2$d Tasks × %3$d Alternativen (Seed %4$d).',
    'Design saved: %1$d versions × %2$d tasks × %3$d alternatives.'
        => 'Design gespeichert: %1$d Versionen × %2$d Tasks × %3$d Alternativen.',
    'Details'
        => 'Angaben',
    'Do not add a "no choice" option here; switch on the none option in ANOUK instead.'
        => 'Ein "no choice" bitte nicht hier, sondern als None-Option in ANOUK aktivieren.',
    'Download as CSV'
        => 'Als CSV herunterladen',
    'Download CSV'
        => 'CSV herunterladen',
    'Download R script'
        => 'R-Skript herunterladen',
    'Draft'
        => 'Entwurf',
    'Dropped out'
        => 'Abgebrochen',
    'E-mail address'
        => 'E-Mail-Adresse',
    'E-mail address or password is wrong.'
        => 'E-Mail-Adresse oder Passwort ist falsch.',
    'e.g. battery-study'
        => 'z. B. akku-studie',
    'Edit'
        => 'Bearbeiten',
    'Emergency code'
        => 'Notfall-Code',
    'Emergency code from the installation'
        => 'Notfall-Code aus der Installation',
    'Enter a name and a valid e-mail address for the administrator.'
        => 'Bitte Name und gültige E-Mail-Adresse für den Administrator angeben.',
    'Enter a name and a valid e-mail address.'
        => 'Bitte Name und gültige E-Mail-Adresse angeben.',
    'Enter a name.'
        => 'Bitte einen Namen angeben.',
    'Enter a title (at most 255 characters).'
        => 'Bitte einen Titel angeben (max. 255 Zeichen).',
    'Enter a valid e-mail address.'
        => 'Bitte eine gültige E-Mail-Adresse angeben.',
    'Enter at least one attribute.'
        => 'Bitte mindestens ein Attribut angeben.',
    'Enter the database name and user.'
        => 'Bitte Datenbankname und Benutzer angeben.',
    'Enter the return URL to LimeSurvey in the settings, check the preview and send the study into the field.'
        => 'In den Einstellungen die Rücksprung-URL zu LimeSurvey eintragen, die Vorschau prüfen und die Studie ins Feld schicken.',
    'Entry link for LimeSurvey'
        => 'Einstiegslink für LimeSurvey',
    'Error %d'
        => 'Fehler %d',
    'error URL'
        => 'Fehler-URL',
    'Error URL (optional)'
        => 'Fehler-URL (optional)',
    'Every task needs at least two alternatives.'
        => 'Jeder Task braucht mindestens zwei Alternativen.',
    'Existing ANOUK database found (%d accounts). It will be used again; no new administrator is needed.'
        => 'Bestehende ANOUK-Datenbank gefunden (%d Konten). Sie wird weiterverwendet; ein neuer Administrator ist nicht nötig.',
    'Export'
        => 'Export',
    'Field status'
        => 'Feldstatus',
    'File (CSV with comma, semicolon or tab)'
        => 'Datei (CSV mit Komma, Semikolon oder Tab)',
    'File style'
        => 'Dateiformat',
    'Fill in the database details from your hosting provider. Use <em>Test connection</em> first; nothing is saved until you click <em>Install</em>.'
        => 'Tragen Sie die Datenbankangaben Ihres Providers ein. Nutzen Sie zuerst <em>Verbindung testen</em>; gespeichert wird erst mit <em>Installieren</em>.',
    'Filter'
        => 'Filtern',
    'Fixed texts such as buttons and messages follow this language.'
        => 'Feste Texte wie Schaltflächen und Hinweise folgen dieser Sprache.',
    'Folder data/ writable (for the lock file)'
        => 'Ordner data/ beschreibbar (für die Sperrdatei)',
    'Folder writable (for config.php)'
        => 'Ordner beschreibbar (für config.php)',
    'For D-efficient designs with priors see ?cbc_priors and method = "cea" or "modfed".'
        => 'Für D-effiziente Designs mit Vorannahmen siehe ?cbc_priors und method = "cea" oder "modfed".',
    'For security, delete <code>install.php</code> now.'
        => 'Löschen Sie aus Sicherheitsgründen jetzt <code>install.php</code>.',
    'For tests, or when R is not at hand. For the actual study, cbcTools is preferable.'
        => 'Für Tests oder wenn kein R zur Hand ist. Für die eigentliche Studie ist cbcTools vorzuziehen.',
    'Format'
        => 'Format',
    'Further errors are not shown.'
        => 'Weitere Fehler werden nicht angezeigt.',
    'Generate in ANOUK'
        => 'In ANOUK erzeugen',
    'generated with “%1$s”, seed %2$s'
        => 'erzeugt mit „%1$s“, Seed %2$s',
    'German Excel (semicolon)'
        => 'Deutsches Excel (Semikolon)',
    'Getting started'
        => 'Erste Schritte',
    'Help'
        => 'Hilfe',
    'Host'
        => 'Host',
    'Host, port or database name contain invalid characters.'
        => 'Host, Port oder Datenbankname enthalten ungültige Zeichen.',
    'HTTPS'
        => 'HTTPS',
    'ID'
        => 'ID',
    'IDs that start with <code>%s</code> are test IDs.'
        => 'IDs, die mit <code>%s</code> beginnen, sind Test-IDs.',
    'If this database comes from an older ANOUK version, the administrator is offered a database update after logging in.'
        => 'Stammt diese Datenbank aus einer älteren ANOUK-Version, wird dem Master-Admin nach der Anmeldung ein Datenbank-Update angeboten.',
    'Ignore columns (comma-separated)'
        => 'Spalten ignorieren (kommagetrennt)',
    'In progress'
        => 'Läuft',
    'In the field'
        => 'Im Feld',
    'Inactive'
        => 'Inaktiv',
    'Include test IDs'
        => 'Test-IDs einschließen',
    'Install'
        => 'Installieren',
    'install.php could not be deleted. Delete it by FTP.'
        => 'install.php konnte nicht gelöscht werden. Bitte per FTP entfernen.',
    'install.php has been deleted.'
        => 'install.php wurde gelöscht.',
    'install.php is missing. Upload install.php again first, otherwise ANOUK cannot be set up afterwards.'
        => 'install.php fehlt. Bitte zuerst install.php wieder hochladen, sonst lässt sich ANOUK danach nicht neu einrichten.',
    'Installation'
        => 'Installation',
    'Installer locked'
        => 'Installer gesperrt',
    'Introduction before the first task (optional)'
        => 'Instruktion vor dem ersten Task (optional)',
    'It is shown only now. Pass it on safely; the person has to choose a password of their own at the first login.'
        => 'Es wird nur jetzt angezeigt. Bitte sicher weitergeben; bei der ersten Anmeldung muss die Person ein eigenes Passwort wählen.',
    'Label of the none option'
        => 'Beschriftung der None-Option',
    'Language'
        => 'Sprache',
    'Language of the respondent pages'
        => 'Sprache der Probandenseiten',
    'Last activity'
        => 'Letzte Aktivität',
    'Last login'
        => 'Letzte Anmeldung',
    'Leave empty for the default text of the chosen language.'
        => 'Leer lassen für den Standardtext der gewählten Sprache.',
    'level labels instead of dummy codes'
        => 'Level-Beschriftungen statt Dummies',
    'Levels'
        => 'Level',
    'LimeSurvey sends respondents to this link; it replaces {TOKEN} with the participant’s token.'
        => 'LimeSurvey schickt die Probanden auf diesen Link und ersetzt {TOKEN} durch den Token des Teilnehmers.',
    'Line %1$d: the attribute “%2$s” exists already.'
        => 'Zeile %1$d: Das Attribut „%2$s“ gibt es bereits.',
    'Line %1$d: the attribute “%3$s” has no level “%2$s”.'
        => 'Zeile %1$d: Das Level „%2$s“ gibt es beim Attribut „%3$s“ nicht.',
    'Line %1$d: the value for the attribute “%2$s” is missing.'
        => 'Zeile %1$d: Beim Attribut „%2$s“ fehlt der Wert.',
    'Line %1$d: version %2$d, task %3$d, alternative %4$d occurs twice.'
        => 'Zeile %1$d: Version %2$d, Task %3$d, Alternative %4$d kommt doppelt vor.',
    'Line %1$d: “%2$s” contains a level twice.'
        => 'Zeile %1$d: „%2$s“ enthält ein Level doppelt.',
    'Line %1$d: “%2$s” is not a valid number for the %3$s.'
        => 'Zeile %1$d: „%2$s“ ist keine gültige Nummer für %3$s.',
    'Line %1$d: “%2$s” needs at least two levels.'
        => 'Zeile %1$d: „%2$s“ braucht mindestens zwei Level.',
    'Line %d: a level is too long (at most 255 characters).'
        => 'Zeile %d: Ein Level ist zu lang (max. 255 Zeichen).',
    'Line %d: the attribute name is missing.'
        => 'Zeile %d: Der Attributname fehlt.',
    'Line %d: the attribute name is too long (at most 190 characters).'
        => 'Zeile %d: Der Attributname ist zu lang (max. 190 Zeichen).',
    'Line %d: the colon is missing. Format: Name: Level 1 | Level 2'
        => 'Zeile %d: Der Doppelpunkt fehlt. Format: Name: Level 1 | Level 2',
    'Link LimeSurvey to the entry link of the study and download the data in long format afterwards.'
        => 'LimeSurvey mit dem Einstiegslink der Studie verbinden und die Daten danach im Langformat herunterladen.',
    'Link name'
        => 'Kürzel für den Link',
    'Link with LimeSurvey'
        => 'Anbindung an LimeSurvey',
    'Log in'
        => 'Anmelden',
    'Log in without deleting'
        => 'Ohne Löschen anmelden',
    'Log out'
        => 'Abmelden',
    'Lower-case letters, digits and hyphens. It appears in the link for respondents and cannot be changed later.'
        => 'Kleinbuchstaben, Ziffern und Bindestriche. Erscheint im Link für die Probanden und lässt sich später nicht ändern.',
    'Manage accounts'
        => 'Konten verwalten',
    'Method'
        => 'Verfahren',
    'Minimal overlap (recommended)'
        => 'Minimaler Overlap (empfohlen)',
    'minoverlap – little overlap (recommended)'
        => 'minoverlap – wenig Overlap (empfohlen)',
    'Name'
        => 'Name',
    'Never'
        => 'Nie',
    'New account'
        => 'Neues Konto',
    'New password'
        => 'Neues Passwort',
    'New starting password'
        => 'Neues Startpasswort',
    'New starting password created. It has to be changed at the next login.'
        => 'Neues Startpasswort erzeugt. Es muss bei der nächsten Anmeldung geändert werden.',
    'New study'
        => 'Neue Studie',
    'Next'
        => 'Weiter',
    'No access'
        => 'Kein Zugriff',
    'No attributes yet. Define them here, or take them from the file when you upload a design.'
        => 'Noch keine Attribute. Hier definieren oder beim Upload eines Designs aus der Datei übernehmen.',
    'No column found for “%1$s”. Expected for example %2$s, or enter the column name in the form.'
        => 'Keine Spalte für „%1$s“ gefunden. Erwartet z. B. %2$s – oder den Spaltennamen im Formular angeben.',
    'No connection: %s'
        => 'Keine Verbindung: %s',
    'No design yet.'
        => 'Noch kein Design.',
    'No new choice task found for version %1$d, task %2$d. Choose fewer tasks or alternatives.'
        => 'Für Version %1$d, Task %2$d wurde keine neue Choice-Situation gefunden. Bitte weniger Tasks oder Alternativen wählen.',
    'No respondents found.'
        => 'Keine Probanden gefunden.',
    'No studies yet. Create a study to get a link for LimeSurvey.'
        => 'Noch keine Studien. Legen Sie eine Studie an, um einen Link für LimeSurvey zu erhalten.',
    'none chosen'
        => 'keine gewählt',
    'None option'
        => 'None-Option',
    'None yet'
        => 'Noch keine',
    'none yet'
        => 'noch keins',
    'Not found'
        => 'Nicht gefunden',
    'not set – respondents only see a thank-you page.'
        => 'nicht eingetragen – Probanden sehen nur eine Dankeseite.',
    'Number of versions: 1 to 10,000.'
        => 'Anzahl Versionen: 1 bis 10.000.',
    'Offer a none option'
        => 'None-Option anbieten',
    'Often <code>localhost</code>; some providers use a separate name.'
        => 'Oft <code>localhost</code>; manche Provider nennen einen eigenen Namen.',
    'One attribute per line, levels separated by <code>|</code>'
        => 'Ein Attribut pro Zeile, Level durch <code>|</code> getrennt',
    'Or upload a cbcTools design of your own right away; the attributes are then taken from the file.'
        => 'Oder direkt ein eigenes cbcTools-Design hochladen; die Attribute werden dann aus der Datei übernommen.',
    'otherwise the design no longer matches the attributes of the study.'
        => 'sonst passt das Design nicht mehr zu den Attributen der Studie.',
    'Output of <code>cbcTools</code> (columns <code>respID</code>, <code>qID</code>, <code>altID</code> and one column per attribute) or any other design in long format:'
        => 'Ausgabe von <code>cbcTools</code> (Spalten <code>respID</code>, <code>qID</code>, <code>altID</code> und eine Spalte je Attribut) oder jedes andere Design im Langformat:',
    'Overview'
        => 'Übersicht',
    'Owner'
        => 'Verantwortlich',
    'Page %1$d of %2$d'
        => 'Seite %1$d von %2$d',
    'Password'
        => 'Passwort',
    'Password changed.'
        => 'Passwort geändert.',
    'Pending updates:'
        => 'Ausstehende Updates:',
    'PHP 8.1 or newer (found: %s)'
        => 'PHP 8.1 oder neuer (vorhanden: %s)',
    'PHP extension mbstring'
        => 'PHP-Erweiterung mbstring',
    'PHP extension pdo_mysql'
        => 'PHP-Erweiterung pdo_mysql',
    'Placeholders: <code>{ID}</code> = the respondent’s ID, <code>{STATUS}</code> = <code>complete</code>. To return into the same survey, LimeSurvey needs a participant list with token-based response persistence.'
        => 'Platzhalter: <code>{ID}</code> = ID des Probanden, <code>{STATUS}</code> = <code>complete</code>. Für die Rückkehr in dieselbe Umfrage braucht LimeSurvey eine Teilnehmertabelle mit token-basierter Antwortpersistenz.',
    'Plain text; blank lines separate paragraphs. Leave empty to start with the first task.'
        => 'Reiner Text; Leerzeilen trennen Absätze. Leer lassen, um direkt mit dem ersten Task zu beginnen.',
    'Port'
        => 'Port',
    'Prefix of test IDs'
        => 'Präfix für Test-IDs',
    'present'
        => 'vorhanden',
    'Preview'
        => 'Vorschau',
    'Previous'
        => 'Zurück',
    'Project leader'
        => 'Projektleitung',
    'Project leaders see and edit only their own studies. ANOUK creates a starting password, which has to be changed at the first login.'
        => 'Projektleitungen sehen und bearbeiten nur ihre eigenen Studien. ANOUK erzeugt ein Startpasswort, das bei der ersten Anmeldung geändert werden muss.',
    'Question above each task'
        => 'Frage über jedem Task',
    'R, SPSS, Python (comma)'
        => 'R, SPSS, Python (Komma)',
    'Random'
        => 'Zufällig',
    'Random attribute order per respondent (the same for all of their tasks)'
        => 'Zufällige Attributreihenfolge je Proband (gleich für alle seine Tasks)',
    'random – random profiles'
        => 'random – zufällige Profile',
    'Really delete the design?'
        => 'Design wirklich löschen?',
    'Really delete this respondent and all their answers?'
        => 'Diesen Probanden mit allen Antworten wirklich löschen?',
    'Release the version of a drop-out after (minutes)'
        => 'Version eines Abbrechers freigeben nach (Minuten)',
    'Repeat new password'
        => 'Neues Passwort wiederholen',
    'Repeat password'
        => 'Passwort wiederholen',
    'Replace'
        => 'Ersetzen',
    'Requirements'
        => 'Voraussetzungen',
    'Reset the configuration'
        => 'Konfiguration zurücksetzen',
    'Reset the configuration (delete config.php)'
        => 'Konfiguration zurücksetzen (config.php löschen)',
    'Respondent'
        => 'Proband',
    'Respondent pages'
        => 'Probandenseiten',
    'Respondent “%s” and their answers deleted.'
        => 'Proband „%s“ und seine Antworten gelöscht.',
    'Respondents'
        => 'Probanden',
    'Return after completion:'
        => 'Rücksprung nach Abschluss:',
    'Return to LimeSurvey'
        => 'Rücksprung zu LimeSurvey',
    'return URL'
        => 'Rücksprung-URL',
    'Return URL after completion'
        => 'Rücksprung-URL nach Abschluss',
    'Returns to the survey'
        => 'Kehrt zur Umfrage zurück',
    'Role'
        => 'Rolle',
    'Save'
        => 'Speichern',
    'Save for ANOUK'
        => 'Für ANOUK speichern',
    'Save variable names'
        => 'Variablennamen speichern',
    'Saved: '
        => 'Gespeichert: ',
    'Search ID'
        => 'ID suchen',
    'Seed (empty = random)'
        => 'Seed (leer = zufällig)',
    'Send into the field'
        => 'Ins Feld schicken',
    'Set it'
        => 'Eintragen',
    'Sets per version'
        => 'Sets je Version',
    'Settings'
        => 'Einstellungen',
    'Settings saved.'
        => 'Einstellungen gespeichert.',
    'Share'
        => 'Anteil',
    'shortcut – a compromise'
        => 'shortcut – Kompromiss',
    'Show progress'
        => 'Fortschritt anzeigen',
    'Show respondents'
        => 'Probanden anzeigen',
    'Started'
        => 'Begonnen',
    'Started, not completed'
        => 'Begonnen, nicht abgeschlossen',
    'Starting password'
        => 'Startpasswort',
    'Starting password for <b>%s</b>:'
        => 'Startpasswort für <b>%s</b>:',
    'Starting password, not yet changed'
        => 'Startpasswort, noch nicht geändert',
    'Status'
        => 'Status',
    'Status: %s.'
        => 'Status: %s.',
    'Studies'
        => 'Studien',
    'Study'
        => 'Studie',
    'Study created. Next, define the attributes or upload a design straight away.'
        => 'Studie angelegt. Als Nächstes Attribute definieren oder direkt ein Design hochladen.',
    'Study “%s” and all its data deleted.'
        => 'Studie „%s“ samt aller Daten gelöscht.',
    'Study: %1$s (%2$s), script created on %3$s'
        => 'Studie: %1$s (%2$s), Skript erzeugt am %3$s',
    'System'
        => 'System',
    'Table prefix'
        => 'Tabellenpräfix',
    'Tables and administrator account have been created.'
        => 'Tabellen und Administrator-Konto sind angelegt.',
    'task'
        => 'den Task',
    'Task %d'
        => 'Task %d',
    'Tasks per version'
        => 'Tasks je Version',
    'Tasks per version: 1 to 50.'
        => 'Tasks je Version: 1 bis 50.',
    'Tasks with a repeated level'
        => 'Tasks mit wiederholtem Level',
    'Test'
        => 'Test',
    'Test connection'
        => 'Verbindung testen',
    'Test IDs'
        => 'Test-IDs',
    'Test IDs do not count for the version assignment and are left out of the export unless chosen.'
        => 'Test-IDs zählen nicht für die Versionsvergabe und fehlen im Export, wenn nicht ausdrücklich gewählt.',
    'The %s must start with http:// or https://.'
        => 'Die %s muss mit http:// oder https:// beginnen.',
    'The administrator can create further accounts for project leaders after logging in.'
        => 'Der Administrator kann nach der Anmeldung weitere Konten für Projektleitungen anlegen.',
    'The administrator password needs at least 10 characters.'
        => 'Das Administrator-Passwort braucht mindestens 10 Zeichen.',
    'The attributes are locked because a design exists or the study is not a draft.'
        => 'Die Attribute sind gesperrt, weil schon ein Design existiert oder die Studie kein Entwurf mehr ist.',
    'The attributes are taken from the file.'
        => 'Die Attribute werden aus der Datei übernommen.',
    'The column “%s” matches no defined attribute. If it is not an attribute, enter it under “Ignore columns”.'
        => 'Die Spalte „%s“ passt zu keinem definierten Attribut. Falls sie kein Attribut ist, unter „Spalten ignorieren“ eintragen.',
    'The current password is wrong.'
        => 'Das aktuelle Passwort stimmt nicht.',
    'The database contains %1$d other tables; ANOUK uses its own tables with the prefix %2$s.'
        => 'Die Datenbank enthält %1$d andere Tabellen; ANOUK nutzt eigene Tabellen mit dem Präfix %2$s.',
    'The database is empty.'
        => 'Die Datenbank ist leer.',
    'The database server was not reached. Check host and port; sometimes the host is not “localhost”.'
        => 'Der Datenbankserver ist nicht erreichbar. Host und Port prüfen; manchmal ist der Host nicht „localhost“.',
    'The database update failed: %s'
        => 'Das Datenbank-Update ist fehlgeschlagen: %s',
    'The design can only be changed while the study is a draft.'
        => 'Das Design lässt sich nur im Entwurf ändern.',
    'The design was not saved.'
        => 'Das Design wurde nicht gespeichert.',
    'The emergency code is wrong.'
        => 'Der Notfall-Code stimmt nicht.',
    'The file contains no attribute columns.'
        => 'Die Datei enthält keine Attributspalten.',
    'The file has no column for the attribute “%s”.'
        => 'Für das Attribut „%s“ fehlt eine Spalte in der Datei.',
    'The file has no column “%s”.'
        => 'Die Spalte „%s“ gibt es in der Datei nicht.',
    'The file is empty or contains only a header row.'
        => 'Die Datei ist leer oder enthält nur eine Kopfzeile.',
    'The file is larger than 20 MB.'
        => 'Die Datei ist größer als 20 MB.',
    'The files on the server are newer than the database (database version %1$d, needed: %2$d). Until the update has run, respondents see a maintenance notice and project leaders cannot work.'
        => 'Die Dateien auf dem Server sind neuer als die Datenbank (Datenbankversion %1$d, benötigt: %2$d). Bis das Update gelaufen ist, sehen Teilnehmende einen Wartungshinweis und Projektleitungen können nicht arbeiten.',
    'The folder is not writable. Create a file <code>config.php</code> next to <code>index.php</code> with this content:'
        => 'Der Ordner ist nicht beschreibbar. Bitte eine Datei <code>config.php</code> neben <code>index.php</code> mit diesem Inhalt anlegen:',
    'The form has expired. Go back, reload the page and try again.'
        => 'Das Formular ist abgelaufen. Bitte zurückgehen, die Seite neu laden und es noch einmal versuchen.',
    'The help file was not found.'
        => 'Die Hilfedatei wurde nicht gefunden.',
    'The installer <code>install.php</code> is still on the server. Without the emergency code it cannot be misused, but it should be deleted.'
        => 'Der Installer <code>install.php</code> liegt noch auf dem Server. Ohne Notfall-Code lässt er sich nicht missbrauchen, sollte aber gelöscht werden.',
    'The installer is locked by <code>data/install.lock</code>, but <code>config.php</code> is missing. To install again, delete <code>data/install.lock</code> by FTP and reload this page. The data in the database are kept.'
        => 'Der Installer ist durch <code>data/install.lock</code> gesperrt, aber <code>config.php</code> fehlt. Für eine Neuinstallation <code>data/install.lock</code> per FTP löschen und die Seite neu laden. Die Daten in der Datenbank bleiben erhalten.',
    'The last active administrator cannot be deactivated.'
        => 'Der letzte aktive Administrator kann nicht deaktiviert werden.',
    'The last active administrator cannot be deleted.'
        => 'Der letzte aktive Administrator kann nicht gelöscht werden.',
    'The last active administrator must keep this role.'
        => 'Der letzte aktive Administrator muss diese Rolle behalten.',
    'The level labels come from ANOUK and must not be changed,'
        => 'Die Level-Beschriftungen stammen aus ANOUK und dürfen nicht verändert werden,',
    'The levels must match an uploaded design exactly. The order of the lines is the display order.'
        => 'Die Level müssen exakt zu einem hochgeladenen Design passen. Die Reihenfolge der Zeilen ist die Anzeigereihenfolge.',
    'The link name may contain only lower-case letters, digits and hyphens (2 to 63 characters).'
        => 'Das Kürzel darf nur Kleinbuchstaben, Ziffern und Bindestriche enthalten (2 bis 63 Zeichen).',
    'The lock file <code>data/install.lock</code> could not be written. Delete <code>install.php</code> now, or make the folder <code>data/</code> writable.'
        => 'Die Sperrdatei <code>data/install.lock</code> konnte nicht geschrieben werden. Bitte jetzt <code>install.php</code> löschen oder den Ordner <code>data/</code> beschreibbar machen.',
    'The new password needs at least 10 characters.'
        => 'Das neue Passwort braucht mindestens 10 Zeichen.',
    'The no-choice rows from cbcTools were removed. For a “none of these” option, switch on the none option in the settings.'
        => 'Die No-Choice-Zeilen aus cbcTools wurden entfernt. Für „keine davon“ bitte die None-Option in den Einstellungen aktivieren.',
    'The none option can no longer be changed because data exist.'
        => 'Die None-Option lässt sich nicht mehr ändern, weil schon Daten vorliegen.',
    'The release time must be between 5 minutes and 7 days.'
        => 'Die Freigabefrist muss zwischen 5 Minuten und 7 Tagen liegen.',
    'The study accepts respondents. Attributes and design are frozen.'
        => 'Die Studie nimmt Probanden an. Attribute und Design sind eingefroren.',
    'The study no longer accepts respondents.'
        => 'Die Studie nimmt keine Probanden mehr an.',
    'The table prefix may contain lower-case letters, digits and underscores, starting with a letter (at most 20 characters).'
        => 'Das Tabellenpräfix darf Kleinbuchstaben, Ziffern und Unterstriche enthalten und muss mit einem Buchstaben beginnen (max. 20 Zeichen).',
    'The title must not be empty.'
        => 'Der Titel darf nicht leer sein.',
    'The two new passwords do not match.'
        => 'Die beiden neuen Passwörter stimmen nicht überein.',
    'The two passwords do not match.'
        => 'Die beiden Passwörter stimmen nicht überein.',
    'The user may create tables.'
        => 'Der Benutzer darf Tabellen anlegen.',
    'The user may not create tables (the CREATE privilege is missing).'
        => 'Der Benutzer darf keine Tabellen anlegen (Recht CREATE fehlt).',
    'The variable name “%s” is used twice.'
        => 'Der Variablenname „%s“ kommt doppelt vor.',
    'The variable names below can still be changed.'
        => 'Die Variablennamen weiter unten lassen sich weiterhin ändern.',
    'These attributes give only %1$d different profiles, too few for %2$d alternatives.'
        => 'Mit diesen Attributen gibt es nur %1$d verschiedene Profile – zu wenige für %2$d Alternativen.',
    'This account owns %d studies. Choose who takes them over; results stay with the studies.'
        => 'Diesem Konto gehören %d Studien. Bitte wählen, wer sie übernimmt; die Ergebnisse bleiben bei den Studien.',
    'This account owns no studies.'
        => 'Diesem Konto gehören keine Studien.',
    'This account was not found.'
        => 'Dieses Konto wurde nicht gefunden.',
    'This database does not exist. Create it in your hosting control panel first.'
        => 'Diese Datenbank gibt es nicht. Bitte zuerst im Kundenmenü des Providers anlegen.',
    'This e-mail address is already registered.'
        => 'Diese E-Mail-Adresse ist schon registriert.',
    'This link name is already taken.'
        => 'Dieses Kürzel ist schon vergeben.',
    'This needs <code>install.php</code> on the server. Upload the file from the ANOUK package again.'
        => 'Dafür muss <code>install.php</code> auf dem Server liegen. Bitte die Datei aus dem ANOUK-Paket wieder hochladen.',
    'This page does not exist.'
        => 'Diese Seite gibt es nicht.',
    'This page is for administrators only.'
        => 'Diese Seite ist Administratoren vorbehalten.',
    'This respondent was not found.'
        => 'Dieser Proband wurde nicht gefunden.',
    'This status change is not possible.'
        => 'Dieser Statuswechsel ist nicht möglich.',
    'This study was not found.'
        => 'Diese Studie wurde nicht gefunden.',
    'This user has no rights on this database.'
        => 'Der Benutzer hat keinen Zugriff auf diese Datenbank.',
    'Tick the confirmation box to delete the account.'
        => 'Zum Löschen des Kontos bitte das Bestätigungsfeld ankreuzen.',
    'Title'
        => 'Titel',
    'To change the attributes, delete the design first.'
        => 'Um die Attribute zu ändern, zuerst das Design löschen.',
    'To confirm, type DELETE.'
        => 'Zur Bestätigung bitte DELETE eintippen.',
    'To delete, type the link name of the study exactly.'
        => 'Zum Löschen bitte das Kürzel der Studie exakt eintippen.',
    'Too many failed attempts. Please try again in %d minutes.'
        => 'Zu viele Fehlversuche. Bitte in %d Minuten erneut versuchen.',
    'Transfer the studies to'
        => 'Studien übertragen an',
    'Type <code>DELETE</code> to confirm'
        => 'Zur Bestätigung <code>DELETE</code> eintippen',
    'Type the link name to confirm'
        => 'Zur Bestätigung das Kürzel eintippen',
    'Under “Design”, download the R script, create the choice sets with cbcTools and upload the CSV file.'
        => 'Unter „Design“ das R-Skript herunterladen, die Choice-Sets mit cbcTools erstellen und die CSV-Datei hochladen.',
    'Unknown method.'
        => 'Unbekanntes Verfahren.',
    'Update database'
        => 'Datenbank aktualisieren',
    'update pending'
        => 'Update ausstehend',
    'Upload and check'
        => 'Hochladen und prüfen',
    'uploaded'
        => 'hochgeladen',
    'uploaded from <code>%s</code>'
        => 'hochgeladen aus <code>%s</code>',
    'Used when the study is not in the field (<code>{STATUS}</code> = <code>closed</code>) or the ID is invalid (<code>invalid_id</code>). Empty: a message page.'
        => 'Wird genutzt, wenn die Studie nicht im Feld ist (<code>{STATUS}</code> = <code>closed</code>) oder die ID ungültig ist (<code>invalid_id</code>). Leer = Hinweisseite.',
    'User name or password is wrong.'
        => 'Benutzername oder Passwort ist falsch.',
    'Variable name'
        => 'Variablenname',
    'Variable names'
        => 'Variablennamen',
    'Variable names saved.'
        => 'Variablennamen gespeichert.',
    'Version'
        => 'Version',
    'version'
        => 'die Version',
    'Version %1$d has %2$d tasks; expected are %3$d (as in the first version).'
        => 'Version %1$d hat %2$d Tasks, erwartet werden %3$d (wie in der ersten Version).',
    'Version %1$d, task %2$d has %3$d alternatives; expected are %4$d.'
        => 'Version %1$d, Task %2$d hat %3$d Alternativen, erwartet werden %4$d.',
    'Version %1$d, task %2$d: alternative %3$d is identical to alternative %4$d.'
        => 'Version %1$d, Task %2$d: Alternative %3$d ist identisch mit Alternative %4$d.',
    'Version %1$d: task %2$d repeats task %3$d.'
        => 'Version %1$d: Task %2$d wiederholt Task %3$d.',
    'Versions'
        => 'Versionen',
    'Versions and tests'
        => 'Versionen und Tests',
    'Versions are different questionnaires. ANOUK spreads them evenly; with more respondents than versions, each version is used several times.'
        => 'Versionen sind verschiedene Fragebögen. ANOUK verteilt sie gleichmäßig; bei mehr Probanden als Versionen wird jede Version mehrfach genutzt.',
    'Versions with completions'
        => 'Versionen mit Abschlüssen',
    'Versions, tasks or alternatives were renumbered consecutively from 1.'
        => 'Versionen, Tasks oder Alternativen wurden fortlaufend ab 1 neu nummeriert.',
    'versions; ANOUK spreads them evenly over the respondents'
        => 'Versionen; ANOUK verteilt sie gleichmäßig auf die Probanden',
    'While the study is a draft, attributes and design can be changed. Respondents see nothing yet.'
        => 'Im Entwurf lassen sich Attribute und Design ändern. Probanden sehen noch nichts.',
    'With this code, <code>config.php</code> can later be deleted on this page, even when the database can no longer be reached. <b>Write it down now</b>; it is not shown again.'
        => 'Mit diesem Code lässt sich <code>config.php</code> später auf dieser Seite löschen, auch wenn die Datenbank nicht mehr erreichbar ist. <b>Bitte jetzt notieren</b>; er wird nicht noch einmal angezeigt.',
    'Without a design the study cannot go into the field.'
        => 'Ohne Design kann die Studie nicht ins Feld gehen.',
    'without prior assumptions: "random", "shortcut", "minoverlap", "balanced"'
        => 'ohne Vorannahmen: "random", "shortcut", "minoverlap", "balanced"',
    'Without R: generate a simple design in ANOUK'
        => 'Ohne R: einfaches Design direkt in ANOUK erzeugen',
    'You are using a starting password. Choose a password of your own to continue.'
        => 'Sie nutzen ein Startpasswort. Bitte wählen Sie ein eigenes Passwort, um weiterzumachen.',
    'You cannot deactivate your own account.'
        => 'Das eigene Konto lässt sich nicht deaktivieren.',
    'You cannot delete your own account.'
        => 'Das eigene Konto lässt sich nicht löschen.',
    'You cannot take away your own administrator role.'
        => 'Die eigene Administrator-Rolle lässt sich nicht entziehen.',
    '“%1$s”: the variable name “%2$s” is not valid. Use lower-case letters, digits and underscores, starting with a letter (at most 64 characters); export column names such as id, task or choice are taken.'
        => '„%1$s“: Der Variablenname „%2$s“ ist ungültig. Erlaubt sind Kleinbuchstaben, Ziffern und Unterstriche, beginnend mit einem Buchstaben (max. 64 Zeichen); Exportspalten wie id, task oder choice sind vergeben.',
    '“Dropped out” means no activity within the release time of the study; the version is then free for others.'
        => '„Abgebrochen“ heißt: keine Aktivität innerhalb der Freigabefrist der Studie; die Version ist dann für andere frei.',
];
