<?php

require_once __DIR__ . '/../bootstrap.php';
require_once UTILS_PATH . '/envSetter.util.php';

try {
    $mongoUri = "mongodb://{$databases['mongoHost']}:{$databases['mongoPort']}";
    $mongo = new MongoDB\Driver\Manager($mongoUri);
    
    // Test the connection
    $command = new MongoDB\Driver\Command(['ping' => 1]);
    $mongo->executeCommand('admin', $command);
    
    echo "✅ Connected to MongoDB successfully.";
} catch (Exception $e) {
    echo "❌ MongoDB connection failed: " . $e->getMessage();
}
