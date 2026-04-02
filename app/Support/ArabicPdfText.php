<?php

namespace App\Support;

use ArPHP\I18N\Arabic;

/**
 * DomPDF does not run full Arabic shaping / bidi; glyphs appear reversed or broken.
 * utf8Glyphs() yields visual-order text suitable for left-to-right PDF rendering.
 */
final class ArabicPdfText
{
    private static ?Arabic $arabic = null;

    public static function forDomPdf(string $text): string
    {
        if ($text === '' || ! self::containsArabic($text)) {
            return $text;
        }

        if (self::$arabic === null) {
            self::$arabic = new Arabic;
        }

        // High limit avoids ar-php wrapping long lines; false = keep Western digits in mixed strings.
        return self::$arabic->utf8Glyphs($text, 10_000, false, false);
    }

    private static function containsArabic(string $text): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text);
    }
}
