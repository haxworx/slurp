<?php

// src/Service/AppLogger.php

namespace App\Service;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class AppLogger
{
    public function __construct()
    {
        $this->logger = new Logger("app");
        $this->logger->pushHandler(new StreamHandler('php://stderr'));
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
