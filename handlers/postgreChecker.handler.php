<?php

require_once __DIR__ . '/../bootstrap.php';
require_once UTILS_PATH . '/envSetter.util.php';

try {
    $host = $databases['pgHost'];
    $port = $databases['pgPort'];
    $dbname = $databases['pgDB'];
    $username = $databases['pgUser'];
    $password = $databases['pgPassword'];

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "✅ PostgreSQL Connection";
} catch (PDOException $e) {
    echo "❌ Connection Failed: " . $e->getMessage();
}
