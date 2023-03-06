<?php

declare(strict_types=1);

/*
 * This file is part of the slurp package.
 * (c) Al Poole <netstar@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Utils;

use Symfony\Component\Uid\Uuid;

class ApiKey
{
    public static function generate(): string
    {
        $uuid = Uuid::v4();
        return $uuid->toRfc4122();
    }
}
