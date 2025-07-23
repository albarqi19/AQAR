#!/bin/bash

# قتل أي عمليات artisan serve موجودة
pkill -f "artisan serve" 2>/dev/null || true

echo "🚫 تم منع تشغيل artisan serve"
echo "✅ تشغيل Apache بدلاً من ذلك..."

# تأكد من PORT
if [ -z "$PORT" ]; then
    PORT=8000
fi

echo "🌐 تشغيل Apache على المنفذ: $PORT"

# تشغيل Apache مباشرة
exec vendor/bin/heroku-php-apache2 -p $PORT public/
