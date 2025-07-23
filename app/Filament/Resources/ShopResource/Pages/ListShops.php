<?php

namespace App\Filament\Resources\ShopResource\Pages;

use App\Filament\Resources\ShopResource;
use App\Models\Shop;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListShops extends ListRecords
{
    protected static string $resource = ShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة محل جديد'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'المحلات';
    }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الجميع')
                ->badge(Shop::count()),
            'vacant' => Tab::make('شاغر')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'vacant'))
                ->badge(Shop::where('status', 'vacant')->count())
                ->badgeColor('warning'),
            'occupied' => Tab::make('مؤجر')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'occupied'))
                ->badge(Shop::where('status', 'occupied')->count())
                ->badgeColor('success'),
            'maintenance' => Tab::make('تحت الصيانة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'maintenance'))
                ->badge(Shop::where('status', 'maintenance')->count())
                ->badgeColor('danger'),
        ];
    }
}
