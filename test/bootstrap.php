<?php

declare(strict_types=1);

use Rad\Config\Config;

require __DIR__ . '/../vendor/autoload.php';

// Load the framework defaults so services (Log, ...) used during parsing resolve.
Config::load();

// Silence logging during the test run.
$logHandlers = Config::getConfig()->services->log->handlers ?? null;
if ($logHandlers !== null) {
    foreach (['output', 'file'] as $handler) {
        if (isset($logHandlers->{$handler}->config)) {
            $logHandlers->{$handler}->config->enabled = false;
        }
    }
}
