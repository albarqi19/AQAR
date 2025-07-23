#!/bin/bash

# قتل أي عمليات artisan serve موجودة
pkill -f "artisan serve" 2>/dev/null || true

echo "🚫 تم منع تشغيل artisan serve"
echo "✅ تشغيل PHP Built-in Server بدلاً من ذلك..."

# تأكد من PORT
if [ -z "$PORT" ]; then
    PORT=8000
fi

echo "🌐 تشغيل PHP Server على المنفذ: $PORT"

# تشغيل PHP Built-in Server مباشرة
exec php -S 0.0.0.0:$PORT -t public
