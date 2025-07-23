#!/bin/bash

echo "🚀 بدء تجهيز نظام إدارة العقارات..."

# التأكد من وجود مجلدات مهمة
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# ضبط الصلاحيات
chmod -R 755 storage bootstrap/cache

# توليد مفتاح التطبيق إذا لم يكن موجوداً
if [ -z "$APP_KEY" ]; then
    echo "📝 توليد مفتاح التطبيق..."
    php artisan key:generate --force
    echo "✅ تم توليد المفتاح"
fi

# تنظيف الكاش
echo "🧹 تنظيف الكاش..."
php artisan config:clear --quiet
php artisan cache:clear --quiet
php artisan route:clear --quiet
php artisan view:clear --quiet

# تشغيل Migration
echo "🗄️ إعداد قاعدة البيانات..."
php artisan migrate --force --quiet

echo "✅ تم تجهيز التطبيق بنجاح!"
echo "🌐 التطبيق جاهز للاستخدام"
