<?php

declare(strict_types=1);

// Define base path
define('BASE_PATH', realpath(__DIR__));

// Define additional paths
define('HANDLERS_PATH', realpath(BASE_PATH . "/handlers"));
define('UTILS_PATH', realpath(BASE_PATH . "/utils"));
define('DATABASE_PATH', realpath(BASE_PATH . "/database"));
define('DUMMIES_PATH', realpath(BASE_PATH . "/staticData/dummies"));
define('PUBLIC_PATH', realpath(BASE_PATH . "/public"));

// Ensure directories exist
$directories = [
    BASE_PATH . '/handlers',
    BASE_PATH . '/utils',
    BASE_PATH . '/database',
    BASE_PATH . '/staticData/dummies',
    BASE_PATH . '/vendor'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
