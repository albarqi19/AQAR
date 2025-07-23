# استخدام صورة Heroku PHP
FROM heroku/heroku:22

# تثبيت PHP 8.3
RUN apt-get update && apt-get install -y software-properties-common \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update \
    && apt-get install -y \
        php8.3 \
        php8.3-cli \
        php8.3-fpm \
        php8.3-pgsql \
        php8.3-mysql \
        php8.3-zip \
        php8.3-intl \
        php8.3-mbstring \
        php8.3-xml \
        php8.3-curl \
        php8.3-gd \
        composer \
    && apt-get clean

# تعيين مجلد العمل
WORKDIR /app

# نسخ ملفات المشروع
COPY . .

# تثبيت اعتماديات PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# تعيين الصلاحيات
RUN chmod -R 755 storage bootstrap/cache

# تشغيل الخادم
EXPOSE $PORT
CMD vendor/bin/heroku-php-apache2 -p $PORT public/
