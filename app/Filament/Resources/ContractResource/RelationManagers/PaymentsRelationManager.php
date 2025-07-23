<?php

namespace App\Filament\Resources\ContractResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'المدفوعات';

    protected static ?string $modelLabel = 'دفعة';

    protected static ?string $pluralModelLabel = 'المدفوعات';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                            ->default(now()->month)
                            ->required(),
                        
                        Forms\Components\TextInput::make('year')
                            ->label('السنة')
                            ->numeric()
                            ->default(now()->year)
                            ->required(),
                    ])->columns(3),
                
                Forms\Components\Section::make('المبالغ المالية')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_amount')
                            ->label('قيمة الفاتورة')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->suffix('ريال')
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                $paidAmount = $get('paid_amount') ?? 0;
                                $set('remaining_amount', $state - $paidAmount);
                            }),
                        
                        Forms\Components\TextInput::make('paid_amount')
                            ->label('المبلغ المحصل')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('ريال')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                $invoiceAmount = $get('invoice_amount') ?? 0;
                                $set('remaining_amount', $invoiceAmount - $state);
                                
                                // تحديث الحالة تلقائياً
                                if ($state == 0) {
                                    $set('status', 'pending');
                                } elseif ($state >= $invoiceAmount) {
                                    $set('status', 'paid');
                                } else {
                                    $set('status', 'partial');
                                }
                            }),
                        
                        Forms\Components\TextInput::make('remaining_amount')
                            ->label('المبلغ المتبقي')
                            ->numeric()
                            ->suffix('ريال')
                            ->disabled(),
                    ])->columns(3),
                
                Forms\Components\Section::make('تفاصيل الدفع')
                    ->schema([
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('تاريخ التحصيل'),
                        
                        Forms\Components\Select::make('payment_method')
                            ->label('طريقة الدفع')
                            ->options([
                                'cash' => 'نقداً',
                                'bank_transfer' => 'تحويل بنكي',
                                'check' => 'شيك',
                                'credit_card' => 'بطاقة ائتمان',
                                'other' => 'أخرى',
                            ]),
                        
                        Forms\Components\Select::make('status')
                            ->label('حالة الدفع')
                            ->options([
                                'pending' => 'في الانتظار',
                                'partial' => 'مدفوع جزئياً',
                                'paid' => 'مدفوع بالكامل',
                                'overdue' => 'متأخر',
                            ])
                            ->default('pending')
                            ->required(),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('month')
                    ->label('الشهر/السنة')
                    ->formatStateUsing(fn ($record) => 
                        match($record->month) {
                            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
                        } . ' ' . $record->year
                    )
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('تاريخ الفاتورة')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => \Carbon\Carbon::parse($record->due_date)->isPast() && $record->status !== 'paid' ? 'danger' : null),
                
                Tables\Columns\TextColumn::make('invoice_amount')
                    ->label('قيمة الفاتورة')
                    ->money('SAR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('المحصل')
                    ->money('SAR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('المتبقي')
                    ->money('SAR')
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'partial',
                        'success' => 'paid',
                        'danger' => 'overdue',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'في الانتظار',
                        'partial' => 'مدفوع جزئياً',
                        'paid' => 'مدفوع بالكامل',
                        'overdue' => 'متأخر',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('تاريخ التحصيل')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة الدفع')
                    ->options([
                        'pending' => 'في الانتظار',
                        'partial' => 'مدفوع جزئياً',
                        'paid' => 'مدفوع بالكامل',
                        'overdue' => 'متأخر',
                    ]),
                
                Tables\Filters\Filter::make('overdue')
                    ->label('متأخرة')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<', now())->where('status', '!=', 'paid')),
                
                Tables\Filters\SelectFilter::make('year')
                    ->label('السنة')
                    ->options(fn () => 
                        collect(range(date('Y') - 2, date('Y') + 1))
                            ->mapWithKeys(fn ($year) => [$year => $year])
                            ->toArray()
                    ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة دفعة جديدة'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\Action::make('mark_paid')
                    ->label('تسديد كامل')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'paid')
                    ->action(function ($record) {
                        $record->update([
                            'paid_amount' => $record->invoice_amount,
                            'remaining_amount' => 0,
                            'status' => 'paid',
                            'payment_date' => now(),
                        ]);
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->emptyStateHeading('لا توجد مدفوعات')
            ->emptyStateDescription('لم يتم إضافة أي دفعة لهذا العقد بعد.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }
}
