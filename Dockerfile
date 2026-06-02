FROM richarvey/nginx-php-fpm:3.1.6

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY . /var/www/html
WORKDIR /var/www/html

# تثبيت الـ dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# توليد APP_KEY
RUN php artisan key:generate --force || true

# إعطاء الصلاحيات
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# إنشاء storage link
RUN php artisan storage:link || true

EXPOSE 10000

CMD ["/start.sh"]