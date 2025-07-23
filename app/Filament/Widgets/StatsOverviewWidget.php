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

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        // إحصائيات المباني والمحلات
        $totalBuildings = Building::count();
        $totalShops = Shop::count();
        $occupiedShops = Shop::where('status', 'occupied')->count();
        
        // إحصائيات مالية
        $currentMonth = Carbon::now();
        $monthlyRevenue = Payment::where('status', 'paid')
            ->whereYear('payment_date', $currentMonth->year)
            ->whereMonth('payment_date', $currentMonth->month)
            ->sum('paid_amount');
            
        // المدفوعات المتأخرة
        $overduePayments = Payment::where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->count();
            
        // معدل الإشغال
        $occupancyRate = $totalShops > 0 ? round(($occupiedShops / $totalShops) * 100, 1) : 0;

        return [
            Stat::make('إجمالي المباني', $totalBuildings)
                ->description('عدد المباني المسجلة')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
                
            Stat::make('معدل الإشغال', $occupancyRate . '%')
                ->description($occupancyRate >= 80 ? 'معدل ممتاز' : ($occupancyRate >= 60 ? 'معدل جيد' : 'يحتاج تحسين'))
                ->descriptionIcon($occupancyRate >= 80 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($occupancyRate >= 80 ? 'success' : ($occupancyRate >= 60 ? 'warning' : 'danger'))
                ->chart($occupancyRate >= 80 ? [3, 5, 8, 9, 8, 10, 9] : [5, 3, 2, 4, 6, 5, 7]),
                
            Stat::make('إيرادات هذا الشهر', number_format($monthlyRevenue, 0) . ' ر.س')
                ->description('الإيرادات المحصلة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([2, 4, 6, 8, 5, 9, 7, 8]),
                
            Stat::make('مدفوعات متأخرة', $overduePayments)
                ->description($overduePayments > 0 ? 'تحتاج متابعة فورية' : 'لا توجد مدفوعات متأخرة')
                ->descriptionIcon($overduePayments > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($overduePayments > 0 ? 'danger' : 'success')
                ->chart($overduePayments > 0 ? [8, 6, 4, 7, 5, 8, 9] : [2, 1, 0, 1, 0, 0, 1]),
        ];
    }
}
