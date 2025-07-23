<?php

namespace App\Filament\Resources\BuildingResource\Pages;

use App\Filament\Resources\BuildingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewBuilding extends ViewRecord
{
    protected static string $resource = BuildingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل المبنى'),
            Actions\DeleteAction::make()
                ->label('حذف المبنى'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'عرض المبنى: ' . $this->record->name;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات المبنى')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('اسم المبنى')
                            ->size('lg')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('building_number')
                            ->label('رقم المبنى')
                            ->badge()
                            ->color('gray'),
                        Infolists\Components\TextEntry::make('district.city.name')
                            ->label('المدينة')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('district.name')
                            ->label('الحي')
                            ->badge()
                            ->color('warning'),
                        Infolists\Components\TextEntry::make('landlord.name')
                            ->label('المكتب العقاري المدير')
                            ->badge()
                            ->color('success'),
                        Infolists\Components\TextEntry::make('address')
                            ->label('العنوان التفصيلي')
                            ->columnSpanFull(),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('حالة النشاط')
                            ->boolean(),
                    ])->columns(3),
                
                Infolists\Components\Section::make('المواصفات الفنية')
                    ->schema([
                        Infolists\Components\TextEntry::make('floors_count')
                            ->label('عدد الأدوار')
                            ->badge()
                            ->color('gray')
                            ->suffix(' دور'),
                        Infolists\Components\TextEntry::make('total_shops')
                            ->label('إجمالي المحلات')
                            ->badge()
                            ->color('success')
                            ->suffix(' محل'),
                        Infolists\Components\TextEntry::make('total_area')
                            ->label('المساحة الإجمالية')
                            ->badge()
                            ->color('info')
                            ->suffix(' م²')
                            ->formatStateUsing(fn (?string $state): string => 
                                $state ? number_format((float)$state, 0) : '-'
                            ),
                        Infolists\Components\TextEntry::make('construction_year')
                            ->label('سنة البناء')
                            ->badge()
                            ->color('warning'),
                    ])->columns(4),
                
                Infolists\Components\Section::make('الإحصائيات')
                    ->schema([
                        Infolists\Components\TextEntry::make('shops_count')
                            ->label('المحلات المسجلة')
                            ->badge()
                            ->color('success')
                            ->getStateUsing(fn ($record) => $record->shops()->count()),
                        Infolists\Components\TextEntry::make('occupied_shops')
                            ->label('المحلات المؤجرة')
                            ->badge()
                            ->color('info')
                            ->getStateUsing(fn ($record) => $record->shops()->where('status', 'occupied')->count()),
                        Infolists\Components\TextEntry::make('vacant_shops')
                            ->label('المحلات الشاغرة')
                            ->badge()
                            ->color('warning')
                            ->getStateUsing(fn ($record) => $record->shops()->where('status', 'vacant')->count()),
                        Infolists\Components\TextEntry::make('maintenance_shops')
                            ->label('محلات تحت الصيانة')
                            ->badge()
                            ->color('danger')
                            ->getStateUsing(fn ($record) => $record->shops()->where('status', 'maintenance')->count()),
                    ])->columns(4),
                
                Infolists\Components\Section::make('تفاصيل إضافية')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->label('وصف المبنى')
                            ->columnSpanFull(),
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
