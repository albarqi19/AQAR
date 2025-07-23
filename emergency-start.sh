#!/bin/bash

echo "🚨 تشغيل طوارئ لنظام إدارة العقارات..."

# تعيين APP_KEY مباشرة كمتغير بيئة
export APP_KEY="base64:1jJ7lx/yprzypdaIzSD6nk1GaImlQuKx4QE2+TqQT2Q="
export APP_ENV="production"
export APP_NAME="نظام إدارة العقارات"
export APP_DEBUG="false"
export APP_LOCALE="ar"
export DB_CONNECTION="sqlite"
export DB_DATABASE="/app/database/database.sqlite"
export LOG_LEVEL="error"

echo "🔑 APP_KEY تم تعيينه: ${APP_KEY:0:20}..."

# نسخ .env الجاهز
echo "📄 استخدام ملف .env جاهز..."
cp .env.railway .env 2>/dev/null || cp .env.example .env

# التأكد من قاعدة البيانات
echo "🗄️ إعداد قاعدة البيانات..."
mkdir -p database
touch database/database.sqlite
php artisan migrate --force --no-interaction 2>/dev/null || echo "⚠️ تخطي migration"

# تنظيف الكاش
echo "🧹 تنظيف الكاش..."
php artisan config:clear --no-interaction 2>/dev/null || true
php artisan cache:clear --no-interaction 2>/dev/null || true

# تشغيل الخادم
FINAL_PORT=${PORT:-8000}
echo "🌐 تشغيل الخادم على المنفذ: $FINAL_PORT"
exec php -S 0.0.0.0:$FINAL_PORT -t public
