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
            id SERIAL PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");
    
    // Create tickets table
    echo "Creating tickets table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS tickets (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            status VARCHAR(20) DEFAULT 'open' CHECK (status IN ('open', 'in_progress', 'closed')),
            priority VARCHAR(20) DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high')),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");
    
    // Create trigger for updated_at on users
    echo "Creating triggers...\n";
    $db->exec("
        CREATE OR REPLACE FUNCTION update_updated_at_column()
        RETURNS TRIGGER AS $$
        BEGIN
            NEW.updated_at = CURRENT_TIMESTAMP;
            RETURN NEW;
        END;
        $$ language 'plpgsql';
    ");
    
    $db->exec("
        DROP TRIGGER IF EXISTS update_users_updated_at ON users;
        CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
        FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
    ");
    
    $db->exec("
        DROP TRIGGER IF EXISTS update_tickets_updated_at ON tickets;
        CREATE TRIGGER update_tickets_updated_at BEFORE UPDATE ON tickets
        FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
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