<?php

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
}
