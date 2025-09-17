<?php

declare(strict_types=1);

// 1) Composer autoload
require_once 'vendor/autoload.php';

// 2) Composer bootstrap
require_once 'bootstrap.php';

// 3) envSetter
require_once UTILS_PATH . '/envSetter.util.php';

$host = $databases['pgHost'];
$port = $databases['pgPort'];
$username = $databases['pgUser'];
$password = $databases['pgPassword'];
$dbname = $databases['pgDB'];

// ——— Connect to PostgreSQL ———
$dsn = "pgsql:host={$databases['pgHost']};port={$port};dbname={$dbname}";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

echo "Dropping old tables…\n";
foreach ([
    'project_users',
    'tasks',
    'projects',
    'users',
] as $table) {
    // Use IF EXISTS so it won't error if the table is already gone
    $pdo->exec("DROP TABLE IF EXISTS {$table} CASCADE;");
}

// Apply schema files
$modelFiles = ['users', 'projects', 'tasks', 'project_users'];

foreach ($modelFiles as $model) {
    echo "Applying schema from database/{$model}.model.sql…\n";
    
    $sql = file_get_contents("database/{$model}.model.sql");
    
    if ($sql === false) {
        throw new RuntimeException("Could not read database/{$model}.model.sql");
    } else {
        echo "Creation Success from the database/{$model}.model.sql\n";
    }
    
    $pdo->exec($sql);
}

echo "✅ PostgreSQL migration complete!\n";
