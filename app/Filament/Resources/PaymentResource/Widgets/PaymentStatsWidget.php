<?php

namespace App\Filament\Resources\PaymentResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Payment;

class PaymentStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalPayments = Payment::count();
        $totalAmount = Payment::sum('invoice_amount');
        $paidAmount = Payment::sum('paid_amount');
        $remainingAmount = Payment::sum('remaining_amount');
        $overdueCount = Payment::where('due_date', '<', now())
                              ->where('status', '!=', 'paid')
                              ->count();
        $currentMonthPayments = Payment::where('month', now()->month)
                                      ->where('year', now()->year)
                                      ->count();

        return [
            Stat::make('إجمالي المدفوعات', $totalPayments)
                ->description('جميع المدفوعات المسجلة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('إجمالي المبلغ', number_format($totalAmount, 2) . ' ر.س')
                ->description('قيمة جميع الفواتير')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),

            Stat::make('المبلغ المدفوع', number_format($paidAmount, 2) . ' ر.س')
                ->description('إجمالي المبالغ المدفوعة')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('المبلغ المتبقي', number_format($remainingAmount, 2) . ' ر.س')
                ->description('المبالغ غير المدفوعة')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($remainingAmount > 0 ? 'danger' : 'success'),

            Stat::make('مدفوعات متأخرة', $overdueCount)
                ->description('تجاوزت تاريخ الاستحقاق')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),

            Stat::make('مدفوعات الشهر الحالي', $currentMonthPayments)
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),
        ];
    }
}
