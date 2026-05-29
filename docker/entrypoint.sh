#!/bin/sh
set -e

# Ensure SQLite database file exists on the mounted volume
mkdir -p database
touch database/database.sqlite

# Ensure storage subdirectories exist (volume may be empty on first run)
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/public

php artisan package:discover --ansi

exec "$@"
