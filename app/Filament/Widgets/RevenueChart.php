<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Payment;
use Carbon\Carbon;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'الإيرادات الشهرية';
    
    protected static ?string $description = 'إجمالي الإيرادات المحصلة خلال آخر 12 شهر';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    public function getDescription(): ?string
    {
        $totalRevenue = Payment::where('status', 'paid')
            ->whereYear('payment_date', Carbon::now()->year)
            ->sum('paid_amount');

        return 'إجمالي الإيرادات هذا العام: ' . number_format($totalRevenue, 2) . ' ر.س';
    }

    protected function getData(): array
    {
        $months = [];
        $revenues = [];
        
        // جلب بيانات آخر 12 شهر
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $monthlyRevenue = Payment::where('status', 'paid')
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('paid_amount');
                
            $revenues[] = (float) $monthlyRevenue;
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
                'title' => [
                    'display' => false,
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
