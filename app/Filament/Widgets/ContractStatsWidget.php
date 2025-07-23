<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;

class ContractStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalContracts = \App\Models\Contract::count();
        $activeContracts = \App\Models\Contract::where('status', 'active')->count();
        $expiredContracts = \App\Models\Contract::where('status', 'expired')->count();
        $totalAnnualRent = \App\Models\Contract::sum('annual_rent');
        $totalPaymentAmount = \App\Models\Contract::sum('payment_amount');
        $expiringThisMonth = \App\Models\Contract::where('end_date', '>=', now()->startOfMonth())
                                               ->where('end_date', '<=', now()->endOfMonth())
                                               ->count();

        return [
            StatsOverviewWidget\Stat::make('إجمالي العقود', $totalContracts)
                ->description('جميع العقود المسجلة')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            StatsOverviewWidget\Stat::make('العقود النشطة', $activeContracts)
                ->description('العقود السارية حالياً')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            StatsOverviewWidget\Stat::make('العقود المنتهية', $expiredContracts)
                ->description('العقود المنتهية الصلاحية')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            StatsOverviewWidget\Stat::make('إجمالي الإيجار السنوي', number_format($totalAnnualRent, 2) . ' ر.س')
                ->description('قيمة جميع الإيجارات السنوية')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),

            StatsOverviewWidget\Stat::make('إجمالي مبالغ الدفع', number_format($totalPaymentAmount, 2) . ' ر.س')
                ->description('مجموع مبالغ الدفعات')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            StatsOverviewWidget\Stat::make('عقود تنتهي هذا الشهر', $expiringThisMonth)
                ->description('عقود تنتهي في ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($expiringThisMonth > 0 ? 'danger' : 'success'),
        ];
    }
}
