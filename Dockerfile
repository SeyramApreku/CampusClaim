FROM php:8.2-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql curl \
    && rm -rf /var/lib/apt/lists/*

# Create a custom Apache configuration to prevent MPM conflicts
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Enable mod_rewrite
RUN a2enmod rewrite

# Create a startup script to handle MPM cleanup
RUN echo '#!/bin/bash\n\
# Remove all MPM modules first\n\
a2dismod mpm_event mpm_worker mpm_prefork 2>/dev/null || true\n\
# Enable only prefork\n\
a2enmod mpm_prefork\n\
# Start Apache\n\
apache2-foreground' > /start.sh && chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
