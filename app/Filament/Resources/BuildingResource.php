<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuildingResource\Pages;
use App\Filament\Resources\BuildingResource\RelationManagers;
use App\Models\Building;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    
    protected static ?string $navigationLabel = 'المباني';
    
    protected static ?string $modelLabel = 'مبنى';
    
    protected static ?string $pluralModelLabel = 'المباني';
    
    protected static ?string $navigationGroup = 'إدارة المواقع';
    
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('الموقع الجغرافي')
                    ->description('حدد موقع المبنى الجغرافي')
                    ->schema([
                        Forms\Components\Select::make('city_id')
                            ->label('المدينة')
                            ->options(\App\Models\City::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('district_id', null))
                            ->helperText('اختر المدينة أولاً'),
                        Forms\Components\Select::make('district_id')
                            ->label('الحي')
                            ->options(fn (Forms\Get $get): array => 
                                \App\Models\District::where('city_id', $get('city_id'))
                                    ->where('is_active', true)
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('يتم تحديث القائمة حسب المدينة المختارة'),
                    ])->columns(2),
                
                Forms\Components\Section::make('المعلومات الأساسية')
                    ->description('البيانات الأساسية للمبنى')
                    ->schema([
                        Forms\Components\Select::make('landlord_id')
                            ->label('المكتب العقاري المدير')
                            ->relationship('landlord', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('المكتب العقاري المسؤول عن إدارة المبنى'),
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المبنى')
                            ->required()
                            ->maxLength(255)
                            ->helperText('أدخل اسم المبنى أو العنوان المختصر'),
                        Forms\Components\TextInput::make('building_number')
                            ->label('رقم المبنى')
                            ->maxLength(255)
                            ->helperText('رقم المبنى الرسمي (اختياري)'),
                        Forms\Components\Textarea::make('address')
                            ->label('العنوان التفصيلي')
                            ->placeholder('أدخل العنوان الكامل والمفصل...')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(3),
                
                Forms\Components\Section::make('المواصفات الفنية')
                    ->description('المواصفات والأرقام الفنية للمبنى')
                    ->schema([
                        Forms\Components\TextInput::make('floors_count')
                            ->label('عدد الأدوار')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->suffix('دور'),
                        Forms\Components\TextInput::make('total_shops')
                            ->label('إجمالي المحلات')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('محل'),
                        Forms\Components\TextInput::make('total_area')
                            ->label('المساحة الإجمالية')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('م²')
                            ->helperText('المساحة الكاملة للمبنى'),
                        Forms\Components\TextInput::make('construction_year')
                            ->label('سنة البناء')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(date('Y'))
                            ->placeholder('مثال: 2020')
                            ->helperText('السنة الهجرية أو الميلادية'),
                    ])->columns(4),
                
                Forms\Components\Section::make('تفاصيل إضافية')
                    ->description('معلومات ووصف تفصيلي')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('وصف المبنى')
                            ->placeholder('أدخل وصف تفصيلي للمبنى، المرافق، المميزات...')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('المبنى نشط')
                            ->default(true)
                            ->required()
                            ->helperText('حدد ما إذا كان المبنى متاحاً للاستخدام'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المبنى')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('district.city.name')
                    ->label('المدينة')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('district.name')
                    ->label('الحي')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('landlord.name')
                    ->label('المكتب العقاري')
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->wrap(),
                Tables\Columns\TextColumn::make('building_number')
                    ->label('رقم المبنى')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('floors_count')
                    ->label('الأدوار')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->suffix(' دور'),
                Tables\Columns\TextColumn::make('total_shops')
                    ->label('المحلات')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->suffix(' محل')
                    ->color('success'),
                Tables\Columns\TextColumn::make('total_area')
                    ->label('المساحة')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->suffix(' م²')
                    ->formatStateUsing(fn (?string $state): string => 
                        $state ? number_format((float)$state, 0) : '-'
                    ),
                Tables\Columns\TextColumn::make('construction_year')
                    ->label('سنة البناء')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('district.city_id')
                    ->label('المدينة')
                    ->relationship('district.city', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('district_id')
                    ->label('الحي')
                    ->relationship('district', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('landlord_id')
                    ->label('المكتب العقاري')
                    ->relationship('landlord', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('حالة النشاط')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->native(false),
                Tables\Filters\Filter::make('construction_year')
                    ->form([
                        Forms\Components\TextInput::make('from')
                            ->label('من سنة')
                            ->numeric(),
                        Forms\Components\TextInput::make('to')
                            ->label('إلى سنة')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->where('construction_year', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->where('construction_year', '<=', $date),
                            );
                    }),
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
            ->emptyStateHeading('لا توجد مباني')
            ->emptyStateDescription('لم يتم إنشاء أي مبنى بعد.')
            ->emptyStateIcon('heroicon-o-building-office');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ShopsRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuildings::route('/'),
            'create' => Pages\CreateBuilding::route('/create'),
            'view' => Pages\ViewBuilding::route('/{record}'),
            'edit' => Pages\EditBuilding::route('/{record}/edit'),
        ];
    }
}
