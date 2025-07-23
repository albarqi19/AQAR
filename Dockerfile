# استخدام صورة PHP الرسمية مع FPM
FROM php:8.2-fpm

# تثبيت المتطلبات الأساسية
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تعيين مجلد العمل
WORKDIR /var/www/html

# نسخ ملفات Composer أولاً
COPY composer.json composer.lock ./

# تثبيت اعتماديات PHP (بدون vendor/ موجود)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# نسخ package.json للـ Node.js
COPY package.json package-lock.json* ./

# تثبيت اعتماديات Node.js
RUN npm install

# الآن نسخ باقي ملفات المشروع
COPY . .

# بناء الأصول
RUN npm run build

# تشغيل سكريبت Composer بعد نسخ الملفات
RUN composer run-script post-autoload-dump

# تعيين الصلاحيات
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# إنشاء تكوين Nginx
RUN echo 'server { \n\
    listen 80; \n\
    server_name localhost; \n\
    root /var/www/html/public; \n\
    index index.php index.html; \n\
    location / { \n\
        try_files $uri $uri/ /index.php?$query_string; \n\
    } \n\
    location ~ \.php$ { \n\
        fastcgi_pass 127.0.0.1:9000; \n\
        fastcgi_index index.php; \n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \n\
        include fastcgi_params; \n\
    } \n\
}' > /etc/nginx/sites-available/default

# تكوين Supervisor
RUN echo '[supervisord] \n\
nodaemon=true \n\
[program:nginx] \n\
command=/usr/sbin/nginx -g "daemon off;" \n\
autostart=true \n\
autorestart=true \n\
[program:php-fpm] \n\
command=/usr/local/sbin/php-fpm -F \n\
autostart=true \n\
autorestart=true' > /etc/supervisor/conf.d/supervisord.conf

# إزالة الملفات غير الضرورية
RUN rm -rf node_modules package-lock.json

EXPOSE 80

# تشغيل Supervisor
CMD ["/usr/bin/supervisord"]
