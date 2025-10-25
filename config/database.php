<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                // Check if we have a DATABASE_URL (Render provides this)
                $databaseUrl = getenv('DATABASE_URL');
                
                if ($databaseUrl) {
                    // Parse DATABASE_URL (format: postgresql://user:password@host:port/database)
                    $url = parse_url($databaseUrl);
                    $host = $url['host'];
                    $port = $url['port'] ?? 5432;
                    $dbname = ltrim($url['path'], '/');
                    $username = $url['user'];
                    $password = $url['pass'];
                } else {
                    // Use individual environment variables (local development)
                    $host = getenv('DB_HOST') ?: 'localhost';
                    $port = getenv('DB_PORT') ?: '5432';
                    $dbname = getenv('DB_NAME') ?: 'wavedesk';
                    $username = getenv('DB_USER') ?: 'postgres';
                    $password = getenv('DB_PASS') ?: '';
                }

                $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
                
                self::$instance = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } catch (PDOException $e) {
                // Log error in production
                error_log("Database connection failed: " . $e->getMessage());
                
                // Show user-friendly error
                die("Unable to connect to the database. Please try again later.");
            }
        }

        return self::$instance;
    }
}