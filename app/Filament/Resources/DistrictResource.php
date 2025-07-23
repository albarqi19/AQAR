<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistrictResource\Pages;
use App\Filament\Resources\DistrictResource\RelationManagers;
use App\Models\District;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DistrictResource extends Resource
{
    protected static ?string $model = District::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    
    protected static ?string $navigationLabel = 'الأحياء';
    
    protected static ?string $modelLabel = 'حي';
    
    protected static ?string $pluralModelLabel = 'الأحياء';
    
    protected static ?string $navigationGroup = 'إدارة المواقع';
    
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'gray';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الحي الأساسية')
                    ->description('أدخل البيانات الأساسية للحي')
                    ->schema([
                        Forms\Components\Select::make('city_id')
                            ->label('المدينة')
                            ->relationship('city', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('اختر المدينة التي يقع فيها الحي'),
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الحي')
                            ->required()
                            ->maxLength(255)
                            ->helperText('أدخل اسم الحي بوضوح'),
                    ])->columns(2),
                
                Forms\Components\Section::make('تفاصيل إضافية')
                    ->description('معلومات إضافية عن الحي')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('وصف الحي')
                            ->placeholder('أدخل وصف تفصيلي للحي...')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('الحي نشط')
                            ->default(true)
                            ->required()
                            ->helperText('حدد ما إذا كان الحي متاحاً للاستخدام'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('city.name')
                    ->label('المدينة')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الحي')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('buildings_count')
                    ->label('عدد المباني')
                    ->badge()
                    ->color('success')
                    ->counts('buildings')
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('city_id')
                    ->label('المدينة')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('حالة النشاط')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->native(false),
                Tables\Filters\Filter::make('has_buildings')
                    ->label('يحتوي على مباني')
                    ->query(fn (Builder $query): Builder => $query->has('buildings')),
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
            ->emptyStateHeading('لا توجد أحياء')
            ->emptyStateDescription('لم يتم إنشاء أي حي بعد.')
            ->emptyStateIcon('heroicon-o-map');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BuildingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDistricts::route('/'),
            'create' => Pages\CreateDistrict::route('/create'),
            'view' => Pages\ViewDistrict::route('/{record}'),
            'edit' => Pages\EditDistrict::route('/{record}/edit'),
        ];
    }
}
