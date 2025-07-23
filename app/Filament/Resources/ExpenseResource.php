<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use App\Models\Building;
use App\Models\Shop;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    protected static ?string $navigationLabel = 'المصروفات';
    
    protected static ?string $modelLabel = 'مصروف';
    
    protected static ?string $pluralModelLabel = 'المصروفات';
    
    protected static ?string $navigationGroup = 'إدارة المباني والمحلات';
    
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الموقع')
                    ->description('اختر المبنى أو المحل المرتبط بهذا المصروف')
                    ->schema([
                        Forms\Components\Select::make('expensable_type')
                            ->label('نوع الموقع')
                            ->options([
                                'App\\Models\\Building' => 'مبنى',
                                'App\\Models\\Shop' => 'محل',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('expensable_id', null)),
                            
                        Forms\Components\Select::make('expensable_id')
                            ->label('الموقع')
                            ->required()
                            ->options(function (callable $get) {
                                $type = $get('expensable_type');
                                if ($type === 'App\\Models\\Building') {
                                    return Building::all()->pluck('name', 'id');
                                } elseif ($type === 'App\\Models\\Shop') {
                                    return Shop::all()->pluck('name', 'id');
                                }
                                return [];
                            })
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('تفاصيل المصروف')
                    ->description('أدخل تفاصيل المصروف')
                    ->schema([
                        Forms\Components\DatePicker::make('expense_date')
                            ->label('تاريخ المصروف')
                            ->required()
                            ->default(now())
                            ->displayFormat('d/m/Y'),
                            
                        Forms\Components\Select::make('expense_type')
                            ->label('نوع المصروف')
                            ->required()
                            ->options(Expense::getExpenseTypes())
                            ->searchable(),
                            
                        Forms\Components\TextInput::make('amount')
                            ->label('المبلغ')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('ريال'),
                            
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->required()
                            ->options(Expense::getStatuses())
                            ->default('pending'),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('معلومات المورد')
                    ->description('معلومات المورد أو الجهة المستفيدة (اختياري)')
                    ->schema([
                        Forms\Components\TextInput::make('vendor_name')
                            ->label('اسم المورد/الجهة')
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('vendor_phone')
                            ->label('هاتف المورد')
                            ->tel()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('رقم الفاتورة')
                            ->maxLength(255),
                            
                        Forms\Components\DatePicker::make('paid_date')
                            ->label('تاريخ السداد')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->columns(2)
                    ->collapsible(),
                    
                Forms\Components\Section::make('مرفقات وملاحظات')
                    ->description('أضف أي مرفقات أو ملاحظات إضافية')
                    ->schema([
                        Forms\Components\FileUpload::make('receipt_path')
                            ->label('إيصال/فاتورة')
                            ->directory('expense-receipts')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(5120),
                            
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                    
                Forms\Components\Hidden::make('created_by')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('location_name')
                    ->label('الموقع')
                    ->getStateUsing(fn ($record) => $record->location_name)
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('expensable_type')
                    ->label('نوع الموقع')
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'App\\Models\\Building' => 'مبنى',
                            'App\\Models\\Shop' => 'محل',
                            default => $state,
                        };
                    })
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('تاريخ المصروف')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('expense_type')
                    ->label('نوع المصروف')
                    ->formatStateUsing(fn (string $state): string => Expense::getExpenseTypes()[$state] ?? $state)
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('SAR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => Expense::getStatuses()[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'approved' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('vendor_name')
                    ->label('المورد')
                    ->placeholder('غير محدد')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('رقم الفاتورة')
                    ->placeholder('غير محدد')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('paid_date')
                    ->label('تاريخ السداد')
                    ->date('d/m/Y')
                    ->placeholder('غير مسدد')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('منشئ السجل')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('expensable_type')
                    ->label('نوع الموقع')
                    ->options([
                        'App\\Models\\Building' => 'مبنى',
                        'App\\Models\\Shop' => 'محل',
                    ]),
                    
                Tables\Filters\SelectFilter::make('expense_type')
                    ->label('نوع المصروف')
                    ->options(Expense::getExpenseTypes()),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(Expense::getStatuses()),
                    
                Tables\Filters\Filter::make('date_range')
                    ->label('فترة زمنية')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')
                            ->label('من تاريخ'),
                        Forms\Components\DatePicker::make('to_date')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from_date'], fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '>=', $date))
                            ->when($data['to_date'], fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '<=', $date));
                    }),
                    
                Tables\Filters\TernaryFilter::make('has_receipt')
                    ->label('الإيصال')
                    ->placeholder('الكل')
                    ->trueLabel('له إيصال')
                    ->falseLabel('بدون إيصال')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('receipt_path'),
                        false: fn (Builder $query) => $query->whereNull('receipt_path'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\Action::make('mark_paid')
                    ->label('تم السداد')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'paid')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'paid',
                            'paid_date' => now()->toDateString(),
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
            ->defaultSort('expense_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('معلومات عامة')
                    ->schema([
                        TextEntry::make('location_name')
                            ->label('الموقع'),
                            
                        TextEntry::make('expensable_type')
                            ->label('نوع الموقع')
                            ->formatStateUsing(function (string $state): string {
                                return match ($state) {
                                    'App\\Models\\Building' => 'مبنى',
                                    'App\\Models\\Shop' => 'محل',
                                    default => $state,
                                };
                            })
                            ->badge()
                            ->color('info'),
                            
                        TextEntry::make('expense_date')
                            ->label('تاريخ المصروف')
                            ->date('d/m/Y'),
                            
                        TextEntry::make('expense_type')
                            ->label('نوع المصروف')
                            ->formatStateUsing(fn (string $state): string => Expense::getExpenseTypes()[$state] ?? $state)
                            ->badge()
                            ->color('primary'),
                    ])
                    ->columns(2),
                    
                Section::make('المبلغ والحالة')
                    ->schema([
                        TextEntry::make('amount')
                            ->label('المبلغ')
                            ->money('SAR'),
                            
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->formatStateUsing(fn (string $state): string => Expense::getStatuses()[$state] ?? $state)
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'gray',
                                'approved' => 'warning',
                                'paid' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                            
                        TextEntry::make('paid_date')
                            ->label('تاريخ السداد')
                            ->date('d/m/Y')
                            ->placeholder('غير مسدد'),
                    ])
                    ->columns(2),
                    
                Section::make('معلومات المورد')
                    ->schema([
                        TextEntry::make('vendor_name')
                            ->label('اسم المورد')
                            ->placeholder('غير محدد'),
                            
                        TextEntry::make('vendor_phone')
                            ->label('هاتف المورد')
                            ->placeholder('غير محدد'),
                            
                        TextEntry::make('invoice_number')
                            ->label('رقم الفاتورة')
                            ->placeholder('غير محدد'),
                    ])
                    ->columns(2),
                    
                Section::make('الوصف والملاحظات')
                    ->schema([
                        TextEntry::make('description')
                            ->label('الوصف')
                            ->placeholder('لا يوجد وصف')
                            ->columnSpanFull(),
                            
                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ]),
                    
                Section::make('المرفقات')
                    ->schema([
                        TextEntry::make('receipt_path')
                            ->label('الإيصال/الفاتورة')
                            ->placeholder('لا يوجد إيصال')
                            ->formatStateUsing(function ($state) {
                                if ($state) {
                                    return '<a href="' . asset('storage/' . $state) . '" target="_blank" class="text-blue-600 hover:underline">عرض الإيصال</a>';
                                }
                                return 'لا يوجد إيصال';
                            })
                            ->html(),
                    ])
                    ->collapsible(),
                    
                Section::make('معلومات النظام')
                    ->schema([
                        TextEntry::make('creator.name')
                            ->label('منشئ السجل'),
                            
                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'view' => Pages\ViewExpense::route('/{record}'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
