<?php

namespace App\Filament\Resources\DistrictResource\Pages;

use App\Filament\Resources\DistrictResource;
use App\Models\District;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDistricts extends ListRecords
{
    protected static string $resource = DistrictResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة حي جديد'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'الأحياء';
    }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الجميع')
                ->badge(District::count()),
            'active' => Tab::make('النشطة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(District::where('is_active', true)->count())
                ->badgeColor('success'),
            'inactive' => Tab::make('غير النشطة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(District::where('is_active', false)->count())
                ->badgeColor('gray'),
            'with_buildings' => Tab::make('تحتوي على مباني')
                ->modifyQueryUsing(fn (Builder $query) => $query->has('buildings'))
                ->badge(District::has('buildings')->count())
                ->badgeColor('info'),
        ];
    }
}
