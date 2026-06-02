FROM richarvey/nginx-php-fpm:3.1.6

# إعدادات Laravel
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1
ENV COMPOSER_ALLOW_SUPERUSER=1

# تثبيت الـ dependencies
COPY . /var/www/html

WORKDIR /var/www/html

# تثبيت Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# توليد APP_KEY إذا لم يكن موجود (سيتم تجاوزه بـ Environment Variable)
RUN php artisan key:generate --force || true

# إعطاء الصلاحيات اللازمة
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# بناء الأصول (إذا كان عندك Vite / Frontend)
# RUN npm install && npm run build

EXPOSE 10000

CMD ["/start.sh"]