<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Shop;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Landlord;
use App\Models\City;
use App\Models\District;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    /**
     * الإحصائيات العامة للنظام
     */
    public function overview()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_buildings' => Building::count(),
                'total_shops' => Shop::count(),
                'total_tenants' => Tenant::count(),
                'total_landlords' => Landlord::count(),
                'total_cities' => City::count(),
                'total_districts' => District::count(),
                'active_contracts' => Contract::where('status', 'active')->count(),
                'expired_contracts' => Contract::where('status', 'expired')->count(),
                'pending_contracts' => Contract::where('status', 'pending')->count(),
                'total_revenue' => Payment::where('status', 'paid')->sum('paid_amount'),
                'pending_payments' => Payment::where('status', 'pending')->sum('invoice_amount'),
                'overdue_payments' => Payment::where('status', 'overdue')->count(),
            ]
        ]);
    }

    /**
     * إحصائيات الإشغال
     */
    public function occupancy()
    {
        $totalShops = Shop::count();
        $occupiedShops = Shop::whereHas('contracts', function($query) {
            $query->where('status', 'active')
                  ->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
        })->count();
        
        $vacantShops = $totalShops - $occupiedShops;
        $occupancyRate = $totalShops > 0 ? ($occupiedShops / $totalShops) * 100 : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_shops' => $totalShops,
                'occupied_shops' => $occupiedShops,
                'vacant_shops' => $vacantShops,
                'occupancy_rate' => round($occupancyRate, 2),
                'shops_by_status' => [
                    'occupied' => $occupiedShops,
                    'vacant' => $vacantShops,
                    'maintenance' => Shop::where('status', 'maintenance')->count(),
                ]
            ]
        ]);
    }

    /**
     * الإيرادات الشهرية
     */
    public function monthlyRevenue()
    {
        $months = [];
        $revenues = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $monthlyRevenue = Payment::where('status', 'paid')
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('paid_amount');
            $revenues[] = (float) $monthlyRevenue;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $months,
                'revenues' => $revenues,
                'total_revenue' => array_sum($revenues),
                'average_monthly' => count($revenues) > 0 ? array_sum($revenues) / count($revenues) : 0,
            ]
        ]);
    }

    /**
     * معدلات التحصيل
     */
    public function collectionRates()
    {
        $months = [];
        $rates = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $totalDue = Payment::whereYear('due_date', $date->year)
                ->whereMonth('due_date', $date->month)
                ->sum('invoice_amount');
            
            $totalCollected = Payment::where('status', 'paid')
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('paid_amount');
            
            $rate = $totalDue > 0 ? ($totalCollected / $totalDue) * 100 : 0;
            $rates[] = round($rate, 1);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $months,
                'rates' => $rates,
                'current_month_rate' => $rates[count($rates) - 1] ?? 0,
            ]
        ]);
    }

    /**
     * مقارنة الأداء الشهري
     */
    public function monthlyComparison()
    {
        $months = [];
        $revenues = [];
        $newContracts = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            // الإيرادات الشهرية
            $monthlyRevenue = Payment::where('status', 'paid')
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('paid_amount');
            $revenues[] = (float) $monthlyRevenue;
            
            // العقود الجديدة
            $monthlyContracts = Contract::whereYear('start_date', $date->year)
                ->whereMonth('start_date', $date->month)
                ->count();
            $newContracts[] = $monthlyContracts;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $months,
                'revenues' => $revenues,
                'new_contracts' => $newContracts,
                'growth_rate' => $this->calculateGrowthRate($revenues),
            ]
        ]);
    }

    /**
     * أداء المباني
     */
    public function buildingPerformance()
    {
        $buildings = Building::with(['shops.contracts.payments'])
            ->get()
            ->map(function ($building) {
                $totalRevenue = 0;
                $occupiedShops = 0;
                $totalShops = $building->shops->count();

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

                    $hasActiveContract = $shop->contracts()
                        ->where('status', 'active')
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())
                        ->exists();
                    
                    if ($hasActiveContract) {
                        $occupiedShops++;
                    }
                }

                $occupancyRate = $totalShops > 0 ? ($occupiedShops / $totalShops) * 100 : 0;

                return [
                    'id' => $building->id,
                    'name' => $building->name,
                    'revenue' => $totalRevenue,
                    'occupancy_rate' => round($occupancyRate, 1),
                    'occupied_shops' => $occupiedShops,
                    'total_shops' => $totalShops,
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'buildings' => $buildings,
                'top_performer' => $buildings->first(),
                'total_buildings' => $buildings->count(),
            ]
        ]);
    }

    /**
     * الأداء المالي السنوي
     */
    public function annualFinancialPerformance()
    {
        $months = [];
        $expectedRevenues = [];
        $actualRevenues = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            // الإيرادات المتوقعة (من العقود النشطة)
            $expectedRevenue = Contract::where('status', 'active')
                ->where('start_date', '<=', $date->endOfMonth())
                ->where('end_date', '>=', $date->startOfMonth())
                ->sum('payment_amount');
            $expectedRevenues[] = (float) $expectedRevenue;
            
            // الإيرادات الفعلية
            $actualRevenue = Payment::where('status', 'paid')
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('paid_amount');
            $actualRevenues[] = (float) $actualRevenue;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $months,
                'expected_revenues' => $expectedRevenues,
                'actual_revenues' => $actualRevenues,
                'total_expected' => array_sum($expectedRevenues),
                'total_actual' => array_sum($actualRevenues),
                'performance_rate' => array_sum($expectedRevenues) > 0 
                    ? (array_sum($actualRevenues) / array_sum($expectedRevenues)) * 100 
                    : 0,
            ]
        ]);
    }

    /**
     * النشاطات الحديثة
     */
    public function recentActivities()
    {
        $recentContracts = Contract::with(['shop', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($contract) {
                return [
                    'type' => 'contract',
                    'description' => "عقد جديد: {$contract->shop->name} مع {$contract->tenant->name}",
                    'amount' => $contract->payment_amount,
                    'date' => $contract->created_at,
                    'status' => $contract->status,
                ];
            });

        $recentPayments = Payment::with(['contract.tenant', 'contract.shop'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'type' => 'payment',
                    'description' => "دفعة من {$payment->contract->tenant->name} للمحل {$payment->contract->shop->name}",
                    'amount' => $payment->paid_amount,
                    'date' => $payment->created_at,
                    'status' => $payment->status,
                ];
            });

        $recentTenants = Tenant::orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($tenant) {
                return [
                    'type' => 'tenant',
                    'description' => "مستأجر جديد: {$tenant->name}",
                    'amount' => null,
                    'date' => $tenant->created_at,
                    'status' => 'active',
                ];
            });

        $activities = collect()
            ->merge($recentContracts)
            ->merge($recentPayments)
            ->merge($recentTenants)
            ->sortByDesc('date')
            ->take(10)
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'activities' => $activities,
                'total_activities' => $activities->count(),
            ]
        ]);
    }

    /**
     * تقرير شامل
     */
    public function dashboard()
    {
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'total_buildings' => Building::count(),
                    'occupancy_rate' => $this->calculateOccupancyRate(),
                    'monthly_revenue' => Payment::where('status', 'paid')
                        ->whereYear('payment_date', $currentMonth->year)
                        ->whereMonth('payment_date', $currentMonth->month)
                        ->sum('paid_amount'),
                    'overdue_payments' => Payment::where('status', 'overdue')->count(),
                ],
                'growth_metrics' => [
                    'revenue_growth' => $this->calculateRevenueGrowth(),
                    'tenant_growth' => $this->calculateTenantGrowth(),
                    'contract_growth' => $this->calculateContractGrowth(),
                ],
                'quick_stats' => [
                    'active_contracts' => Contract::where('status', 'active')->count(),
                    'total_tenants' => Tenant::count(),
                    'average_rent' => Contract::where('status', 'active')->avg('payment_amount'),
                    'collection_rate' => $this->calculateCurrentCollectionRate(),
                ],
            ]
        ]);
    }

    /**
     * Helper Methods
     */
    private function calculateGrowthRate($data)
    {
        if (count($data) < 2) return 0;
        
        $current = end($data);
        $previous = $data[count($data) - 2];
        
        return $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;
    }

    private function calculateOccupancyRate()
    {
        $totalShops = Shop::count();
        $occupiedShops = Shop::whereHas('contracts', function($query) {
            $query->where('status', 'active')
                  ->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
        })->count();
        
        return $totalShops > 0 ? ($occupiedShops / $totalShops) * 100 : 0;
    }

    private function calculateRevenueGrowth()
    {
        $currentMonth = Payment::where('status', 'paid')
            ->whereYear('payment_date', Carbon::now()->year)
            ->whereMonth('payment_date', Carbon::now()->month)
            ->sum('paid_amount');
            
        $lastMonth = Payment::where('status', 'paid')
            ->whereYear('payment_date', Carbon::now()->subMonth()->year)
            ->whereMonth('payment_date', Carbon::now()->subMonth()->month)
            ->sum('paid_amount');

        return $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0;
    }

    private function calculateTenantGrowth()
    {
        $currentMonthTenants = Tenant::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
            
        $lastMonthTenants = Tenant::whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();

        return $lastMonthTenants > 0 ? (($currentMonthTenants - $lastMonthTenants) / $lastMonthTenants) * 100 : 0;
    }

    private function calculateContractGrowth()
    {
        $currentMonthContracts = Contract::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
            
        $lastMonthContracts = Contract::whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();

        return $lastMonthContracts > 0 ? (($currentMonthContracts - $lastMonthContracts) / $lastMonthContracts) * 100 : 0;
    }

    private function calculateCurrentCollectionRate()
    {
        $currentMonth = Carbon::now();
        
        $totalDue = Payment::whereYear('due_date', $currentMonth->year)
            ->whereMonth('due_date', $currentMonth->month)
            ->sum('invoice_amount');
        
        $totalCollected = Payment::where('status', 'paid')
            ->whereYear('payment_date', $currentMonth->year)
            ->whereMonth('payment_date', $currentMonth->month)
            ->sum('paid_amount');
        
        return $totalDue > 0 ? ($totalCollected / $totalDue) * 100 : 0;
    }
}
