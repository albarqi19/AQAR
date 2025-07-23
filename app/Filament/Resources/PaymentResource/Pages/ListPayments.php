<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\PaymentResource\Widgets\PaymentStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;
    
    protected static ?string $title = 'المدفوعات';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة مدفوعة جديدة')
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PaymentStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->badge(fn () => \App\Models\Payment::count()),
                
            'pending' => Tab::make('في الانتظار')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => \App\Models\Payment::where('status', 'pending')->count())
                ->badgeColor('warning'),
                
            'partial' => Tab::make('دفع جزئي')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'partial'))
                ->badge(fn () => \App\Models\Payment::where('status', 'partial')->count())
                ->badgeColor('info'),
                
            'paid' => Tab::make('مدفوع')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'paid'))
                ->badge(fn () => \App\Models\Payment::where('status', 'paid')->count())
                ->badgeColor('success'),
                
            'overdue' => Tab::make('متأخر')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'overdue'))
                ->badge(fn () => \App\Models\Payment::where('status', 'overdue')->count())
                ->badgeColor('danger'),
                
            'current_month' => Tab::make('الشهر الحالي')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('month', now()->month)
                          ->where('year', now()->year)
                )
                ->badge(fn () => \App\Models\Payment::where('month', now()->month)
                                                 ->where('year', now()->year)
                                                 ->count())
                ->badgeColor('primary'),
                
            'due_soon' => Tab::make('تستحق قريباً')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereBetween('due_date', [now(), now()->addDays(7)])
                          ->where('status', '!=', 'paid')
                )
                ->badge(fn () => \App\Models\Payment::whereBetween('due_date', [now(), now()->addDays(7)])
                                                  ->where('status', '!=', 'paid')
                                                  ->count())
                ->badgeColor('warning'),
        ];
    }
}
