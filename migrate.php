#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

use App\Database\Connection;

echo "FastClient Migration Runner\n";
echo "===========================\n\n";

try {
    Connection::runMigrations();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
