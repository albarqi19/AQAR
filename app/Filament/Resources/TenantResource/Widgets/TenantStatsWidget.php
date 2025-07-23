<?php

namespace App\Filament\Resources\TenantResource\Widgets;

use Filament\Widgets\StatsOverviewWidget;

class TenantStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalTenants = \App\Models\Tenant::count();
        $activeTenants = \App\Models\Tenant::where('is_active', true)->count();
        $inactiveTenants = \App\Models\Tenant::where('is_active', false)->count();
        $companies = \App\Models\Tenant::whereNotNull('company_name')->count();
        $individuals = \App\Models\Tenant::whereNull('company_name')->count();
        $tenantsWithContracts = \App\Models\Tenant::has('contracts')->count();

        return [
            StatsOverviewWidget\Stat::make('إجمالي المستأجرين', $totalTenants)
                ->description('جميع المستأجرين المسجلين')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            StatsOverviewWidget\Stat::make('المستأجرين النشطين', $activeTenants)
                ->description('المستأجرين الفعالين')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            StatsOverviewWidget\Stat::make('المستأجرين غير النشطين', $inactiveTenants)
                ->description('المستأجرين المعطلين')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            StatsOverviewWidget\Stat::make('الشركات', $companies)
                ->description('المستأجرين من الشركات')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            StatsOverviewWidget\Stat::make('الأفراد', $individuals)
                ->description('المستأجرين الأفراد')
                ->descriptionIcon('heroicon-m-user')
                ->color('warning'),

            StatsOverviewWidget\Stat::make('لديهم عقود', $tenantsWithContracts)
                ->description('مستأجرين لديهم عقود نشطة')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
        ];
    }
}
