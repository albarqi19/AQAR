#!/bin/bash

# تشغيل سريع ومبسط
export PORT=${PORT:-8000}
echo "🌐 تشغيل نظام إدارة العقارات على المنفذ: $PORT"
php -S 0.0.0.0:$PORT -t public
