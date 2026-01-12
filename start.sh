#!/bin/bash

# Set PORT to Railway's PORT or default to 80
export PORT=${PORT:-80}

# Disable all MPM modules first to prevent conflicts
a2dismod mpm_event mpm_worker mpm_prefork 2>/dev/null || true

# Enable only mpm_prefork (required for mod_php)
a2enmod mpm_prefork

# Update Apache ports configuration
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf

# Update Apache configuration with the PORT variable
export APACHE_RUN_USER=www-data
export APACHE_RUN_GROUP=www-data
export APACHE_LOG_DIR=/var/log/apache2

echo "Starting Apache on port ${PORT}"

# Start Apache in foreground
apache2-foreground
