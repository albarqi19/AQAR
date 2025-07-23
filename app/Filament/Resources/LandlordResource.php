<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LandlordResource\Pages;
use App\Filament\Resources\LandlordResource\RelationManagers;
use App\Models\Landlord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LandlordResource extends Resource
{
    protected static ?string $model = Landlord::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    
    protected static ?string $navigationLabel = 'المكاتب العقارية';
    
    protected static ?string $modelLabel = 'مكتب عقاري';
    
    protected static ?string $pluralModelLabel = 'المكاتب العقارية';
    
    protected static ?string $navigationGroup = 'إدارة الأشخاص';
    
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('المعلومات الأساسية')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المكتب العقاري')
                            ->required()
                            ->maxLength(255)
                            ->helperText('أدخل اسم المكتب العقاري أو اسم المالك'),

                        Forms\Components\TextInput::make('company_name')
                            ->label('اسم الشركة')
                            ->maxLength(255)
                            ->helperText('اسم الشركة الرسمي (اختياري)'),

                        Forms\Components\TextInput::make('commercial_registration')
                            ->label('رقم السجل التجاري')
                            ->maxLength(255)
                            ->helperText('رقم السجل التجاري للشركة'),

                        Forms\Components\TextInput::make('license_number')
                            ->label('رقم الرخصة')
                            ->maxLength(255)
                            ->helperText('رقم رخصة ممارسة النشاط العقاري'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('معلومات الاتصال')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->required()
                            ->maxLength(255)
                            ->helperText('رقم الهاتف الأساسي للتواصل'),

                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->maxLength(255)
                            ->helperText('البريد الإلكتروني للمراسلات'),

                        Forms\Components\TextInput::make('contact_person')
                            ->label('الشخص المسؤول')
                            ->maxLength(255)
                            ->helperText('اسم الشخص المسؤول عن التواصل'),

                        Forms\Components\Textarea::make('address')
                            ->label('العنوان')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('العنوان الكامل للمكتب العقاري'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('الإعدادات المالية')
                    ->schema([
                        Forms\Components\TextInput::make('commission_rate')
                            ->label('معدل العمولة (%)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0.00)
                            ->suffix('%')
                            ->helperText('نسبة العمولة المستحقة للمكتب العقاري'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->helperText('هل المكتب العقاري نشط حالياً؟')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('المستندات')
                    ->schema([
                        Forms\Components\FileUpload::make('documents')
                            ->label('المستندات')
                            ->multiple()
                            ->directory('landlords')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->helperText('ارفع المستندات ذات الصلة (السجل التجاري، الرخصة، إلخ)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المكتب')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->company_name ?: 'مكتب فردي'),

                Tables\Columns\TextColumn::make('company_name')
                    ->label('اسم الشركة')
                    ->searchable()
                    ->sortable()
                    ->placeholder('مكتب فردي')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ رقم الهاتف')
                    ->icon('heroicon-m-phone'),

                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ البريد الإلكتروني')
                    ->icon('heroicon-m-envelope')
                    ->placeholder('غير متوفر')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('contact_person')
                    ->label('الشخص المسؤول')
                    ->searchable()
                    ->placeholder('غير محدد')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('معدل العمولة')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('commercial_registration')
                    ->label('السجل التجاري')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ السجل التجاري')
                    ->placeholder('غير متوفر')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('license_number')
                    ->label('رقم الرخصة')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ رقم الرخصة')
                    ->placeholder('غير متوفر')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('buildings_count')
                    ->label('عدد المباني')
                    ->counts('buildings')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('active_buildings_count')
                    ->label('المباني النشطة')
                    ->getStateUsing(fn ($record) => $record->buildings()->count())
                    ->badge()
                    ->color('primary'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->native(false),
                    
                Tables\Filters\Filter::make('has_company')
                    ->label('له شركة')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('company_name'))
                    ->indicator('له شركة'),
                    
                Tables\Filters\Filter::make('has_commercial_registration')
                    ->label('له سجل تجاري')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('commercial_registration'))
                    ->indicator('له سجل تجاري'),
                    
                Tables\Filters\Filter::make('has_buildings')
                    ->label('له مباني')
                    ->query(fn (Builder $query): Builder => $query->has('buildings'))
                    ->indicator('له مباني'),
                    
                Tables\Filters\Filter::make('high_commission')
                    ->label('عمولة عالية (أكثر من 5%)')
                    ->query(fn (Builder $query): Builder => $query->where('commission_rate', '>', 5))
                    ->indicator('عمولة عالية'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('عرض'),
                    Tables\Actions\EditAction::make()
                        ->label('تعديل'),
                    Tables\Actions\Action::make('toggle_status')
                        ->label(fn ($record) => $record->is_active ? 'إلغاء التفعيل' : 'تفعيل')
                        ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->action(fn ($record) => $record->update(['is_active' => !$record->is_active])),
                    Tables\Actions\DeleteAction::make()
                        ->label('حذف'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('تفعيل المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_active' => true]));
                        }),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('إلغاء تفعيل المحدد')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_active' => false]));
                        }),
                ])
                ->label('إجراءات جماعية'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_all')
                    ->label('تصدير جميع المكاتب العقارية')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        // منطق التصدير
                    }),
            ])
            ->emptyStateHeading('لا توجد مكاتب عقارية')
            ->emptyStateDescription('ابدأ بإضافة مكتب عقاري جديد')
            ->emptyStateIcon('heroicon-o-briefcase');
    }

    public static function getRelations(): array
    {
        return [
            LandlordResource\RelationManagers\BuildingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLandlords::route('/'),
            'create' => Pages\CreateLandlord::route('/create'),
            'view' => Pages\ViewLandlord::route('/{record}'),
            'edit' => Pages\EditLandlord::route('/{record}/edit'),
        ];
    }
}
