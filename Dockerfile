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
COPY composer.json composer.lock* ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy all application files
COPY . .

# Regenerate autoloader with all files
RUN composer dump-autoload --no-dev --optimize

# Expose port
EXPOSE 8080

# Start application
CMD php migrate.php && php -S 0.0.0.0:$PORT -t .