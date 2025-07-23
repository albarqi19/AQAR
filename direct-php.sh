#!/bin/bash

echo "🔥 تشغيل مباشر مع PHP"

# تعيين متغيرات البيئة
export APP_KEY="base64:1jJ7lx/yprzypdaIzSD6nk1GaImlQuKx4QE2+TqQT2Q="
export APP_ENV="production"
export APP_DEBUG="false"
export APP_LOCALE="ar"
export DB_CONNECTION="sqlite"
export DB_DATABASE="/app/database/database.sqlite"

echo "🔑 APP_KEY: ${APP_KEY:0:20}..."

# تشغيل PHP script للإعداد
php start-laravel.php

# لا نحتاج تشغيل الخادم هنا لأن start-laravel.php سيتولى الأمر
