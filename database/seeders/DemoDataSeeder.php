<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\District;
use App\Models\Landlord;
use App\Models\Building;
use App\Models\Shop;
use App\Models\Tenant;
use App\Models\Contract;
use App\Models\Payment;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('إنشاء البيانات التجريبية...');

        // إضافة مدن إضافية
        $cities = [
            ['name' => 'جدة', 'code' => 'JED'],
            ['name' => 'الدمام', 'code' => 'DMM'],
            ['name' => 'مكة المكرمة', 'code' => 'MKK'],
        ];

        foreach ($cities as $cityData) {
            City::firstOrCreate(['name' => $cityData['name']], $cityData);
        }

        // إضافة أحياء إضافية
        $riyadhCity = City::where('name', 'الرياض')->first();
        $jeddahCity = City::where('name', 'جدة')->first();
        $dammamCity = City::where('name', 'الدمام')->first();

        $districts = [
            // أحياء الرياض
            ['name' => 'الملز', 'city_id' => $riyadhCity?->id],
            ['name' => 'النخيل', 'city_id' => $riyadhCity?->id],
            ['name' => 'السويدي', 'city_id' => $riyadhCity?->id],
            ['name' => 'الربوة', 'city_id' => $riyadhCity?->id],
            
            // أحياء جدة
            ['name' => 'الحمراء', 'city_id' => $jeddahCity?->id],
            ['name' => 'الشاطئ', 'city_id' => $jeddahCity?->id],
            ['name' => 'الروضة', 'city_id' => $jeddahCity?->id],
            
            // أحياء الدمام
            ['name' => 'الفردوس', 'city_id' => $dammamCity?->id],
            ['name' => 'الشاطئ الشرقي', 'city_id' => $dammamCity?->id],
        ];

        foreach ($districts as $districtData) {
            if ($districtData['city_id']) {
                District::firstOrCreate(
                    ['name' => $districtData['name'], 'city_id' => $districtData['city_id']], 
                    $districtData
                );
            }
        }

        // إضافة مكاتب عقارية إضافية
        $landlords = [
            [
                'name' => 'مكتب الأندلس العقاري',
                'company_name' => 'شركة الأندلس للاستثمار العقاري',
                'commercial_registration' => '1010567890',
                'phone' => '0112345678',
                'email' => 'info@andalus-realestate.com',
                'address' => 'شارع الملك فهد، الرياض',
                'is_active' => true,
            ],
            [
                'name' => 'مكتب النخبة العقاري',
                'company_name' => 'مؤسسة النخبة للتطوير العقاري',
                'commercial_registration' => '2020678901',
                'phone' => '0123456789',
                'email' => 'contact@elite-realestate.com',
                'address' => 'شارع الأمير محمد بن عبدالعزيز، جدة',
                'is_active' => true,
            ],
            [
                'name' => 'مكتب الشرق العقاري',
                'company_name' => 'شركة الشرق للاستثمار التجاري',
                'commercial_registration' => '3030789012',
                'phone' => '0134567890',
                'email' => 'info@sharq-properties.com',
                'address' => 'طريق الملك عبدالعزيز، الدمام',
                'is_active' => true,
            ],
        ];

        foreach ($landlords as $landlordData) {
            Landlord::firstOrCreate(['email' => $landlordData['email']], $landlordData);
        }

        // إضافة مباني إضافية
        $allLandlords = Landlord::all();
        $allDistricts = District::all();

        $buildings = [
            [
                'name' => 'مجمع الأندلس التجاري',
                'district_id' => $allDistricts->where('name', 'الملز')->first()?->id,
                'landlord_id' => $allLandlords->where('name', 'مكتب الأندلس العقاري')->first()?->id,
                'building_number' => 'A-101',
                'address' => 'شارع الملز الرئيسي، مقابل مجمع المملكة',
                'floors_count' => 3,
                'total_shops' => 15,
                'total_area' => 2500.00,
                'construction_year' => 2020,
                'description' => 'مجمع تجاري حديث في موقع مميز',
                'is_active' => true,
            ],
            [
                'name' => 'برج النخبة التجاري',
                'district_id' => $allDistricts->where('name', 'الحمراء')->first()?->id,
                'landlord_id' => $allLandlords->where('name', 'مكتب النخبة العقاري')->first()?->id,
                'building_number' => 'B-205',
                'address' => 'طريق الحمراء، بجوار مول العرب',
                'floors_count' => 4,
                'total_shops' => 20,
                'total_area' => 3200.00,
                'construction_year' => 2019,
                'description' => 'برج تجاري متطور بمواصفات عالية',
                'is_active' => true,
            ],
            [
                'name' => 'مركز الشرق التجاري',
                'district_id' => $allDistricts->where('name', 'الفردوس')->first()?->id,
                'landlord_id' => $allLandlords->where('name', 'مكتب الشرق العقاري')->first()?->id,
                'building_number' => 'C-301',
                'address' => 'شارع الأمير محمد بن فهد، الدمام',
                'floors_count' => 2,
                'total_shops' => 12,
                'total_area' => 1800.00,
                'construction_year' => 2021,
                'description' => 'مركز تجاري في المنطقة الشرقية',
                'is_active' => true,
            ],
        ];

        foreach ($buildings as $buildingData) {
            if ($buildingData['district_id'] && $buildingData['landlord_id']) {
                Building::firstOrCreate([
                    'name' => $buildingData['name'],
                    'landlord_id' => $buildingData['landlord_id']
                ], $buildingData);
            }
        }

        // إضافة محلات للمباني الجديدة
        $allBuildings = Building::all();
        
        foreach ($allBuildings as $building) {
            // تحديد عدد المحلات حسب total_shops
            $shopsCount = $building->total_shops ?? 5;
            
            for ($i = 1; $i <= $shopsCount; $i++) {
                $shopData = [
                    'building_id' => $building->id,
                    'shop_number' => sprintf('%s-%03d', $building->building_number ?? 'S', $i),
                    'floor' => ceil($i / 5), // 5 محلات لكل طابق
                    'area' => rand(50, 200),
                    'shop_type' => collect(['retail', 'office', 'restaurant', 'clinic'])->random(),
                    'status' => collect(['vacant', 'occupied', 'maintenance'])->random(),
                    'description' => 'محل تجاري في موقع مميز',
                    'is_active' => true,
                ];

                Shop::firstOrCreate([
                    'building_id' => $building->id,
                    'shop_number' => $shopData['shop_number']
                ], $shopData);
            }
        }

        // إضافة مستأجرين
        $tenants = [
            [
                'name' => 'أحمد محمد السالم',
                'company_name' => 'مؤسسة السالم التجارية',
                'commercial_registration' => '1234567890',
                'phone' => '0501234567',
                'email' => 'ahmed.salem@email.com',
                'national_id' => '1234567890',
                'address' => 'حي النخيل، الرياض',
                'emergency_contact' => 'محمد السالم',
                'emergency_phone' => '0509876543',
                'is_active' => true,
            ],
            [
                'name' => 'فاطمة علي الحارثي',
                'company_name' => 'مجموعة الحارثي للأزياء',
                'commercial_registration' => '2345678901',
                'phone' => '0512345678',
                'email' => 'fatma.alharthy@email.com',
                'national_id' => '2345678901',
                'address' => 'حي الروضة، جدة',
                'emergency_contact' => 'علي الحارثي',
                'emergency_phone' => '0518765432',
                'is_active' => true,
            ],
            [
                'name' => 'خالد عبدالله الزهراني',
                'company_name' => 'شركة الزهراني للإلكترونيات',
                'commercial_registration' => '3456789012',
                'phone' => '0523456789',
                'email' => 'khalid.zahrani@email.com',
                'national_id' => '3456789012',
                'address' => 'حي الفردوس، الدمام',
                'emergency_contact' => 'عبدالله الزهراني',
                'emergency_phone' => '0527654321',
                'is_active' => true,
            ],
            [
                'name' => 'نورا سعد القحطاني',
                'company_name' => 'مطعم نورا للمأكولات الشعبية',
                'commercial_registration' => '4567890123',
                'phone' => '0534567890',
                'email' => 'nora.qahtani@email.com',
                'national_id' => '4567890123',
                'address' => 'حي السويدي، الرياض',
                'emergency_contact' => 'سعد القحطاني',
                'emergency_phone' => '0536543210',
                'is_active' => true,
            ],
            [
                'name' => 'عبدالرحمن يوسف الغامدي',
                'company_name' => 'عيادة الغامدي لطب الأسنان',
                'commercial_registration' => '5678901234',
                'phone' => '0545678901',
                'email' => 'abdulrahman.ghamdi@email.com',
                'national_id' => '5678901234',
                'address' => 'حي الشاطئ، جدة',
                'emergency_contact' => 'يوسف الغامدي',
                'emergency_phone' => '0545432109',
                'is_active' => true,
            ],
        ];

        foreach ($tenants as $tenantData) {
            Tenant::firstOrCreate(['email' => $tenantData['email']], $tenantData);
        }

        // إضافة عقود
        $allTenants = Tenant::all();
        $availableShops = Shop::where('status', 'vacant')->get();

        $contracts = [];
        $contractNumber = 1001;

        foreach ($allTenants as $index => $tenant) {
            if ($index < $availableShops->count()) {
                $shop = $availableShops[$index];
                $startDate = Carbon::now()->subMonths(rand(1, 12));
                $endDate = $startDate->copy()->addYear();
                $monthlyRent = rand(3000, 15000); // إيجار شهري عشوائي
                $annualRent = $monthlyRent * 12;
                
                $contractData = [
                    'shop_id' => $shop->id,
                    'tenant_id' => $tenant->id,
                    'contract_number' => 'C-' . $contractNumber++,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'duration_months' => 12,
                    'annual_rent' => $annualRent,
                    'payment_amount' => $monthlyRent,
                    'payment_frequency' => 'monthly',
                    'tax_rate' => 15.00,
                    'tax_amount' => $annualRent * 0.15,
                    'fixed_amounts' => 0,
                    'total_annual_amount' => $annualRent + ($annualRent * 0.15),
                    'status' => 'active',
                    'terms' => 'العقد صالح لمدة سنة واحدة قابلة للتجديد',
                ];

                $contract = Contract::create($contractData);
                $contracts[] = $contract;
                
                // تحديث حالة المحل إلى مؤجر
                $shop->update(['status' => 'occupied']);
            }
        }

        // إضافة مدفوعات للعقود
        foreach ($contracts as $contract) {
            $startDate = Carbon::parse($contract->start_date);
            $endDate = Carbon::parse($contract->end_date);
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate) && $currentDate->lte(Carbon::now())) {
                $paymentStatus = $currentDate->lt(Carbon::now()->subMonth()) ? 'paid' : 
                                ($currentDate->lt(Carbon::now()) ? 'pending' : 'pending');
                
                $paymentDate = $paymentStatus === 'paid' ? $currentDate->copy()->addDays(rand(1, 5)) : null;
                $paidAmount = $paymentStatus === 'paid' ? $contract->payment_amount : 0;
                $remainingAmount = $contract->payment_amount - $paidAmount;
                
                Payment::create([
                    'contract_id' => $contract->id,
                    'invoice_number' => 'INV-' . $contract->id . '-' . $currentDate->format('Ym'),
                    'invoice_date' => $currentDate->copy(),
                    'invoice_amount' => $contract->payment_amount,
                    'paid_amount' => $paidAmount,
                    'remaining_amount' => $remainingAmount,
                    'due_date' => $currentDate->copy(),
                    'payment_date' => $paymentDate,
                    'payment_method' => $paymentStatus === 'paid' ? collect(['bank_transfer', 'cash', 'check'])->random() : null,
                    'status' => $paymentStatus,
                    'notes' => $paymentStatus === 'paid' ? 'تم الدفع في الموعد المحدد' : null,
                    'month' => $currentDate->month,
                    'year' => $currentDate->year,
                ]);
                
                $currentDate->addMonth();
            }
        }

        $this->command->info('تم إنشاء البيانات التجريبية بنجاح!');
        $this->command->info('المستأجرين: ' . Tenant::count());
        $this->command->info('العقود: ' . Contract::count());
        $this->command->info('المدفوعات: ' . Payment::count());
        $this->command->info('إجمالي المحلات: ' . Shop::count());
        $this->command->info('إجمالي المباني: ' . Building::count());
    }
}
