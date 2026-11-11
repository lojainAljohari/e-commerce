#!/bin/sh
set -eu
rm -f bootstrap/cache/config.php
if [ "$1" = "php-fpm" ]; then
    php artisan migrate --force
fi

exec "$@"
