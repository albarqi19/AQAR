#!/bin/bash

echo "🔍 تشخيص متغيرات البيئة..."
echo "PORT = $PORT"
echo "APP_ENV = $APP_ENV"
echo "PWD = $PWD"

echo "🌐 قائمة متغيرات البيئة المتعلقة بـ PORT:"
env | grep -i port || echo "لا توجد متغيرات PORT"

echo "🚀 تشغيل PHP Server..."
# تأكد من PORT
FINAL_PORT=${PORT:-8000}
echo "المنفذ النهائي: $FINAL_PORT"

# تشغيل PHP Server
exec php -S 0.0.0.0:$FINAL_PORT -t public
