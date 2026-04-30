FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Install MySQL client (optional, for debugging)
RUN apt-get update && apt-get install -y default-mysql-client

# Copy all application files to Apache's document root
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Enable Apache mod_rewrite (if you need pretty URLs)
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]