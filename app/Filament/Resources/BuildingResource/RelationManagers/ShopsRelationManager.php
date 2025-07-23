<?php

namespace App\Filament\Resources\BuildingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShopsRelationManager extends RelationManager
{
    protected static string $relationship = 'shops';

    protected static ?string $title = 'المحلات';

    protected static ?string $modelLabel = 'محل';

    protected static ?string $pluralModelLabel = 'المحلات';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المحل')
                    ->schema([
                        Forms\Components\TextInput::make('shop_number')
                            ->label('رقم المحل')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('floor')
                            ->label('الدور')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\TextInput::make('area')
                            ->label('المساحة (م²)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->minValue(1)
                            ->suffix('م²'),
                        Forms\Components\TextInput::make('shop_type')
                            ->label('نوع المحل')
                            ->maxLength(255)
                            ->placeholder('مطعم، محل ملابس، صيدلية...'),
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
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('shop_number')
            ->columns([
                Tables\Columns\TextColumn::make('shop_number')
                    ->label('رقم المحل')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
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
                    ->wrap()
                    ->toggleable(),
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
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y')
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
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('النشاط')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة محل جديد'),
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
            ->emptyStateHeading('لا توجد محلات')
            ->emptyStateDescription('لم يتم إضافة أي محل لهذا المبنى بعد.')
            ->emptyStateIcon('heroicon-o-building-storefront');
    }
}
