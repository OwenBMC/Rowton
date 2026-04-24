#!/bin/sh
# Generate Wayfinder types at runtime (Fly secrets available)
php artisan wayfinder:generate --with-form

# Start supervisord to run php-fpm + nginx
exec /usr/bin/supervisord -c /etc/supervisord.conf