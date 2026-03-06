FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Enable Apache rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Install Laravel dependencies (including dev for Faker)
RUN composer install --optimize-autoloader --no-interaction

# Fix Laravel permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Set Apache document root to Laravel public folder
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Clear Laravel caches
RUN php artisan config:clear || true
RUN php artisan cache:clear || true

# Expose port
EXPOSE 80

# Run migrations and seeders, then start Apache
CMD php artisan migrate --force && \
    php artisan db:seed --force && \
    apache2-foreground