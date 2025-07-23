<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use App\Filament\Resources\ContractResource\Widgets\ContractStatsWidget;
use App\Models\Contract;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListContracts extends ListRecords
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة عقد جديد'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'العقود';
    }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الجميع')
                ->badge(Contract::count()),
                
            'active' => Tab::make('النشطة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->badge(Contract::where('status', 'active')->count())
                ->badgeColor('success'),
                
            'expired' => Tab::make('المنتهية')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'expired'))
                ->badge(Contract::where('status', 'expired')->count())
                ->badgeColor('danger'),
                
            'renewal_pending' => Tab::make('في انتظار التجديد')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'renewal_pending'))
                ->badge(Contract::where('status', 'renewal_pending')->count())
                ->badgeColor('warning'),
                
            'terminated' => Tab::make('المفسوخة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'terminated'))
                ->badge(Contract::where('status', 'terminated')->count())
                ->badgeColor('gray'),
                
            'ending_soon' => Tab::make('تنتهي قريباً')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('end_date', '<=', now()->addDays(30))->where('status', 'active'))
                ->badge(Contract::where('end_date', '<=', now()->addDays(30))->where('status', 'active')->count())
                ->badgeColor('orange'),
                
            'monthly' => Tab::make('شهرية')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payment_frequency', 'monthly'))
                ->badge(Contract::where('payment_frequency', 'monthly')->count())
                ->badgeColor('info'),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            ContractStatsWidget::class,
        ];
    }
}
