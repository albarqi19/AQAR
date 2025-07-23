<?php

namespace App\Filament\Resources\LandlordResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BuildingsRelationManager extends RelationManager
{
    protected static string $relationship = 'buildings';

    protected static ?string $title = 'المباني';

    protected static ?string $modelLabel = 'مبنى';

    protected static ?string $pluralModelLabel = 'المباني';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المبنى')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المبنى')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\Select::make('district_id')
                            ->label('الحي')
                            ->options(\App\Models\District::with('city')->get()->mapWithKeys(function ($district) {
                                return [$district->id => $district->name . ' - ' . $district->city->name];
                            }))
                            ->searchable()
                            ->preload()
                            ->required(),
                            
                        Forms\Components\TextInput::make('building_number')
                            ->label('رقم المبنى')
                            ->maxLength(255),
                            
                        Forms\Components\Textarea::make('address')
                            ->label('العنوان')
                            ->rows(3),
                            
                        Forms\Components\TextInput::make('floors_count')
                            ->label('عدد الطوابق')
                            ->numeric()
                            ->default(1),
                            
                        Forms\Components\TextInput::make('total_shops')
                            ->label('إجمالي المحلات')
                            ->numeric()
                            ->default(1),
                            
                        Forms\Components\TextInput::make('total_area')
                            ->label('المساحة الإجمالية')
                            ->numeric()
                            ->suffix('متر مربع'),
                            
                        Forms\Components\TextInput::make('construction_year')
                            ->label('سنة البناء')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(date('Y')),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المبنى')
                    ->searchable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('city.name')
                    ->label('المدينة')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('district.name')
                    ->label('الحي')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('address')
                    ->label('العنوان')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->address),
                    
                Tables\Columns\TextColumn::make('shops_count')
                    ->label('عدد المحلات')
                    ->counts('shops')
                    ->badge()
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('occupied_shops')
                    ->label('المحلات المؤجرة')
                    ->getStateUsing(fn ($record) => 
                        $record->shops()->whereHas('contracts', fn($q) => $q->where('status', 'active'))->count()
                    )
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('available_shops')
                    ->label('المحلات المتاحة')
                    ->getStateUsing(fn ($record) => 
                        $record->shops()->whereDoesntHave('contracts', fn($q) => $q->where('status', 'active'))->count()
                    )
                    ->badge()
                    ->color('warning'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('district_id')
                    ->label('الحي')
                    ->options(\App\Models\District::with('city')->get()->mapWithKeys(function ($district) {
                        return [$district->id => $district->name . ' - ' . $district->city->name];
                    }))
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة مبنى جديد'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض')
                    ->url(fn ($record) => route('filament.admin.resources.buildings.view', $record)),
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->url(fn ($record) => route('filament.admin.resources.buildings.edit', $record)),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->emptyStateHeading('لا توجد مباني')
            ->emptyStateDescription('ابدأ بإضافة مبنى جديد لهذا المكتب العقاري')
            ->emptyStateIcon('heroicon-o-building-office');
    }
}
