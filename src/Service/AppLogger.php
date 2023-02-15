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

    public function debug(string $message)
    {
        $this->logger->debug($message);
    }

    public function info(string $message)
    {
        $this->logger->info($message);
    }

    public function notice(string $message)
    {
        $this->logger->notice($message);
    }

    public function warning(string $message)
    {
        $this->logger->warning($message);
    }

    public function error(string $message)
    {
        $this->logger->error($message);
    }

    public function critical(string $message)
    {
        $this->logger->critical($message);
    }

    public function alert(string $message)
    {
        $this->logger->alert($message);
    }

    public function emergency(string $message)
    {
        $this->logger->emergency($message);
    }
}
