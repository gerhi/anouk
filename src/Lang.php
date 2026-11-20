<?php
/**
 * Interface translations.
 *
 * Texts live in lang/<code>.php. Each file returns an array:
 *   '_name'          the language's own name, shown in menus
 *   'survey.<key>'   texts on the respondent pages
 *   '<English text>' admin area and installer; the English text is the key
 * The available languages are the files in lang/; adding a file adds a language.
 * Missing or empty entries fall back to English, then to the key itself.
 *
 *   t('Text')                  translated text (raw – escape it yourself)
 *   te('Text')                 translated and HTML-escaped
 *   tm('Text <b>%s</b>', $x)   trusted template with markup; arguments are escaped
 */

declare(strict_types=1);

/** Available languages: code => own name, English first. */
function anouk_langs(): array
{
    static $langs = null;
    if ($langs === null) {
        $langs = [];
        foreach (glob(ANOUK_ROOT . '/lang/*.php') ?: [] as $file) {
            $code = basename($file, '.php');
            if (preg_match('/^[a-z]{2}$/', $code)) {
                $langs[$code] = (string)(i18n_dict($code)['_name'] ?? $code);
            }
        }
        uksort($langs, fn($a, $b) => [$a !== 'en', $a] <=> [$b !== 'en', $b]);
    }
    return $langs;
}

function lang_exists(string $code): bool
{
    return isset(anouk_langs()[$code]);
}

function i18n_set(string $lang): void
{
    $GLOBALS['ANOUK_LANG'] = lang_exists($lang) ? $lang : 'en';
}

function i18n_lang(): string
{
    return $GLOBALS['ANOUK_LANG'] ?? 'en';
}

/** Best match from the browser's Accept-Language header, else English. */
function i18n_detect(): string
{
    $header = strtolower((string)($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    $best = 'en';
    $bestQ = -1.0;
    foreach (explode(',', $header) as $part) {
        $bits = explode(';', trim($part));
        $code = substr(trim($bits[0]), 0, 2);
        $q = 1.0;
        if (isset($bits[1]) && preg_match('/q=([0-9.]+)/', $bits[1], $m)) {
            $q = (float)$m[1];
        }
        if (lang_exists($code) && $q > $bestQ) {
            $best = $code;
            $bestQ = $q;
        }
    }
    return $best;
}

/** Language before login: cookie from the language switch, else the browser's. */
function i18n_init(): void
{
    $lang = (string)($_COOKIE['anouk_lang'] ?? '');
    i18n_set(lang_exists($lang) ? $lang : i18n_detect());
}

function i18n_dict(string $lang): array
{
    static $dicts = [];
    if (!isset($dicts[$lang])) {
        $file = ANOUK_ROOT . '/lang/' . $lang . '.php';
        $dicts[$lang] = (preg_match('/^[a-z]{2}$/', $lang) && is_file($file)) ? (array)(require $file) : [];
    }
    return $dicts[$lang];
}

/** Looks up a key in a language, then in English. */
function i18n_lookup(string $lang, string $key): ?string
{
    foreach ([$lang, 'en'] as $l) {
        $v = i18n_dict($l)[$key] ?? '';
        if (is_string($v) && $v !== '') {
            return $v;
        }
    }
    return null;
}

function t(string $text, mixed ...$args): string
{
    $text = i18n_lookup(i18n_lang(), $text) ?? $text;
    return $args ? vsprintf($text, $args) : $text;
}

function te(string $text, mixed ...$args): string
{
    return htmlspecialchars(t($text, ...$args), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function tm(string $text, mixed ...$args): string
{
    $safe = array_map(fn($a) => is_string($a) ? htmlspecialchars($a, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : $a, $args);
    return t($text, ...$safe);
}
