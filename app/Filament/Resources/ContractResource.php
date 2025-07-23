<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Filament\Resources\ContractResource\RelationManagers;
use App\Models\Contract;
use App\Exports\ContractsExport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'العقود';
    
    protected static ?string $modelLabel = 'عقد';
    
    protected static ?string $pluralModelLabel = 'العقود';
    
    protected static ?string $navigationGroup = 'إدارة العقود والمدفوعات';
    
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اختيار الموقع والمحل')
                    ->description('اختر المدينة والحي والمبنى والمحل')
                    ->schema([
                        Forms\Components\Select::make('city_id')
                            ->label('المدينة')
                            ->options(\App\Models\City::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('district_id', null))
                            ->required(),
                        Forms\Components\Select::make('district_id')
                            ->label('الحي')
                            ->options(fn (callable $get) => 
                                $get('city_id') 
                                    ? \App\Models\District::where('city_id', $get('city_id'))
                                        ->where('is_active', true)
                                        ->pluck('name', 'id')
                                    : []
                            )
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('building_id', null))
                            ->required(),
                        Forms\Components\Select::make('building_id')
                            ->label('المبنى')
                            ->options(fn (callable $get) => 
                                $get('district_id') 
                                    ? \App\Models\Building::where('district_id', $get('district_id'))
                                        ->where('is_active', true)
                                        ->pluck('name', 'id')
                                    : []
                            )
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('shop_id', null))
                            ->required(),
                        Forms\Components\Select::make('shop_id')
                            ->label('المحل')
                            ->options(fn (callable $get) => 
                                $get('building_id') 
                                    ? \App\Models\Shop::where('building_id', $get('building_id'))
                                        ->where('is_active', true)
                                        ->where('status', 'vacant')
                                        ->get()
                                        ->mapWithKeys(fn ($shop) => [$shop->id => "محل رقم {$shop->shop_number} - الدور {$shop->floor} - {$shop->area} م²"])
                                    : []
                            )
                            ->searchable()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('المستأجر')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('المستأجر')
                            ->relationship('tenant', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('الاسم')
                                    ->required(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('رقم الهاتف')
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->label('البريد الإلكتروني')
                                    ->email(),
                            ])
                            ->required(),
                    ]),

                Forms\Components\Section::make('تفاصيل العقد')
                    ->schema([
                        Forms\Components\TextInput::make('contract_number')
                            ->label('رقم العقد')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('تاريخ بداية العقد')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                $duration = $get('duration_months');
                                if ($state && $duration) {
                                    $durationInt = (int) $duration;
                                    $endDate = \Carbon\Carbon::parse($state)->addMonths($durationInt)->subDay();
                                    $set('end_date', $endDate->format('Y-m-d'));
                                }
                            }),
                        Forms\Components\TextInput::make('duration_months')
                            ->label('مدة العقد (بالأشهر)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->inputMode('numeric')
                            ->extraInputAttributes([
                                'pattern' => '[0-9]*',
                                'inputmode' => 'numeric'
                            ])
                            ->rule('regex:/^[0-9]+$/')
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                $startDate = $get('start_date');
                                if ($startDate && $state) {
                                    $stateInt = (int) $state;
                                    $endDate = \Carbon\Carbon::parse($startDate)->addMonths($stateInt)->subDay();
                                    $set('end_date', $endDate->format('Y-m-d'));
                                }
                            }),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('تاريخ نهاية العقد')
                            ->required()
                            ->readonly(),
                    ])->columns(2),

                Forms\Components\Section::make('المعلومات المالية')
                    ->schema([
                        Forms\Components\TextInput::make('annual_rent')
                            ->label('قيمة الإيجار السنوي (ريال)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->minValue(1)
                            ->inputMode('decimal')
                            ->extraInputAttributes([
                                'pattern' => '[0-9]*',
                                'inputmode' => 'decimal'
                            ])
                            ->rule('regex:/^[0-9]+(\.[0-9]{1,2})?$/')
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                $taxRate = $get('tax_rate') ?? 15;
                                $fixedAmounts = $get('fixed_amounts') ?? 0;
                                
                                if ($state) {
                                    $taxAmount = ($state * $taxRate) / 100;
                                    $total = $state + $taxAmount + $fixedAmounts;
                                    
                                    $set('tax_amount', $taxAmount);
                                    $set('total_annual_amount', $total);
                                    
                                    $frequency = $get('payment_frequency') ?? 'annual';
                                    $divisor = match($frequency) {
                                        'monthly' => 12,
                                        'quarterly' => 4,
                                        'semi_annual' => 2,
                                        default => 1
                                    };
                                    $set('payment_amount', $total / $divisor);
                                }
                            }),
                        Forms\Components\Select::make('payment_frequency')
                            ->label('دورية السداد')
                            ->options([
                                'monthly' => 'شهري',
                                'quarterly' => 'ربع سنوي',
                                'semi_annual' => 'نصف سنوي',
                                'annual' => 'سنوي',
                            ])
                            ->default('annual')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                $totalAnnual = $get('total_annual_amount');
                                if ($totalAnnual && $state) {
                                    $divisor = match($state) {
                                        'monthly' => 12,
                                        'quarterly' => 4,
                                        'semi_annual' => 2,
                                        default => 1
                                    };
                                    $set('payment_amount', $totalAnnual / $divisor);
                                }
                            }),
                        Forms\Components\TextInput::make('tax_rate')
                            ->label('نسبة الضريبة (%)')
                            ->numeric()
                            ->default(15)
                            ->step(0.01)
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                $annualRent = $get('annual_rent');
                                $fixedAmounts = $get('fixed_amounts') ?? 0;
                                
                                if ($annualRent && $state !== null) {
                                    $taxAmount = ($annualRent * $state) / 100;
                                    $total = $annualRent + $taxAmount + $fixedAmounts;
                                    
                                    $set('tax_amount', $taxAmount);
                                    $set('total_annual_amount', $total);
                                    
                                    $frequency = $get('payment_frequency') ?? 'annual';
                                    $divisor = match($frequency) {
                                        'monthly' => 12,
                                        'quarterly' => 4,
                                        'semi_annual' => 2,
                                        default => 1
                                    };
                                    $set('payment_amount', $total / $divisor);
                                }
                            }),
                        Forms\Components\TextInput::make('fixed_amounts')
                            ->label('المبالغ الثابتة (ريال)')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                $annualRent = $get('annual_rent');
                                $taxRate = $get('tax_rate') ?? 15;
                                
                                if ($annualRent) {
                                    $taxAmount = ($annualRent * $taxRate) / 100;
                                    $total = $annualRent + $taxAmount + ($state ?? 0);
                                    
                                    $set('tax_amount', $taxAmount);
                                    $set('total_annual_amount', $total);
                                    
                                    $frequency = $get('payment_frequency') ?? 'annual';
                                    $divisor = match($frequency) {
                                        'monthly' => 12,
                                        'quarterly' => 4,
                                        'semi_annual' => 2,
                                        default => 1
                                    };
                                    $set('payment_amount', $total / $divisor);
                                }
                            }),
                        Forms\Components\TextInput::make('tax_amount')
                            ->label('قيمة الضريبة (ريال)')
                            ->numeric()
                            ->readonly(),
                        Forms\Components\TextInput::make('payment_amount')
                            ->label('قيمة الدفعة (ريال)')
                            ->numeric()
                            ->readonly(),
                        Forms\Components\TextInput::make('total_annual_amount')
                            ->label('القيمة الإجمالية السنوية (ريال)')
                            ->numeric()
                            ->readonly(),
                    ])->columns(3),

                Forms\Components\Section::make('تفاصيل إضافية')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('حالة العقد')
                            ->options([
                                'active' => 'نشط',
                                'expired' => 'منتهي',
                                'terminated' => 'مفسوخ',
                                'renewal_pending' => 'في انتظار التجديد',
                            ])
                            ->default('active')
                            ->required(),
                        Forms\Components\Textarea::make('terms')
                            ->label('شروط العقد')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('صورة العقد')
                    ->description('يمكنك رفع صورة أو ملف PDF للعقد الموقع (اختياري)')
                    ->schema([
                        Forms\Components\FileUpload::make('contract_document_path')
                            ->label('صورة العقد')
                            ->directory('contracts')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(10240) // 10MB
                            ->downloadable()
                            ->previewable()
                            ->columnSpanFull()
                            ->helperText('يمكن رفع ملف PDF أو صورة للعقد بحد أقصى 10 ميجابايت')
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $file = $state->getClientOriginalName();
                                    $size = $state->getSize();
                                    $mime = $state->getMimeType();
                                    
                                    $set('contract_document_name', $file);
                                    $set('contract_document_size', $size);
                                    $set('contract_document_mime_type', $mime);
                                }
                            }),
                            
                        Forms\Components\Hidden::make('contract_document_name'),
                        Forms\Components\Hidden::make('contract_document_size'),
                        Forms\Components\Hidden::make('contract_document_mime_type'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contract_number')
                    ->label('رقم العقد')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('تم نسخ رقم العقد')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('shop.building.district.city.name')
                    ->label('المدينة')
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('shop.building.name')
                    ->label('المبنى')
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('shop.shop_number')
                    ->label('رقم المحل')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($record) => "محل {$record->shop->shop_number}"),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('المستأجر')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('تاريخ البداية')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(fn ($record) => 'منذ ' . \Carbon\Carbon::parse($record->start_date)->diffForHumans()),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('تاريخ النهاية')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(fn ($record) => \Carbon\Carbon::parse($record->end_date)->isPast() ? 'منتهي' : 'باقي ' . \Carbon\Carbon::parse($record->end_date)->diffForHumans())
                    ->color(fn ($record) => \Carbon\Carbon::parse($record->end_date)->isPast() ? 'danger' : (\Carbon\Carbon::parse($record->end_date)->diffInDays() <= 30 ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('duration_months')
                    ->label('مدة العقد')
                    ->sortable()
                    ->suffix(' شهر')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('annual_rent')
                    ->label('الإيجار السنوي')
                    ->money('SAR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('SAR')),
                Tables\Columns\TextColumn::make('payment_amount')
                    ->label('قيمة الدفعة')
                    ->money('SAR')
                    ->sortable()
                    ->description(fn ($record) => match($record->payment_frequency) {
                        'monthly' => 'شهرياً',
                        'quarterly' => 'ربع سنوي',
                        'semi_annual' => 'نصف سنوي',
                        'annual' => 'سنوياً',
                        default => ''
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'expired',
                        'warning' => 'renewal_pending',
                        'secondary' => 'terminated',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'terminated' => 'مفسوخ',
                        'renewal_pending' => 'في انتظار التجديد',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('has_contract_document')
                    ->label('صورة العقد')
                    ->getStateUsing(fn ($record) => $record->hasContractDocument())
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document-minus')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record->hasContractDocument() ? 'يوجد صورة للعقد' : 'لا توجد صورة للعقد'),
                Tables\Columns\TextColumn::make('payments_count')
                    ->label('المدفوعات')
                    ->counts('payments')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('shop.building.district.city_id')
                    ->label('المدينة')
                    ->relationship('shop.building.district.city', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('shop.building_id')
                    ->label('المبنى')
                    ->relationship('shop.building', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('tenant_id')
                    ->label('المستأجر')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة العقد')
                    ->options([
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'terminated' => 'مفسوخ',
                        'renewal_pending' => 'في انتظار التجديد',
                    ]),
                Tables\Filters\SelectFilter::make('payment_frequency')
                    ->label('دورية السداد')
                    ->options([
                        'monthly' => 'شهري',
                        'quarterly' => 'ربع سنوي',
                        'semi_annual' => 'نصف سنوي',
                        'annual' => 'سنوي',
                    ]),
                Tables\Filters\Filter::make('ending_soon')
                    ->label('ينتهي قريباً (خلال 30 يوم)')
                    ->query(fn (Builder $query): Builder => $query->where('end_date', '<=', now()->addDays(30)))
                    ->indicator('ينتهي قريباً'),
                Tables\Filters\Filter::make('expired')
                    ->label('منتهي الصلاحية')
                    ->query(fn (Builder $query): Builder => $query->where('end_date', '<', now()))
                    ->indicator('منتهي'),
                Tables\Filters\Filter::make('duration')
                    ->form([
                        Forms\Components\TextInput::make('min_duration')
                            ->label('أقل مدة (بالأشهر)')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_duration')
                            ->label('أكبر مدة (بالأشهر)')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_duration'],
                                fn (Builder $query, $duration): Builder => $query->where('duration_months', '>=', $duration),
                            )
                            ->when(
                                $data['max_duration'],
                                fn (Builder $query, $duration): Builder => $query->where('duration_months', '<=', $duration),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('عرض'),
                    Tables\Actions\EditAction::make()
                        ->label('تعديل'),
                    Tables\Actions\Action::make('view_contract_document')
                        ->label('صورة العقد')
                        ->icon('heroicon-o-document')
                        ->color('info')
                        ->visible(fn ($record) => $record->hasContractDocument())
                        ->url(fn ($record) => $record->getContractDocumentUrl())
                        ->openUrlInNewTab()
                        ->tooltip('عرض صورة العقد المرفوعة'),
                    Tables\Actions\Action::make('renew')
                        ->label('تجديد العقد')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === 'active' && \Carbon\Carbon::parse($record->end_date)->diffInDays() <= 60)
                        ->action(function ($record) {
                            // منطق تجديد العقد
                        }),
                    Tables\Actions\Action::make('terminate')
                        ->label('فسخ العقد')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => $record->status === 'active')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['status' => 'terminated']);
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->label('حذف'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('تحديث الحالة')
                        ->icon('heroicon-o-pencil-square')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('الحالة الجديدة')
                                ->options([
                                    'active' => 'نشط',
                                    'expired' => 'منتهي',
                                    'terminated' => 'مفسوخ',
                                    'renewal_pending' => 'في انتظار التجديد',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each->update(['status' => $data['status']]);
                        }),
                ])
                ->label('إجراءات جماعية'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('تصدير العقود')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        try {
                            $fileName = 'contracts_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
                            
                            Notification::make()
                                ->title('تم تصدير العقود بنجاح')
                                ->success()
                                ->send();
                                
                            return Excel::download(new ContractsExport(), $fileName);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('خطأ في تصدير العقود')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->emptyStateHeading('لا توجد عقود')
            ->emptyStateDescription('لم يتم إنشاء أي عقد بعد.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'view' => Pages\ViewContract::route('/{record}'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }
}
