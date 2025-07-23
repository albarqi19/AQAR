<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceResource\Pages;
use App\Filament\Resources\MaintenanceResource\RelationManagers;
use App\Models\Maintenance;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MaintenanceResource extends Resource
{
    protected static ?string $model = Maintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    
    protected static ?string $navigationLabel = 'الصيانة';
    
    protected static ?string $pluralModelLabel = 'سجلات الصيانة';
    
    protected static ?string $modelLabel = 'صيانة';
    
    protected static ?string $navigationGroup = 'إدارة المباني والمحلات';
    
    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'in_progress')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اختيار الموقع')
                    ->description('حدد المبنى أو المحل المراد صيانته')
                    ->schema([
                        Forms\Components\Select::make('maintainable_type')
                            ->label('نوع الموقع')
                            ->options([
                                Building::class => 'مبنى',
                                Shop::class => 'محل',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('maintainable_id', null)),
                            
                        Forms\Components\Select::make('maintainable_id')
                            ->label('الموقع')
                            ->options(function (Forms\Get $get) {
                                $type = $get('maintainable_type');
                                
                                if ($type === Building::class) {
                                    return Building::query()
                                        ->with('district.city')
                                        ->get()
                                        ->mapWithKeys(function ($building) {
                                            return [$building->id => "{$building->name} - {$building->district?->city?->name}"];
                                        });
                                }
                                
                                if ($type === Shop::class) {
                                    return Shop::query()
                                        ->with('building.district.city')
                                        ->get()
                                        ->mapWithKeys(function ($shop) {
                                            return [$shop->id => "محل {$shop->shop_number} - {$shop->building?->name}"];
                                        });
                                }
                                
                                return [];
                            })
                            ->required()
                            ->searchable(),
                    ])->columns(2),

                Forms\Components\Section::make('تفاصيل الصيانة')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('maintenance_date')
                                    ->label('تاريخ الصيانة')
                                    ->required()
                                    ->default(now()),
                                    
                                Forms\Components\Select::make('maintenance_type')
                                    ->label('نوع الصيانة')
                                    ->options(Maintenance::getMaintenanceTypes())
                                    ->required()
                                    ->searchable(),
                                    
                                Forms\Components\Select::make('status')
                                    ->label('حالة الصيانة')
                                    ->options(Maintenance::getStatuses())
                                    ->default('pending')
                                    ->required(),
                            ]),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('وصف الصيانة')
                            ->required()
                            ->rows(3)
                            ->placeholder('اكتب وصفاً مفصلاً للصيانة المطلوبة...')
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات إضافية')
                            ->rows(2)
                            ->placeholder('أي ملاحظات أو تعليمات خاصة...')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('التكلفة والمقاول')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('cost')
                                    ->label('التكلفة (ريال)')
                                    ->numeric()
                                    ->prefix('ر.س')
                                    ->placeholder('0.00')
                                    ->helperText('اختياري - يمكن تركه فارغاً'),
                                    
                                Forms\Components\TextInput::make('contractor_name')
                                    ->label('اسم المقاول')
                                    ->maxLength(255)
                                    ->placeholder('اسم الشركة أو المقاول'),
                                    
                                Forms\Components\TextInput::make('contractor_phone')
                                    ->label('رقم هاتف المقاول')
                                    ->tel()
                                    ->placeholder('+966501234567'),
                            ]),
                    ])->collapsible(),

                Forms\Components\Section::make('التواريخ المهمة')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('scheduled_date')
                                    ->label('تاريخ البدء المجدول')
                                    ->helperText('متى من المفترض أن تبدأ الصيانة؟'),
                                    
                                Forms\Components\DatePicker::make('completed_date')
                                    ->label('تاريخ الإنجاز')
                                    ->helperText('يُملأ عند اكتمال الصيانة'),
                            ]),
                    ])->collapsible(),
                    
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
                    
                Tables\Columns\TextColumn::make('maintainable_type')
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
                    
                Tables\Columns\TextColumn::make('maintenance_date')
                    ->label('تاريخ الصيانة')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('maintenance_type')
                    ->label('نوع الصيانة')
                    ->formatStateUsing(fn (string $state): string => Maintenance::getMaintenanceTypes()[$state] ?? $state)
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => Maintenance::getStatuses()[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('cost')
                    ->label('التكلفة')
                    ->money('SAR')
                    ->placeholder('غير محدد')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('contractor_name')
                    ->label('المقاول')
                    ->placeholder('غير محدد')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('scheduled_date')
                    ->label('التاريخ المجدول')
                    ->date('d/m/Y')
                    ->placeholder('غير محدد')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('completed_date')
                    ->label('تاريخ الإنجاز')
                    ->date('d/m/Y')
                    ->placeholder('غير مكتمل')
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
                Tables\Filters\SelectFilter::make('maintainable_type')
                    ->label('نوع الموقع')
                    ->options([
                        'App\\Models\\Building' => 'مبنى',
                        'App\\Models\\Shop' => 'محل',
                    ]),
                    
                Tables\Filters\SelectFilter::make('maintenance_type')
                    ->label('نوع الصيانة')
                    ->options(Maintenance::getMaintenanceTypes()),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(Maintenance::getStatuses()),
                    
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
                            ->when($data['from_date'], fn (Builder $query, $date): Builder => $query->whereDate('maintenance_date', '>=', $date))
                            ->when($data['to_date'], fn (Builder $query, $date): Builder => $query->whereDate('maintenance_date', '<=', $date));
                    }),
                    
                Tables\Filters\TernaryFilter::make('has_cost')
                    ->label('التكلفة')
                    ->placeholder('الكل')
                    ->trueLabel('لها تكلفة')
                    ->falseLabel('بدون تكلفة')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('cost')->where('cost', '>', 0),
                        false: fn (Builder $query) => $query->whereNull('cost')->orWhere('cost', 0),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\Action::make('mark_completed')
                    ->label('تم الإنجاز')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'completed')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'completed',
                            'completed_date' => now()->toDateString(),
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
            ->defaultSort('maintenance_date', 'desc');
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
                            
                        TextEntry::make('maintainable_type')
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
                            
                        TextEntry::make('maintenance_date')
                            ->label('تاريخ الصيانة')
                            ->date('d/m/Y'),
                            
                        TextEntry::make('maintenance_type')
                            ->label('نوع الصيانة')
                            ->formatStateUsing(fn (string $state): string => Maintenance::getMaintenanceTypes()[$state] ?? $state)
                            ->badge()
                            ->color('primary'),
                    ])
                    ->columns(2),
                    
                Section::make('الحالة والتكلفة')
                    ->schema([
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->formatStateUsing(fn (string $state): string => Maintenance::getStatuses()[$state] ?? $state)
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'gray',
                                'in_progress' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                            
                        TextEntry::make('cost')
                            ->label('التكلفة')
                            ->money('SAR')
                            ->placeholder('غير محدد'),
                            
                        TextEntry::make('scheduled_date')
                            ->label('التاريخ المجدول')
                            ->date('d/m/Y')
                            ->placeholder('غير محدد'),
                            
                        TextEntry::make('completed_date')
                            ->label('تاريخ الإنجاز')
                            ->date('d/m/Y')
                            ->placeholder('غير مكتمل'),
                    ])
                    ->columns(2),
                    
                Section::make('معلومات المقاول')
                    ->schema([
                        TextEntry::make('contractor_name')
                            ->label('اسم المقاول')
                            ->placeholder('غير محدد'),
                            
                        TextEntry::make('contractor_phone')
                            ->label('هاتف المقاول')
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
            'index' => Pages\ListMaintenances::route('/'),
            'create' => Pages\CreateMaintenance::route('/create'),
            'view' => Pages\ViewMaintenance::route('/{record}'),
            'edit' => Pages\EditMaintenance::route('/{record}/edit'),
        ];
    }
}
