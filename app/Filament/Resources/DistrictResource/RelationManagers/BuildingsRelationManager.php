<?php

namespace App\Filament\Resources\DistrictResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Forms\Components\Select::make('landlord_id')
                    ->label('المكتب العقاري المدير')
                    ->options(\App\Models\Landlord::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('اسم المبنى')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('building_number')
                    ->label('رقم المبنى')
                    ->maxLength(255),
                Forms\Components\TextInput::make('floors_count')
                    ->label('عدد الأدوار')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
                Forms\Components\TextInput::make('total_shops')
                    ->label('إجمالي المحلات')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Forms\Components\Textarea::make('description')
                    ->label('وصف المبنى')
                    ->rows(3),
                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('landlord.name')
                    ->label('المكتب العقاري')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('building_number')
                    ->label('رقم المبنى')
                    ->searchable(),
                Tables\Columns\TextColumn::make('floors_count')
                    ->label('عدد الأدوار')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('total_shops')
                    ->label('إجمالي المحلات')
                    ->badge()
                    ->color('success'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('landlord_id')
                    ->label('المكتب العقاري')
                    ->options(\App\Models\Landlord::all()->pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('النشاط')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة مبنى جديد'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
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
            ->emptyStateDescription('لم يتم إضافة أي مبنى لهذا الحي بعد.')
            ->emptyStateIcon('heroicon-o-building-office');
    }
}
