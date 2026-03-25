<?php

namespace App\Support;

use NumberFormatter;

final class AedMoney
{
    /**
     * @return array{0: int, 1: int} Whole dirhams and fils (0–99).
     */
    public static function splitDhsFils(float $amount): array
    {
        $cents = (int) round($amount * 100);

        return [intdiv($cents, 100), $cents % 100];
    }

    public static function inWords(float $amount): string
    {
        $cents = (int) round($amount * 100);
        $dhs = intdiv($cents, 100);
        $fils = $cents % 100;

        if (! extension_loaded('intl')) {
            return number_format($amount, 2).' UAE Dirhams only';
        }

        $fmt = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $dhsWord = ucfirst((string) $fmt->format($dhs));
        if ($fils === 0) {
            return $dhsWord.' Dirhams only';
        }

        $filsWord = ucfirst((string) $fmt->format($fils));

        return $dhsWord.' Dirhams and '.$filsWord.' Fils only';
    }
}
