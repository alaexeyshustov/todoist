#!/bin/sh
set -e

# Initialize SQLite database on its own dedicated volume (separate from app code)
mkdir -p /var/www/data
touch /var/www/data/database.sqlite
chown -R www-data:www-data /var/www/data

# Ensure storage subdirectories exist and are writable by www-data (volume may be empty on first run)
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/public
chown -R www-data:www-data storage bootstrap/cache

# Copy built public assets to the shared volume so Caddy can serve them
cp -r /var/www/html/public/. /mnt/public/

php artisan package:discover --ansi

exec "$@"
