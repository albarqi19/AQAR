<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\City;
use App\Models\Contract;
use App\Models\District;
use App\Models\Landlord;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompleteDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على البيانات الأساسية
        $riyadh = City::where('name', 'الرياض')->first();
        $jeddah = City::where('name', 'جدة')->first();
        
        $olaya = District::where('name', 'العليا')->first();
        $malaz = District::where('name', 'الملز')->first();
        $rawda = District::where('name', 'الروضة')->first();

        // إضافة مكاتب عقارية إضافية
        $landlord1 = Landlord::where('name', 'مكتب العقار الذهبي')->first();
        
        $landlord2 = Landlord::firstOrCreate(
            ['commercial_registration' => '1010234567'],
            [
                'name' => 'مكتب الرياض للاستثمار العقاري',
                'company_name' => 'شركة الرياض للاستثمار العقاري المحدودة',
                'license_number' => 'RE-2024-002',
                'phone' => '0555234567',
                'email' => 'info@riyadh-investment.com',
                'address' => 'الرياض، حي الملز، طريق الملك عبدالعزيز',
                'contact_person' => 'سارة أحمد المطيري',
                'commission_rate' => 4.50,
                'notes' => 'مكتب متخصص في العقارات التجارية',
                'is_active' => true,
            ]
        );

        $landlord3 = Landlord::firstOrCreate(
            ['commercial_registration' => '4010345678'],
            [
                'name' => 'مكتب جدة العقاري المتميز',
                'company_name' => 'مؤسسة جدة العقارية للتطوير',
                'license_number' => 'RE-2024-003',
                'phone' => '0555345678',
                'email' => 'contact@jeddah-properties.com',
                'address' => 'جدة، حي الروضة، شارع التحلية',
                'contact_person' => 'محمد علي الحربي',
                'commission_rate' => 5.50,
                'notes' => 'رائد في مجال العقارات السكنية والتجارية',
                'is_active' => true,
            ]
        );

        // إضافة مباني
        $building1 = Building::create([
            'landlord_id' => $landlord1->id,
            'district_id' => $olaya->id,
            'name' => 'برج العليا التجاري',
            'address' => 'شارع الملك فهد، تقاطع العليا العام',
            'floors_count' => 5,
            'total_shops' => 20,
            'total_area' => 2500.00,
            'construction_year' => 2020,
            'description' => 'برج تجاري حديث في قلب حي العليا',
            'is_active' => true,
        ]);

        $building2 = Building::create([
            'landlord_id' => $landlord2->id,
            'district_id' => $malaz->id,
            'name' => 'مجمع الملز التجاري',
            'address' => 'طريق الملك عبدالعزيز، حي الملز',
            'floors_count' => 3,
            'total_shops' => 15,
            'total_area' => 1800.00,
            'construction_year' => 2019,
            'description' => 'مجمع تجاري في موقع استراتيجي',
            'is_active' => true,
        ]);

        $building3 = Building::create([
            'landlord_id' => $landlord3->id,
            'district_id' => $rawda->id,
            'name' => 'مركز الروضة للأعمال',
            'address' => 'شارع التحلية، حي الروضة',
            'floors_count' => 4,
            'total_shops' => 12,
            'total_area' => 2000.00,
            'construction_year' => 2021,
            'description' => 'مركز أعمال متطور في جدة',
            'is_active' => true,
        ]);

        // إضافة محلات
        $shops = [];
        
        // محلات برج العليا التجاري
        for ($i = 1; $i <= 20; $i++) {
            $shop = Shop::create([
                'building_id' => $building1->id,
                'shop_number' => "A{$i}",
                'floor' => ceil($i / 4),
                'area' => rand(50, 200),
                'shop_type' => $this->getRandomShopType(),
                'status' => $i <= 15 ? 'occupied' : 'vacant',
                'description' => "محل رقم A{$i} في برج العليا التجاري",
                'is_active' => true,
            ]);
            $shops[] = $shop;
        }

        // محلات مجمع الملز التجاري
        for ($i = 1; $i <= 15; $i++) {
            $shop = Shop::create([
                'building_id' => $building2->id,
                'shop_number' => "B{$i}",
                'floor' => ceil($i / 5),
                'area' => rand(40, 150),
                'shop_type' => $this->getRandomShopType(),
                'status' => $i <= 10 ? 'occupied' : 'vacant',
                'description' => "محل رقم B{$i} في مجمع الملز التجاري",
                'is_active' => true,
            ]);
            $shops[] = $shop;
        }

        // محلات مركز الروضة للأعمال
        for ($i = 1; $i <= 12; $i++) {
            $shop = Shop::create([
                'building_id' => $building3->id,
                'shop_number' => "C{$i}",
                'floor' => ceil($i / 3),
                'area' => rand(60, 180),
                'shop_type' => $this->getRandomShopType(),
                'status' => $i <= 8 ? 'occupied' : ($i == 9 ? 'maintenance' : 'vacant'),
                'description' => "محل رقم C{$i} في مركز الروضة للأعمال",
                'is_active' => true,
            ]);
            $shops[] = $shop;
        }

        // إضافة مستأجرين
        $tenants = [];
        
        $tenantNames = [
            'شركة النور للتجارة',
            'مؤسسة الفجر للمقاولات',
            'محل البركة للأقمشة',
            'صيدلية الشفاء',
            'مطعم الذوق الرفيع',
            'مكتبة المعرفة',
            'معرض الأناقة للأزياء',
            'مخبز الطيبات',
            'محل الإلكترونيات الحديثة',
            'مركز اللياقة البدنية',
            'صالون الجمال',
            'عيادة الأسنان المتقدمة',
            'محل الهدايا والتحف',
            'شركة التقنية المتطورة',
            'مطعم البيت الشامي',
            'محل الألعاب والترفيه',
            'مكتب المحاماة والاستشارات',
            'معهد اللغات الدولي',
            'شركة التسويق الرقمي',
            'محل الساعات والمجوهرات',
            'مركز التدريب المهني',
            'شركة الخدمات اللوجستية',
            'محل الأدوات المنزلية',
            'مركز الصحة والعافية',
            'شركة الاستشارات المالية',
            'محل الرياضة واللياقة',
            'مطعم المأكولات البحرية',
            'محل الحقائب والأحذية',
            'مركز التجميل والعناية',
            'شركة التطوير العقاري',
            'محل الأثاث المودرن',
            'مركز الطباعة والتصميم',
            'شركة الحلول التقنية'
        ];

        $phones = [
            '0555111111', '0555222222', '0555333333', '0555444444', '0555555555',
            '0566111111', '0566222222', '0566333333', '0566444444', '0566555555',
            '0544111111', '0544222222', '0544333333', '0544444444', '0544555555'
        ];

        foreach ($tenantNames as $index => $name) {
            $tenant = Tenant::create([
                'name' => $name,
                'company_name' => $name,
                'phone' => $phones[$index % count($phones)],
                'email' => strtolower(str_replace(' ', '', $name)) . '@example.com',
                'address' => $this->getRandomAddress(),
                'commercial_registration' => '10101' . str_pad($index + 1, 5, '0', STR_PAD_LEFT),
                'emergency_contact' => $this->getRandomPersonName(),
                'emergency_phone' => $phones[($index + 5) % count($phones)],
                'is_active' => true,
            ]);
            $tenants[] = $tenant;
        }

        // إضافة عقود للمحلات المؤجرة
        $contractNumber = 1000;
        $occupiedShops = array_filter($shops, fn($shop) => $shop->status === 'occupied');
        
        foreach ($occupiedShops as $index => $shop) {
            if ($index < count($tenants)) {
                $tenant = $tenants[$index];
                $startDate = Carbon::now()->subMonths(rand(1, 12));
                $duration = 12; // سنة واحدة
                $endDate = $startDate->copy()->addMonths($duration)->subDay();
                $annualRent = rand(50000, 200000);
                $taxRate = 15;
                $taxAmount = ($annualRent * $taxRate) / 100;
                $totalAmount = $annualRent + $taxAmount;
                $paymentFrequency = ['monthly', 'quarterly', 'semi_annual', 'annual'][rand(0, 3)];
                
                $divisor = match($paymentFrequency) {
                    'monthly' => 12,
                    'quarterly' => 4,
                    'semi_annual' => 2,
                    default => 1
                };
                
                $contract = Contract::create([
                    'shop_id' => $shop->id,
                    'tenant_id' => $tenant->id,
                    'contract_number' => (string)$contractNumber++,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'duration_months' => $duration,
                    'annual_rent' => $annualRent,
                    'payment_amount' => $totalAmount / $divisor,
                    'payment_frequency' => $paymentFrequency,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'fixed_amounts' => 0,
                    'total_annual_amount' => $totalAmount,
                    'status' => $endDate->isFuture() ? 'active' : 'expired',
                    'terms' => 'عقد إيجار تجاري وفقاً للأنظمة السعودية',
                ]);

                // إضافة مدفوعات للعقد
                $this->createPaymentsForContract($contract);
            }
        }
    }

    private function getRandomShopType(): string
    {
        $types = [
            'مطعم', 'صيدلية', 'محل ملابس', 'محل إلكترونيات', 'صالون تجميل',
            'عيادة', 'مكتب', 'محل أحذية', 'محل هدايا', 'مخبز', 'كافيه',
            'محل رياضة', 'محل أثاث', 'محل ساعات', 'محل كتب', 'محل ألعاب'
        ];
        return $types[array_rand($types)];
    }

    private function getRandomPersonName(): string
    {
        $names = [
            'أحمد محمد العلي', 'فاطمة سعد المطيري', 'محمد عبدالله الحربي',
            'نورا أحمد القحطاني', 'سعد محمد الدوسري', 'هند عبدالرحمن السعد',
            'عبدالعزيز سليمان الراشد', 'مريم محمد الزهراني', 'خالد أحمد المالكي',
            'سارة عبدالله الشمري', 'فهد محمد العتيبي', 'عائشة سعد البقمي'
        ];
        return $names[array_rand($names)];
    }

    private function getRandomAddress(): string
    {
        $addresses = [
            'الرياض، حي العليا، شارع التحلية',
            'جدة، حي الروضة، طريق الكورنيش',
            'الدمام، حي الفيصلية، شارع الملك فهد',
            'الرياض، حي الملز، طريق الملك عبدالعزيز',
            'جدة، حي السلامة، شارع فلسطين',
            'الرياض، حي السليمانية، شارع الأمير محمد',
        ];
        return $addresses[array_rand($addresses)];
    }

    private function createPaymentsForContract(Contract $contract): void
    {
        $paymentCount = match($contract->payment_frequency) {
            'monthly' => 12,
            'quarterly' => 4,
            'semi_annual' => 2,
            default => 1
        };

        $monthsInterval = match($contract->payment_frequency) {
            'monthly' => 1,
            'quarterly' => 3,
            'semi_annual' => 6,
            default => 12
        };

        for ($i = 0; $i < $paymentCount; $i++) {
            $dueDate = $contract->start_date->copy()->addMonths($i * $monthsInterval);
            
            // تحديد حالة السداد (معظمها مدفوعة)
            $isPaid = $dueDate->isPast() && rand(1, 10) <= 8; // 80% مدفوعة
            
            $invoiceAmount = $contract->payment_amount;
            $paidAmount = $isPaid ? $invoiceAmount : 0;
            $remainingAmount = $invoiceAmount - $paidAmount;
            
            Payment::create([
                'contract_id' => $contract->id,
                'invoice_number' => 'INV-' . $contract->contract_number . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'invoice_date' => $dueDate->copy()->subDays(5),
                'invoice_amount' => $invoiceAmount,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'due_date' => $dueDate,
                'payment_date' => $isPaid ? $dueDate->copy()->addDays(rand(-5, 10)) : null,
                'payment_method' => $isPaid ? ['نقدي', 'تحويل بنكي', 'شيك'][rand(0, 2)] : null,
                'status' => $isPaid ? 'paid' : ($dueDate->isPast() ? 'overdue' : 'pending'),
                'notes' => $isPaid ? 'تم السداد في الموعد المحدد' : null,
                'month' => $dueDate->month,
                'year' => $dueDate->year,
            ]);
        }
    }
}
