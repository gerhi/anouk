<?php
declare(strict_types=1);

/** Builds an R script that creates the choice sets of a study with cbcTools. */
final class RScript
{
    /** cbcTools methods that need no priors. */
    public const METHODS = [
        'minoverlap' => 'minoverlap – little overlap (recommended)', // labels are translated with t()
        'balanced'   => 'balanced – equal level frequencies',
        'shortcut'   => 'shortcut – a compromise',
        'random'     => 'random – random profiles',
    ];

    /** R string literal; non-ASCII characters as \u escapes, so the script works with any encoding. */
    public static function str(string $s): string
    {
        $out = '';
        foreach (mb_str_split($s) as $ch) {
            $cp = mb_ord($ch);
            $out .= match (true) {
                $ch === '\\' => '\\\\',
                $ch === '"'  => '\\"',
                $ch === "\n" => '\\n',
                $ch === "\t" => '\\t',
                $cp < 0x20   => sprintf('\\x%02x', $cp),
                $cp < 0x80   => $ch,
                $cp <= 0xFFFF => sprintf('\\u%04x', $cp),
                default      => sprintf('\\U{%x}', $cp),
            };
        }
        return '"' . $out . '"';
    }

    public static function build(array $study, array $attrs, int $nResp, int $nQ, int $nAlts, string $method, int $seed): string
    {
        $rNames = Study::varNames($attrs); // the CSV columns carry the variable names; ANOUK matches them on upload
        $width = max(array_map('strlen', $rNames));
        $profileLines = [];
        foreach ($attrs as $k => $a) {
            $levels = implode(', ', array_map(fn($l) => self::str($l['label']), $a['levels']));
            $profileLines[] = '  ' . str_pad($rNames[$k], $width) . ' = c(' . $levels . ')';
        }
        $profilesBlock = implode(",\n", $profileLines);
        $file = $study['slug'] . '_design.csv';
        $title = self::comment($study['title']);
        $date = gmdate('Y-m-d');

        $c = [
            'head'    => t('ANOUK – create choice sets with cbcTools'),
            'study'   => t('Study: %1$s (%2$s), script created on %3$s', $title, $study['slug'], $date),
            'step1'   => t('1. Run this script in R (written for cbcTools 0.7 or newer: install.packages("cbcTools")).'),
            'step2'   => t('2. Upload the file %s in ANOUK under “Design”.', $file),
            'labels1' => t('The level labels come from ANOUK and must not be changed,'),
            'labels2' => t('otherwise the design no longer matches the attributes of the study.'),
            'attrs'   => t('Attributes and levels'),
            'design'  => t('Design'),
            'methods' => t('without prior assumptions: "random", "shortcut", "minoverlap", "balanced"'),
            'alts'    => t('alternatives per choice set'),
            'q'       => t('choice sets per version'),
            'resp'    => t('versions; ANOUK spreads them evenly over the respondents'),
            'none'    => t('Do not add a "no choice" option here; switch on the none option in ANOUK instead.'),
            'priors'  => t('For D-efficient designs with priors see ?cbc_priors and method = "cea" or "modfed".'),
            'check'   => t('Check'),
            'save'    => t('Save for ANOUK'),
            'decode'  => t('level labels instead of dummy codes'),
            'saved'   => t('Saved: '),
        ];
        $rule = fn(string $label) => '# ' . $label . ' ' . str_repeat('-', max(3, 76 - mb_strlen($label)));

        return "# {$c['head']}\n"
            . "# {$c['study']}\n#\n"
            . "# {$c['step1']}\n"
            . "# {$c['step2']}\n#\n"
            . "# {$c['labels1']}\n# {$c['labels2']}\n\n"
            . "library(cbcTools)\nset.seed({$seed})\n\n"
            . $rule($c['attrs']) . "\nprofiles <- cbc_profiles(\n{$profilesBlock}\n)\n\n"
            . $rule($c['design']) . "\ndesign <- cbc_design(\n"
            . "  profiles = profiles,\n"
            . "  method   = \"{$method}\",  # {$c['methods']}\n"
            . "  n_alts   = {$nAlts},  # {$c['alts']}\n"
            . "  n_q      = {$nQ},  # {$c['q']}\n"
            . "  n_resp   = {$nResp}  # {$c['resp']}\n)\n"
            . "# {$c['none']}\n# {$c['priors']}\n\n"
            . $rule($c['check']) . "\ncbc_inspect(design)\n\n"
            . $rule($c['save']) . "\n"
            . "d <- as.data.frame(cbc_encode(design, coding = \"standard\"))  # {$c['decode']}\n"
            . "write.csv(d, \"{$file}\", row.names = FALSE, fileEncoding = \"UTF-8\")\n"
            . "message(" . self::str($c['saved']) . ", normalizePath(\"{$file}\"))\n";
    }

    private static function comment(string $s): string
    {
        return str_replace(["\r", "\n"], ' ', $s);
    }
}
