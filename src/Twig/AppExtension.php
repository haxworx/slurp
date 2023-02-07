<?php

// src/Twig/AppExtension.php

namespace App\Twig;

use App\Utils\FuzzyDateTime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('ellipsis', [$this, 'ellipsis']),
            new TwigFilter('fuzzy_date', [$this, 'fuzzyDate']),
            new TwigFilter('has_error', [$this, 'hasError']),
            new TwigFilter('size_format', [$this, 'sizeFormat']),
        ];
    }

    public function ellipsis(?string $text): string
    {
        $length = strlen($text);
        if ($length >= 32) {
            return substr($text, 0, 32).'...';
        }

        return $text;
    }

    public function fuzzyDate(?\DateTime $dateTime): string
    {
        return FuzzyDateTime::get($dateTime);
    }

    public function hasError(?bool $hasError): string
    {
        return $hasError ? 'true' : 'false';
    }

    // Take bytes and convert to a human-readable string.
    public function sizeFormat(?int $bytes): string
    {
        $i = 0;
        $powi = 1;
        $precision = 2;
        $powj = 1;
        $units = [
            'B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'N/A',
        ];

        if (is_null($bytes) || (!is_integer($bytes))) {
            $bytes = 0;
        }

        $value = $bytes;

        while ($value > 1024) {
            if (($value / 1024) < $powi) {
                break;
            }
            $powi *= 1024;
            ++$i;
            if ($i === (count($units) - 1)) {
                break;
            }
        }

        if (!$i) {
            $precision = 0;
        }

        while ($precision > 0) {
            $powj *= 10;
            if (($value / $powi) < $powj) {
                break;
            }
            --$precision;
        }

        return sprintf('%1.*f %s', $precision, $value / $powi, $units[$i]);
    }
}
