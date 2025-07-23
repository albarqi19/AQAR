# نظام إدارة العقارات - AQAR 🏢

نظام شامل لإدارة العقارات والمباني والعقود مبني باستخدام Laravel 12 و Filament 3.3

## 🌟 المميزات

- **إدارة المباني والوحدات العقارية**
- **إدارة الملاك والمستأجرين**
- **نظام العقود والمدفوعات**
- **تتبع أعمال الصيانة**
- **إدارة المصروفات والوثائق**
- **تقارير مفصلة وتصدير البيانات**
- **واجهة إدارية باللغة العربية**
- **دعم كامل للطباعة والـ PDF**

## 🛠️ التقنيات المستخدمة

- **Laravel 12** - إطار العمل الرئيسي
- **Filament 3.3** - لوحة التحكم
- **TailwindCSS** - التصميم
- **PostgreSQL/MySQL** - قاعدة البيانات
- **TCPDF & DomPDF** - إنشاء ملفات PDF
- **Maatwebsite Excel** - تصدير البيانات

## 🚀 النشر على Railway

### الطريقة السهلة - النشر المباشر

[![Deploy on Railway](https://railway.app/button.svg)](https://railway.app/template/laravel)

### الطريقة اليدوية

1. **إنشاء حساب على Railway**
   ```bash
   # قم بزيارة railway.app وأنشئ حساباً جديداً
   ```

2. **ربط المشروع بـ Railway**
   ```bash
   # في مجلد المشروع
   npm install -g @railway/cli
   railway login
   railway init
   ```

3. **إضافة قاعدة البيانات**
   ```bash
   railway add postgresql
   # أو
   railway add mysql
   ```

4. **تعيين متغيرات البيئة**
   - قم بزيارة Railway Dashboard
   - اختر مشروعك
   - اذهب إلى Variables
   - أضف المتغيرات التالية:

   ```env
   APP_NAME="نظام إدارة العقارات"
   APP_ENV=production
   APP_DEBUG=false
   APP_LOCALE=ar
   APP_URL=https://your-app.railway.app
   
   # سيتم ملؤها تلقائياً من قاعدة البيانات
   DATABASE_URL=${{DATABASE_URL}}
   DB_CONNECTION=pgsql
   DB_HOST=${{PGHOST}}
   DB_PORT=${{PGPORT}}
   DB_DATABASE=${{PGDATABASE}}
   DB_USERNAME=${{PGUSER}}
   DB_PASSWORD=${{PGPASSWORD}}
   ```

5. **النشر**
   ```bash
   railway up
   ```

## 🔧 الإعداد المحلي

1. **استنساخ المشروع**
   ```bash
   git clone https://github.com/albarqi19/AQAR.git
   cd AQAR
   ```

2. **تثبيت التبعيات**
   ```bash
   composer install
   npm install
   ```

3. **إعداد البيئة**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **إعداد قاعدة البيانات**
   ```bash
   # للـ SQLite (محلياً)
   touch database/database.sqlite
   
   # أو قم بتحديث .env لـ MySQL/PostgreSQL
   php artisan migrate --seed
   ```

5. **تشغيل التطبيق**
   ```bash
   npm run dev
   php artisan serve
   ```

## 👤 الوصول للوحة التحكم

- **الرابط**: `/admin`
- **إنشاء مستخدم إداري**:
  ```bash
  php artisan make:filament-user
  ```

## 📊 نماذج البيانات الأساسية

- **Building** - المباني
- **Shop** - المتاجر/الوحدات
- **Landlord** - الملاك
- **Tenant** - المستأجرين
- **Contract** - العقود
- **Payment** - المدفوعات
- **Maintenance** - الصيانة
- **Expense** - المصروفات
- **Document** - الوثائق

## 🔒 الأمان

- مصادقة متعددة العوامل
- تشفير البيانات الحساسة
- تسجيل جميع العمليات
- صلاحيات مستخدمين متدرجة

## 📱 الواجهات

- **واجهة إدارية**: `/admin` (Filament)
- **API**: `/api` (Laravel Sanctum)
- **التقارير**: تصدير PDF و Excel

## 🛠️ أدوات التطوير

```bash
# تشغيل الاختبارات
php artisan test

# تنظيف الكاش
php artisan optimize:clear

# إنشاء موارد جديدة
php artisan make:filament-resource ModelName

# تحديث قاعدة البيانات
php artisan migrate:fresh --seed
```

## 📈 مراقبة الأداء

يدعم المشروع أدوات المراقبة التالية:
- Laravel Telescope (للتطوير)
- Railway Metrics (للإنتاج)
- Custom Logging

## 🌍 الدعم الدولي

- اللغة العربية بشكل كامل
- دعم RTL
- تنسيق التواريخ العربية
- العملة بالريال السعودي

## 🤝 المساهمة

1. Fork المشروع
2. إنشاء فرع للميزة (`git checkout -b feature/AmazingFeature`)
3. Commit التغييرات (`git commit -m 'Add some AmazingFeature'`)
4. Push للفرع (`git push origin feature/AmazingFeature`)
5. فتح Pull Request

## 📄 الترخيص

هذا المشروع مرخص تحت [MIT License](LICENSE).

## 📞 التواصل

- **المطور**: الباقي
- **البريد الإلكتروني**: [بريدك الإلكتروني]
- **GitHub**: [@albarqi19](https://github.com/albarqi19)

---

<div dir="rtl" align="center">
  
**تم تطويره بـ ❤️ باستخدام Laravel & Filament**

</div>
