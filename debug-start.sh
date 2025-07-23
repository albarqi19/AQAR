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

echo "🔧 فحص vendor/bin:"
ls -la vendor/bin/ | grep heroku

echo "🚀 تشغيل Apache..."
vendor/bin/heroku-php-apache2 -p $PORT public/
