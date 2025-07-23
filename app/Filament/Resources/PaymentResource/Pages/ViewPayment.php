<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected static ?string $title = 'عرض المدفوعة';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل المدفوعة'),
            Actions\DeleteAction::make()
                ->label('حذف المدفوعة'),
            Actions\Action::make('mark_paid')
                ->label('تسديد كامل')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status !== 'paid')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'paid_amount' => $this->record->invoice_amount,
                        'remaining_amount' => 0,
                        'status' => 'paid',
                        'payment_date' => now(),
                    ]);
                    $this->redirect(request()->header('Referer'));
                }),
            Actions\Action::make('download_receipt')
                ->label('تنزيل الإيصال')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn () => $this->record->receipt_file)
                ->url(fn () => \Illuminate\Support\Facades\Storage::url($this->record->receipt_file))
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات المدفوعة')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('invoice_number')
                                    ->label('رقم الفاتورة')
                                    ->weight(FontWeight::Bold)
                                    ->copyable()
                                    ->copyMessage('تم نسخ رقم الفاتورة'),
                                    
                                Infolists\Components\TextEntry::make('status')
                                    ->label('حالة الدفع')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'partial' => 'info',
                                        'paid' => 'success',
                                        'overdue' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(function ($state) {
                                        return match($state) {
                                            'pending' => 'في الانتظار',
                                            'partial' => 'دفع جزئي',
                                            'paid' => 'مدفوع بالكامل',
                                            'overdue' => 'متأخر',
                                            default => $state
                                        };
                                    }),
                                    
                                Infolists\Components\TextEntry::make('payment_method')
                                    ->label('طريقة الدفع')
                                    ->badge()
                                    ->placeholder('غير محدد'),
                            ]),
                    ])
                    ->icon('heroicon-o-banknotes'),

                Infolists\Components\Section::make('معلومات العقد')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('contract.contract_number')
                                    ->label('رقم العقد')
                                    ->weight(FontWeight::Bold)
                                    ->url(fn () => route('filament.admin.resources.contracts.view', $this->record->contract)),
                                    
                                Infolists\Components\TextEntry::make('contract.tenant.name')
                                    ->label('المستأجر')
                                    ->weight(FontWeight::Medium),
                                    
                                Infolists\Components\TextEntry::make('contract.shop.building.name')
                                    ->label('المبنى'),
                                    
                                Infolists\Components\TextEntry::make('contract.shop.shop_number')
                                    ->label('رقم المحل')
                                    ->formatStateUsing(fn () => "محل {$this->record->contract->shop->shop_number}"),
                            ]),
                    ])
                    ->icon('heroicon-o-document-text'),

                Infolists\Components\Section::make('التواريخ والفترة')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('month_year')
                                    ->label('الشهر/السنة')
                                    ->state(function () {
                                        $months = [
                                            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                                            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                                            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
                                        ];
                                        return ($months[$this->record->month] ?? $this->record->month) . ' ' . $this->record->year;
                                    })
                                    ->badge()
                                    ->color('warning'),
                                    
                                Infolists\Components\TextEntry::make('invoice_date')
                                    ->label('تاريخ الفاتورة')
                                    ->date('d/m/Y'),
                                    
                                Infolists\Components\TextEntry::make('due_date')
                                    ->label('تاريخ الاستحقاق')
                                    ->date('d/m/Y')
                                    ->color(function () {
                                        if (\Carbon\Carbon::parse($this->record->due_date)->isPast() && $this->record->status !== 'paid') {
                                            return 'danger';
                                        }
                                        return null;
                                    }),
                                    
                                Infolists\Components\TextEntry::make('payment_date')
                                    ->label('تاريخ الدفع')
                                    ->date('d/m/Y')
                                    ->placeholder('لم يتم الدفع')
                                    ->color('success'),
                            ]),
                    ])
                    ->icon('heroicon-o-calendar-days'),

                Infolists\Components\Section::make('المبالغ المالية')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('invoice_amount')
                                    ->label('قيمة الفاتورة')
                                    ->money('SAR')
                                    ->weight(FontWeight::Bold)
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                    
                                Infolists\Components\TextEntry::make('paid_amount')
                                    ->label('المبلغ المدفوع')
                                    ->money('SAR')
                                    ->color('success')
                                    ->weight(FontWeight::Medium),
                                    
                                Infolists\Components\TextEntry::make('remaining_amount')
                                    ->label('المبلغ المتبقي')
                                    ->money('SAR')
                                    ->color(fn () => $this->record->remaining_amount > 0 ? 'danger' : 'success')
                                    ->weight(FontWeight::Medium),
                            ]),
                    ])
                    ->icon('heroicon-o-currency-dollar'),

                Infolists\Components\Section::make('ملاحظات ومرفقات')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                            
                        Infolists\Components\TextEntry::make('receipt_file')
                            ->label('ملف الإيصال')
                            ->formatStateUsing(fn ($state) => $state ? 'متوفر' : 'غير متوفر')
                            ->color(fn ($state) => $state ? 'success' : 'gray')
                            ->badge(),
                    ])
                    ->icon('heroicon-o-paper-clip')
                    ->collapsible(),

                Infolists\Components\Section::make('معلومات النظام')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('تاريخ الإنشاء')
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
