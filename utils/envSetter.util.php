<?php

require_once BASE_PATH . '/bootstrap.php';

// Check if vendor/autoload.php exists before requiring it
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
    
    // Only use Dotenv if it's available
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
        $dotenv->load();
    }
}

// Function to get database configuration
function getDatabaseConfig() {
    return [
        'pgHost' => $_ENV['PG_HOST'] ?? 'host.docker.internal',
        'pgPort' => $_ENV['PG_PORT'] ?? '5432',
        'pgDB' => $_ENV['PG_DB'] ?? 'project_management_db',
        'pgUser' => $_ENV['PG_USER'] ?? 'admin',
        'pgPassword' => $_ENV['PG_PASSWORD'] ?? 'admin123',
        'mongoHost' => $_ENV['MONGO_HOST'] ?? 'host.docker.internal',
        'mongoPort' => $_ENV['MONGO_PORT'] ?? '27017',
        'mongoDB' => $_ENV['MONGO_DB'] ?? 'project_management_mongo',
        'mongoUser' => $_ENV['MONGO_USER'] ?? 'admin',
        'mongoPassword' => $_ENV['MONGO_PASSWORD'] ?? 'admin123',
    ];
}

// Function to get app configuration
function getAppConfig() {
    return [
        'env' => $_ENV['APP_ENV'] ?? 'development',
        'debug' => $_ENV['APP_DEBUG'] ?? 'true',
    ];
}

// Distribute the data using array key with fallback values (for backward compatibility)
$databases = getDatabaseConfig();
$appConfig = getAppConfig();
