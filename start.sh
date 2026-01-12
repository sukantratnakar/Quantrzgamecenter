#!/bin/bash
set -e

# Set PORT to Railway's PORT or default to 8080
export PORT=${PORT:-8080}

echo "=========================================="
echo "Starting Quantraz Game Center"
echo "Port: ${PORT}"
echo "=========================================="

# Ensure session directory exists and has correct permissions
mkdir -p /var/lib/php/sessions
chown -R www-data:www-data /var/lib/php/sessions
chmod -R 755 /var/lib/php/sessions

# Generate Nginx config from template with PORT substitution
echo "Configuring Nginx..."
envsubst '${PORT}' < /etc/nginx/sites-available/default.template > /etc/nginx/sites-available/default

# Remove default nginx config if it exists
rm -f /etc/nginx/sites-enabled/default

# Enable our site configuration
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Test Nginx configuration
echo "Testing Nginx configuration..."
nginx -t

# Start PHP-FPM in the background
echo "Starting PHP-FPM..."
php-fpm -D

# Wait a moment for PHP-FPM to start
sleep 2

# Verify PHP-FPM is running
if pgrep -x "php-fpm" > /dev/null; then
    echo "✓ PHP-FPM started successfully"
else
    echo "✗ PHP-FPM failed to start"
    exit 1
fi

# Start Nginx in the foreground
echo "Starting Nginx on port ${PORT}..."
echo "=========================================="
nginx -g 'daemon off;'
