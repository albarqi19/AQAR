<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use App\Filament\Resources\TenantResource\Widgets\TenantStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTenants extends ListRecords
{
    protected static string $resource = TenantResource::class;
    
    protected static ?string $title = 'المستأجرين';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة مستأجر جديد')
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TenantStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->badge(fn () => \App\Models\Tenant::count()),
                
            'active' => Tab::make('نشط')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => \App\Models\Tenant::where('is_active', true)->count())
                ->badgeColor('success'),
                
            'inactive' => Tab::make('غير نشط')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(fn () => \App\Models\Tenant::where('is_active', false)->count())
                ->badgeColor('danger'),
                
            'companies' => Tab::make('الشركات')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('company_name'))
                ->badge(fn () => \App\Models\Tenant::whereNotNull('company_name')->count())
                ->badgeColor('info'),
                
            'individuals' => Tab::make('الأفراد')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('company_name'))
                ->badge(fn () => \App\Models\Tenant::whereNull('company_name')->count())
                ->badgeColor('warning'),
                
            'with_contracts' => Tab::make('لديهم عقود')
                ->modifyQueryUsing(fn (Builder $query) => $query->has('contracts'))
                ->badge(fn () => \App\Models\Tenant::has('contracts')->count())
                ->badgeColor('primary'),
        ];
    }
}
