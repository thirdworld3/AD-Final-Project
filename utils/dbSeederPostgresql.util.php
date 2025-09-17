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

    // Load dummy data
    $users = require_once DUMMIES_PATH . '/users.staticData.php';

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

try {
    // Apply schema files first
    $modelFiles = ['users', 'projects', 'tasks', 'project_users'];

    foreach ($modelFiles as $model) {
        echo "Applying schema from database/{$model}.model.sql…\n";
        
        $sql = file_get_contents("database/{$model}.model.sql");
        
        if ($sql === false) {
            throw new RuntimeException("Could not read database/{$model}.model.sql");
        }
        
        $pdo->exec($sql);
        echo "✅ Created {$model} table\n";
    }

    // Clean the tables first
    echo "Truncating tables…\n";
    foreach (['project_users', 'tasks', 'projects', 'users'] as $table) {
        try {
            $pdo->exec("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE;");
            echo "✅ Cleaned {$table} table\n";
        } catch (Exception $e) {
            echo "⚠️  Could not clean {$table}: " . $e->getMessage() . "\n";
        }
    }

    // Seed users
    echo "Seeding users…\n";

    $stmt = $pdo->prepare("
        INSERT INTO users (username, role, first_name, middle_name, last_name, password)
        VALUES (:username, :role, :fn, :mn, :ln, :pw)
    ");

    foreach ($users as $u) {
        try {
            $stmt->execute([
                ':username' => $u['username'],
                ':role' => $u['role'],
                ':fn' => $u['first_name'],
                ':mn' => $u['middle_name'],
                ':ln' => $u['last_name'],
                ':pw' => $u['password'],
            ]);
            echo "✅ Created user: {$u['username']}\n";
        } catch (Exception $e) {
            echo "⚠️  Could not create user {$u['username']}: " . $e->getMessage() . "\n";
        }
    }

    echo "✅ PostgreSQL seeding complete!\n";

} catch (Exception $e) {
    echo "❌ Error during seeding: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
