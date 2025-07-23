<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Building;
use App\Models\Shop;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Tenant;
use Carbon\Carbon;

class ReportsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        return false; // لا يظهر في الصفحة الرئيسية
    }

    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي المستأجرين', Tenant::count())
                ->description('عدد المستأجرين المسجلين')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('العقود النشطة', Contract::where('status', 'active')->count())
                ->description('عقود سارية المفعول')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('success'),

            Stat::make('إجمالي الإيرادات', 'ر.س ' . number_format(Payment::where('status', 'paid')->sum('paid_amount'), 2))
                ->description('إجمالي المدفوعات المحصلة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('المدفوعات المتأخرة', Payment::where('status', 'overdue')->count())
                ->description('مدفوعات تحتاج متابعة')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('متوسط الإيجار الشهري', 'ر.س ' . number_format(Contract::where('status', 'active')->avg('payment_amount') ?? 0, 2))
                ->description('متوسط إيجار المحلات النشطة')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('إيرادات هذا الشهر', 'ر.س ' . number_format(
                Payment::where('status', 'paid')
                    ->whereYear('payment_date', Carbon::now()->year)
                    ->whereMonth('payment_date', Carbon::now()->month)
                    ->sum('paid_amount'), 2
                ))
                ->description('المدفوعات المحصلة هذا الشهر')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
        ];
    }
}
