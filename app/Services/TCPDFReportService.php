<?php

namespace App\Services;

use TCPDF;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Building;
use App\Models\Shop;
use App\Models\Expense;
use Carbon\Carbon;

class TCPDFReportService
{
    private $pdf;

    public function __construct()
    {
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->setupPDF();
    }

    private function setupPDF()
    {
        // إعدادات أساسية
        $this->pdf->SetCreator('نظام إدارة العقارات');
        $this->pdf->SetAuthor('نظام إدارة العقارات');
        $this->pdf->SetTitle('تقرير شامل');
        $this->pdf->SetSubject('تقرير إحصائي');

        // إعدادات الخط والاتجاه للعربية
        $this->pdf->setLanguageArray(array(
            'a_meta_charset' => 'UTF-8',
            'a_meta_dir' => 'rtl',
            'a_meta_language' => 'ar',
            'w_page' => 'صفحة'
        ));

        // إزالة الهيدر والفوتر الافتراضي
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        // إعدادات الهوامش
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetAutoPageBreak(true, 25);
    }

    public function generateReport(string $period)
    {
        // إعداد الخط العربي المحسن
        $this->pdf->SetFont('aealarabiya', '', 12);
        $this->pdf->setRTL(true);
        
        $reportService = new \App\Services\ReportService();
        $data = $reportService->generateReport($period);

        $this->pdf->AddPage();

        // الهيدر
        $this->addHeader($data);
        
        // الإحصائيات الرئيسية
        $this->addStats($data['stats']);
        
        // صافي الدخل
        $this->addNetIncome($data['stats']);
        
        // أداء المباني
        if (count($data['building_performance']) > 0) {
            $this->addBuildingPerformance($data['building_performance']);
        }
        
        // أعلى المدفوعات
        if (count($data['top_payments']) > 0) {
            $this->addTopPayments($data['top_payments']);
        }
        
        // العقود الجديدة
        if (count($data['new_contracts']) > 0) {
            $this->addNewContracts($data['new_contracts']);
        }

        // المدفوعات الشهرية
        if (count($data['monthly_payments']) > 0) {
            $this->addMonthlyPayments($data['monthly_payments']);
        }

        // المصروفات حسب النوع
        if (count($data['expenses_by_type']) > 0) {
            $this->addExpensesByType($data['expenses_by_type']);
        }

        // إضافة الفوتر
        $this->addFooter();

        $fileName = 'تقرير_' . str_replace([' ', '-'], '_', $data['period_name']) . '_' . now()->format('Y-m-d_H-i') . '.pdf';
        
        return response()->streamDownload(function () {
            echo $this->pdf->Output('', 'S');
        }, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function addHeader($data)
    {
        // خلفية للهيدر
        $this->pdf->SetFillColor(0, 123, 255);
        $this->pdf->Rect(0, 0, 210, 45, 'F');
        
        // إضافة شعار جميل مع تصميم محسن
        $this->addBrandLogo();
        
        // العنوان الرئيسي
        $this->pdf->SetTextColor(255, 255, 255);
        $this->setArabicFont('B', 16);
        $this->pdf->SetXY(45, 8);
        $this->pdf->Cell(150, 8, 'نظام إدارة العقارات المتطور', 0, 1, 'C');
        
        // عنوان التقرير
        $this->setArabicFont('B', 14);
        $this->pdf->SetXY(45, 18);
        $this->pdf->Cell(150, 8, 'تقرير ' . $data['period_name'], 0, 1, 'C');
        
        // التاريخ والفترة
        $this->setArabicFont('', 9);
        $this->pdf->SetXY(15, 35);
        $this->pdf->Cell(90, 6, 'تم إنشاؤه في: ' . $data['generated_at']->format('Y-m-d H:i'), 0, 0, 'L');
        $this->pdf->SetXY(105, 35);
        $this->pdf->Cell(90, 6, 'الفترة: من ' . $data['date_range'][0] . ' إلى ' . $data['date_range'][1], 0, 1, 'R');
        
        $this->pdf->Ln(18);
        $this->pdf->SetTextColor(0, 0, 0);
    }

    private function addStats($stats)
    {
        $this->pdf->SetFont('dejavusans', 'B', 14);
        $this->pdf->Cell(0, 10, 'الإحصائيات الرئيسية', 0, 1, 'R');
        $this->pdf->Ln(5);

        // صف أول من الإحصائيات
        $this->pdf->SetFont('dejavusans', '', 10);
        $y = $this->pdf->GetY();
        
        // إجمالي العقود
        $this->pdf->SetFillColor(248, 249, 250);
        $this->pdf->Rect(15, $y, 60, 25, 'F');
        $this->pdf->SetXY(20, $y + 5);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(50, 5, 'إجمالي العقود', 0, 1, 'C');
        $this->pdf->SetXY(20, $y + 12);
        $this->pdf->SetFont('dejavusans', 'B', 14);
        $this->pdf->Cell(50, 8, number_format($stats['total_contracts']), 0, 1, 'C');
        
        // العقود النشطة
        $this->pdf->Rect(80, $y, 60, 25, 'F');
        $this->pdf->SetXY(85, $y + 5);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(50, 5, 'العقود النشطة', 0, 1, 'C');
        $this->pdf->SetXY(85, $y + 12);
        $this->pdf->SetFont('dejavusans', 'B', 14);
        $this->pdf->Cell(50, 8, number_format($stats['active_contracts']), 0, 1, 'C');
        
        // إجمالي المدفوعات
        $this->pdf->Rect(145, $y, 60, 25, 'F');
        $this->pdf->SetXY(150, $y + 5);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(50, 5, 'إجمالي المدفوعات', 0, 1, 'C');
        $this->pdf->SetXY(150, $y + 12);
        $this->pdf->SetFont('dejavusans', 'B', 12);
        $this->pdf->SetTextColor(40, 167, 69);
        $this->pdf->Cell(50, 8, number_format($stats['total_payments']) . ' ر.س', 0, 1, 'C');
        
        $this->pdf->SetY($y + 30);
        
        // صف ثاني من الإحصائيات
        $y = $this->pdf->GetY();
        
        // إجمالي المصروفات - أحمر فاتح
        $this->pdf->SetFillColor(248, 215, 218);
        $this->pdf->SetDrawColor(220, 53, 69);
        $this->pdf->Rect(15, $y, 60, 25, 'DF');
        $this->pdf->SetXY(20, $y + 5);
        $this->pdf->SetTextColor(220, 53, 69);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(50, 5, 'إجمالي المصروفات', 0, 1, 'C');
        $this->pdf->SetXY(20, $y + 12);
        $this->pdf->SetFont('dejavusans', 'B', 12);
        $this->pdf->Cell(50, 8, number_format($stats['total_expenses']) . ' ر.س', 0, 1, 'C');
        
        // عدد المباني - بنفسجي فاتح
        $this->pdf->SetFillColor(237, 229, 253);
        $this->pdf->SetDrawColor(111, 66, 193);
        $this->pdf->Rect(80, $y, 60, 25, 'DF');
        $this->pdf->SetXY(85, $y + 5);
        $this->pdf->SetTextColor(111, 66, 193);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(50, 5, 'عدد المباني', 0, 1, 'C');
        $this->pdf->SetXY(85, $y + 12);
        $this->pdf->SetFont('dejavusans', 'B', 14);
        $this->pdf->Cell(50, 8, number_format($stats['total_buildings']), 0, 1, 'C');
        
        // معدل الإشغال - برتقالي فاتح
        $this->pdf->SetFillColor(255, 243, 205);
        $this->pdf->SetDrawColor(253, 126, 20);
        $this->pdf->Rect(145, $y, 60, 25, 'DF');
        $this->pdf->SetXY(150, $y + 5);
        $this->pdf->SetTextColor(253, 126, 20);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        $this->pdf->Cell(50, 5, 'معدل الإشغال', 0, 1, 'C');
        $this->pdf->SetXY(150, $y + 12);
        $this->pdf->SetFont('dejavusans', 'B', 14);
        $this->pdf->Cell(50, 8, $stats['occupancy_rate'] . '%', 0, 1, 'C');
        
        $this->pdf->SetY($y + 35);
        $this->pdf->SetTextColor(0, 0, 0);
    }

    private function addNetIncome($stats)
    {
        $netIncome = $stats['total_payments'] - $stats['total_expenses'];
        $isPositive = $netIncome >= 0;
        
        // خلفية متدرجة حسب النتيجة
        if ($isPositive) {
            $this->pdf->SetFillColor(212, 237, 218); // أخضر فاتح
            $this->pdf->SetDrawColor(40, 167, 69);   // أخضر
        } else {
            $this->pdf->SetFillColor(248, 215, 218); // أحمر فاتح
            $this->pdf->SetDrawColor(220, 53, 69);   // أحمر
        }
        
        $this->pdf->Rect(15, $this->pdf->GetY(), 180, 25, 'DF');
        
        // أيقونة نصية
        $this->pdf->SetXY(20, $this->pdf->GetY() + 5);
        $this->pdf->SetFont('dejavusans', 'B', 16);
        $this->pdf->SetTextColor($isPositive ? 40 : 220, $isPositive ? 167 : 53, $isPositive ? 69 : 69);
        $this->pdf->Cell(15, 8, $isPositive ? '↗' : '↘', 0, 0, 'C');
        
        // النص
        $this->pdf->SetFont('dejavusans', 'B', 12);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Cell(80, 8, 'صافي الدخل للفترة:', 0, 0, 'L');
        
        // المبلغ
        $this->pdf->SetTextColor($isPositive ? 40 : 220, $isPositive ? 167 : 53, $isPositive ? 69 : 69);
        $this->pdf->SetFont('dejavusans', 'B', 18);
        $this->pdf->Cell(80, 8, number_format(abs($netIncome)) . ' ر.س', 0, 0, 'R');
        
        $this->pdf->Ln(30);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->addSectionDivider();
    }

    private function addSectionDivider()
    {
        $this->pdf->SetDrawColor(0, 123, 255);
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line(15, $this->pdf->GetY(), 195, $this->pdf->GetY());
        $this->pdf->Ln(10);
    }

    private function addBuildingPerformance($buildings)
    {
        // عنوان القسم مع أيقونة
        $this->pdf->SetFont('dejavusans', 'B', 14);
        $this->pdf->SetTextColor(0, 123, 255);
        $this->pdf->Cell(8, 10, '*', 0, 0, 'C');
        $this->pdf->Cell(0, 10, 'أداء المباني', 0, 1, 'R');
        $this->pdf->Ln(3);
        
        // هيدر الجدول
        $this->pdf->SetFillColor(0, 123, 255);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        
        $this->pdf->Cell(50, 8, 'اسم المبنى', 1, 0, 'C', true);
        $this->pdf->Cell(25, 8, 'إجمالي المحلات', 1, 0, 'C', true);
        $this->pdf->Cell(25, 8, 'المحلات المؤجرة', 1, 0, 'C', true);
        $this->pdf->Cell(25, 8, 'معدل الإشغال', 1, 0, 'C', true);
        $this->pdf->Cell(55, 8, 'إجمالي المدفوعات', 1, 1, 'C', true);
        
        // بيانات الجدول
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('dejavusans', '', 8);
        
        foreach (array_slice($buildings, 0, 10) as $building) {
            $this->pdf->Cell(50, 6, $building['name'], 1, 0, 'C');
            $this->pdf->Cell(25, 6, $building['total_shops'], 1, 0, 'C');
            $this->pdf->Cell(25, 6, $building['occupied_shops'], 1, 0, 'C');
            $this->pdf->Cell(25, 6, $building['occupancy_rate'] . '%', 1, 0, 'C');
            $this->pdf->SetTextColor(40, 167, 69);
            $this->pdf->Cell(55, 6, number_format($building['total_payments']) . ' ر.س', 1, 1, 'C');
            $this->pdf->SetTextColor(0, 0, 0);
        }
        
        $this->pdf->Ln(10);
        $this->addSectionDivider();
    }

    private function addTopPayments($payments)
    {
        // عنوان القسم مع أيقونة
        $this->pdf->SetFont('dejavusans', 'B', 14);
        $this->pdf->SetTextColor(0, 123, 255);
        $this->pdf->Cell(8, 10, '$', 0, 0, 'C');
        $this->pdf->Cell(0, 10, 'أعلى المدفوعات', 0, 1, 'R');
        $this->pdf->Ln(3);
        
        // هيدر الجدول
        $this->pdf->SetFillColor(0, 123, 255);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        
        $this->pdf->Cell(30, 8, 'رقم الدفعة', 1, 0, 'C', true);
        $this->pdf->Cell(40, 8, 'المستأجر', 1, 0, 'C', true);
        $this->pdf->Cell(30, 8, 'المبنى', 1, 0, 'C', true);
        $this->pdf->Cell(35, 8, 'المبلغ', 1, 0, 'C', true);
        $this->pdf->Cell(45, 8, 'تاريخ الدفع', 1, 1, 'C', true);
        
        // بيانات الجدول
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('dejavusans', '', 8);
        
        foreach ($payments->take(8) as $payment) {
            $this->pdf->Cell(30, 6, $payment->invoice_number ?? 'غير محدد', 1, 0, 'C');
            $this->pdf->Cell(40, 6, mb_substr(optional(optional($payment->contract)->tenant)->name ?? 'غير محدد', 0, 15), 1, 0, 'C');
            $this->pdf->Cell(30, 6, mb_substr(optional(optional(optional($payment->contract)->shop)->building)->name ?? 'غير محدد', 0, 12), 1, 0, 'C');
            $this->pdf->SetTextColor(40, 167, 69);
            $this->pdf->Cell(35, 6, number_format($payment->paid_amount ?? 0) . ' ر.س', 1, 0, 'C');
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->Cell(45, 6, $payment->payment_date ? $payment->payment_date->format('Y-m-d') : 'غير محدد', 1, 1, 'C');
        }
        
        $this->pdf->Ln(10);
        $this->addSectionDivider();
    }

    private function addNewContracts($contracts)
    {
        // عنوان القسم مع أيقونة
        $this->pdf->SetFont('dejavusans', 'B', 14);
        $this->pdf->SetTextColor(0, 123, 255);
        $this->pdf->Cell(8, 10, '#', 0, 0, 'C');
        $this->pdf->Cell(0, 10, 'العقود الجديدة', 0, 1, 'R');
        $this->pdf->Ln(3);
        
        // هيدر الجدول
        $this->pdf->SetFillColor(0, 123, 255);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('dejavusans', 'B', 9);
        
        $this->pdf->Cell(30, 8, 'رقم العقد', 1, 0, 'C', true);
        $this->pdf->Cell(40, 8, 'المستأجر', 1, 0, 'C', true);
        $this->pdf->Cell(30, 8, 'المبنى', 1, 0, 'C', true);
        $this->pdf->Cell(35, 8, 'الإيجار الشهري', 1, 0, 'C', true);
        $this->pdf->Cell(45, 8, 'تاريخ الإنشاء', 1, 1, 'C', true);
        
        // بيانات الجدول
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('dejavusans', '', 8);
        
        foreach ($contracts->take(8) as $contract) {
            $this->pdf->Cell(30, 6, $contract->contract_number ?? 'غير محدد', 1, 0, 'C');
            $this->pdf->Cell(40, 6, mb_substr(optional($contract->tenant)->name ?? 'غير محدد', 0, 15), 1, 0, 'C');
            $this->pdf->Cell(30, 6, mb_substr(optional(optional($contract->shop)->building)->name ?? 'غير محدد', 0, 12), 1, 0, 'C');
            $this->pdf->SetTextColor(40, 167, 69);
            $this->pdf->Cell(35, 6, number_format($contract->monthly_rent ?? 0) . ' ر.س', 1, 0, 'C');
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->Cell(45, 6, $contract->created_at ? $contract->created_at->format('Y-m-d') : 'غير محدد', 1, 1, 'C');
        }
        
        $this->pdf->Ln(10);
    }

    private function addMonthlyPayments($payments)
    {
        $this->pdf->SetFont('dejavusans', 'B', 14);
        $this->pdf->Cell(0, 10, 'المدفوعات الشهرية', 0, 1, 'R');
        $this->pdf->Ln(3);
        
        // هيدر الجدول
        $this->pdf->SetFillColor(0, 123, 255);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('dejavusans', 'B', 10);
        
        $this->pdf->Cell(60, 8, 'الشهر', 1, 0, 'C', true);
        $this->pdf->Cell(40, 8, 'السنة', 1, 0, 'C', true);
        $this->pdf->Cell(80, 8, 'المبلغ', 1, 1, 'C', true);
        
        // بيانات الجدول
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('dejavusans', '', 9);
        
        foreach ($payments as $payment) {
            $this->pdf->Cell(60, 6, $payment['month'], 1, 0, 'C');
            $this->pdf->Cell(40, 6, $payment['year'], 1, 0, 'C');
            $this->pdf->SetTextColor(40, 167, 69);
            $this->pdf->Cell(80, 6, number_format($payment['amount']) . ' ر.س', 1, 1, 'C');
            $this->pdf->SetTextColor(0, 0, 0);
        }
        
        $this->pdf->Ln(10);
    }

    private function addExpensesByType($expenses)
    {
        $totalExpenses = $expenses->sum('total');
        
        $this->pdf->SetFont('dejavusans', 'B', 14);
        $this->pdf->Cell(0, 10, 'المصروفات حسب النوع', 0, 1, 'R');
        $this->pdf->Ln(3);
        
        // هيدر الجدول
        $this->pdf->SetFillColor(0, 123, 255);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('dejavusans', 'B', 10);
        
        $this->pdf->Cell(60, 8, 'نوع المصروف', 1, 0, 'C', true);
        $this->pdf->Cell(60, 8, 'إجمالي المبلغ', 1, 0, 'C', true);
        $this->pdf->Cell(60, 8, 'النسبة', 1, 1, 'C', true);
        
        // بيانات الجدول
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('dejavusans', '', 9);
        
        foreach ($expenses as $expense) {
            $percentage = $totalExpenses > 0 ? round(($expense->total / $totalExpenses) * 100, 1) : 0;
            
            $this->pdf->Cell(60, 6, $expense->expense_type, 1, 0, 'C');
            $this->pdf->SetTextColor(220, 53, 69);
            $this->pdf->Cell(60, 6, number_format($expense->total) . ' ر.س', 1, 0, 'C');
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->Cell(60, 6, $percentage . '%', 1, 1, 'C');
        }
        
        $this->pdf->Ln(10);
    }

    private function addFooter()
    {
        $this->pdf->Ln(20);
        
        // خط فاصل
        $this->pdf->SetDrawColor(0, 123, 255);
        $this->pdf->SetLineWidth(1);
        $this->pdf->Line(15, $this->pdf->GetY(), 195, $this->pdf->GetY());
        
        $this->pdf->Ln(10);
        
        // معلومات الفوتر
        $this->pdf->SetFont('dejavusans', '', 9);
        $this->pdf->SetTextColor(100, 100, 100);
        $this->pdf->Cell(0, 5, 'تم إنشاء هذا التقرير بواسطة نظام إدارة العقارات "عقاري"', 0, 1, 'C');
        $this->pdf->Cell(0, 5, 'تاريخ الإنشاء: ' . now()->format('Y-m-d H:i:s'), 0, 1, 'C');
        $this->pdf->Cell(0, 5, 'جميع الحقوق محفوظة © ' . now()->year, 0, 1, 'C');
    }

    private function addBrandLogo()
    {
        $logoAdded = false;
        
        // محاولة استخدام شعار JPG بديل أولاً
        $simpleLogoPath = public_path('logo_simple.jpg');
        if (file_exists($simpleLogoPath)) {
            try {
                $this->pdf->Image($simpleLogoPath, 15, 8, 25, 25, 'JPG');
                $logoAdded = true;
            } catch (\Exception $e) {
                $logoAdded = false;
            }
        }
        
        // محاولة استخدام الشعار الأصلي إذا لم يعمل البديل
        if (!$logoAdded) {
            $logoPath = public_path('logo.png');
            if (file_exists($logoPath) && extension_loaded('gd')) {
                try {
                    // تحويل PNG إلى JPG مؤقتاً لتجنب مشكلة Alpha channel
                    $tempJpgPath = public_path('temp_logo.jpg');
                    
                    $image = imagecreatefrompng($logoPath);
                    $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
                    imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
                    imagealphablending($bg, TRUE);
                    imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
                    imagejpeg($bg, $tempJpgPath, 90);
                    imagedestroy($image);
                    imagedestroy($bg);
                    
                    $this->pdf->Image($tempJpgPath, 15, 8, 25, 25, 'JPG');
                    unlink($tempJpgPath); // حذف الملف المؤقت
                    $logoAdded = true;
                } catch (\Exception $e) {
                    $logoAdded = false;
                }
            }
        }
        
        if (!$logoAdded) {
            // شعار نصي جميل ومصمم باحترافية
            $this->createTextualLogo();
        }
    }

    private function createTextualLogo()
    {
        // إنشاء شعار نصي جميل
        
        // خلفية دائرية للشعار
        $this->pdf->SetFillColor(255, 255, 255);
        $this->pdf->SetDrawColor(255, 255, 255);
        $this->pdf->Circle(27.5, 20.5, 12, 0, 360, 'DF');
        
        // النص الرئيسي "عقاري"
        $this->pdf->SetTextColor(0, 123, 255);
        $this->setArabicFont('B', 16);
        $this->pdf->SetXY(15, 15);
        $this->pdf->Cell(25, 8, 'عقاري', 0, 1, 'C');
        
        // خط فاصل
        $this->pdf->SetDrawColor(0, 123, 255);
        $this->pdf->Line(18, 24, 37, 24);
        
        // النص الثانوي
        $this->setArabicFont('', 8);
        $this->pdf->SetXY(15, 25);
        $this->pdf->Cell(25, 4, 'إدارة العقارات', 0, 0, 'C');
    }

    private function setArabicFont($style = '', $size = 12)
    {
        // محاولة استخدام خط عربي محسن، مع fallback إلى DejaVu
        try {
            $this->pdf->SetFont('aealarabiya', $style, $size);
        } catch (\Exception $e) {
            $this->pdf->SetFont('dejavusans', $style, $size);
        }
    }
}
