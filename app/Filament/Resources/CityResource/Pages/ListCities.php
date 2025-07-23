<?php

namespace App\Filament\Resources\CityResource\Pages;

use App\Filament\Resources\CityResource;
use App\Models\City;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCities extends ListRecords
{
    protected static string $resource = CityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة مدينة جديدة'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'المدن';
    }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الجميع')
                ->badge(City::count()),
            'active' => Tab::make('النشطة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(City::where('is_active', true)->count())
                ->badgeColor('success'),
            'inactive' => Tab::make('غير النشطة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(City::where('is_active', false)->count())
                ->badgeColor('gray'),
            'with_districts' => Tab::make('تحتوي على أحياء')
                ->modifyQueryUsing(fn (Builder $query) => $query->has('districts'))
                ->badge(City::has('districts')->count())
                ->badgeColor('info'),
        ];
    }
}
