#!/bin/bash

set -e  # Exit immediately if a command exits with a non-zero status

echo "🎬 Laravel entrypoint started..."

# Set permissions (optional — do only if needed)
# chown -R www-data:www-data /var/www

# Copy the initial storage structure if it doesn't exist (optional)
if [ ! -f /var/www/storage/app/.initialized ]; then
  echo "🗃️ Initializing storage directory..."
  cp -r /var/www/storage-init/* /var/www/storage/
  touch /var/www/storage/app/.initialized
fi

# Run Laravel storage and cache setup
echo "🧹 Clearing old caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "⚡ Caching config and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optional: run migrations (only if you want auto-migrate in production)
echo "🗄️ Running database migrations..."
php artisan migrate --force

echo "🗄️ Running database seeders..."
if ! php artisan db:seed; then
  echo "⚠️ Seeding failed. Check the seeders for errors."
fi

# Finally, run the main container command (PHP-FPM)
echo "🚀 Starting PHP-FPM..."
exec "$@"
