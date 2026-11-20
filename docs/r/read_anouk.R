# read_anouk.R – read ANOUK exports into R (base R only, no packages needed)
#
#   source("read_anouk.R")
#   d  <- read_anouk("anouk_diga_long_20261119.csv",
#                    codebook = "anouk_diga_codebook_20261119.csv")
#   w  <- read_anouk("anouk_diga_wide_20261119.csv")
#
# Works with both file styles of ANOUK: "R, SPSS, Python" (comma) and
# "German Excel" (semicolon, UTF-8 with BOM).

# Reads one ANOUK export (long or wide).
#   file      path of the CSV file
#   codebook  optional path of the codebook export; then the attribute columns
#             become factors with the levels in the order defined in ANOUK
#   fill_none long format with a none option: fill the empty attribute cells of
#             the none rows with the first (reference) level, so models such as
#             logitr can estimate the none option as a parameter of its own
read_anouk <- function(file, codebook = NULL, fill_none = TRUE) {
  d <- .anouk_csv(file)

  # timestamps: ISO 8601 in UTC, e.g. 2026-11-19T08:01:00Z
  for (col in intersect(c("answered_at", "started_at", "completed_at"), names(d))) {
    d[[col]] <- as.POSIXct(d[[col]], format = "%Y-%m-%dT%H:%M:%SZ", tz = "UTC")
  }
  for (col in intersect(c("status"), names(d))) {
    d[[col]] <- factor(d[[col]], levels = c("active", "complete"))
  }
  if ("is_test" %in% names(d)) d$is_test <- d$is_test == 1

  if (!is.null(codebook)) {
    cb <- .anouk_csv(codebook)
    attrs <- cb[cb$type == "categorical", , drop = FALSE]
    for (i in seq_len(nrow(attrs))) {
      v <- attrs$variable[i]
      if (!v %in% names(d)) next
      lv <- trimws(strsplit(attrs$values[i], "|", fixed = TRUE)[[1]])
      if (fill_none && "none" %in% names(d)) {
        d[[v]][d$none == 1] <- lv[1]
      }
      d[[v]] <- factor(d[[v]], levels = lv)
      attr(d[[v]], "label") <- attrs$label[i]
    }
  }
  d
}

# Internal: reads a CSV export, detecting comma or semicolon and the BOM.
.anouk_csv <- function(file) {
  first <- readLines(file, n = 1, encoding = "UTF-8", warn = FALSE)
  sep <- if (grepl(";", first, fixed = TRUE)) ";" else ","  # header names never contain either
  d <- utils::read.csv(file, sep = sep, fileEncoding = "UTF-8-BOM", stringsAsFactors = FALSE,
                       check.names = FALSE, na.strings = "")
  # the Excel style puts an apostrophe in front of cells that look like formulas
  if (sep == ";") {
    for (col in names(d)) {
      if (is.character(d[[col]])) d[[col]] <- sub("^'([=+@-])", "\\1", d[[col]])
    }
  }
  d
}

# Example: multinomial logit with logitr (install.packages("logitr"))
#
#   library(logitr)
#   d <- read_anouk("anouk_diga_long_20261119.csv",
#                   codebook = "anouk_diga_codebook_20261119.csv")
#   m <- logitr(data = d, outcome = "choice", obsID = "obs_id",
#               pars = c("wirksamkeitsnachweis", "begleitung_durch_fachperson",
#                        "zeitaufwand_pro_woche", "speicherung_der_daten",
#                        "eigenanteil_pro_monat", "none"))
#   summary(m)
#
# Merge with the survey data (wide format, one row per ID):
#
#   w   <- read_anouk("anouk_diga_wide_20261119.csv")
#   all <- merge(limesurvey_data, w, by.x = "token", by.y = "id", all.x = TRUE)
