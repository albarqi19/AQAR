<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Payment;
use App\Models\Building;
use App\Models\Shop;
use App\Models\Expense;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ReportService
{
    public function generateReport(string $period): array
    {
        $data = [
            'period' => $period,
            'period_name' => $this->getPeriodName($period),
            'generated_at' => now(),
        ];

        $dateRange = $this->getDateRange($period);
        $data['date_range'] = $dateRange;
        
        // إحصائيات عامة
        $data['stats'] = [
            'total_contracts' => Contract::whereBetween('created_at', $dateRange)->count() ?? 0,
            'active_contracts' => Contract::where('status', 'active')
                ->whereBetween('created_at', $dateRange)->count() ?? 0,
            'total_payments' => Payment::whereBetween('payment_date', $dateRange)->sum('paid_amount') ?? 0,
            'total_expenses' => Expense::whereBetween('expense_date', $dateRange)->sum('amount') ?? 0,
            'total_buildings' => Building::count() ?? 0,
            'total_shops' => Shop::count() ?? 0,
            'occupancy_rate' => $this->calculateOccupancyRate(),
        ];

        // إحصائيات الدفعات الشهرية
        $data['monthly_payments'] = $this->getMonthlyPayments($dateRange);
        
        // أداء المباني
        $data['building_performance'] = $this->getBuildingPerformance($dateRange);
        
        // أعلى المدفوعات
        $data['top_payments'] = Payment::with(['contract.tenant', 'contract.shop.building'])
            ->whereBetween('payment_date', $dateRange)
            ->orderBy('paid_amount', 'desc')
            ->limit(10)
            ->get() ?? collect([]);
            
        // العقود الجديدة
        $data['new_contracts'] = Contract::with(['tenant', 'shop.building'])
            ->whereBetween('created_at', $dateRange)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get() ?? collect([]);

        // المصروفات حسب النوع
        $data['expenses_by_type'] = Expense::selectRaw('expense_type, SUM(amount) as total')
            ->whereBetween('expense_date', $dateRange)
            ->groupBy('expense_type')
            ->get() ?? collect([]);

        return $data;
    }

    public function generatePdfReport(string $period)
    {
        try {
            $data = $this->generateReport($period);
            
            $pdf = Pdf::loadView('reports.pdf.comprehensive_arabic', $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => true,
                    'isRemoteEnabled' => false,
                    'enable_font_subsetting' => true,
                    'chroot' => public_path(),
                ]);

            $fileName = 'تقرير_' . str_replace([' ', '-'], '_', $data['period_name']) . '_' . now()->format('Y-m-d_H-i') . '.pdf';
            
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $fileName, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            Log::error('Report generation error: ' . $e->getMessage());
            throw new \Exception('فشل في إنشاء التقرير: ' . $e->getMessage());
        }
    }

    private function getPeriodName(string $period): string
    {
        return match($period) {
            'monthly' => 'شهري - ' . now()->format('F Y'),
            'quarterly' => 'ربع سنوي - Q' . now()->quarter . ' ' . now()->year,
            'semi_annual' => 'نصف سنوي - ' . (now()->month <= 6 ? 'النصف الأول' : 'النصف الثاني') . ' ' . now()->year,
            'annual' => 'سنوي - ' . now()->year,
            default => 'شهري',
        };
    }

    private function getDateRange(string $period): array
    {
        $now = now();
        
        return match($period) {
            'monthly' => [
                $now->startOfMonth()->toDateString(),
                $now->endOfMonth()->toDateString()
            ],
            'quarterly' => [
                $now->startOfQuarter()->toDateString(),
                $now->endOfQuarter()->toDateString()
            ],
            'semi_annual' => [
                $now->month <= 6 
                    ? $now->startOfYear()->toDateString()
                    : $now->startOfYear()->addMonths(6)->toDateString(),
                $now->month <= 6 
                    ? $now->startOfYear()->addMonths(5)->endOfMonth()->toDateString()
                    : $now->endOfYear()->toDateString()
            ],
            'annual' => [
                $now->startOfYear()->toDateString(),
                $now->endOfYear()->toDateString()
            ],
            default => [
                $now->startOfMonth()->toDateString(),
                $now->endOfMonth()->toDateString()
            ],
        };
    }

    private function getMonthlyPayments(array $dateRange): array
    {
        $payments = Payment::selectRaw('MONTH(payment_date) as month, YEAR(payment_date) as year, SUM(paid_amount) as total')
            ->whereBetween('payment_date', $dateRange)
            ->groupByRaw('YEAR(payment_date), MONTH(payment_date)')
            ->orderByRaw('YEAR(payment_date), MONTH(payment_date)')
            ->get();

        $result = [];
        foreach ($payments as $payment) {
            $monthName = Carbon::create($payment->year, $payment->month)->locale('ar')->monthName;
            $result[] = [
                'month' => $monthName,
                'year' => $payment->year,
                'amount' => $payment->total,
            ];
        }

        return $result;
    }

    private function getBuildingPerformance(array $dateRange): array
    {
        return Building::with(['shops.contracts.payments'])
            ->get()
            ->map(function ($building) use ($dateRange) {
                $totalPayments = 0;
                
                foreach ($building->shops as $shop) {
                    foreach ($shop->contracts as $contract) {
                        foreach ($contract->payments as $payment) {
                            if ($payment->payment_date >= $dateRange[0] && $payment->payment_date <= $dateRange[1]) {
                                $totalPayments += $payment->paid_amount;
                            }
                        }
                    }
                }

                $occupiedShops = $building->shops->filter(function ($shop) {
                    return $shop->contracts()->where('status', 'active')->exists();
                })->count();

                return [
                    'name' => $building->name,
                    'total_shops' => $building->shops->count(),
                    'occupied_shops' => $occupiedShops,
                    'occupancy_rate' => $building->shops->count() > 0 
                        ? round(($occupiedShops / $building->shops->count()) * 100, 2) 
                        : 0,
                    'total_payments' => $totalPayments,
                ];
            })
            ->sortByDesc('total_payments')
            ->values()
            ->toArray();
    }

    private function calculateOccupancyRate(): float
    {
        $totalShops = Shop::count();
        $occupiedShops = Shop::whereHas('contracts', function ($query) {
            $query->where('status', 'active');
        })->count();

        return $totalShops > 0 ? round(($occupiedShops / $totalShops) * 100, 2) : 0;
    }
}
