<?php

declare(strict_types=1);

/*
 * This file is part of the slurp package.
 * (c) Al Poole <netstar@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Utils;

class Dates
{
    public static function createArray(string $epoch): array
    {
        $dates = [];
        $start = new \DateTime($epoch);
        $end = new \DateTime('NOW');
        $interval = new \DateInterval('P1D');
        $end->add($interval);
        $period = new \DatePeriod($start, $interval, $end);

        foreach ($period as $date) {
            $dates[] = ['date' => $date->format('Y-m-d'), 'weekday' => $date->format('l')];
        }

        return $dates;
    }

    public static function getFuzzyDate(?\DateTime $dateTime): string
    {
        $out = 'n/a';
        if (null === $dateTime) {
            return $out;
        }

        $now = new \DateTime();

        $secs = $now->format('U') - $dateTime->format('U');
        if ($secs < 3600) {
            $mins = round($secs / 60);
            $out = "{$mins} minute".(1 != $mins ? 's' : '').' ago';
        } elseif (($secs > 3600) && ($secs < 86400)) {
            $hours = round($secs / 3600);
            $out = "{$hours} hour".(1 != $hours ? 's' : '').' ago';
        } else {
            $days = round($secs / 86400);
            $out = "{$days} day".(1 != $days ? 's' : '').' ago';
        }

        return $out;
    }
}
