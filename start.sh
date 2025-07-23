#!/bin/bash

# انتظار قاعدة البيانات
echo "انتظار قاعدة البيانات..."
sleep 5

# تنظيف التخزين المؤقت
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# تشغيل التطبيق
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
