<?php

declare(strict_types=1);

try {
    // 1) Composer autoload
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
    }

    // 2) Composer bootstrap
    require_once 'bootstrap.php';

    // 3) envSetter
    require_once UTILS_PATH . '/envSetter.util.php';

    $host = $databases['pgHost'];
    $port = $databases['pgPort'];
    $username = $databases['pgUser'];
    $password = $databases['pgPassword'];
    $dbname = $databases['pgDB'];

    echo "Connecting to PostgreSQL at {$host}:{$port}...\n";

    // ——— Connect to PostgreSQL ———
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "✅ Connected successfully!\n";

} catch (Exception $e) {
    echo "❌ Error during initialization: " . $e->getMessage() . "\n";
    echo "Make sure Docker is running and database is accessible.\n";
    exit(1);
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

// Clean the tables
echo "Truncating tables…\n";
foreach (['project_users', 'tasks', 'projects', 'users'] as $table) {
    $pdo->exec("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE;");
}

echo "✅ PostgreSQL reset complete!\n";
