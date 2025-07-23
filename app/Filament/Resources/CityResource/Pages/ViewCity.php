<?php

namespace App\Filament\Resources\CityResource\Pages;

use App\Filament\Resources\CityResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewCity extends ViewRecord
{
    protected static string $resource = CityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل المدينة'),
            Actions\DeleteAction::make()
                ->label('حذف المدينة'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'عرض المدينة: ' . $this->record->name;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات المدينة')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('اسم المدينة')
                            ->size('lg')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('code')
                            ->label('رمز المدينة')
                            ->badge()
                            ->color('gray'),
                        Infolists\Components\TextEntry::make('description')
                            ->label('الوصف')
                            ->columnSpanFull(),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('حالة النشاط')
                            ->boolean(),
                    ])->columns(2),
                
                Infolists\Components\Section::make('الإحصائيات')
                    ->schema([
                        Infolists\Components\TextEntry::make('districts_count')
                            ->label('عدد الأحياء')
                            ->badge()
                            ->color('info')
                            ->getStateUsing(fn ($record) => $record->districts()->count()),
                        Infolists\Components\TextEntry::make('buildings_count')
                            ->label('عدد المباني')
                            ->badge()
                            ->color('success')
                            ->getStateUsing(fn ($record) => $record->districts()->withCount('buildings')->get()->sum('buildings_count')),
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
