<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Tenant;
use Carbon\Carbon;

class RecentActivitiesWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-activities';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return false; // لا يظهر في الصفحة الرئيسية
    }

    public function getViewData(): array
    {
        return [
            'recentContracts' => $this->getRecentContracts(),
            'recentPayments' => $this->getRecentPayments(),
            'recentTenants' => $this->getRecentTenants(),
        ];
    }

    private function getRecentContracts()
    {
        return Contract::with(['shop', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($contract) {
                return [
                    'type' => 'عقد جديد',
                    'description' => "عقد {$contract->shop->name} مع {$contract->tenant->name}",
                    'amount' => $contract->payment_amount,
                    'date' => $contract->created_at,
                    'status' => $contract->status,
                    'icon' => 'heroicon-o-document-text',
                    'color' => match($contract->status) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    },
                ];
            });
    }

    private function getRecentPayments()
    {
        return Payment::with(['contract.tenant', 'contract.shop'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'type' => 'دفعة',
                    'description' => "دفعة من {$payment->contract->tenant->name} للمحل {$payment->contract->shop->name}",
                    'amount' => $payment->paid_amount,
                    'date' => $payment->created_at,
                    'status' => $payment->status,
                    'icon' => 'heroicon-o-banknotes',
                    'color' => match($payment->status) {
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    },
                ];
            });
    }

    private function getRecentTenants()
    {
        return Tenant::orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($tenant) {
                return [
                    'type' => 'مستأجر جديد',
                    'description' => "مستأجر جديد: {$tenant->name}",
                    'amount' => null,
                    'date' => $tenant->created_at,
                    'status' => 'active',
                    'icon' => 'heroicon-o-user-plus',
                    'color' => 'info',
                ];
            });
    }
}
