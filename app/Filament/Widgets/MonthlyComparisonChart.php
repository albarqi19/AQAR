<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Payment;
use App\Models\Contract;
use Carbon\Carbon;

class MonthlyComparisonChart extends ChartWidget
{
    protected static ?string $heading = 'مقارنة الأداء الشهري';
    
    protected static ?string $description = 'مقارنة الإيرادات والعقود الجديدة شهرياً';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return false; // لا يظهر في الصفحة الرئيسية
    }

    public function getDescription(): ?string
    {
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();
        
        $currentRevenue = Payment::where('status', 'paid')
            ->whereYear('payment_date', $currentMonth->year)
            ->whereMonth('payment_date', $currentMonth->month)
            ->sum('paid_amount');
            
        $lastRevenue = Payment::where('status', 'paid')
            ->whereYear('payment_date', $lastMonth->year)
            ->whereMonth('payment_date', $lastMonth->month)
            ->sum('paid_amount');

        $growth = $lastRevenue > 0 ? round((($currentRevenue - $lastRevenue) / $lastRevenue) * 100, 1) : 0;
        $growthText = $growth > 0 ? "نمو {$growth}%" : "انخفاض " . abs($growth) . "%";

        return "مقارنة مع الشهر الماضي: {$growthText}";
    }

    protected function getData(): array
    {
        $months = [];
        $revenues = [];
        $newContracts = [];
        
        // جلب بيانات آخر 6 أشهر
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            // الإيرادات الشهرية
            $monthlyRevenue = Payment::where('status', 'paid')
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('paid_amount');
            $revenues[] = (float) $monthlyRevenue;
            
            // العقود الجديدة
            $monthlyContracts = Contract::whereYear('start_date', $date->year)
                ->whereMonth('start_date', $date->month)
                ->count();
            $newContracts[] = $monthlyContracts * 1000; // ضرب في 1000 لتوحيد المقياس
        }

        return [
            'datasets' => [
                [
                    'label' => 'الإيرادات (ر.س)',
                    'data' => $revenues,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'العقود الجديدة',
                    'data' => $newContracts,
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'الإيرادات (ر.س)',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'عدد العقود',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}
