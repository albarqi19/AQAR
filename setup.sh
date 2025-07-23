#!/bin/bash

echo "🚀 بدء تجهيز التطبيق..."

# توليد مفتاح التطبيق إذا لم يكن موجوداً
if [ -z "$APP_KEY" ]; then
    echo "📝 توليد مفتاح التطبيق..."
    php artisan key:generate --force
    echo "✅ تم توليد المفتاح"
fi

# تنظيف الكاش
echo "🧹 تنظيف الكاش..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# تشغيل Migration
echo "🗄️ تشغيل قاعدة البيانات..."
php artisan migrate --force

# إنشاء مستخدم ادمن تجريبي
echo "👤 إنشاء مستخدم تجريبي..."
php artisan tinker --execute="
try {
    if (!\App\Models\User::where('email', 'admin@test.com')->exists()) {
        \App\Models\User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now()
        ]);
        echo 'تم إنشاء المستخدم: admin@test.com / password';
    } else {
        echo 'المستخدم موجود بالفعل';
    }
} catch (\Exception \$e) {
    echo 'خطأ في إنشاء المستخدم: ' . \$e->getMessage();
}
"

echo "✅ تم تجهيز التطبيق بنجاح!"
echo "🌐 يمكنك الآن زيارة /admin"
echo "👤 البيانات: admin@test.com / password"
