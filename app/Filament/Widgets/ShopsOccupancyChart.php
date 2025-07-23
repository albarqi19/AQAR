<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Shop;
use App\Models\Contract;

class ShopsOccupancyChart extends ChartWidget
{
    protected static ?string $heading = 'إحصائيات المحلات';
    
    protected static ?string $description = 'توزيع المحلات حسب حالة الإشغال';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    public function getDescription(): ?string
    {
        $totalShops = Shop::count();
        $occupiedShops = Shop::where('status', 'occupied')->count();
        $occupancyRate = $totalShops > 0 ? round(($occupiedShops / $totalShops) * 100, 1) : 0;

        return "معدل الإشغال: {$occupancyRate}% من إجمالي {$totalShops} محل";
    }

    protected function getData(): array
    {
        $occupied = Shop::where('status', 'occupied')->count();
        $vacant = Shop::where('status', 'vacant')->count();
        $maintenance = Shop::where('status', 'maintenance')->count();

        return [
            'datasets' => [
                [
                    'data' => [$occupied, $vacant, $maintenance],
                    'backgroundColor' => [
                        '#10B981', // أخضر للمشغول
                        '#F59E0B', // أصفر للشاغر
                        '#EF4444', // أحمر للصيانة
                    ],
                    'borderColor' => [
                        '#059669',
                        '#D97706',
                        '#DC2626',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['مؤجر', 'شاغر', 'صيانة'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { 
                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                            var percentage = ((context.raw / total) * 100).toFixed(1);
                            return context.label + ": " + context.raw + " محل (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
        ];
    }
}
