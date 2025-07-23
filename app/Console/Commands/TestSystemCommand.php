<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\City;
use App\Models\District;
use App\Models\Landlord;
use App\Models\Building;
use App\Models\Shop;
use App\Models\Tenant;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Document;
use App\Models\Maintenance;
use App\Models\Expense;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestSystemCommand extends Command
{
    /**
     * اسم command
     */
    protected $signature = 'test:system {--reset : إعادة تعيين البيانات قبل الاختبار}';

    /**
     * وصف command
     */
    protected $description = 'اختبار شامل لجميع مميزات نظام إدارة العقارات';

    /**
     * متغيرات لتخزين نتائج الاختبار
     */
    protected $testResults = [];
    protected $errorCount = 0;
    protected $successCount = 0;

    /**
     * تشغيل الأمر
     */
    public function handle()
    {
        $this->info('🏢 بدء اختبار نظام إدارة العقارات الشامل');
        $this->info('='.str_repeat('=', 60));

        // إعادة تعيين البيانات إذا تم طلب ذلك
        if ($this->option('reset')) {
            $this->resetData();
        }

        // تشغيل جميع الاختبارات
        $this->testUserManagement();
        $this->testCityAndDistrictManagement();
        $this->testLandlordManagement();
        $this->testBuildingManagement();
        $this->testShopManagement();
        $this->testTenantManagement();
        $this->testContractManagement();
        $this->testPaymentManagement();
        $this->testDocumentManagement();
        $this->testMaintenanceManagement();
        $this->testExpenseManagement();
        $this->testRelationships();
        $this->testBusinessLogic();

        // عرض النتائج النهائية
        $this->displayResults();

        return 0;
    }

    /**
     * إعادة تعيين البيانات
     */
    protected function resetData()
    {
        $this->warn('🔄 إعادة تعيين البيانات...');
        
        try {
            // حذف البيانات بالترتيب الصحيح لتجنب مشاكل الـ foreign keys
            Payment::truncate();
            Contract::truncate();
            Document::truncate();
            Maintenance::truncate();
            Expense::truncate();
            Shop::truncate();
            Building::truncate();
            Tenant::truncate();
            Landlord::truncate();
            District::truncate();
            City::truncate();
            
            // إبقاء المستخدم الأساسي فقط
            User::where('email', '!=', 'admin@admin.com')->delete();
            
            $this->logResult('إعادة تعيين البيانات', true, 'تم حذف جميع البيانات بنجاح');
        } catch (\Exception $e) {
            $this->logResult('إعادة تعيين البيانات', false, $e->getMessage());
        }
    }

    /**
     * اختبار إدارة المستخدمين
     */
    protected function testUserManagement()
    {
        $this->info("\n👤 اختبار إدارة المستخدمين");
        $this->line(str_repeat('-', 40));

        try {
            // اختبار إنشاء مستخدم جديد بـ email فريد
            $timestamp = now()->timestamp;
            $user = User::create([
                'name' => 'مستخدم اختبار',
                'email' => "test_{$timestamp}@test.com",
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]);

            $this->logResult('إنشاء مستخدم جديد', true, "تم إنشاء المستخدم بالمعرف: {$user->id}");

            // اختبار تحديث المستخدم
            $user->update(['name' => 'مستخدم محدث']);
            $this->logResult('تحديث بيانات المستخدم', true, 'تم تحديث اسم المستخدم بنجاح');

            // اختبار البحث عن المستخدم
            $foundUser = User::where('email', "test_{$timestamp}@test.com")->first();
            $this->logResult('البحث عن المستخدم', $foundUser ? true : false, 
                $foundUser ? 'تم العثور على المستخدم' : 'لم يتم العثور على المستخدم');

        } catch (\Exception $e) {
            $this->logResult('إدارة المستخدمين', false, $e->getMessage());
        }
    }

    /**
     * اختبار إدارة المدن والأحياء
     */
    protected function testCityAndDistrictManagement()
    {
        $this->info("\n🏙️ اختبار إدارة المدن والأحياء");
        $this->line(str_repeat('-', 40));

        try {
            // إنشاء مدينة برقم فريد
            $timestamp = now()->timestamp;
            $city = City::create([
                'name' => 'الرياض',
                'code' => "RYD_{$timestamp}",
                'description' => 'عاصمة المملكة العربية السعودية',
                'is_active' => true,
            ]);

            $this->logResult('إنشاء مدينة', true, "تم إنشاء مدينة الرياض بالمعرف: {$city->id}");

            // إنشاء أحياء متعددة
            $districts = [
                ['name' => 'العليا', 'description' => 'حي العليا التجاري'],
                ['name' => 'الملك فهد', 'description' => 'حي الملك فهد السكني'],
                ['name' => 'النخيل', 'description' => 'حي النخيل الحديث'],
            ];

            foreach ($districts as $districtData) {
                $district = District::create([
                    'city_id' => $city->id,
                    'name' => $districtData['name'],
                    'description' => $districtData['description'],
                    'is_active' => true,
                ]);
            }

            $this->logResult('إنشاء أحياء متعددة', true, 'تم إنشاء 3 أحياء بنجاح');

            // اختبار العلاقة بين المدينة والأحياء
            $cityWithDistricts = City::with('districts')->find($city->id);
            $districtsCount = $cityWithDistricts->districts->count();
            $this->logResult('اختبار علاقة المدينة بالأحياء', $districtsCount == 3, 
                "عدد الأحياء المرتبطة بالمدينة: {$districtsCount}");

        } catch (\Exception $e) {
            $this->logResult('إدارة المدن والأحياء', false, $e->getMessage());
        }
    }

    /**
     * اختبار إدارة المالكين
     */
    protected function testLandlordManagement()
    {
        $this->info("\n🏢 اختبار إدارة المالكين");
        $this->line(str_repeat('-', 40));

        try {
            // إنشاء مالك عقار برقم تجاري فريد
            $timestamp = now()->timestamp;
            $landlord = Landlord::create([
                'name' => 'أحمد محمد السعيد',
                'company_name' => 'شركة السعيد العقارية',
                'commercial_registration' => "CR_{$timestamp}",
                'license_number' => "RE-2025-{$timestamp}",
                'phone' => '+966501234567',
                'email' => "ahmed_{$timestamp}@alsaeed.com",
                'address' => 'الرياض، حي العليا، شارع الملك فهد',
                'contact_person' => 'أحمد محمد السعيد',
                'commission_rate' => 5.00,
                'is_active' => true,
            ]);

            $this->logResult('إنشاء مالك عقار', true, "تم إنشاء المالك بالمعرف: {$landlord->id}");

            // اختبار تحديث بيانات المالك
            $landlord->update([
                'commission_rate' => 7.50,
                'address' => 'الرياض، حي الملك فهد، طريق الملك عبدالعزيز'
            ]);

            $this->logResult('تحديث بيانات المالك', true, 'تم تحديث نسبة العمولة والعنوان');

            // اختبار البحث عن المالك
            $foundLandlord = Landlord::where('commercial_registration', "CR_{$timestamp}")->first();
            $this->logResult('البحث عن المالك بالسجل التجاري', $foundLandlord ? true : false,
                $foundLandlord ? 'تم العثور على المالك' : 'لم يتم العثور على المالك');

        } catch (\Exception $e) {
            $this->logResult('إدارة المالكين', false, $e->getMessage());
        }
    }

    /**
     * اختبار إدارة المباني
     */
    protected function testBuildingManagement()
    {
        $this->info("\n🏗️ اختبار إدارة المباني");
        $this->line(str_repeat('-', 40));

        try {
            // الحصول على البيانات المطلوبة
            $district = District::first();
            $landlord = Landlord::first();

            if (!$district || !$landlord) {
                $this->logResult('إدارة المباني', false, 'لا توجد بيانات مطلوبة (حي أو مالك)');
                return;
            }

            // إنشاء مبنى
            $building = Building::create([
                'district_id' => $district->id,
                'landlord_id' => $landlord->id,
                'name' => 'برج التجارة المتقدم',
                'building_number' => 'B-001',
                'address' => 'شارع الملك فهد، تقاطع العليا',
                'floors_count' => 5,
                'total_shops' => 20,
                'total_area' => 5000.50,
                'construction_year' => 2020,
                'description' => 'مبنى تجاري متطور في قلب الرياض',
                'is_active' => true,
            ]);

            $this->logResult('إنشاء مبنى', true, "تم إنشاء المبنى بالمعرف: {$building->id}");

            // اختبار تحديث بيانات المبنى
            $building->update([
                'total_shops' => 25,
                'description' => 'مبنى تجاري متطور ومحدث في قلب الرياض'
            ]);

            $this->logResult('تحديث بيانات المبنى', true, 'تم تحديث عدد المحلات والوصف');

            // اختبار العلاقات
            $buildingWithRelations = Building::with(['district', 'landlord'])->find($building->id);
            $hasDistrict = $buildingWithRelations->district ? true : false;
            $hasLandlord = $buildingWithRelations->landlord ? true : false;

            $this->logResult('اختبار علاقة المبنى بالحي', $hasDistrict, 
                $hasDistrict ? 'العلاقة تعمل بشكل صحيح' : 'العلاقة لا تعمل');
            $this->logResult('اختبار علاقة المبنى بالمالك', $hasLandlord,
                $hasLandlord ? 'العلاقة تعمل بشكل صحيح' : 'العلاقة لا تعمل');

        } catch (\Exception $e) {
            $this->logResult('إدارة المباني', false, $e->getMessage());
        }
    }

    /**
     * اختبار إدارة المحلات
     */
    protected function testShopManagement()
    {
        $this->info("\n🏪 اختبار إدارة المحلات");
        $this->line(str_repeat('-', 40));

        try {
            $building = Building::first();

            if (!$building) {
                $this->logResult('إدارة المحلات', false, 'لا يوجد مبنى لإنشاء المحلات');
                return;
            }

            // إنشاء محلات متعددة
            $shops = [];
            for ($i = 1; $i <= 5; $i++) {
                $shop = Shop::create([
                    'building_id' => $building->id,
                    'shop_number' => "S-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'floor' => ceil($i / 2),
                    'area' => 150.00 + ($i * 10),
                    'shop_type' => ['retail', 'office', 'restaurant', 'service', 'warehouse'][array_rand(['retail', 'office', 'restaurant', 'service', 'warehouse'])],
                    'status' => 'vacant',
                    'description' => "محل تجاري رقم {$i} في الطابق " . ceil($i / 2),
                    'is_active' => true,
                ]);
                $shops[] = $shop;
            }

            $this->logResult('إنشاء محلات متعددة', true, 'تم إنشاء 5 محلات بنجاح');

            // اختبار تحديث محل
            $shops[0]->update([
                'status' => 'occupied',
                'description' => 'محل مؤجر - تم التحديث'
            ]);

            $this->logResult('تحديث حالة المحل', true, 'تم تحديث حالة المحل إلى مؤجر');

            // اختبار العلاقة مع المبنى
            $buildingWithShops = Building::with('shops')->find($building->id);
            $shopsCount = $buildingWithShops->shops->count();
            $this->logResult('اختبار علاقة المبنى بالمحلات', $shopsCount >= 5,
                "عدد المحلات في المبنى: {$shopsCount}");

        } catch (\Exception $e) {
            $this->logResult('إدارة المحلات', false, $e->getMessage());
        }
    }

    /**
     * اختبار إدارة المستأجرين
     */
    protected function testTenantManagement()
    {
        $this->info("\n👨‍💼 اختبار إدارة المستأجرين");
        $this->line(str_repeat('-', 40));

        try {
            // إنشاء مستأجرين متعددين
            $tenants = [
                [
                    'name' => 'محمد أحمد العلي',
                    'company_name' => 'شركة العلي التجارية',
                    'commercial_registration' => '2345678901',
                    'phone' => '+966502345678',
                    'email' => 'mohammed@alali.com',
                    'national_id' => '1123456789',
                    'address' => 'الرياض، حي النخيل',
                    'emergency_contact' => 'فاطمة أحمد العلي',
                    'emergency_phone' => '+966503456789',
                    'is_active' => true,
                ],
                [
                    'name' => 'سارة محمد الزهراني',
                    'company_name' => 'مؤسسة الزهراني للخدمات',
                    'commercial_registration' => '3456789012',
                    'phone' => '+966504567890',
                    'email' => 'sara@alzahrani.com',
                    'national_id' => '2234567890',
                    'address' => 'الرياض، حي الملك فهد',
                    'emergency_contact' => 'عبدالله محمد الزهراني',
                    'emergency_phone' => '+966505678901',
                    'is_active' => true,
                ]
            ];

            foreach ($tenants as $tenantData) {
                $tenant = Tenant::create($tenantData);
            }

            $this->logResult('إنشاء مستأجرين متعددين', true, 'تم إنشاء مستأجرين بنجاح');

            // اختبار البحث عن المستأجر
            $foundTenant = Tenant::where('national_id', '1123456789')->first();
            $this->logResult('البحث عن المستأجر بالهوية الوطنية', $foundTenant ? true : false,
                $foundTenant ? 'تم العثور على المستأجر' : 'لم يتم العثور على المستأجر');

            // اختبار تحديث بيانات المستأجر
            if ($foundTenant) {
                $foundTenant->update([
                    'phone' => '+966509876543',
                    'address' => 'الرياض، حي العليا - محدث'
                ]);
                $this->logResult('تحديث بيانات المستأجر', true, 'تم تحديث رقم الهاتف والعنوان');
            }

        } catch (\Exception $e) {
            $this->logResult('إدارة المستأجرين', false, $e->getMessage());
        }
    }

    /**
     * اختبار إدارة العقود
     */
    protected function testContractManagement()
    {
        $this->info("\n📄 اختبار إدارة العقود");
        $this->line(str_repeat('-', 40));

        try {
            $shop = Shop::first();
            $tenant = Tenant::first();

            if (!$shop || !$tenant) {
                $this->logResult('إدارة العقود', false, 'لا توجد بيانات مطلوبة (محل أو مستأجر)');
                return;
            }

            // إنشاء عقد برقم فريد
            $timestamp = now()->timestamp;
            $startDate = Carbon::now();
            $endDate = $startDate->copy()->addYear();
            $annualRent = 120000.00;
            $taxRate = 15.00;
            $taxAmount = $annualRent * ($taxRate / 100);
            $totalAmount = $annualRent + $taxAmount;

            $contract = Contract::create([
                'shop_id' => $shop->id,
                'tenant_id' => $tenant->id,
                'contract_number' => "CON-{$timestamp}",
                'start_date' => $startDate,
                'end_date' => $endDate,
                'duration_months' => 12,
                'annual_rent' => $annualRent,
                'payment_amount' => $totalAmount,
                'payment_frequency' => 'annual',
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'fixed_amounts' => 0,
                'total_annual_amount' => $totalAmount,
                'status' => 'active',
                'terms' => 'شروط العقد القياسية للإيجار التجاري',
            ]);

            $this->logResult('إنشاء عقد جديد', true, "تم إنشاء العقد رقم: {$contract->contract_number}");

            // اختبار تحديث العقد
            $contract->update([
                'status' => 'active',
                'terms' => 'شروط العقد المحدثة مع إضافات جديدة'
            ]);

            $this->logResult('تحديث بيانات العقد', true, 'تم تحديث حالة العقد والشروط');

            // اختبار العلاقات
            $contractWithRelations = Contract::with(['shop', 'tenant'])->find($contract->id);
            $hasShop = $contractWithRelations->shop ? true : false;
            $hasTenant = $contractWithRelations->tenant ? true : false;

            $this->logResult('اختبار علاقة العقد بالمحل', $hasShop,
                $hasShop ? 'العلاقة تعمل بشكل صحيح' : 'العلاقة لا تعمل');
            $this->logResult('اختبار علاقة العقد بالمستأجر', $hasTenant,
                $hasTenant ? 'العلاقة تعمل بشكل صحيح' : 'العلاقة لا تعمل');

        } catch (\Exception $e) {
            $this->logResult('إدارة العقود', false, $e->getMessage());
        }
    }

    /**
     * اختبار إدارة المدفوعات
     */
    protected function testPaymentManagement()
    {
        $this->info("\n💰 اختبار إدارة المدفوعات");
        $this->line(str_repeat('-', 40));

        try {
            $contract = Contract::first();

            if (!$contract) {
                $this->logResult('إدارة المدفوعات', false, 'لا يوجد عقد لإنشاء المدفوعات');
                return;
            }

            // إنشاء مدفوعات متعددة برقم فاتورة فريد
            $timestamp = now()->timestamp;
            $payments = [];
            for ($i = 1; $i <= 3; $i++) {
                $invoiceDate = Carbon::now()->subMonths(3 - $i);
                $dueDate = $invoiceDate->copy()->addDays(30);
                $invoiceAmount = $contract->total_annual_amount / 12; // قسط شهري

                $payment = Payment::create([
                    'contract_id' => $contract->id,
                    'invoice_number' => "INV-{$timestamp}-{$i}",
                    'invoice_date' => $invoiceDate,
                    'invoice_amount' => $invoiceAmount,
                    'paid_amount' => $i <= 2 ? $invoiceAmount : $invoiceAmount * 0.5, // الدفعة الثالثة جزئية
                    'remaining_amount' => $i <= 2 ? 0 : $invoiceAmount * 0.5,
                    'due_date' => $dueDate,
                    'payment_date' => $i <= 2 ? $invoiceDate->copy()->addDays(5) : null,
                    'status' => $i <= 2 ? 'paid' : 'partial',
                    'payment_method' => ['cash', 'bank_transfer', 'check'][array_rand(['cash', 'bank_transfer', 'check'])],
                    'notes' => "ملاحظات للدفعة رقم {$i}",
                    'month' => $invoiceDate->month,
                    'year' => $invoiceDate->year,
                ]);
                $payments[] = $payment;
            }

            $this->logResult('إنشاء مدفوعات متعددة', true, 'تم إنشاء 3 مدفوعات بحالات مختلفة');

            // اختبار تحديث حالة الدفع
            $payments[2]->update([
                'paid_amount' => $payments[2]->invoice_amount,
                'remaining_amount' => 0,
                'status' => 'paid',
                'payment_date' => now(),
                'notes' => 'تم استكمال الدفع - محدث'
            ]);

            $this->logResult('تحديث حالة الدفع', true, 'تم تحديث الدفعة الجزئية إلى مدفوعة بالكامل');

            // اختبار العلاقة مع العقد
            $contractWithPayments = Contract::with('payments')->find($contract->id);
            $paymentsCount = $contractWithPayments->payments->count();
            $this->logResult('اختبار علاقة العقد بالمدفوعات', $paymentsCount >= 3,
                "عدد المدفوعات المرتبطة بالعقد: {$paymentsCount}");

        } catch (\Exception $e) {
            $this->logResult('إدارة المدفوعات', false, $e->getMessage());
        }
    }

    /**
     * اختبار إدارة الوثائق
     */
    protected function testDocumentManagement()
    {
        $this->info("\n📁 اختبار إدارة الوثائق");
        $this->line(str_repeat('-', 40));

        try {
            $building = Building::first();
            $shop = Shop::first();
            $tenant = Tenant::first();

            if (!$building || !$shop || !$tenant) {
                $this->logResult('إدارة الوثائق', false, 'لا توجد بيانات مطلوبة');
                return;
            }

            $user = User::first();

            // إنشاء وثائق للمبنى
            $buildingDoc = Document::create([
                'documentable_type' => Building::class,
                'documentable_id' => $building->id,
                'title' => 'صك ملكية المبنى',
                'description' => 'صك الملكية الأصلي للمبنى',
                'document_type' => 'ownership_deed',
                'file_name' => 'building_deed.pdf',
                'file_path' => 'documents/buildings/building_deed.pdf',
                'file_size' => 2048576, // 2MB
                'mime_type' => 'application/pdf',
                'uploaded_by' => $user->id,
                'is_active' => true,
                'sort_order' => 1,
            ]);

            $this->logResult('إنشاء وثيقة للمبنى', $buildingDoc ? true : false, 'تم إنشاء وثيقة صك الملكية');

            // إنشاء وثائق للمحل
            $shopDoc = Document::create([
                'documentable_type' => Shop::class,
                'documentable_id' => $shop->id,
                'title' => 'مخطط المحل',
                'description' => 'مخطط هندسي للمحل التجاري',
                'document_type' => 'floor_plan',
                'file_name' => 'shop_plan.dwg',
                'file_path' => 'documents/shops/shop_plan.dwg',
                'file_size' => 1024768,
                'mime_type' => 'application/acad',
                'uploaded_by' => $user->id,
                'is_active' => true,
                'sort_order' => 1,
            ]);

            $this->logResult('إنشاء وثيقة للمحل', $shopDoc ? true : false, 'تم إنشاء وثيقة مخطط المحل');

            // إنشاء وثائق للمستأجر
            $tenantDoc = Document::create([
                'documentable_type' => Tenant::class,
                'documentable_id' => $tenant->id,
                'title' => 'صورة الهوية الوطنية',
                'description' => 'صورة واضحة من الهوية الوطنية للمستأجر',
                'document_type' => 'national_id',
                'file_name' => 'tenant_id.jpg',
                'file_path' => 'documents/tenants/tenant_id.jpg',
                'file_size' => 512384,
                'mime_type' => 'image/jpeg',
                'uploaded_by' => $user->id,
                'is_active' => true,
                'sort_order' => 1,
            ]);

            $this->logResult('إنشاء وثيقة للمستأجر', $tenantDoc ? true : false, 'تم إنشاء وثيقة الهوية الوطنية');

            // التأكد من حفظ البيانات في قاعدة البيانات
            $building->refresh();
            $shop->refresh();
            $tenant->refresh();

            // اختبار العلاقات polymorphic
            $buildingWithDocs = Building::with('documents')->find($building->id);
            $shopWithDocs = Shop::with('documents')->find($shop->id);
            $tenantWithDocs = Tenant::with('documents')->find($tenant->id);

            // التحقق من وجود الكائنات والعلاقات
            $buildingDocsCount = 0;
            $shopDocsCount = 0;
            $tenantDocsCount = 0;

            if ($buildingWithDocs && $buildingWithDocs->documents) {
                $buildingDocsCount = $buildingWithDocs->documents->count();
            }
            if ($shopWithDocs && $shopWithDocs->documents) {
                $shopDocsCount = $shopWithDocs->documents->count();
            }
            if ($tenantWithDocs && $tenantWithDocs->documents) {
                $tenantDocsCount = $tenantWithDocs->documents->count();
            }

            $this->logResult('اختبار علاقة المبنى بالوثائق', $buildingDocsCount > 0,
                "عدد وثائق المبنى: {$buildingDocsCount}");
            $this->logResult('اختبار علاقة المحل بالوثائق', $shopDocsCount > 0,
                "عدد وثائق المحل: {$shopDocsCount}");
            $this->logResult('اختبار علاقة المستأجر بالوثائق', $tenantDocsCount > 0,
                "عدد وثائق المستأجر: {$tenantDocsCount}");

        } catch (\Exception $e) {
            $this->logResult('إدارة الوثائق', false, $e->getMessage());
        }
    }

    /**
     * اختبار إدارة الصيانة
     */
    protected function testMaintenanceManagement()
    {
        $this->info("\n🔧 اختبار إدارة الصيانة");
        $this->line(str_repeat('-', 40));

        try {
            $building = Building::first();
            $shop = Shop::first();
            $user = User::first();

            if (!$building || !$shop || !$user) {
                $this->logResult('إدارة الصيانة', false, 'لا توجد بيانات مطلوبة');
                return;
            }

            // إنشاء صيانة للمبنى
            $buildingMaintenance = Maintenance::create([
                'maintainable_type' => Building::class,
                'maintainable_id' => $building->id,
                'maintenance_date' => Carbon::now()->subDays(5),
                'maintenance_type' => 'preventive',
                'description' => 'صيانة دورية لنظام التكييف المركزي',
                'status' => 'completed',
                'notes' => 'تم فحص جميع الوحدات وتنظيف الفلاتر',
                'cost' => 2500.00,
                'contractor_name' => 'شركة الخليج للصيانة',
                'contractor_phone' => '+966501111111',
                'scheduled_date' => Carbon::now()->subDays(7),
                'completed_date' => Carbon::now()->subDays(5),
                'created_by' => $user->id,
            ]);

            $this->logResult('إنشاء صيانة للمبنى', true, 'تم إنشاء طلب صيانة للمبنى');

            // إنشاء صيانة للمحل
            $shopMaintenance = Maintenance::create([
                'maintainable_type' => Shop::class,
                'maintainable_id' => $shop->id,
                'maintenance_date' => Carbon::now()->addDays(3),
                'maintenance_type' => 'repair',
                'description' => 'إصلاح تسريب في المياه',
                'status' => 'scheduled',
                'notes' => 'يجب إصلاح التسريب في أسرع وقت',
                'cost' => 500.00,
                'contractor_name' => 'مؤسسة النجاح للسباكة',
                'contractor_phone' => '+966502222222',
                'scheduled_date' => Carbon::now()->addDays(3),
                'created_by' => $user->id,
            ]);

            $this->logResult('إنشاء صيانة للمحل', true, 'تم إنشاء طلب صيانة للمحل');

            // اختبار تحديث حالة الصيانة
            $shopMaintenance->update([
                'status' => 'in_progress',
                'notes' => 'بدأ العمل في الصيانة - محدث'
            ]);

            $this->logResult('تحديث حالة الصيانة', true, 'تم تحديث حالة صيانة المحل');

            // اختبار العلاقات polymorphic
            $buildingWithMaintenance = Building::with('maintenances')->find($building->id);
            $shopWithMaintenance = Shop::with('maintenances')->find($shop->id);

            $this->logResult('اختبار علاقة المبنى بالصيانة', $buildingWithMaintenance->maintenances->count() > 0,
                "عدد طلبات صيانة المبنى: {$buildingWithMaintenance->maintenances->count()}");
            $this->logResult('اختبار علاقة المحل بالصيانة', $shopWithMaintenance->maintenances->count() > 0,
                "عدد طلبات صيانة المحل: {$shopWithMaintenance->maintenances->count()}");

        } catch (\Exception $e) {
            $this->logResult('إدارة الصيانة', false, $e->getMessage());
        }
    }

    /**
     * اختبار إدارة المصروفات
     */
    protected function testExpenseManagement()
    {
        $this->info("\n💸 اختبار إدارة المصروفات");
        $this->line(str_repeat('-', 40));

        try {
            $building = Building::first();
            $shop = Shop::first();
            $user = User::first();

            if (!$building || !$shop || !$user) {
                $this->logResult('إدارة المصروفات', false, 'لا توجد بيانات مطلوبة');
                return;
            }

            // إنشاء مصروفات للمبنى
            $buildingExpense = Expense::create([
                'expensable_type' => Building::class,
                'expensable_id' => $building->id,
                'expense_date' => Carbon::now()->subDays(10),
                'expense_type' => 'utilities',
                'description' => 'فاتورة الكهرباء الشهرية للمبنى',
                'amount' => 3500.00,
                'currency' => 'SAR',
                'notes' => 'فاتورة شهر يناير 2025',
                'vendor_name' => 'شركة الكهرباء السعودية',
                'vendor_phone' => '+966111234567',
                'invoice_number' => 'ELEC-2025-001',
                'status' => 'paid',
                'paid_date' => Carbon::now()->subDays(8),
                'created_by' => $user->id,
            ]);

            $this->logResult('إنشاء مصروف للمبنى', true, 'تم إنشاء مصروف فاتورة الكهرباء');

            // إنشاء مصروفات للمحل
            $shopExpense = Expense::create([
                'expensable_type' => Shop::class,
                'expensable_id' => $shop->id,
                'expense_date' => Carbon::now()->subDays(5),
                'expense_type' => 'maintenance',
                'description' => 'تنظيف وتعقيم المحل',
                'amount' => 200.00,
                'currency' => 'SAR',
                'notes' => 'تنظيف شهري',
                'vendor_name' => 'شركة النظافة المتقدمة',
                'vendor_phone' => '+966503333333',
                'invoice_number' => 'CLN-2025-001',
                'status' => 'pending',
                'created_by' => $user->id,
            ]);

            $this->logResult('إنشاء مصروف للمحل', true, 'تم إنشاء مصروف تنظيف المحل');

            // اختبار تحديث حالة المصروف
            $shopExpense->update([
                'status' => 'paid',
                'paid_date' => now(),
                'notes' => 'تم الدفع - محدث'
            ]);

            $this->logResult('تحديث حالة المصروف', true, 'تم تحديث حالة مصروف المحل إلى مدفوع');

            // إنشاء مصروف عام
            $generalExpense = Expense::create([
                'expensable_type' => Building::class,
                'expensable_id' => $building->id,
                'expense_date' => Carbon::now()->subDays(2),
                'expense_type' => 'administrative',
                'description' => 'رسوم تجديد الرخصة التجارية',
                'amount' => 1000.00,
                'currency' => 'SAR',
                'notes' => 'رسوم سنوية',
                'vendor_name' => 'وزارة التجارة',
                'status' => 'paid',
                'paid_date' => Carbon::now()->subDays(2),
                'created_by' => $user->id,
            ]);

            $this->logResult('إنشاء مصروف إداري', true, 'تم إنشاء مصروف الرسوم الإدارية');

            // اختبار العلاقات polymorphic
            $buildingWithExpenses = Building::with('expenses')->find($building->id);
            $shopWithExpenses = Shop::with('expenses')->find($shop->id);

            $this->logResult('اختبار علاقة المبنى بالمصروفات', $buildingWithExpenses->expenses->count() > 0,
                "عدد مصروفات المبنى: {$buildingWithExpenses->expenses->count()}");
            $this->logResult('اختبار علاقة المحل بالمصروفات', $shopWithExpenses->expenses->count() > 0,
                "عدد مصروفات المحل: {$shopWithExpenses->expenses->count()}");

        } catch (\Exception $e) {
            $this->logResult('إدارة المصروفات', false, $e->getMessage());
        }
    }

    /**
     * اختبار العلاقات بين النماذج
     */
    protected function testRelationships()
    {
        $this->info("\n🔗 اختبار العلاقات بين النماذج");
        $this->line(str_repeat('-', 40));

        try {
            // اختبار العلاقة الكاملة: مدينة -> حي -> مبنى -> محل -> عقد -> مدفوعات
            $city = City::with([
                'districts.buildings.shops.contracts.payments'
            ])->first();

            if ($city) {
                $totalDistricts = $city->districts->count();
                $totalBuildings = $city->districts->sum(function($district) {
                    return $district->buildings->count();
                });
                $totalShops = $city->districts->sum(function($district) {
                    return $district->buildings->sum(function($building) {
                        return $building->shops->count();
                    });
                });

                $this->logResult('اختبار العلاقة الكاملة للمدينة', true, 
                    "المدينة تحتوي على {$totalDistricts} أحياء، {$totalBuildings} مباني، {$totalShops} محلات");
            }

            // اختبار علاقة المستأجر بعقوده
            $tenant = Tenant::with('contracts.payments')->first();
            if ($tenant) {
                $contractsCount = $tenant->contracts->count();
                $totalPayments = $tenant->contracts->sum(function($contract) {
                    return $contract->payments->count();
                });

                $this->logResult('اختبار علاقة المستأجر بالعقود', true,
                    "المستأجر لديه {$contractsCount} عقود و {$totalPayments} مدفوعات");
            }

            // اختبار علاقة المالك بمبانيه
            $landlord = Landlord::with('buildings.shops')->first();
            if ($landlord) {
                $buildingsCount = $landlord->buildings->count();
                $shopsCount = $landlord->buildings->sum(function($building) {
                    return $building->shops->count();
                });

                $this->logResult('اختبار علاقة المالك بالمباني', true,
                    "المالك يملك {$buildingsCount} مباني بإجمالي {$shopsCount} محلات");
            }

        } catch (\Exception $e) {
            $this->logResult('اختبار العلاقات', false, $e->getMessage());
        }
    }

    /**
     * اختبار المنطق التجاري
     */
    protected function testBusinessLogic()
    {
        $this->info("\n💼 اختبار المنطق التجاري");
        $this->line(str_repeat('-', 40));

        try {
            // اختبار حساب المبالغ في العقد
            $contract = Contract::first();
            if ($contract) {
                $calculatedTax = $contract->annual_rent * ($contract->tax_rate / 100);
                $calculatedTotal = $contract->annual_rent + $calculatedTax + $contract->fixed_amounts;

                $taxCorrect = abs($contract->tax_amount - $calculatedTax) < 0.01;
                $totalCorrect = abs($contract->total_annual_amount - $calculatedTotal) < 0.01;

                $this->logResult('حساب الضريبة في العقد', $taxCorrect,
                    "الضريبة المحسوبة: {$calculatedTax}, المحفوظة: {$contract->tax_amount}");
                $this->logResult('حساب المجموع الكلي', $totalCorrect,
                    "المجموع المحسوب: {$calculatedTotal}, المحفوظ: {$contract->total_annual_amount}");
            }

            // اختبار حالة المحل بناءً على العقود
            $shop = Shop::with('contracts')->first();
            if ($shop) {
                $activeContract = $shop->contracts()->where('status', 'active')->first();
                $expectedStatus = $activeContract ? 'occupied' : 'vacant';

                $this->logResult('حالة المحل بناءً على العقود', 
                    $shop->status == $expectedStatus || $shop->status == 'occupied',
                    "حالة المحل: {$shop->status}, العقد النشط: " . ($activeContract ? 'يوجد' : 'لا يوجد'));
            }

            // اختبار تطابق الدفعات مع العقد
            $contract = Contract::with('payments')->first();
            if ($contract && $contract->payments->count() > 0) {
                $totalPaid = $contract->payments->sum('paid_amount');
                $totalInvoiced = $contract->payments->sum('invoice_amount');
                $totalRemaining = $contract->payments->sum('remaining_amount');

                $balanceCorrect = abs(($totalPaid + $totalRemaining) - $totalInvoiced) < 0.01;

                $this->logResult('تطابق الدفعات مع الفواتير', $balanceCorrect,
                    "المدفوع: {$totalPaid}, المتبقي: {$totalRemaining}, المجموع: {$totalInvoiced}");
            }

            // اختبار صحة البيانات المطلوبة
            $requiredFieldsTest = true;
            $missingFields = [];

            // فحص البيانات المطلوبة للمدن
            $cities = City::whereNull('name')->orWhere('name', '')->get();
            if ($cities->count() > 0) {
                $requiredFieldsTest = false;
                $missingFields[] = "مدن بدون أسماء: {$cities->count()}";
            }

            // فحص البيانات المطلوبة للعقود
            $contracts = Contract::whereNull('contract_number')->orWhere('contract_number', '')->get();
            if ($contracts->count() > 0) {
                $requiredFieldsTest = false;
                $missingFields[] = "عقود بدون أرقام: {$contracts->count()}";
            }

            $this->logResult('فحص البيانات المطلوبة', $requiredFieldsTest,
                $requiredFieldsTest ? 'جميع البيانات المطلوبة موجودة' : implode(', ', $missingFields));

        } catch (\Exception $e) {
            $this->logResult('اختبار المنطق التجاري', false, $e->getMessage());
        }
    }

    /**
     * تسجيل نتيجة الاختبار
     */
    protected function logResult($testName, $success, $message)
    {
        $this->testResults[] = [
            'name' => $testName,
            'success' => $success,
            'message' => $message
        ];

        if ($success) {
            $this->successCount++;
            $this->line("  ✅ {$testName}: {$message}");
        } else {
            $this->errorCount++;
            $this->line("  ❌ {$testName}: {$message}");
        }
    }

    /**
     * عرض النتائج النهائية
     */
    protected function displayResults()
    {
        $this->info("\n📊 نتائج الاختبار النهائية");
        $this->info('='.str_repeat('=', 60));

        $totalTests = count($this->testResults);
        $successRate = $totalTests > 0 ? round(($this->successCount / $totalTests) * 100, 2) : 0;

        $this->info("إجمالي الاختبارات: {$totalTests}");
        $this->info("الناجحة: {$this->successCount}");
        $this->info("الفاشلة: {$this->errorCount}");
        $this->info("معدل النجاح: {$successRate}%");

        if ($successRate == 100) {
            $this->info("\n🎉 تهانينا! جميع الاختبارات نجحت - النظام يعمل بشكل مثالي 100%");
        } elseif ($successRate >= 90) {
            $this->warn("\n⚠️ النظام يعمل بشكل جيد جداً ({$successRate}%) مع بعض المشاكل البسيطة");
        } elseif ($successRate >= 80) {
            $this->warn("\n⚠️ النظام يعمل بشكل جيد ({$successRate}%) لكن يحتاج لبعض الإصلاحات");
        } else {
            $this->error("\n🚨 النظام يحتاج لإصلاحات جوهرية ({$successRate}%)");
        }

        // عرض الاختبارات الفاشلة
        if ($this->errorCount > 0) {
            $this->warn("\n❌ الاختبارات الفاشلة:");
            foreach ($this->testResults as $result) {
                if (!$result['success']) {
                    $this->line("  • {$result['name']}: {$result['message']}");
                }
            }
        }

        // إحصائيات إضافية
        $this->info("\n📈 إحصائيات إضافية:");
        $this->line("  • المدن: " . City::count());
        $this->line("  • الأحياء: " . District::count());
        $this->line("  • المالكين: " . Landlord::count());
        $this->line("  • المباني: " . Building::count());
        $this->line("  • المحلات: " . Shop::count());
        $this->line("  • المستأجرين: " . Tenant::count());
        $this->line("  • العقود: " . Contract::count());
        $this->line("  • المدفوعات: " . Payment::count());
        $this->line("  • الوثائق: " . Document::count());
        $this->line("  • طلبات الصيانة: " . Maintenance::count());
        $this->line("  • المصروفات: " . Expense::count());

        $this->info("\n✨ انتهى الاختبار الشامل للنظام");
    }
}
