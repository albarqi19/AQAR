# استخدام صورة PHP الرسمية
FROM php:8.2-apache

# تثبيت المتطلبات الأساسية
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تعيين مجلد العمل
WORKDIR /var/www/html

# نسخ ملفات المشروع
COPY . .

# تثبيت اعتماديات PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# تثبيت اعتماديات Node.js وبناء الأصول
RUN npm install && npm run build

# تعيين الصلاحيات
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# تمكين mod_rewrite
RUN a2enmod rewrite

# نسخ تكوين Apache
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf

# تشغيل Apache
CMD ["apache2-foreground"]
