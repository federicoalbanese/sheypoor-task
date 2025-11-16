#!/usr/bin/env sh

set -e

role=${CONTAINER_ROLE:-app}
env=${APP_ENV:-production}

#if [ "$env" = "production" ]; then
#    echo "Caching configuration..."
#    (cd /var/www/html && php artisan config:cache && php artisan route:cache && php artisan view:cache)
#fi

if [ "$role" = "app" ]; then

    exec php-fpm -R

elif [ "$role" = "queue" ]; then

    echo "Queue role"
    php /var/www/html/artisan queue:work --timeout=3600

elif [ "$role" = "scheduler" ]; then

    echo "Scheduler role"
    exit 1

else
    echo "Could not match the container role \"$role\""
    exit 1
fi
