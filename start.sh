#!/bin/bash

# تشغيل عمليات التهيئة
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# تشغيل التطبيق
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
