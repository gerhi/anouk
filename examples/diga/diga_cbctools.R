# ANOUK-Beispiel: Choice-Sets zu Digitalen Gesundheitsanwendungen (DiGA) mit cbcTools
#
# Erzeugt diga_design.csv für den Upload in ANOUK.
# Voraussetzung: cbcTools >= 0.7 (install.packages("cbcTools"))

library(cbcTools)
set.seed(2026)

# 1. Attribute und Level -------------------------------------------------------
# Kurze Variablennamen für R; die Anzeigenamen für ANOUK folgen in Schritt 4.
# Zeitaufwand und Eigenanteil bleiben numerisch, damit sie sich später
# auch linear schätzen lassen.
profiles <- cbc_profiles(
  nutzen     = c("Nutzen in Studien belegt", "Nutzen wird noch erprobt"),
  begleitung = c("keine", "Chat mit Therapeut/in", "monatlicher Videotermin"),
  aufwand    = c(15, 30, 60),  # Minuten pro Woche
  daten      = c("nur auf dem Smartphone", "Server in Deutschland", "Server in der EU"),
  kosten     = c(0, 10, 25)    # Euro Eigenanteil pro Monat
)
nrow(profiles)  # 2 x 3 x 3 x 3 x 3 = 162 Profile

# 2. Design --------------------------------------------------------------------
design <- cbc_design(
  profiles = profiles,
  method   = "minoverlap",  # oder "random", "shortcut", "balanced"
  n_alts   = 3,             # Alternativen je Choice-Set
  n_q      = 8,             # Choice-Sets je Version
  n_resp   = 200            # Versionen; ANOUK verteilt sie gleichmäßig auf die Probanden
)

# Mit Vorannahmen über die Präferenzen ließe sich auch ein D-effizientes Design
# erzeugen (method = "cea" oder "modfed" mit priors = cbc_priors(...)).
# Details: ?cbc_priors und ?cbc_design

# 3. Prüfen --------------------------------------------------------------------
cbc_inspect(design)

# 4. Für ANOUK aufbereiten -----------------------------------------------------
d <- as.data.frame(cbc_encode(design, coding = "standard"))

# Anzeigetexte für die numerischen Attribute
d$aufwand <- paste(d$aufwand, "Minuten")
d$kosten  <- paste(d$kosten, "€")

# Spaltennamen = Attributnamen, wie die Probanden sie sehen
names(d)[match(c("nutzen", "begleitung", "aufwand", "daten", "kosten"), names(d))] <- c(
  "Wirksamkeitsnachweis",
  "Begleitung durch Fachperson",
  "Zeitaufwand pro Woche",
  "Speicherung der Daten",
  "Eigenanteil pro Monat"
)

write.csv(d, "diga_design.csv", row.names = FALSE, fileEncoding = "UTF-8")

# Nach der Erhebung: Zahlen aus den Anzeigetexten zurückgewinnen, z. B.
# daten$kosten  <- as.numeric(sub(" .*", "", daten$`Eigenanteil pro Monat`))
# daten$aufwand <- as.numeric(sub(" .*", "", daten$`Zeitaufwand pro Woche`))
