<?php

// إعداد متغيرات البيئة مباشرة
$_ENV['APP_KEY'] = 'base64:1jJ7lx/yprzypdaIzSD6nk1GaImlQuKx4QE2+TqQT2Q=';
$_ENV['APP_ENV'] = 'production';
$_ENV['APP_NAME'] = 'نظام إدارة العقارات';
$_ENV['APP_DEBUG'] = 'false';
$_ENV['APP_LOCALE'] = 'ar';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = '/app/database/database.sqlite';
$_ENV['LOG_LEVEL'] = 'error';

// تعيين متغيرات النظام
putenv('APP_KEY=base64:1jJ7lx/yprzypdaIzSD6nk1GaImlQuKx4QE2+TqQT2Q=');
putenv('APP_ENV=production');
putenv('APP_NAME=نظام إدارة العقارات');
putenv('APP_DEBUG=false');
putenv('APP_LOCALE=ar');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=/app/database/database.sqlite');

echo "🔑 تم تعيين APP_KEY: " . substr($_ENV['APP_KEY'], 0, 20) . "...\n";

// إنشاء ملف .env
$envContent = <<<ENV
APP_NAME="نظام إدارة العقارات"
APP_ENV=production
APP_KEY=base64:1jJ7lx/yprzypdaIzSD6nk1GaImlQuKx4QE2+TqQT2Q=
APP_DEBUG=false
APP_URL=https://your-app.railway.app

APP_LOCALE=ar
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite

CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=log
ENV;

file_put_contents('.env', $envContent);
echo "📄 تم إنشاء ملف .env\n";

// إنشاء قاعدة البيانات
if (!is_dir('database')) {
    mkdir('database', 0755, true);
}
if (!file_exists('database/database.sqlite')) {
    touch('database/database.sqlite');
}

echo "🗄️ تم إعداد قاعدة البيانات\n";

// تشغيل Laravel
echo "🚀 تشغيل Laravel...\n";

// تضمين bootstrap Laravel
require_once __DIR__.'/public/index.php';
