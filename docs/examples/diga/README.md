# Example: preferences for digital health applications (DiGA)

A small CBC study on which properties of a DiGA (a German “app on prescription”) matter to people. Labels and texts are in German, as the study is meant for German respondents.

| File | Content |
| --- | --- |
| `diga_cbctools.R` | R script that creates the choice sets with `cbcTools` and saves them as `diga_design.csv` |
| `diga_design.csv` | ready-made design to try out: 200 versions × 8 choice sets × 3 alternatives |

**Origin of the CSV:** `diga_design.csv` has the output format of `cbcTools` (columns `profileID`, `respID`, `qID`, `altID`, `obsID`), but was created without R by ANOUK's built-in minimal-overlap generator. It is meant for trying ANOUK out. For a real study, run `diga_cbctools.R`; it writes a file with the same structure.

## Attributes and levels

| Attribute (variable name in ANOUK) | Levels |
| --- | --- |
| Wirksamkeitsnachweis (`wirksamkeitsnachweis`) | Nutzen in Studien belegt · Nutzen wird noch erprobt |
| Begleitung durch Fachperson (`begleitung_durch_fachperson`) | keine · Chat mit Therapeut/in · monatlicher Videotermin |
| Zeitaufwand pro Woche (`zeitaufwand_pro_woche`) | 15 Minuten · 30 Minuten · 60 Minuten |
| Speicherung der Daten (`speicherung_der_daten`) | nur auf dem Smartphone · Server in Deutschland · Server in der EU |
| Eigenanteil pro Monat (`eigenanteil_pro_monat`) | 0 € · 10 € · 25 € |

The two levels of *Wirksamkeitsnachweis* (proof of benefit) follow the permanent and the provisional (trial) listing in the German DiGA directory. A co-payment is usually not foreseen for prescribed DiGA; here the attribute measures hypothetical willingness to pay.

## Trying it in ANOUK

1. Create a new study, e.g. with the link name `diga` and German respondent pages.
2. Under *Design*, upload `diga_design.csv`. The attributes are taken from the file; ANOUK derives the variable names shown above, which you can shorten under *Attributes* (e.g. `kosten`).
3. Settings, for example:
   - Question: *Welche dieser Gesundheits-Apps würden Sie am ehesten nutzen?*
   - None option: *Keine dieser Apps*
   - Introduction:

     > Stellen Sie sich vor, Ihre Ärztin oder Ihr Arzt schlägt Ihnen eine App vor, die bei Ihren Beschwerden helfen soll. Auf den folgenden Seiten sehen Sie jeweils drei solcher Apps, die sich in einigen Eigenschaften unterscheiden.
     >
     > Bitte wählen Sie jeweils die App, die Sie am ehesten nutzen würden. Es gibt keine richtigen oder falschen Antworten.

4. Open *Preview* and click through.

## Analysis in R (sketch)

```r
source("../../r/read_anouk.R")
d <- read_anouk("anouk_diga_long_YYYYMMDD.csv", codebook = "anouk_diga_codebook_YYYYMMDD.csv")

# time and co-payment as numbers, for linear effects
d$kosten  <- as.numeric(sub(" .*", "", as.character(d$eigenanteil_pro_monat)))
d$aufwand <- as.numeric(sub(" .*", "", as.character(d$zeitaufwand_pro_woche)))

library(logitr)
m <- logitr(data = d, outcome = "choice", obsID = "obs_id",
            pars = c("wirksamkeitsnachweis", "begleitung_durch_fachperson",
                     "aufwand", "speicherung_der_daten", "kosten", "none"))
summary(m)
```

With the none option switched on, the export has an extra row per choice set (`none = 1`). `read_anouk()` fills its attribute cells with the reference level, so `none` is estimated as a parameter of its own; for the numeric `kosten` and `aufwand`, the none rows then hold the lowest level (0 € and 15 minutes), which only shifts the meaning of the `none` estimate (it then includes the utility of 15 minutes).
