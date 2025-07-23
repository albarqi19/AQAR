# حل مشكلة النشر على Railway

## المشكلة التي واجهتها:
كانت هناك مشكلة في تكوين Nixpacks مما أدى إلى فشل في بناء المشروع.

## الحلول المطبقة:

### 1. تبسيط ملف `nixpacks.toml`:
```toml
[variables]
NODE_VERSION = "18"
PHP_VERSION = "8.2"

[phases.install]
cmds = ["composer install --no-dev --optimize-autoloader --no-interaction"]

[phases.build]
cmds = [
    "npm install",
    "npm run build"
]

[start]
cmd = "php artisan serve --host=0.0.0.0 --port=$PORT"
```

### 2. إضافة `Dockerfile` مخصص:
يمكنك الآن اختيار استخدام Docker بدلاً من Nixpacks في Railway.

### 3. تحديث `railway.toml`:
```toml
[build]
builder = "nixpacks"

[deploy]
startCommand = "php artisan serve --host=0.0.0.0 --port=$PORT"
healthcheckPath = "/"
healthcheckTimeout = 100
restartPolicyType = "ON_FAILURE"
```

## خيارات النشر في Railway:

### الخيار 1: استخدام Nixpacks (الموصى به):
1. في إعدادات Railway، تأكد من أن البناء يستخدم "Nixpacks"
2. سيقوم بقراءة ملف `nixpacks.toml` المحدث

### الخيار 2: استخدام Docker:
1. في إعدادات Railway، غير البناء إلى "Docker"
2. سيقوم بقراءة ملف `Dockerfile`

### الخيار 3: استخدام Heroku Buildpacks:
1. في إعدادات Railway، استخدم "Heroku Buildpacks"
2. سيقوم بقراءة ملف `Procfile`

## المتغيرات المطلوبة في Railway:

```
APP_NAME=نظام_إدارة_العقارات
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=false
APP_TIMEZONE=Asia/Riyadh
APP_URL=https://your-app.railway.app

DB_CONNECTION=postgresql
DB_HOST=${{PGHOST}}
DB_PORT=${{PGPORT}}
DB_DATABASE=${{PGDATABASE}}
DB_USERNAME=${{PGUSER}}
DB_PASSWORD=${{PGPASSWORD}}

CACHE_DRIVER=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

LOG_CHANNEL=stderr
```

## خطوات النشر:

1. **ادخل إلى Railway**: [railway.app](https://railway.app)
2. **اربط المستودع**: اختر مستودع GitHub
3. **أضف قاعدة بيانات**: PostgreSQL
4. **أضف المتغيرات**: انسخ المتغيرات أعلاه
5. **انشر**: سيتم النشر تلقائياً

## إذا واجهت مشاكل أخرى:

### مشكلة في تثبيت الحزم:
```bash
# في Railway Console
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### مشكلة في قاعدة البيانات:
```bash
# في Railway Console
php artisan migrate --force
php artisan db:seed --force
```

### مشكلة في الذاكرة التخزين المؤقت:
```bash
# في Railway Console
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

تم إصلاح المشكلة ورفع التحديثات إلى GitHub. جرب النشر مرة أخرى! 🚀
