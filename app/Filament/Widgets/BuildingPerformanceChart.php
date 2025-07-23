<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Building;
use App\Models\Shop;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class BuildingPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'أداء المباني';
    
    protected static ?string $description = 'ترتيب المباني حسب الإيرادات ومعدل الإشغال';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return false; // لا يظهر في الصفحة الرئيسية
    }

    public function getDescription(): ?string
    {
        $topBuilding = $this->getTopPerformingBuilding();
        return $topBuilding ? "أفضل أداء: {$topBuilding['name']}" : 'لا توجد بيانات كافية';
    }

    protected function getData(): array
    {
        $buildings = Building::with(['shops.contracts.payments'])
            ->get()
            ->map(function ($building) {
                // حساب إجمالي الإيرادات للمبنى
                $totalRevenue = 0;
                $occupiedShops = 0;
                $totalShops = $building->shops->count();

                foreach ($building->shops as $shop) {
                    // حساب الإيرادات من المدفوعات
                    $shopRevenue = $shop->contracts()
                        ->with('payments')
                        ->get()
                        ->sum(function ($contract) {
                            return $contract->payments()
                                ->where('status', 'paid')
                                ->sum('paid_amount');
                        });
                    
                    $totalRevenue += $shopRevenue;

                    // فحص ما إذا كان المحل مؤجر حالياً
                    $hasActiveContract = $shop->contracts()
                        ->where('status', 'active')
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())
                        ->exists();
                    
                    if ($hasActiveContract) {
                        $occupiedShops++;
                    }
                }

                // حساب معدل الإشغال
                $occupancyRate = $totalShops > 0 ? ($occupiedShops / $totalShops) * 100 : 0;

                return [
                    'name' => $building->name,
                    'revenue' => $totalRevenue,
                    'occupancy_rate' => round($occupancyRate, 1),
                    'occupied_shops' => $occupiedShops,
                    'total_shops' => $totalShops,
                ];
            })
            ->sortByDesc('revenue')
            ->take(10); // أفضل 10 مباني

        $labels = $buildings->pluck('name')->toArray();
        $revenues = $buildings->pluck('revenue')->map(fn($revenue) => (float) $revenue)->toArray();
        $occupancyRates = $buildings->pluck('occupancy_rate')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'الإيرادات (ر.س)',
                    'data' => $revenues,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor' => '#10B981',
                    'borderWidth' => 1,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'معدل الإشغال (%)',
                    'data' => $occupancyRates,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => '#3B82F6',
                    'borderWidth' => 1,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
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
                'tooltip' => [
                    'callbacks' => [
                        'afterLabel' => 'function(context) {
                            if (context.datasetIndex === 0) {
                                return "ر.س " + context.parsed.y.toLocaleString();
                            } else {
                                return context.parsed.y + "%";
                            }
                        }',
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'المباني',
                    ],
                ],
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
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'معدل الإشغال (%)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }

    private function getTopPerformingBuilding(): ?array
    {
        $buildings = Building::with(['shops.contracts.payments'])->get();
        
        $topBuilding = null;
        $maxRevenue = 0;

        foreach ($buildings as $building) {
            $totalRevenue = 0;
            
            foreach ($building->shops as $shop) {
                $shopRevenue = $shop->contracts()
                    ->with('payments')
                    ->get()
                    ->sum(function ($contract) {
                        return $contract->payments()
                            ->where('status', 'paid')
                            ->sum('paid_amount');
                    });
                
                $totalRevenue += $shopRevenue;
            }

            if ($totalRevenue > $maxRevenue) {
                $maxRevenue = $totalRevenue;
                $topBuilding = [
                    'name' => $building->name,
                    'revenue' => $totalRevenue,
                ];
            }
        }

        return $topBuilding;
    }
}
