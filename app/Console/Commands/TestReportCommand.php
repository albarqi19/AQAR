<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportService;
use App\Services\TCPDFReportService;
use Exception;

class TestReportCommand extends Command
{
    protected $signature = 'report:test {period=monthly}';
    protected $description = 'Test report generation to debug issues';

    public function handle()
    {
        $this->info('🔍 بدء اختبار توليد التقارير...');
        
        try {
            $period = $this->argument('period');
            $this->info("📊 اختبار التقرير للفترة: {$period}");
            
            // اختبار ReportService أولاً
            $this->info('1️⃣ اختبار ReportService...');
            $reportService = new ReportService();
            
            $this->info('2️⃣ توليد بيانات التقرير مباشرة...');
            $data = $reportService->generateReport($period);
            
            $this->info('✅ بيانات التقرير:');
            $this->table(['المفتاح', 'القيمة'], [
                ['period_name', $data['period_name'] ?? 'غير موجود'],
                ['start_date', $data['start_date'] ?? 'غير موجود'],
                ['end_date', $data['end_date'] ?? 'غير موجود'],
                ['total_contracts', $data['stats']['total_contracts'] ?? 'غير موجود'],
                ['active_contracts', $data['stats']['active_contracts'] ?? 'غير موجود'],
                ['total_payments', $data['stats']['total_payments'] ?? 'غير موجود'],
                ['total_expenses', $data['stats']['total_expenses'] ?? 'غير موجود'],
                ['total_buildings', $data['stats']['total_buildings'] ?? 'غير موجود'],
                ['occupancy_rate', $data['stats']['occupancy_rate'] ?? 'غير موجود'],
            ]);
            
            $this->info('4️⃣ اختبار TCPDFReportService...');
            $tcpdfService = new TCPDFReportService();
            
            $this->info('5️⃣ توليد ملف PDF...');
            $response = $tcpdfService->generateReport($period);
            
            if ($response) {
                $this->info('✅ تم توليد PDF بنجاح!');
                $this->info('📄 نوع الاستجابة: ' . get_class($response));
            } else {
                $this->error('❌ فشل في توليد PDF');
            }
            
        } catch (Exception $e) {
            $this->error('❌ خطأ: ' . $e->getMessage());
            $this->error('📍 في الملف: ' . $e->getFile() . ' السطر: ' . $e->getLine());
            $this->error('🔍 تفاصيل الخطأ:');
            $this->line($e->getTraceAsString());
        }
    }
}
