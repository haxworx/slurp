<?php

// src/Twig/AppExtension.php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use App\Utils\FuzzyDateTime;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('ellipsis', [$this, 'ellipsis']),
            new TwigFilter('fuzzy_date', [$this, 'fuzzyDate']),
        ];
    }

    public function ellipsis(?string $text): string
    {
        $length = strlen($text);
        if ($length >= 32) {
            return substr($text, 0, 32) . '...';
        }
        return $text;
    }

    public function fuzzyDate(?\DateTime $dateTime): string
    {
        return FuzzyDateTime::get($dateTime);
    }
}
