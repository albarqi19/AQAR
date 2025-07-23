#!/bin/bash

echo "🔍 تشخيص شامل لمشكلة APP_KEY..."

echo "======================================"
echo "1. فحص متغيرات البيئة الحالية:"
echo "APP_KEY = ${APP_KEY:0:20}..."
echo "APP_ENV = $APP_ENV"
echo "PORT = $PORT"

echo "======================================"
echo "2. فحص ملفات الإعدادات:"
echo "وجود .env:"
if [ -f .env ]; then
    echo "✅ موجود"
    echo "محتوى APP_KEY في .env:"
    grep "APP_KEY" .env || echo "❌ غير موجود"
else
    echo "❌ غير موجود"
fi

echo "وجود .env.example:"
if [ -f .env.example ]; then
    echo "✅ موجود"
    echo "محتوى APP_KEY في .env.example:"
    grep "APP_KEY" .env.example || echo "❌ غير موجود"
else
    echo "❌ غير موجود"
fi

echo "======================================"
echo "3. إصلاح مشكلة APP_KEY:"

# تعيين مباشر لمتغيرات البيئة
export APP_KEY="base64:1jJ7lx/yprzypdaIzSD6nk1GaImlQuKx4QE2+TqQT2Q="
export APP_ENV="production"
export APP_NAME="نظام إدارة العقارات"
export APP_DEBUG="false"
export APP_LOCALE="ar"
export DB_CONNECTION="sqlite"
export DB_DATABASE="/app/database/database.sqlite"
export LOG_LEVEL="error"
export CACHE_STORE="file"
export SESSION_DRIVER="file"

echo "✅ تم تعيين جميع متغيرات البيئة المطلوبة"

# إنشاء ملف .env
echo "📄 إنشاء ملف .env جديد..."
cat > .env << EOF
APP_NAME="نظام إدارة العقارات"
APP_ENV=production
APP_KEY=base64:1jJ7lx/yprzypdaIzSD6nk1GaImlQuKx4QE2+TqQT2Q=
APP_DEBUG=false
APP_URL=\$APP_URL

APP_LOCALE=ar
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite

CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=log
EOF

echo "✅ تم إنشاء ملف .env"

echo "======================================"
echo "4. اختبار Laravel:"
php artisan --version || echo "❌ خطأ في Laravel"

echo "======================================"
echo "5. تشغيل الخادم:"
FINAL_PORT=${PORT:-8000}
echo "🌐 المنفذ: $FINAL_PORT"
exec php -S 0.0.0.0:$FINAL_PORT -t public
