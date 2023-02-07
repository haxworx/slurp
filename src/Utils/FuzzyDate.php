<?php

// src/Utils/FuzzyDate.php

namespace App\Utils;

class FuzzyDateTime
{
    public static function get(?\DateTime $dateTime): string
    {
        $out = 'n/a';
        if (null === $dateTime) {
            return $out;
        }

        $now = new \DateTime();

        $secs = $now->format('U') - $dateTime->format('U');
        if ($secs < 3600) {
            $mins = round($secs / 60);
            $out = "$mins minute".(1 != $mins ? 's' : '').' ago';
        } elseif (($secs > 3600) && ($secs < 86400)) {
            $hours = round($secs / 3600);
            $out = "$hours hour".(1 != $hours ? 's' : '').' ago';
        } else {
            $days = round($secs / 86400);
            $out = "$days day".(1 != $days ? 's' : '').' ago';
        }

        return $out;
    }
}
