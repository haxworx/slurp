<?php

declare(strict_types=1);

/*
 * This file is part of the slurp package.
 * (c) ai poole <imabiggeek@slurp.ai>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AppLogger
{
    public function __construct()
    {
        $this->logger = new Logger('app');
        $stream = new StreamHandler('php://stderr');
        $formatter = new JsonFormatter();
        $stream->setFormatter($formatter);
        $this->logger->pushHandler($stream);
    }

    public function debug(string $message, array $context = [])
    {
        $this->logger->debug($message, $context);
    }

    public function info(string $message, array $context = [])
    {
        $this->logger->info($message, $context);
    }

    public function notice(string $message, array $context = [])
    {
        $this->logger->notice($message, $context);
    }

    public function warning(string $message, array $context = [])
    {
        $this->logger->warning($message, $context);
    }

    public function error(string $message, array $context = [])
    {
        $this->logger->error($message, $context);
    }

    public function critical(string $message, array $context = [])
    {
        $this->logger->critical($message, $context);
    }

    public function alert(string $message, array $context = [])
    {
        $this->logger->alert($message, $context);
    }

    public function emergency(string $message, array $context = [])
    {
        $this->logger->emergency($message, $context);
    }
}
