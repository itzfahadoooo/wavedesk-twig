<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                // Get database configuration from environment variables
                $host = getenv('DB_HOST') ?: 'localhost';
                $dbname = getenv('DB_NAME') ?: 'wavedesk';
                $username = getenv('DB_USER') ?: 'root';
                $password = getenv('DB_PASS') ?: '';

                $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
                
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