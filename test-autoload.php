<?php
require_once __DIR__ . '/vendor/autoload.php';

echo "Testing autoloader...\n\n";

try {
    echo "1. Testing Database class...\n";
    $db = App\Config\Database::getConnection();
    echo "   ✓ Database class loaded\n\n";
    
    echo "2. Testing AuthService class...\n";
    $auth = new App\Services\AuthService();
    echo "   ✓ AuthService class loaded\n\n";
    
    echo "3. Testing TicketService class...\n";
    $ticket = new App\Services\TicketService();
    echo "   ✓ TicketService class loaded\n\n";
    
    echo "✅ All classes loaded successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}