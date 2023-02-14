<?php

namespace App\Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\Dates;

class DatesTest extends TestCase
{
    public function testNullFuzzyDate(): void
    {
        $result = Dates::getFuzzyDate(null);
        $this->assertSame('n/a', $result);
    }

    public function testFuzzyDate(): void
    {
        $date = new \DateTime();
        
        $date->setTimestamp(time());
        $result = Dates::getFuzzyDate($date);
        $this->assertSame('0 minutes ago', $result);

        $date->setTimestamp(time() - 50);
        $result = Dates::getFuzzyDate($date);
        $this->assertSame('1 minute ago', $result);

        $date->setTimestamp(time() - (60 * 30));
        $result = Dates::getFuzzyDate($date);
        $this->assertSame('30 minutes ago', $result);

        $date->setTimestamp(time() - 3601);
        $result = Dates::getFuzzyDate($date);
        $this->assertSame('1 hour ago', $result);

        $date->setTimestamp(time() - (3601 * 2));
        $result = Dates::getFuzzyDate($date);
        $this->assertSame('2 hours ago', $result);
        
        $date->setTimestamp(time() - (86400 + 1));
        $result = Dates::getFuzzyDate($date);
        $this->assertSame('1 day ago', $result);

        $date->setTimestamp(time() - (10 * 86400 + 1));
        $result = Dates::getFuzzyDate($date);
        $this->assertSame('10 days ago', $result);
    }

    public function testCreateArray(): void
    {
        $dates = Dates::createArray("7 days ago");
        $this->assertSame(count($dates), 8);

        $dates = array_reverse($dates);
        for ($i = 0; $i < 8; $i++) {
            $date = new \DateTime("$i days ago");
            $this->assertSame($dates[$i]['date'], $date->format('Y-m-d'));
        }
    }
}
