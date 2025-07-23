<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // إنشاء مستخدم افتراضي للدخول
        User::firstOrCreate(
            ['email' => 'admin@aqari.com'],
            [
                'name' => 'مدير النظام',
                'email' => 'admin@aqari.com',
                'password' => bcrypt('123456'),
                'email_verified_at' => now(),
            ]
        );

        // تشغيل البيانات الأساسية أولاً (إذا لم تكن موجودة)
        $this->call([
            DemoDataSeeder::class,
            DocumentSeeder::class,
        ]);
        
        // إنشاء مدن تجريبية (إذا لم تكن موجودة)
        if (\App\Models\City::count() == 0) {
            $riyadh = \App\Models\City::create([
                'name' => 'الرياض',
                'code' => 'RUH',
                'description' => 'عاصمة المملكة العربية السعودية',
                'is_active' => true,
            ]);

            $jeddah = \App\Models\City::create([
                'name' => 'جدة',
                'code' => 'JED',
                'description' => 'عروس البحر الأحمر',
                'is_active' => true,
            ]);

            // إنشاء أحياء تجريبية
            $olaya = \App\Models\District::create([
                'city_id' => $riyadh->id,
                'name' => 'العليا',
                'description' => 'حي العليا التجاري',
                'is_active' => true,
            ]);

            $malaz = \App\Models\District::create([
                'city_id' => $riyadh->id,
                'name' => 'الملز',
                'description' => 'حي الملز السكني',
                'is_active' => true,
            ]);

            // إنشاء مكتب عقاري تجريبي
            $office = \App\Models\Landlord::create([
                'name' => 'مكتب الخليج العقاري',
                'company_name' => 'شركة الخليج للاستثمار العقاري',
                'commercial_registration' => '1010123456',
                'phone' => '+966501234567',
                'email' => 'info@gulf-realestate.com',
                'address' => 'الرياض - حي العليا',
                'contact_person' => 'أحمد محمد',
                'commission_rate' => 5.00,
                'is_active' => true,
            ]);
        }
    }
}
