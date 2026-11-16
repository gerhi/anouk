<?php
/**
 * Interface translations (same approach as JUST-CAT).
 *
 * Source strings are English and double as keys. Translations live in
 * app/lang/{code}.json (key = English string). Missing entries fall back
 * to English. Placeholders follow sprintf (%s, %d, %1$s …).
 *
 *   t('Text')                  translated text (raw – escape it yourself)
 *   te('Text')                 translated and HTML-escaped
 *   tm('Text <b>%s</b>', $x)   trusted template with markup; arguments are escaped
 *
 * More languages: add the code to ANOUK_LANGS and a file app/lang/{code}.json.
 */

declare(strict_types=1);

const ANOUK_LANGS = [
    'en' => 'English',
    'de' => 'Deutsch',
];

function i18n_set(string $lang): void
{
    $GLOBALS['ANOUK_LANG'] = isset(ANOUK_LANGS[$lang]) ? $lang : 'en';
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
        if (isset(ANOUK_LANGS[$code]) && $q > $bestQ) {
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
    i18n_set(isset(ANOUK_LANGS[$lang]) ? $lang : i18n_detect());
}

function i18n_dict(string $lang): array
{
    static $dicts = [];
    if (!isset($dicts[$lang])) {
        $file = __DIR__ . '/lang/' . $lang . '.json';
        $dicts[$lang] = is_file($file) ? (json_decode((string)file_get_contents($file), true) ?: []) : [];
    }
    return $dicts[$lang];
}

function t(string $text, mixed ...$args): string
{
    $lang = i18n_lang();
    if ($lang !== 'en') {
        $dict = i18n_dict($lang);
        if (isset($dict[$text]) && $dict[$text] !== '') {
            $text = $dict[$text];
        }
    }
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
