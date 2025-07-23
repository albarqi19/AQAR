<?php

namespace App\Filament\Resources\LandlordResource\Pages;

use App\Filament\Resources\LandlordResource;
use App\Filament\Resources\LandlordResource\Widgets\LandlordStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLandlords extends ListRecords
{
    protected static string $resource = LandlordResource::class;
    
    protected static ?string $title = 'المكاتب العقارية';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة مكتب عقاري جديد')
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LandlordStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->badge(fn () => \App\Models\Landlord::count()),
                
            'active' => Tab::make('نشط')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => \App\Models\Landlord::where('is_active', true)->count())
                ->badgeColor('success'),
                
            'inactive' => Tab::make('غير نشط')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(fn () => \App\Models\Landlord::where('is_active', false)->count())
                ->badgeColor('danger'),
                
            'companies' => Tab::make('الشركات')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('company_name'))
                ->badge(fn () => \App\Models\Landlord::whereNotNull('company_name')->count())
                ->badgeColor('info'),
                
            'individuals' => Tab::make('الأفراد')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('company_name'))
                ->badge(fn () => \App\Models\Landlord::whereNull('company_name')->count())
                ->badgeColor('warning'),
                
            'with_buildings' => Tab::make('لديهم مباني')
                ->modifyQueryUsing(fn (Builder $query) => $query->has('buildings'))
                ->badge(fn () => \App\Models\Landlord::has('buildings')->count())
                ->badgeColor('primary'),
                
            'high_commission' => Tab::make('عمولة عالية')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('commission_rate', '>', 5))
                ->badge(fn () => \App\Models\Landlord::where('commission_rate', '>', 5)->count())
                ->badgeColor('success'),
        ];
    }
}
