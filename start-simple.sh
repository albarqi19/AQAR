#!/bin/bash

# تشغيل بسيط للخادم
export PORT=${PORT:-8000}
echo "🌐 تشغيل نظام إدارة العقارات على المنفذ: $PORT"
exec php -S 0.0.0.0:$PORT -t public
