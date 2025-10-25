#!/bin/bash
set -e

echo "=== WaveDesk Startup ==="

# Verify autoloader exists
if [ ! -f "vendor/autoload.php" ]; then
    echo "ERROR: Autoloader not found!"
    exit 1
fi

echo "✓ Autoloader found"

# Run migrations
echo "Running database migrations..."
php migrate.php

if [ $? -ne 0 ]; then
    echo "ERROR: Migration failed!"
    exit 1
fi

echo "✓ Migrations completed"

# Start server
echo "Starting PHP server on 0.0.0.0:$PORT"
exec php -S 0.0.0.0:$PORT -t .