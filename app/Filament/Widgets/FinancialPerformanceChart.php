<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Payment;
use App\Models\Contract;
use Carbon\Carbon;

class FinancialPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'الأداء المالي';
    
    protected static ?string $description = 'مقارنة الإيرادات المتوقعة مع المحصلة';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function getDescription(): ?string
    {
        $currentYear = Carbon::now()->year;
        $expectedRevenue = Payment::whereYear('due_date', $currentYear)->sum('invoice_amount');
        $actualRevenue = Payment::where('status', 'paid')->whereYear('payment_date', $currentYear)->sum('paid_amount');
        
        $performance = $expectedRevenue > 0 ? round(($actualRevenue / $expectedRevenue) * 100, 1) : 0;

        return "نسبة الأداء المالي لهذا العام: {$performance}%";
    }

    protected function getData(): array
    {
        $months = [];
        $expectedRevenues = [];
        $actualRevenues = [];
        
        // جلب بيانات آخر 12 شهر
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            // الإيرادات المتوقعة (إجمالي الفواتير المستحقة)
            $expectedRevenue = Payment::whereYear('due_date', $date->year)
                ->whereMonth('due_date', $date->month)
                ->sum('invoice_amount');
            $expectedRevenues[] = (float) $expectedRevenue;
            
            // الإيرادات الفعلية (المدفوعات المحصلة)
            $actualRevenue = Payment::where('status', 'paid')
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('paid_amount');
            $actualRevenues[] = (float) $actualRevenue;
        }

        return [
            'datasets' => [
                [
                    'label' => 'الإيرادات المتوقعة (ر.س)',
                    'data' => $expectedRevenues,
                    'borderColor' => '#F59E0B',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'الإيرادات المحصلة (ر.س)',
                    'data' => $actualRevenues,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
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
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return value.toLocaleString() + " ر.س"; }',
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
