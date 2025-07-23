<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewContract extends ViewRecord
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل العقد'),
            Actions\Action::make('view_contract_document')
                ->label('صورة العقد')
                ->icon('heroicon-o-document')
                ->color('info')
                ->visible(fn () => $this->record->hasContractDocument())
                ->url(fn () => $this->record->getContractDocumentUrl())
                ->openUrlInNewTab(),
            Actions\Action::make('renew')
                ->label('تجديد العقد')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->visible(fn () => $this->record->status === 'active' && \Carbon\Carbon::parse($this->record->end_date)->diffInDays() <= 60),
            Actions\Action::make('terminate')
                ->label('فسخ العقد')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status === 'active')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'terminated']);
                }),
            Actions\DeleteAction::make()
                ->label('حذف العقد'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'عرض العقد: ' . $this->record->contract_number;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات العقد الأساسية')
                    ->schema([
                        Infolists\Components\TextEntry::make('contract_number')
                            ->label('رقم العقد')
                            ->size('lg')
                            ->weight('bold')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('status')
                            ->label('حالة العقد')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'expired' => 'danger',
                                'renewal_pending' => 'warning',
                                'terminated' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active' => 'نشط',
                                'expired' => 'منتهي',
                                'terminated' => 'مفسوخ',
                                'renewal_pending' => 'في انتظار التجديد',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('start_date')
                            ->label('تاريخ البداية')
                            ->date('d/m/Y')
                            ->helperText(fn ($record) => 'منذ ' . \Carbon\Carbon::parse($record->start_date)->diffForHumans()),
                        Infolists\Components\TextEntry::make('end_date')
                            ->label('تاريخ النهاية')
                            ->date('d/m/Y')
                            ->helperText(fn ($record) => \Carbon\Carbon::parse($record->end_date)->isPast() ? 'منتهي' : 'باقي ' . \Carbon\Carbon::parse($record->end_date)->diffForHumans())
                            ->color(fn ($record) => \Carbon\Carbon::parse($record->end_date)->isPast() ? 'danger' : (\Carbon\Carbon::parse($record->end_date)->diffInDays() <= 30 ? 'warning' : 'success')),
                        Infolists\Components\TextEntry::make('duration_months')
                            ->label('مدة العقد')
                            ->suffix(' شهر'),
                    ])->columns(3),
                
                Infolists\Components\Section::make('معلومات الموقع')
                    ->schema([
                        Infolists\Components\TextEntry::make('shop.building.district.city.name')
                            ->label('المدينة')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('shop.building.district.name')
                            ->label('الحي')
                            ->badge()
                            ->color('gray'),
                        Infolists\Components\TextEntry::make('shop.building.name')
                            ->label('المبنى')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('shop.shop_number')
                            ->label('رقم المحل')
                            ->badge()
                            ->color('warning'),
                        Infolists\Components\TextEntry::make('shop.floor')
                            ->label('الدور')
                            ->suffix(' دور'),
                        Infolists\Components\TextEntry::make('shop.area')
                            ->label('مساحة المحل')
                            ->suffix(' م²'),
                    ])->columns(3),
                
                Infolists\Components\Section::make('معلومات المستأجر')
                    ->schema([
                        Infolists\Components\TextEntry::make('tenant.name')
                            ->label('اسم المستأجر')
                            ->size('lg')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('tenant.phone')
                            ->label('رقم الهاتف')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('tenant.email')
                            ->label('البريد الإلكتروني')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('tenant.national_id')
                            ->label('رقم الهوية')
                            ->copyable(),
                    ])->columns(2),
                
                Infolists\Components\Section::make('المعلومات المالية')
                    ->schema([
                        Infolists\Components\TextEntry::make('annual_rent')
                            ->label('الإيجار السنوي')
                            ->money('SAR')
                            ->size('lg')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('payment_frequency')
                            ->label('دورية السداد')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'monthly' => 'شهري',
                                'quarterly' => 'ربع سنوي',
                                'semi_annual' => 'نصف سنوي',
                                'annual' => 'سنوي',
                                default => $state,
                            })
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('payment_amount')
                            ->label('قيمة الدفعة')
                            ->money('SAR')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('tax_rate')
                            ->label('نسبة الضريبة')
                            ->suffix('%'),
                        Infolists\Components\TextEntry::make('tax_amount')
                            ->label('قيمة الضريبة')
                            ->money('SAR'),
                        Infolists\Components\TextEntry::make('fixed_amounts')
                            ->label('المبالغ الثابتة')
                            ->money('SAR'),
                        Infolists\Components\TextEntry::make('total_annual_amount')
                            ->label('القيمة الإجمالية السنوية')
                            ->money('SAR')
                            ->size('lg')
                            ->weight('bold'),
                    ])->columns(3),
                
                Infolists\Components\Section::make('الإحصائيات')
                    ->schema([
                        Infolists\Components\TextEntry::make('payments_count')
                            ->label('عدد المدفوعات')
                            ->badge()
                            ->color('success')
                            ->getStateUsing(fn ($record) => $record->payments()->count()),
                        Infolists\Components\TextEntry::make('paid_amount')
                            ->label('إجمالي المدفوع')
                            ->money('SAR')
                            ->getStateUsing(fn ($record) => $record->payments()->sum('paid_amount')),
                        Infolists\Components\TextEntry::make('remaining_amount')
                            ->label('إجمالي المتبقي')
                            ->money('SAR')
                            ->getStateUsing(fn ($record) => $record->payments()->sum('remaining_amount')),
                        Infolists\Components\TextEntry::make('days_remaining')
                            ->label('الأيام المتبقية')
                            ->getStateUsing(fn ($record) => \Carbon\Carbon::parse($record->end_date)->diffInDays())
                            ->suffix(' يوم'),
                    ])->columns(4),
                
                Infolists\Components\Section::make('صورة العقد')
                    ->schema([
                        Infolists\Components\TextEntry::make('contract_document_name')
                            ->label('اسم الملف')
                            ->visible(fn ($record) => $record->hasContractDocument()),
                        Infolists\Components\TextEntry::make('formatted_contract_document_size')
                            ->label('حجم الملف')
                            ->getStateUsing(fn ($record) => $record->getFormattedContractDocumentSize())
                            ->visible(fn ($record) => $record->hasContractDocument()),
                        Infolists\Components\TextEntry::make('contract_document_type')
                            ->label('نوع الملف')
                            ->getStateUsing(fn ($record) => $record->isContractDocumentPdf() ? 'ملف PDF' : ($record->isContractDocumentImage() ? 'صورة' : 'ملف'))
                            ->visible(fn ($record) => $record->hasContractDocument()),
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('view_document')
                                ->label('عرض صورة العقد')
                                ->icon('heroicon-o-eye')
                                ->color('primary')
                                ->url(fn ($record) => $record->getContractDocumentUrl())
                                ->openUrlInNewTab()
                                ->visible(fn ($record) => $record->hasContractDocument()),
                            Infolists\Components\Actions\Action::make('download_document')
                                ->label('تحميل')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->color('success')
                                ->url(fn ($record) => $record->getContractDocumentUrl())
                                ->openUrlInNewTab()
                                ->visible(fn ($record) => $record->hasContractDocument()),
                        ]),
                        Infolists\Components\TextEntry::make('no_document')
                            ->label('')
                            ->getStateUsing(fn () => 'لم يتم رفع صورة للعقد')
                            ->color('warning')
                            ->visible(fn ($record) => !$record->hasContractDocument()),
                    ])->columns(3)
                    ->visible(fn ($record) => $record->hasContractDocument() || !$record->hasContractDocument()),

                Infolists\Components\Section::make('تفاصيل إضافية')
                    ->schema([
                        Infolists\Components\TextEntry::make('terms')
                            ->label('شروط العقد')
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
