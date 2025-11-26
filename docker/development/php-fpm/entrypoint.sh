#!/bin/sh
set -e

# Check if $UID and $GID are set, else fallback to default (1000:1000)
USER_ID=${UID:-1000}
GROUP_ID=${GID:-1000}

# Fix file ownership and permissions using the passed UID and GID
echo "Fixing file permissions with UID=${USER_ID} and GID=${GROUP_ID}..."
chown -R ${USER_ID}:${GROUP_ID} /var/www || echo "Some files could not be changed"

# Clear configurations to avoid caching issues in development
echo "Clearing and caching config..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan view:clear

# Run the default command (e.g., php-fpm or bash)

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
exec "$@"
