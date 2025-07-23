<?php

namespace App\Filament\Resources\ContractResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Contract;

class ContractStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalContracts = Contract::count();
        $activeContracts = Contract::where('status', 'active')->count();
        $expiredContracts = Contract::where('status', 'expired')->count();
        $pendingContracts = Contract::where('status', 'pending')->count();
        $totalAnnualRent = Contract::sum('annual_rent');
        $totalPaymentAmount = Contract::sum('payment_amount');
        $expiringThisMonth = Contract::where('end_date', '>=', now()->startOfMonth())
                                   ->where('end_date', '<=', now()->endOfMonth())
                                   ->where('status', 'active')
                                   ->count();

        return [
            Stat::make('إجمالي العقود', $totalContracts)
                ->description('جميع العقود المسجلة')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('عقود نشطة', $activeContracts)
                ->description('العقود الجارية حالياً')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('عقود منتهية', $expiredContracts)
                ->description('العقود المنتهية الصلاحية')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('عقود معلقة', $pendingContracts)
                ->description('في انتظار الموافقة')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('إجمالي الإيجار السنوي', number_format($totalAnnualRent, 2) . ' ر.س')
                ->description('مجموع الإيجارات السنوية')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),

            Stat::make('إجمالي مبالغ الدفع', number_format($totalPaymentAmount, 2) . ' ر.س')
                ->description('مجموع مبالغ الدفعات')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}
