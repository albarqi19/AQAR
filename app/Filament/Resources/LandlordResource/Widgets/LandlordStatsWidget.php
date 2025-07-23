<?php

namespace App\Filament\Resources\LandlordResource\Widgets;

use Filament\Widgets\StatsOverviewWidget;

class LandlordStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalLandlords = \App\Models\Landlord::count();
        $activeLandlords = \App\Models\Landlord::where('is_active', true)->count();
        $inactiveLandlords = \App\Models\Landlord::where('is_active', false)->count();
        $companies = \App\Models\Landlord::whereNotNull('company_name')->count();
        $totalBuildings = \App\Models\Landlord::withCount('buildings')->get()->sum('buildings_count');
        $averageCommission = \App\Models\Landlord::avg('commission_rate');

        return [
            StatsOverviewWidget\Stat::make('إجمالي المكاتب العقارية', $totalLandlords)
                ->description('جميع المكاتب المسجلة')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),

            StatsOverviewWidget\Stat::make('المكاتب النشطة', $activeLandlords)
                ->description('المكاتب الفعالة')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            StatsOverviewWidget\Stat::make('المكاتب غير النشطة', $inactiveLandlords)
                ->description('المكاتب المعطلة')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            StatsOverviewWidget\Stat::make('الشركات', $companies)
                ->description('المكاتب المؤسسية')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            StatsOverviewWidget\Stat::make('إجمالي المباني', $totalBuildings)
                ->description('مباني جميع المكاتب')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('warning'),

            StatsOverviewWidget\Stat::make('متوسط العمولة', number_format($averageCommission, 1) . '%')
                ->description('متوسط معدل العمولة')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),
        ];
    }
}
