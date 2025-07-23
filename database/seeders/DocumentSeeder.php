<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Building;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first(); // المستخدم الأول
        $buildings = Building::take(2)->get();
        $shops = Shop::take(3)->get();

        // وثائق المباني
        foreach ($buildings as $building) {
            // صك الملكية
            Document::create([
                'documentable_type' => Building::class,
                'documentable_id' => $building->id,
                'title' => "صك ملكية مبنى {$building->name}",
                'description' => 'وثيقة صك الملكية الخاصة بالمبنى',
                'document_type' => 'deed',
                'file_name' => "deed_{$building->id}.pdf",
                'file_path' => "documents/buildings/deed_{$building->id}.pdf",
                'file_size' => 2048576, // 2MB
                'mime_type' => 'application/pdf',
                'uploaded_by' => $user->id,
                'is_active' => true,
                'sort_order' => 1,
            ]);

            // فحص التربة
            Document::create([
                'documentable_type' => Building::class,
                'documentable_id' => $building->id,
                'title' => "تقرير فحص التربة - {$building->name}",
                'description' => 'تقرير فحص التربة قبل البناء',
                'document_type' => 'soil_test',
                'file_name' => "soil_test_{$building->id}.pdf",
                'file_path' => "documents/buildings/soil_test_{$building->id}.pdf",
                'file_size' => 1024768, // 1MB
                'mime_type' => 'application/pdf',
                'uploaded_by' => $user->id,
                'is_active' => true,
                'sort_order' => 2,
            ]);

            // صورة المبنى
            Document::create([
                'documentable_type' => Building::class,
                'documentable_id' => $building->id,
                'title' => "صورة واجهة المبنى - {$building->name}",
                'description' => 'صورة للواجهة الخارجية للمبنى',
                'document_type' => 'building_image',
                'file_name' => "building_image_{$building->id}.jpg",
                'file_path' => "documents/buildings/building_image_{$building->id}.jpg",
                'file_size' => 3145728, // 3MB
                'mime_type' => 'image/jpeg',
                'uploaded_by' => $user->id,
                'is_active' => true,
                'sort_order' => 3,
            ]);

            // تصميم أوتوكاد
            Document::create([
                'documentable_type' => Building::class,
                'documentable_id' => $building->id,
                'title' => "المخططات الهندسية - {$building->name}",
                'description' => 'المخططات الهندسية للمبنى بصيغة AutoCAD',
                'document_type' => 'autocad_design',
                'file_name' => "autocad_design_{$building->id}.dwg",
                'file_path' => "documents/buildings/autocad_design_{$building->id}.dwg",
                'file_size' => 5242880, // 5MB
                'mime_type' => 'application/octet-stream',
                'uploaded_by' => $user->id,
                'is_active' => true,
                'sort_order' => 4,
            ]);
        }

        // وثائق المحلات
        foreach ($shops as $shop) {
            // صورة المحل
            Document::create([
                'documentable_type' => Shop::class,
                'documentable_id' => $shop->id,
                'title' => "صورة المحل رقم {$shop->shop_number}",
                'description' => 'صورة داخلية للمحل',
                'document_type' => 'building_image',
                'file_name' => "shop_image_{$shop->id}.jpg",
                'file_path' => "documents/shops/shop_image_{$shop->id}.jpg",
                'file_size' => 2097152, // 2MB
                'mime_type' => 'image/jpeg',
                'uploaded_by' => $user->id,
                'is_active' => true,
                'sort_order' => 1,
            ]);

            // تقرير الصيانة
            Document::create([
                'documentable_type' => Shop::class,
                'documentable_id' => $shop->id,
                'title' => "تقرير الصيانة - محل {$shop->shop_number}",
                'description' => 'تقرير آخر صيانة للمحل',
                'document_type' => 'maintenance_report',
                'file_name' => "maintenance_report_{$shop->id}.pdf",
                'file_path' => "documents/shops/maintenance_report_{$shop->id}.pdf",
                'file_size' => 512000, // 500KB
                'mime_type' => 'application/pdf',
                'uploaded_by' => $user->id,
                'is_active' => true,
                'sort_order' => 2,
            ]);
        }
    }
}
