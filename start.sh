#!/bin/bash
cd /var/www/html
php artisan migrate --force
php artisan config:cache
php-fpm -D
sleep 4
/start.sh