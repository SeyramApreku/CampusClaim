FROM php:8.2-apache

# Install mysqli and pdo_mysql extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql && \
    docker-php-ext-enable mysqli pdo_mysql

# Copy all application files to Apache's document root
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Disable conflicting MPM modules and ensure only prefork is enabled
RUN a2dismod mpm_event mpm_worker && a2enmod mpm_prefork

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]