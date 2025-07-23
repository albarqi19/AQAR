<?php

namespace App\Filament\Resources\DistrictResource\Pages;

use App\Filament\Resources\DistrictResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewDistrict extends ViewRecord
{
    protected static string $resource = DistrictResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل الحي'),
            Actions\DeleteAction::make()
                ->label('حذف الحي'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'عرض الحي: ' . $this->record->name;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات الحي')
                    ->schema([
                        Infolists\Components\TextEntry::make('city.name')
                            ->label('المدينة')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('name')
                            ->label('اسم الحي')
                            ->size('lg')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('description')
                            ->label('الوصف')
                            ->columnSpanFull(),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('حالة النشاط')
                            ->boolean(),
                    ])->columns(2),
                
                Infolists\Components\Section::make('الإحصائيات')
                    ->schema([
                        Infolists\Components\TextEntry::make('buildings_count')
                            ->label('عدد المباني')
                            ->badge()
                            ->color('success')
                            ->getStateUsing(fn ($record) => $record->buildings()->count()),
                        Infolists\Components\TextEntry::make('shops_count')
                            ->label('عدد المحلات')
                            ->badge()
                            ->color('warning')
                            ->getStateUsing(fn ($record) => $record->buildings()->withCount('shops')->get()->sum('shops_count')),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('d/m/Y H:i'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('تاريخ آخر تحديث')
                            ->dateTime('d/m/Y H:i'),
                    ])->columns(2),
            ]);
    }
}
