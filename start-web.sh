#!/bin/bash

echo "🚀 بدء تشغيل نظام إدارة العقارات..."

# التأكد من متغير PORT
if [ -z "$PORT" ]; then
    export PORT=8000
    echo "⚠️  تم ضبط PORT على 8000"
fi

echo "🌐 تشغيل Apache على المنفذ: $PORT"

# تشغيل Apache بدلاً من artisan serve
exec vendor/bin/heroku-php-apache2 -p $PORT public/
