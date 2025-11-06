#!/usr/bin/env bash

IBIM_BACKOFFICE_PATH="/usr/share/nginx/ibim-license-management"

##
# Ensure /.composer exists and is writable
#
if [ ! -d /.composer ]; then
    mkdir /.composer
fi

chmod -R ugo+rw /.composer


# find /etc /opt/etc /usr/local/etc -type f -name newrelic.ini \
#     -exec sed -i \
#         -e "s/REPLACE_WITH_REAL_KEY/${NEW_RELIC_LICENSE_KEY}/" \
#         -e "s/newrelic.appname[[:space:]]=[[:space:]].*/newrelic.appname = \"${NEW_RELIC_APP_NAME}\"/" \
#         -e '$anewrelic.labels = "nr_deployed_by:newrelic-cli"' {} \;
#         # -e '$anewrelic.daemon.address="newrelic-php-daemon:31339"' {} \;

# cp /etc/php/8.1/fpm/conf.d/newrelic.ini /etc/php/8.1/mods-available/

mkdir -p $IBIM_BACKOFFICE_PATH/bootstrap/cache
mkdir -p $IBIM_BACKOFFICE_PATH/storage/framework/cache/data
mkdir -p $IBIM_BACKOFFICE_PATH/storage/framework/sessions
mkdir -p $IBIM_BACKOFFICE_PATH/storage/framework/views

chown -R www-data:www-data $IBIM_BACKOFFICE_PATH/storage
chmod -R 777 $IBIM_BACKOFFICE_PATH/storage
chmod -R 777 $IBIM_BACKOFFICE_PATH/bootstrap

composer install

php artisan migrate

PASSPORT_PRIVATE_KEY="$IBIM_BACKOFFICE_PATH/storage/oauth-private.key"
if [ -f "$PASSPORT_PRIVATE_KEY" ]; then
    echo "$PASSPORT_PRIVATE_KEY exists."
elif [ -f "$IBIM_BACKOFFICE_PATH/docker-setup/oauth-private.key" ]; then
    echo "$PASSPORT_PRIVATE_KEY exists."
    cp "$IBIM_BACKOFFICE_PATH/docker-setup/oauth-private.key" "$IBIM_BACKOFFICE_PATH/storage/oauth-private.key"
    cp "$IBIM_BACKOFFICE_PATH/docker-setup/oauth-public.key" "$IBIM_BACKOFFICE_PATH/storage/oauth-public.key"
else 
    echo "$PASSPORT_PRIVATE_KEY does not exist."
    php artisan passport:install 
fi

php artisan db:seed
##
# Run a command or start supervisord
#

if [ $# -gt 0 ];then
    # If we passed a command, run it
    exec "$@"
else
    # Otherwise start supervisord
    /usr/bin/supervisord
fi

# cp ../beta-files/oauth-private.key storage/
# cp ../beta-files/oauth-public.key storage/

exit 0