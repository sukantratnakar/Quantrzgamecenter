#!/bin/bash
set -e

# Set PORT to Railway's PORT or default to 8080
export PORT=${PORT:-8080}

echo "Starting services on port ${PORT}"

# Generate Nginx config from template with PORT substitution
envsubst '${PORT}' < /etc/nginx/sites-available/default.template > /etc/nginx/sites-available/default

# Remove default nginx config if it exists
rm -f /etc/nginx/sites-enabled/default

# Enable our site configuration
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Start PHP-FPM in the background
echo "Starting PHP-FPM..."
php-fpm -D

# Start Nginx in the foreground
echo "Starting Nginx on port ${PORT}..."
nginx -g 'daemon off;'
