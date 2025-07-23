#!/bin/bash

# Laravel Deployment Script for Railway
echo "🚀 بدء عملية النشر..."

# Generate app key if not exists
if [ -z "$APP_KEY" ]; then
    echo "📱 إنشاء مفتاح التطبيق..."
    php artisan key:generate --force
fi

# Run database migrations
echo "🗄️ تشغيل قاعدة البيانات..."
php artisan migrate --force

# Clear and cache config
echo "⚙️ تحديث الكاش..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
echo "🔗 إنشاء رابط التخزين..."
php artisan storage:link

# Seed database (optional - remove if you don't want to seed in production)
# php artisan db:seed --force

echo "✅ تم النشر بنجاح!"
