<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;

class Connection
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';

            // Build DSN - omit host/port for Unix socket connections
            $dsnParts = [];
            if (!empty($config['host'])) {
                $dsnParts[] = 'host=' . $config['host'];
            }
            if (!empty($config['port'])) {
                $dsnParts[] = 'port=' . $config['port'];
            }
            $dsnParts[] = 'dbname=' . $config['database'];
            $dsn = $config['driver'] . ':' . implode(';', $dsnParts);

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new PDOException("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    public static function runMigrations(): void
    {
        $pdo = self::getInstance();

        // Create migrations tracking table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id SERIAL PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Get already executed migrations
        $stmt = $pdo->query("SELECT migration FROM migrations");
        $executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Get migration files
        $migrationPath = __DIR__ . '/migrations';
        $files = glob($migrationPath . '/*.sql');
        sort($files);

        foreach ($files as $file) {
            $migrationName = basename($file);

            if (in_array($migrationName, $executed)) {
                continue;
            }

            echo "Running migration: {$migrationName}\n";

            $sql = file_get_contents($file);
            $pdo->exec($sql);

            $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
            $stmt->execute([$migrationName]);

            echo "Completed: {$migrationName}\n";
        }

        echo "All migrations completed.\n";
    }
}
