<?php

namespace App\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class CreateTenantLogger
{
    public function __invoke(array $config): Logger
    {
        $handler = new StreamHandler(
            $config['path']  ?? storage_path('logs/laravel.log'),
            $config['level'] ?? Logger::DEBUG,
        );

        $handler->setFormatter(new TenantAwareFormatter());

        return new Logger('tenant', [$handler]);
    }
}