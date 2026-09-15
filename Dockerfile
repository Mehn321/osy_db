FROM php:8.4-apache-bookworm

# Install required system packages and PHP extensions
RUN apt-get update && apt-get upgrade -y && apt-get install -y --no-install-recommends \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mysqli mbstring zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy the custom entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy application files (we'll rely on volume mount for development, but good practice to copy)
COPY . /var/www/html/

# Expose port 80
EXPOSE 80

# Use the custom entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Default command
CMD ["apache2-foreground"]
