#!/bin/bash

e# نسخ ملف البيئة
if [ ! -f .env ]; then
    echo "� إنشاء ملف .env..."
    cp .env.example .env
fi

# إصلاح مفتاح التطبيق
echo "🔑 إصلاح مفتاح التطبيق..."
php artisan fix:app-key --no-interaction || {
    echo "⚠️ فشل في استخدام الكومند المخصص، استخدام الطريقة التقليدية..."
    php artisan key:generate --force --no-interaction
}
echo "✅ تم إعداد المفتاح"ء تجهيز نظام إدارة العقارات..."

# التأكد من وجود مجلدات مهمة
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# ضبط الصلاحيات
chmod -R 755 storage bootstrap/cache

# نسخ ملف البيئة
if [ ! -f .env ]; then
    echo "� إنشاء ملف .env..."
    cp .env.example .env
fi

# توليد مفتاح التطبيق
echo "🔑 توليد مفتاح التطبيق..."
php artisan key:generate --force --no-interaction
echo "✅ تم توليد المفتاح"

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
