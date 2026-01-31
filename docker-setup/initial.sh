#!/usr/bin/env bash

IBIM_BACKOFFICE_PATH="/usr/share/nginx/ibim-license-management"

echo "Setting up Laravel..."

mkdir -p $IBIM_BACKOFFICE_PATH/bootstrap/cache
mkdir -p $IBIM_BACKOFFICE_PATH/storage/framework/cache/data
mkdir -p $IBIM_BACKOFFICE_PATH/storage/framework/sessions
mkdir -p $IBIM_BACKOFFICE_PATH/storage/framework/views

# Install deps
composer install --no-interaction --prefer-dist --optimize-autoloader

# Migrate DB
php artisan migrate --force

# Passport keys check
PASSPORT_PRIVATE_KEY="$IBIM_BACKOFFICE_PATH/storage/oauth-private.key"

if [ ! -f "$PASSPORT_PRIVATE_KEY" ]; then
    echo "Generating Passport keys..."
    php artisan passport:keys --force
fi

# Seed DB (optional)
php artisan db:seed --force


echo "Fixing permissions..."

chown -R www-data:www-data $IBIM_BACKOFFICE_PATH/storage
chown -R www-data:www-data $IBIM_BACKOFFICE_PATH/bootstrap/cache

chmod -R 775 $IBIM_BACKOFFICE_PATH/storage
chmod -R 775 $IBIM_BACKOFFICE_PATH/bootstrap/cache

# Secure oauth keys
chmod 600 $IBIM_BACKOFFICE_PATH/storage/oauth-*.key

php artisan optimize:clear

echo "Setup complete."

################################################

if [ $# -gt 0 ]; then
    exec "$@"
else
    /usr/bin/supervisord
fi
