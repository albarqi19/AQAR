<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;

class PaymentStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalPayments = \App\Models\Payment::count();
        $totalAmount = \App\Models\Payment::sum('invoice_amount');
        $paidAmount = \App\Models\Payment::sum('paid_amount');
        $remainingAmount = \App\Models\Payment::sum('remaining_amount');
        $overdueCount = \App\Models\Payment::where('due_date', '<', now())
                                          ->where('status', '!=', 'paid')
                                          ->count();
        $currentMonthPayments = \App\Models\Payment::where('month', now()->month)
                                                  ->where('year', now()->year)
                                                  ->count();

        return [
            StatsOverviewWidget\Stat::make('إجمالي المدفوعات', $totalPayments)
                ->description('جميع المدفوعات المسجلة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            StatsOverviewWidget\Stat::make('إجمالي المبلغ', number_format($totalAmount, 2) . ' ر.س')
                ->description('قيمة جميع الفواتير')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),

            StatsOverviewWidget\Stat::make('المبلغ المدفوع', number_format($paidAmount, 2) . ' ر.س')
                ->description('إجمالي المبالغ المدفوعة')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            StatsOverviewWidget\Stat::make('المبلغ المتبقي', number_format($remainingAmount, 2) . ' ر.س')
                ->description('المبالغ غير المدفوعة')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($remainingAmount > 0 ? 'danger' : 'success'),

            StatsOverviewWidget\Stat::make('مدفوعات متأخرة', $overdueCount)
                ->description('تجاوزت تاريخ الاستحقاق')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),

            StatsOverviewWidget\Stat::make('مدفوعات الشهر الحالي', $currentMonthPayments)
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),
        ];
    }
}
