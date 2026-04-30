FROM php:8.2-apache

# Install mysqli and pdo_mysql extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql && \
    docker-php-ext-enable mysqli pdo_mysql

# Disable conflicting MPM modules FIRST before copying files
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true && \
    a2enmod mpm_prefork

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Now copy application files to Apache's document root
COPY . /var/www/html/

# Remove .DS_Store and other Mac system files
RUN find /var/www/html -name ".DS_Store" -delete || true

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]