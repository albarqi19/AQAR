#!/bin/bash

echo "🔑 إصلاح مفتاح التطبيق..."

# إنشاء ملف .env إذا لم يكن موجوداً
if [ ! -f .env ]; then
    echo "📄 إنشاء ملف .env من .env.example..."
    cp .env.example .env
fi

# توليد مفتاح جديد
echo "🔄 توليد مفتاح APP_KEY جديد..."
php artisan key:generate --force --no-interaction

# عرض المفتاح المولد
echo "✅ تم إنشاء مفتاح التطبيق بنجاح!"

# تنظيف الكاش
php artisan config:clear --quiet
php artisan cache:clear --quiet

echo "🌐 جاهز للاستخدام!"
