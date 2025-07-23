<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use App\Models\Landlord;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BasicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إضافة مدن أساسية
        $riyadh = City::create([
            'name' => 'الرياض',
            'is_active' => true,
        ]);

        $jeddah = City::create([
            'name' => 'جدة',
            'is_active' => true,
        ]);

        $dammam = City::create([
            'name' => 'الدمام',
            'is_active' => true,
        ]);

        // إضافة أحياء للرياض
        District::create([
            'city_id' => $riyadh->id,
            'name' => 'العليا',
            'is_active' => true,
        ]);

        District::create([
            'city_id' => $riyadh->id,
            'name' => 'الملز',
            'is_active' => true,
        ]);

        District::create([
            'city_id' => $riyadh->id,
            'name' => 'السليمانية',
            'is_active' => true,
        ]);

        // إضافة أحياء لجدة
        District::create([
            'city_id' => $jeddah->id,
            'name' => 'الروضة',
            'is_active' => true,
        ]);

        District::create([
            'city_id' => $jeddah->id,
            'name' => 'السلامة',
            'is_active' => true,
        ]);

        // إضافة مكتب عقاري أساسي
        Landlord::create([
            'name' => 'مكتب العقار الذهبي',
            'company_name' => 'شركة العقار الذهبي للاستثمار العقاري',
            'commercial_registration' => '1010123456',
            'license_number' => 'RE-2024-001',
            'phone' => '0555123456',
            'email' => 'info@golden-realestate.com',
            'address' => 'الرياض، حي العليا، شارع الملك فهد',
            'contact_person' => 'أحمد محمد العلي',
            'commission_rate' => 5.00,
            'notes' => 'مكتب عقاري رائد في السوق السعودي',
            'is_active' => true,
        ]);
    }
}
