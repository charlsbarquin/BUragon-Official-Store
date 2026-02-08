FROM php:7.4-apache

# Install PostgreSQL extensions (pdo_pgsql)
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy project files from the subfolder
COPY bicol-university-ecommerce/ /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Create uploads directory for image uploads
RUN mkdir -p uploads

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Set permissions for uploads
RUN chown -R www-data:www-data /var/www/html/uploads
RUN chmod -R 775 /var/www/html/uploads

# Expose port 80
EXPOSE 80
