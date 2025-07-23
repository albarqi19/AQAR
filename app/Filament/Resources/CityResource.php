<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages;
use App\Filament\Resources\CityResource\RelationManagers;
use App\Models\City;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    
    protected static ?string $navigationLabel = 'المدن';
    
    protected static ?string $modelLabel = 'مدينة';
    
    protected static ?string $pluralModelLabel = 'المدن';
    
    protected static ?string $navigationGroup = 'إدارة المواقع';
    
    protected static ?int $navigationSort = 1;

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
                Forms\Components\Section::make('معلومات المدينة الأساسية')
                    ->description('أدخل البيانات الأساسية للمدينة')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المدينة')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('أدخل اسم المدينة بوضوح'),
                        Forms\Components\TextInput::make('code')
                            ->label('رمز المدينة')
                            ->maxLength(10)
                            ->unique(ignoreRecord: true)
                            ->placeholder('مثال: RYD, JED, DMM')
                            ->helperText('رمز مختصر للمدينة (اختياري)'),
                    ])->columns(2),
                
                Forms\Components\Section::make('تفاصيل إضافية')
                    ->description('معلومات إضافية عن المدينة')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('وصف المدينة')
                            ->placeholder('أدخل وصف تفصيلي للمدينة...')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('المدينة نشطة')
                            ->default(true)
                            ->required()
                            ->helperText('حدد ما إذا كانت المدينة متاحة للاستخدام'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المدينة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('code')
                    ->label('رمز المدينة')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('districts_count')
                    ->label('عدد الأحياء')
                    ->badge()
                    ->color('info')
                    ->counts('districts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('buildings_count')
                    ->label('عدد المباني')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(fn ($record) => $record->districts()->withCount('buildings')->get()->sum('buildings_count'))
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
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('حالة النشاط')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->native(false),
                Tables\Filters\Filter::make('has_districts')
                    ->label('تحتوي على أحياء')
                    ->query(fn (Builder $query): Builder => $query->has('districts')),
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
            ->emptyStateHeading('لا توجد مدن')
            ->emptyStateDescription('لم يتم إنشاء أي مدينة بعد.')
            ->emptyStateIcon('heroicon-o-building-office-2');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DistrictsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'view' => Pages\ViewCity::route('/{record}'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
        ];
    }
}
