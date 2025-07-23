<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;
use App\Models\Building;
use App\Models\Shop;
use App\Models\Tenant;

class CheckDocumentsCommand extends Command
{
    protected $signature = 'debug:documents';
    protected $description = 'فحص علاقات الوثائق';

    public function handle()
    {
        $this->info('فحص علاقات الوثائق...');
        
        // فحص إجمالي الوثائق
        $totalDocs = Document::count();
        $this->line("إجمالي الوثائق في قاعدة البيانات: {$totalDocs}");
        
        // فحص الوثائق لكل نوع
        $buildingDocs = Document::where('documentable_type', Building::class)->count();
        $shopDocs = Document::where('documentable_type', Shop::class)->count();
        $tenantDocs = Document::where('documentable_type', Tenant::class)->count();
        
        $this->line("وثائق المباني: {$buildingDocs}");
        $this->line("وثائق المحلات: {$shopDocs}");
        $this->line("وثائق المستأجرين: {$tenantDocs}");
        
        // فحص العلاقات
        $building = Building::first();
        if ($building) {
            $this->line("اختبار علاقة المبنى:");
            $this->line("  - ID المبنى: {$building->id}");
            $this->line("  - عدد الوثائق عبر العلاقة: " . $building->documents()->count());
            $this->line("  - عدد الوثائق بالاستعلام المباشر: " . Document::where('documentable_type', Building::class)->where('documentable_id', $building->id)->count());
        }
        
        // عرض تفاصيل الوثائق
        $docs = Document::select('documentable_type', 'documentable_id', 'title')->get();
        foreach ($docs as $doc) {
            $this->line("  - {$doc->title}: {$doc->documentable_type} #{$doc->documentable_id}");
        }
        
        return 0;
    }
}
