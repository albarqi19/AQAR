#!/bin/bash

echo "🚀 بدء تشغيل نظام إدارة العقارات..."

# التأكد من متغير PORT
if [ -z "$PORT" ]; then
    export PORT=8000
    echo "⚠️  تم ضبط PORT على 8000"
fi

echo "🌐 تشغيل PHP Server على المنفذ: $PORT"

# تشغيل PHP Built-in Server بدلاً من Apache
exec php -S 0.0.0.0:$PORT -t public
