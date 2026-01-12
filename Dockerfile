FROM php:8.2-fpm

# Install system dependencies and Nginx
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    gettext-base \
    && docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy Nginx configuration template
COPY nginx.conf /etc/nginx/sites-available/default.template

# Copy startup script
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Create session directory and set proper permissions
RUN mkdir -p /var/lib/php/sessions \
    && chown -R www-data:www-data /var/lib/php/sessions \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose dynamic port
EXPOSE ${PORT:-8080}

# Start services with custom script
CMD ["/usr/local/bin/start.sh"]
