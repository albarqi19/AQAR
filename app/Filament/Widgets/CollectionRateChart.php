<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Payment;
use Carbon\Carbon;

class CollectionRateChart extends ChartWidget
{
    protected static ?string $heading = 'معدلات التحصيل الشهرية';
    
    protected static ?string $description = 'نسبة المدفوعات المحصلة مقابل المستحقة';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    public function getDescription(): ?string
    {
        $currentMonth = Carbon::now();
        $totalDue = Payment::whereYear('due_date', $currentMonth->year)
            ->whereMonth('due_date', $currentMonth->month)
            ->sum('invoice_amount');
            
        $totalCollected = Payment::whereYear('due_date', $currentMonth->year)
            ->whereMonth('due_date', $currentMonth->month)
            ->sum('paid_amount');

        $collectionRate = $totalDue > 0 ? round(($totalCollected / $totalDue) * 100, 1) : 0;

        return "معدل التحصيل هذا الشهر: {$collectionRate}%";
    }

    protected function getData(): array
    {
        $months = [];
        $collectionRates = [];
        
        // جلب بيانات آخر 6 أشهر
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $totalDue = Payment::whereYear('due_date', $date->year)
                ->whereMonth('due_date', $date->month)
                ->sum('invoice_amount');
                
            $totalCollected = Payment::whereYear('due_date', $date->year)
                ->whereMonth('due_date', $date->month)
                ->sum('paid_amount');
                
            $collectionRate = $totalDue > 0 ? round(($totalCollected / $totalDue) * 100, 2) : 0;
            $collectionRates[] = $collectionRate;
        }

        return [
            'datasets' => [
                [
                    'label' => 'معدل التحصيل (%)',
                    'data' => $collectionRates,
                    'backgroundColor' => [
                        '#10B981',
                        '#10B981',
                        '#10B981',
                        '#10B981',
                        '#10B981',
                        '#10B981',
                    ],
                    'borderColor' => '#059669',
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
                    'beginAtZero' => true,
                    'max' => 100,
                    'ticks' => [
                        'callback' => 'function(value) { return value + "%"; }',
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
