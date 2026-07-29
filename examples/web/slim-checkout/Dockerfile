# NOTE: Build context must be packages/sdk-php (SDK root)
# docker build -f examples/web/slim-checkout/Dockerfile -t sdk-php-demo .
FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install system dependencies for composer
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy SDK first
COPY composer.json ./sdk/composer.json
COPY src ./sdk/src/

# Copy example app
COPY examples/web/slim-checkout ./html/

# Update composer.json to point to SDK in /var/www/sdk
WORKDIR /var/www/html
RUN sed -i 's|"url": "../../../"|"url": "/var/www/sdk"|g' composer.json

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Configure Apache
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# Create storage directory
RUN mkdir -p /var/www/html/src/storage && \
    chown -R www-data:www-data /var/www/html/src/storage

EXPOSE 80

CMD ["apache2-foreground"]
