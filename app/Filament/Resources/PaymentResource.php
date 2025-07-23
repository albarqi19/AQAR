<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use App\Exports\PaymentsExport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    protected static ?string $navigationLabel = 'المدفوعات';
    
    protected static ?string $modelLabel = 'دفعة';
    
    protected static ?string $pluralModelLabel = 'المدفوعات';
    
    protected static ?string $navigationGroup = 'إدارة العقود والمدفوعات';
    
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $pendingCount = static::getModel()::where('status', 'pending')->count();
        if ($pendingCount > 10) return 'danger';
        if ($pendingCount > 5) return 'warning';
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اختيار العقد')
                    ->schema([
                        Forms\Components\Select::make('contract_id')
                            ->label('العقد')
                            ->relationship('contract', 'contract_number')
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                return "{$record->contract_number} - {$record->tenant->name} - {$record->shop->formatted_name}";
                            })
                            ->searchable(['contract_number', 'tenant.name', 'shop.shop_number'])
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state) {
                                    $contract = \App\Models\Contract::find($state);
                                    if ($contract) {
                                        $set('invoice_amount', $contract->payment_amount);
                                        $set('remaining_amount', $contract->payment_amount);
                                    }
                                }
                            }),
                    ]),
                
                Forms\Components\Section::make('تفاصيل الفاتورة')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('رقم الفاتورة')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->default(fn () => 'INV-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)),
                        
                        Forms\Components\DatePicker::make('invoice_date')
                            ->label('تاريخ الفاتورة')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\DatePicker::make('due_date')
                            ->label('تاريخ الاستحقاق')
                            ->required()
                            ->default(now()->addDays(30)),
                        
                        Forms\Components\Select::make('month')
                            ->label('الشهر')
                            ->options([
                                1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                                5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                                9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
                            ])
                            ->required()
                            ->default(date('n')),
                        
                        Forms\Components\TextInput::make('year')
                            ->label('السنة')
                            ->required()
                            ->numeric()
                            ->minValue(2020)
                            ->maxValue(2050)
                            ->default(date('Y')),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('المبالغ المالية')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_amount')
                            ->label('قيمة الفاتورة (ر.س)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('ر.س'),
                        
                        Forms\Components\TextInput::make('paid_amount')
                            ->label('المبلغ المدفوع (ر.س)')
                            ->numeric()
                            ->step(0.01)
                            ->default(0.00)
                            ->prefix('ر.س')
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $get, $state) {
                                $invoiceAmount = $get('invoice_amount') ?: 0;
                                $paidAmount = $state ?: 0;
                                $set('remaining_amount', $invoiceAmount - $paidAmount);
                            }),
                        
                        Forms\Components\TextInput::make('remaining_amount')
                            ->label('المبلغ المتبقي (ر.س)')
                            ->numeric()
                            ->step(0.01)
                            ->default(0.00)
                            ->prefix('ر.س')
                            ->disabled(),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('تفاصيل الدفع')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('حالة الدفع')
                            ->options([
                                'pending' => 'في الانتظار',
                                'partial' => 'دفع جزئي',
                                'paid' => 'مدفوع بالكامل',
                                'overdue' => 'متأخر',
                            ])
                            ->default('pending')
                            ->required(),
                        
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('تاريخ الدفع الفعلي'),
                        
                        Forms\Components\TextInput::make('payment_method')
                            ->label('طريقة الدفع')
                            ->maxLength(255)
                            ->placeholder('نقداً، تحويل بنكي، شيك...'),
                        
                        Forms\Components\FileUpload::make('receipt_file')
                            ->label('إيصال الدفع')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('تم نسخ رقم الفاتورة'),
                    
                Tables\Columns\TextColumn::make('contract.contract_number')
                    ->label('رقم العقد')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.contracts.view', $record->contract)),
                    
                Tables\Columns\TextColumn::make('contract.tenant.name')
                    ->label('المستأجر')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('contract.shop.building.name')
                    ->label('المبنى')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('contract.shop.shop_number')
                    ->label('رقم المحل')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => "محل {$record->contract->shop->shop_number}")
                    ->badge()
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('month')
                    ->label('الشهر/السنة')
                    ->formatStateUsing(function ($record) {
                        $months = [
                            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
                        ];
                        return ($months[$record->month] ?? $record->month) . ' ' . $record->year;
                    })
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('تاريخ الفاتورة')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function ($record) {
                        if (\Carbon\Carbon::parse($record->due_date)->isPast() && $record->status !== 'paid') {
                            return 'danger';
                        }
                        return null;
                    })
                    ->description(function ($record) {
                        $dueDate = \Carbon\Carbon::parse($record->due_date);
                        if ($dueDate->isPast() && $record->status !== 'paid') {
                            return 'متأخر ' . $dueDate->diffForHumans();
                        } elseif (!$dueDate->isPast() && $record->status !== 'paid') {
                            return 'يستحق ' . $dueDate->diffForHumans();
                        }
                        return null;
                    }),
                    
                Tables\Columns\TextColumn::make('invoice_amount')
                    ->label('قيمة الفاتورة')
                    ->money('SAR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('SAR')),
                    
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('المدفوع')
                    ->money('SAR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('SAR'))
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('المتبقي')
                    ->money('SAR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('SAR'))
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'partial',
                        'success' => 'paid',
                        'danger' => 'overdue',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'pending' => 'في الانتظار',
                            'partial' => 'دفع جزئي',
                            'paid' => 'مدفوع بالكامل',
                            'overdue' => 'متأخر',
                            default => $state
                        };
                    }),
                    
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('لم يتم الدفع')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('receipt_file')
                    ->label('الإيصال')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة الدفع')
                    ->options([
                        'pending' => 'في الانتظار',
                        'partial' => 'دفع جزئي',
                        'paid' => 'مدفوع بالكامل',
                        'overdue' => 'متأخر',
                    ]),
                    
                Tables\Filters\SelectFilter::make('contract.tenant_id')
                    ->label('المستأجر')
                    ->relationship('contract.tenant', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('contract.shop.building_id')
                    ->label('المبنى')
                    ->relationship('contract.shop.building', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('month')
                    ->label('الشهر')
                    ->options([
                        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                        5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                        9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
                    ]),
                    
                Tables\Filters\SelectFilter::make('year')
                    ->label('السنة')
                    ->options(fn () => 
                        collect(range(date('Y') - 2, date('Y') + 1))
                            ->mapWithKeys(fn ($year) => [$year => $year])
                            ->toArray()
                    ),
                    
                Tables\Filters\Filter::make('overdue')
                    ->label('متأخرة')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('due_date', '<', now())
                              ->where('status', '!=', 'paid')
                    )
                    ->indicator('متأخرة'),
                    
                Tables\Filters\Filter::make('unpaid')
                    ->label('غير مدفوعة')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('remaining_amount', '>', 0)
                    )
                    ->indicator('غير مدفوعة'),
                    
                Tables\Filters\Filter::make('has_receipt')
                    ->label('لديها إيصال')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('receipt_file')
                    )
                    ->indicator('لديها إيصال'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('عرض'),
                    Tables\Actions\EditAction::make()
                        ->label('تعديل'),
                    Tables\Actions\Action::make('mark_paid')
                        ->label('تسديد كامل')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->status !== 'paid')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update([
                                'paid_amount' => $record->invoice_amount,
                                'remaining_amount' => 0,
                                'status' => 'paid',
                                'payment_date' => now(),
                            ]);
                        }),
                    Tables\Actions\Action::make('download_receipt')
                        ->label('تنزيل الإيصال')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->visible(fn ($record) => $record->receipt_file)
                        ->url(fn ($record) => Storage::url($record->receipt_file))
                        ->openUrlInNewTab(),
                    Tables\Actions\DeleteAction::make()
                        ->label('حذف'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                    Tables\Actions\BulkAction::make('mark_paid')
                        ->label('تسديد كامل للمحدد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'paid_amount' => $record->invoice_amount,
                                    'remaining_amount' => 0,
                                    'status' => 'paid',
                                    'payment_date' => now(),
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('export')
                        ->label('تصدير المحدد')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(function ($records) {
                            try {
                                $fileName = 'selected_payments_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
                                
                                Notification::make()
                                    ->title('تم تصدير المدفوعات المحددة بنجاح')
                                    ->success()
                                    ->send();
                                    
                                return Excel::download(new PaymentsExport($records), $fileName);
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('خطأ في تصدير المدفوعات')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                ])
                ->label('إجراءات جماعية'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_all')
                    ->label('تصدير جميع المدفوعات')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        try {
                            $fileName = 'all_payments_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
                            
                            Notification::make()
                                ->title('تم تصدير جميع المدفوعات بنجاح')
                                ->success()
                                ->send();
                                
                            return Excel::download(new PaymentsExport(), $fileName);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('خطأ في تصدير المدفوعات')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->emptyStateHeading('لا توجد مدفوعات')
            ->emptyStateDescription('ابدأ بإضافة مدفوعة جديدة للعقود النشطة')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
