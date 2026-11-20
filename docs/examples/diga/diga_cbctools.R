# ANOUK example: choice sets on digital health applications (DiGA) with cbcTools
#
# Creates diga_design.csv for the upload in ANOUK.
# Requires cbcTools >= 0.7 (install.packages("cbcTools"))

library(cbcTools)
set.seed(2026)

# 1. Attributes and levels -----------------------------------------------------
# Short variable names for R; the labels respondents see follow in step 4.
# Time and co-payment stay numeric here, so they can later also be
# estimated as linear effects.
profiles <- cbc_profiles(
  nutzen     = c("Nutzen in Studien belegt", "Nutzen wird noch erprobt"),
  begleitung = c("keine", "Chat mit Therapeut/in", "monatlicher Videotermin"),
  aufwand    = c(15, 30, 60),  # minutes per week
  daten      = c("nur auf dem Smartphone", "Server in Deutschland", "Server in der EU"),
  kosten     = c(0, 10, 25)    # euro co-payment per month
)
nrow(profiles)  # 2 x 3 x 3 x 3 x 3 = 162 profiles

# 2. Design --------------------------------------------------------------------
design <- cbc_design(
  profiles = profiles,
  method   = "minoverlap",  # or "random", "shortcut", "balanced"
  n_alts   = 3,             # alternatives per choice set
  n_q      = 8,             # choice sets per version
  n_resp   = 200            # versions; ANOUK spreads them evenly over the respondents
)

# With prior assumptions about the preferences, a D-efficient design is possible
# too (method = "cea" or "modfed" with priors = cbc_priors(...)).
# Details: ?cbc_priors and ?cbc_design

# 3. Check ---------------------------------------------------------------------
cbc_inspect(design)

# 4. Prepare for ANOUK ---------------------------------------------------------
d <- as.data.frame(cbc_encode(design, coding = "standard"))

# Display texts for the numeric attributes
d$aufwand <- paste(d$aufwand, "Minuten")
d$kosten  <- paste(d$kosten, "€")

# Column names = attribute labels as respondents see them.
# ANOUK derives the variable names for the export from them
# (e.g. eigenanteil_pro_monat); they can be changed under “Attributes”.
names(d)[match(c("nutzen", "begleitung", "aufwand", "daten", "kosten"), names(d))] <- c(
  "Wirksamkeitsnachweis",
  "Begleitung durch Fachperson",
  "Zeitaufwand pro Woche",
  "Speicherung der Daten",
  "Eigenanteil pro Monat"
)

write.csv(d, "diga_design.csv", row.names = FALSE, fileEncoding = "UTF-8")

# After data collection: get the numbers back from the display texts, e.g.
# dat$kosten  <- as.numeric(sub(" .*", "", dat$eigenanteil_pro_monat))
# dat$aufwand <- as.numeric(sub(" .*", "", dat$zeitaufwand_pro_woche))
