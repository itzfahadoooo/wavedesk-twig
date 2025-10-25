FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json ./

# Install dependencies (this will fail initially, but that's ok)
RUN composer install --no-dev --no-scripts --no-autoloader || true

# Copy all application files
COPY . .

# Now regenerate the autoloader with all files present
RUN composer dump-autoload --no-dev --optimize

# Verify autoloader was created
RUN ls -la vendor/composer/

# Expose port
EXPOSE 8080

# Start application (run migration then start server)
CMD php migrate.php && php -S 0.0.0.0:$PORT -t .