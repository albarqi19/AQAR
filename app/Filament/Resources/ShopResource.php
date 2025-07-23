<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShopResource\Pages;
use App\Filament\Resources\ShopResource\RelationManagers;
use App\Models\Shop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShopResource extends Resource
{
    protected static ?string $model = Shop::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    
    protected static ?string $navigationLabel = 'المحلات';
    
    protected static ?string $modelLabel = 'محل';
    
    protected static ?string $pluralModelLabel = 'المحلات';
    
    protected static ?string $navigationGroup = 'إدارة المواقع';
    
    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $occupiedCount = static::getModel()::where('status', 'occupied')->count();
        $totalCount = static::getModel()::count();
        $occupancyRate = $totalCount > 0 ? ($occupiedCount / $totalCount) * 100 : 0;
        
        if ($occupancyRate >= 80) return 'success';
        if ($occupancyRate >= 60) return 'warning';
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المحل الأساسية')
                    ->description('أدخل المعلومات الأساسية للمحل')
                    ->schema([
                        Forms\Components\Select::make('building_id')
                            ->label('المبنى')
                            ->relationship('building', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('اختر المبنى الذي يقع فيه المحل'),
                        Forms\Components\TextInput::make('shop_number')
                            ->label('رقم المحل')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('رقم المحل يجب أن يكون فريداً'),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('floor')
                                    ->label('الدور')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('أدخل رقم الدور (0 للطابق الأرضي)'),
                                Forms\Components\TextInput::make('area')
                                    ->label('المساحة (م²)')
                                    ->required()
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(1)
                                    ->suffix('م²')
                                    ->helperText('أدخل المساحة بالمتر المربع'),
                            ]),
                    ])->columns(2),
                
                Forms\Components\Section::make('تفاصيل إضافية')
                    ->description('معلومات إضافية عن المحل')
                    ->schema([
                        Forms\Components\TextInput::make('shop_type')
                            ->label('نوع المحل')
                            ->maxLength(255)
                            ->placeholder('مطعم، محل ملابس، صيدلية...')
                            ->helperText('أدخل نوع النشاط التجاري للمحل'),
                        Forms\Components\Select::make('status')
                            ->label('حالة المحل')
                            ->options([
                                'vacant' => 'شاغر',
                                'occupied' => 'مؤجر',
                                'maintenance' => 'تحت الصيانة',
                            ])
                            ->default('vacant')
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('description')
                            ->label('وصف المحل')
                            ->placeholder('أدخل وصف تفصيلي للمحل...')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('المحل نشط')
                            ->default(true)
                            ->required()
                            ->helperText('حدد ما إذا كان المحل متاحاً للإيجار أم لا'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('building.name')
                    ->label('المبنى')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('shop_number')
                    ->label('رقم المحل')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('floor')
                    ->label('الدور')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('area')
                    ->label('المساحة (م²)')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn (string $state): string => number_format((float)$state, 2)),
                Tables\Columns\TextColumn::make('shop_type')
                    ->label('نوع المحل')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'success' => 'occupied',
                        'warning' => 'vacant',
                        'danger' => 'maintenance',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'vacant' => 'شاغر',
                        'occupied' => 'مؤجر',
                        'maintenance' => 'تحت الصيانة',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('contract_status')
                    ->label('حالة العقد')
                    ->getStateUsing(function (Shop $record): string {
                        $activeContract = $record->contracts()
                            ->where('status', 'active')
                            ->where('start_date', '<=', now())
                            ->where('end_date', '>=', now())
                            ->first();
                        
                        if ($activeContract) {
                            return 'active_contract';
                        }
                        
                        $hasAnyContract = $record->contracts()->exists();
                        return $hasAnyContract ? 'has_contract' : 'no_contract';
                    })
                    ->colors([
                        'success' => 'active_contract',
                        'warning' => 'has_contract',
                        'gray' => 'no_contract',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active_contract' => 'عقد نشط',
                        'has_contract' => 'يوجد عقد',
                        'no_contract' => 'لا يوجد عقد',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة المحل')
                    ->options([
                        'vacant' => 'شاغر',
                        'occupied' => 'مؤجر',
                        'maintenance' => 'تحت الصيانة',
                    ]),
                Tables\Filters\Filter::make('has_contract')
                    ->label('يوجد عقد')
                    ->query(fn (Builder $query): Builder => $query->whereHas('contracts')),
                Tables\Filters\Filter::make('no_contract')
                    ->label('لا يوجد عقد')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave('contracts')),
                Tables\Filters\Filter::make('has_active_contract')
                    ->label('يوجد عقد نشط')
                    ->query(fn (Builder $query): Builder => $query->whereHas('contracts', function (Builder $query) {
                        $query->where('status', 'active')
                              ->where('start_date', '<=', now())
                              ->where('end_date', '>=', now());
                    })),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ])
                ->label('إجراءات جماعية'),
            ])
            ->emptyStateHeading('لا توجد محلات')
            ->emptyStateDescription('لم يتم إنشاء أي محل بعد.')
            ->emptyStateIcon('heroicon-o-building-storefront');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ContractsRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShops::route('/'),
            'create' => Pages\CreateShop::route('/create'),
            'view' => Pages\ViewShop::route('/{record}'),
            'edit' => Pages\EditShop::route('/{record}/edit'),
        ];
    }
}
