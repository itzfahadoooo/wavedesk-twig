<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;

echo "Running database migrations...\n";

try {
    $db = Database::getConnection();
    
    // Create users table
    echo "Creating users table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    
    // Create tickets table
    echo "Creating tickets table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS tickets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            status ENUM('open', 'in_progress', 'closed') DEFAULT 'open',
            priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    
    // Seed demo user
    echo "Seeding demo user...\n";
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['test@example.com']);
    
    if (!$stmt->fetch()) {
        $hashedPassword = password_hash('Password123!', PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->execute(['test@example.com', $hashedPassword]);
        echo "Demo user created: test@example.com / Password123!\n";
    } else {
        echo "Demo user already exists.\n";
    }
    
    echo "✅ Migration completed successfully!\n";
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}