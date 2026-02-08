FROM php:7.4-apache

# Install PDO and MySQL extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Create uploads directory
RUN mkdir -p uploads

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Set permissions for uploads
RUN chown -R www-data:www-data /var/www/html/uploads
RUN chmod -R 775 /var/www/html/uploads

# Expose port 80
EXPOSE 80
