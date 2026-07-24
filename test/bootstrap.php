<?php

declare(strict_types=1);

use Rad\Config\Config;

require __DIR__ . '/../vendor/autoload.php';

// Load the framework defaults so services (Log, ...) used during parsing resolve.
Config::load();
