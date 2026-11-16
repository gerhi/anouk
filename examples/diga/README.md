# Beispiel: Präferenzen für Digitale Gesundheitsanwendungen (DiGA)

Eine kleine CBC-Studie zur Frage, welche Eigenschaften einer DiGA („App auf Rezept“) Menschen wichtig sind.

| Datei | Inhalt |
| --- | --- |
| `diga_cbctools.R` | R-Skript, das die Choice-Sets mit `cbcTools` erstellt und als `diga_design.csv` speichert |
| `diga_design.csv` | fertiges Design zum Ausprobieren: 200 Versionen × 8 Choice-Sets × 3 Alternativen |

**Zur Herkunft der CSV:** `diga_design.csv` liegt im Ausgabeformat von `cbcTools` vor (Spalten `profileID`, `respID`, `qID`, `altID`, `obsID`), wurde aber ohne R mit dem in ANOUK eingebauten Minimal-Overlap-Verfahren erzeugt. Sie dient zum Ausprobieren. Für eine echte Studie bitte `diga_cbctools.R` ausführen; das Skript schreibt eine Datei mit demselben Aufbau.

## Attribute und Level

| Attribut | Level |
| --- | --- |
| Wirksamkeitsnachweis | Nutzen in Studien belegt · Nutzen wird noch erprobt |
| Begleitung durch Fachperson | keine · Chat mit Therapeut/in · monatlicher Videotermin |
| Zeitaufwand pro Woche | 15 Minuten · 30 Minuten · 60 Minuten |
| Speicherung der Daten | nur auf dem Smartphone · Server in Deutschland · Server in der EU |
| Eigenanteil pro Monat | 0 € · 10 € · 25 € |

Die beiden Level des Wirksamkeitsnachweises lehnen sich an die dauerhafte und die vorläufige (Erprobungs-)Aufnahme ins DiGA-Verzeichnis an. Ein Eigenanteil ist bei verordneten DiGA in der Regel nicht vorgesehen; das Attribut misst hier die hypothetische Zahlungsbereitschaft.

## In ANOUK ausprobieren

1. Neue Studie anlegen, z. B. Kürzel `diga`.
2. Unter „Design“ die Datei `diga_design.csv` hochladen. Die Attribute werden aus der Datei übernommen.
3. Einstellungen, zum Beispiel:
   - Frage: *Welche dieser Gesundheits-Apps würden Sie am ehesten nutzen?*
   - None-Option: *Keine dieser Apps*
   - Instruktion:

     > Stellen Sie sich vor, Ihre Ärztin oder Ihr Arzt schlägt Ihnen eine App vor, die bei Ihren Beschwerden helfen soll. Auf den folgenden Seiten sehen Sie jeweils drei solcher Apps, die sich in einigen Eigenschaften unterscheiden.
     >
     > Bitte wählen Sie jeweils die App, die Sie am ehesten nutzen würden. Es gibt keine richtigen oder falschen Antworten.

4. „Vorschau“ öffnen und durchklicken.

## Auswertung in R (Skizze)

```r
library(logitr)
daten <- read.csv("anouk_diga_long_JJJJMMTT.csv", fileEncoding = "UTF-8", check.names = FALSE)
daten$kosten  <- as.numeric(sub(" .*", "", daten$`Eigenanteil pro Monat`))
daten$aufwand <- as.numeric(sub(" .*", "", daten$`Zeitaufwand pro Woche`))
names(daten)[names(daten) == "Wirksamkeitsnachweis"]        <- "nutzen"
names(daten)[names(daten) == "Begleitung durch Fachperson"] <- "begleitung"
names(daten)[names(daten) == "Speicherung der Daten"]       <- "daten_ort"

m <- logitr(data = daten, outcome = "choice", obsID = "obs_id",
            pars = c("nutzen", "begleitung", "aufwand", "daten_ort", "kosten"))
summary(m)
```

Mit aktivierter None-Option enthält der Export je Choice-Set eine zusätzliche Zeile (`none = 1`, Attribute leer). Diese Zeilen vor der Schätzung passend kodieren, z. B. die Attribute auf die Referenzkategorie bzw. 0 setzen und `none` als eigenen Parameter aufnehmen.
