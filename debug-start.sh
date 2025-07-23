#!/bin/bash

echo "🔍 تشخيص مشكلة التشغيل..."

echo "📋 متغيرات البيئة:"
echo "PORT = $PORT"
echo "APP_ENV = $APP_ENV"
echo "DYNO = $DYNO"

echo "📁 محتوى المجلد:"
ls -la

echo "📄 محتوى Procfile:"
cat Procfile

echo "🔧 فحص PHP:"
php -v

echo "🌐 تشغيل PHP Built-in Server على المنفذ: ${PORT:-8000}"
exec php -S 0.0.0.0:${PORT:-8000} -t public
