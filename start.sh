#!/bin/bash

echo "🌐 بدء تشغيل خادم الويب..."

# انتظار قاعدة البيانات
echo "⏳ انتظار قاعدة البيانات..."
sleep 5

# تنظيف التخزين المؤقت
echo "🧹 تنظيف الكاش..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# تشغيل Apache
echo "🚀 تشغيل Apache على المنفذ $PORT"
vendor/bin/heroku-php-apache2 -p $PORT public/
