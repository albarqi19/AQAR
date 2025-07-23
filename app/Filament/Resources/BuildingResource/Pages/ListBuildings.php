<?php

namespace App\Filament\Resources\BuildingResource\Pages;

use App\Filament\Resources\BuildingResource;
use App\Models\Building;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBuildings extends ListRecords
{
    protected static string $resource = BuildingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة مبنى جديد'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'المباني';
    }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الجميع')
                ->badge(Building::count()),
            'active' => Tab::make('النشطة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(Building::where('is_active', true)->count())
                ->badgeColor('success'),
            'inactive' => Tab::make('غير النشطة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(Building::where('is_active', false)->count())
                ->badgeColor('gray'),
            'with_shops' => Tab::make('تحتوي على محلات')
                ->modifyQueryUsing(fn (Builder $query) => $query->has('shops'))
                ->badge(Building::has('shops')->count())
                ->badgeColor('info'),
            'recent' => Tab::make('المضافة حديثاً')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(30)))
                ->badge(Building::where('created_at', '>=', now()->subDays(30))->count())
                ->badgeColor('warning'),
        ];
    }
}
