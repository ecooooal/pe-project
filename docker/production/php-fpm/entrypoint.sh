#!/bin/sh
set -e

echo "Clearing and caching config..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan view:clear

# Initialize storage directory if empty
# -----------------------------------------------------------
# If the storage directory is empty, copy the initial contents
# and set the correct permissions.
# -----------------------------------------------------------
echo "Setting permissions for storage and bootstrap/cache directories..."
if [ ! "$(ls -A /var/www/storage)" ]; then
  echo "Initializing storage directory..."
  cp -R /var/www/storage-init/. /var/www/storage
  chown -R www-data:www-data /var/www/storage
fi

mkdir -p /var/www/storage/logs \
         /var/www/storage/framework/cache \
         /var/www/storage/framework/sessions \
         /var/www/storage/framework/views

# Fix ownership
chown -R www-data:www-data /var/www/storage

# Remove storage-init directory
rm -rf /var/www/storage-init

# Run Laravel migrations
# -----------------------------------------------------------
# Ensure the database schema is up to date.
# -----------------------------------------------------------

check_migration_status() {
    php artisan migrate:status > /dev/null 2>&1
    return $?
}
echo "Checking migration status..."
check_migration_status
if [ $? -eq 0 ]; then
    echo "Migrations table exists. Skipping migration."
else
    echo "Migrations table does not exist or there is an issue. Running migrations."
    php artisan migrate --force
fi

echo "Starting PHP-FPM..."

# Run the default command
exec "$@"
