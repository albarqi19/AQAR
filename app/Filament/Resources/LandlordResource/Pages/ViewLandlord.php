<?php

namespace App\Filament\Resources\LandlordResource\Pages;

use App\Filament\Resources\LandlordResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;

class ViewLandlord extends ViewRecord
{
    protected static string $resource = LandlordResource::class;

    protected static ?string $title = 'عرض المكتب العقاري';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل المكتب العقاري'),
            Actions\DeleteAction::make()
                ->label('حذف المكتب العقاري'),
            Actions\Action::make('toggle_status')
                ->label(fn () => $this->record->is_active ? 'إلغاء التفعيل' : 'تفعيل')
                ->icon(fn () => $this->record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn () => $this->record->is_active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['is_active' => !$this->record->is_active]);
                    $this->redirect(request()->header('Referer'));
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('المعلومات الأساسية')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('اسم المكتب العقاري')
                                    ->weight(FontWeight::Bold)
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                    
                                Infolists\Components\TextEntry::make('is_active')
                                    ->label('الحالة')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'نشط' : 'غير نشط'),
                                    
                                Infolists\Components\TextEntry::make('company_name')
                                    ->label('اسم الشركة')
                                    ->placeholder('مكتب فردي')
                                    ->weight(FontWeight::Medium),
                                    
                                Infolists\Components\TextEntry::make('commercial_registration')
                                    ->label('رقم السجل التجاري')
                                    ->placeholder('غير متوفر')
                                    ->copyable()
                                    ->copyMessage('تم نسخ السجل التجاري'),
                                    
                                Infolists\Components\TextEntry::make('license_number')
                                    ->label('رقم الرخصة')
                                    ->placeholder('غير متوفر')
                                    ->copyable()
                                    ->copyMessage('تم نسخ رقم الرخصة'),
                                    
                                Infolists\Components\TextEntry::make('commission_rate')
                                    ->label('معدل العمولة')
                                    ->suffix('%')
                                    ->badge()
                                    ->color('success'),
                            ]),
                    ])
                    ->icon('heroicon-o-briefcase'),

                Infolists\Components\Section::make('معلومات الاتصال')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('phone')
                                    ->label('رقم الهاتف')
                                    ->icon('heroicon-m-phone')
                                    ->copyable()
                                    ->copyMessage('تم نسخ رقم الهاتف'),
                                    
                                Infolists\Components\TextEntry::make('email')
                                    ->label('البريد الإلكتروني')
                                    ->icon('heroicon-m-envelope')
                                    ->copyable()
                                    ->copyMessage('تم نسخ البريد الإلكتروني')
                                    ->placeholder('غير متوفر'),
                                    
                                Infolists\Components\TextEntry::make('contact_person')
                                    ->label('الشخص المسؤول')
                                    ->placeholder('غير محدد'),
                                    
                                Infolists\Components\TextEntry::make('address')
                                    ->label('العنوان')
                                    ->placeholder('غير متوفر')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->icon('heroicon-o-phone'),

                Infolists\Components\Section::make('إحصائيات المباني')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_buildings')
                                    ->label('إجمالي المباني')
                                    ->state(fn () => $this->record->buildings()->count())
                                    ->badge()
                                    ->color('info'),
                                    
                                Infolists\Components\TextEntry::make('total_shops')
                                    ->label('إجمالي المحلات')
                                    ->state(fn () => $this->record->buildings()->withCount('shops')->get()->sum('shops_count'))
                                    ->badge()
                                    ->color('primary'),
                                    
                                Infolists\Components\TextEntry::make('active_contracts')
                                    ->label('العقود النشطة')
                                    ->state(function () {
                                        return $this->record->buildings()
                                            ->with(['shops.contracts' => fn($q) => $q->where('status', 'active')])
                                            ->get()
                                            ->pluck('shops')
                                            ->flatten()
                                            ->pluck('contracts')
                                            ->flatten()
                                            ->count();
                                    })
                                    ->badge()
                                    ->color('success'),
                            ]),
                    ])
                    ->icon('heroicon-o-building-office'),

                Infolists\Components\Section::make('المستندات')
                    ->schema([
                        Infolists\Components\TextEntry::make('documents')
                            ->label('المستندات المرفقة')
                            ->formatStateUsing(function ($state) {
                                if (!$state || count($state) === 0) {
                                    return 'لا توجد مستندات مرفقة';
                                }
                                return count($state) . ' مستند مرفق';
                            })
                            ->badge()
                            ->color(fn ($state) => ($state && count($state) > 0) ? 'success' : 'gray'),
                    ])
                    ->icon('heroicon-o-paper-clip')
                    ->collapsible(),

                Infolists\Components\Section::make('معلومات النظام')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('تاريخ التسجيل')
                                    ->dateTime('d/m/Y H:i'),
                                    
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('آخر تحديث')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
