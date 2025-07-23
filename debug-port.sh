#!/bin/bash

echo "🔍 تشخيص متغيرات البيئة..."
echo "PORT = $PORT"
echo "APP_ENV = $APP_ENV"
echo "PWD = $PWD"

echo "🌐 قائمة متغيرات البيئة المتعلقة بـ PORT:"
env | grep -i port || echo "لا توجد متغيرات PORT"

# إصلاح APP_KEY أولاً
echo "🔑 فحص وإصلاح APP_KEY..."

# إنشاء ملف .env إذا لم يكن موجوداً
if [ ! -f .env ]; then
    echo "📄 إنشاء ملف .env من .env.example..."
    cp .env.example .env
fi

# فحص APP_KEY الحالي
if grep -q "APP_KEY=base64:" .env; then
    echo "✅ APP_KEY موجود"
else
    echo "🔄 توليد APP_KEY جديد..."
    php artisan key:generate --force --no-interaction || {
        echo "⚠️ فشل في توليد المفتاح بـ artisan، استخدام طريقة بديلة..."
        # توليد مفتاح يدوياً
        KEY=$(openssl rand -base64 32)
        echo "APP_KEY=base64:$KEY" >> .env
    }
fi

# تنظيف الكاش
php artisan config:clear --quiet 2>/dev/null || true
php artisan cache:clear --quiet 2>/dev/null || true

echo "🚀 تشغيل PHP Server..."
# تأكد من PORT
FINAL_PORT=${PORT:-8000}
echo "المنفذ النهائي: $FINAL_PORT"

# تشغيل PHP Server
exec php -S 0.0.0.0:$FINAL_PORT -t public
