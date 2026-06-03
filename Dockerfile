FROM richarvey/nginx-php-fpm:3.1.6

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1
ENV COMPOSER_ALLOW_SUPERUSER=1

# نسخ المشروع
COPY . /var/www/html
WORKDIR /var/www/html

# تثبيت Dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# توليد APP_KEY (مع تجاهل خطأ .env)
RUN php artisan key:generate --force || true

# الصلاحيات
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Storage Link
RUN php artisan storage:link || true

# إنشاء مجلد scripts
RUN mkdir -p /scripts

# نسخ سكريبت البداية
COPY start.sh /usr/local/bin/migrate-and-start.sh
RUN chmod +x /usr/local/bin/migrate-and-start.sh

EXPOSE 10000

CMD ["/usr/local/bin/migrate-and-start.sh"]