<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Resources\TenantResource\RelationManagers;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'المستأجرين';
    
    protected static ?string $modelLabel = 'مستأجر';
    
    protected static ?string $pluralModelLabel = 'المستأجرين';
    
    protected static ?string $navigationGroup = 'إدارة الأشخاص';
    
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('المعلومات الأساسية')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_name')
                            ->label('اسم الشركة')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('commercial_registration')
                            ->label('السجل التجاري')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('national_id')
                            ->label('رقم الهوية')
                            ->maxLength(255),
                    ])->columns(2),
                Forms\Components\Section::make('معلومات الاتصال')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->label('العنوان')
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('جهة الاتصال في حالة الطوارئ')
                    ->schema([
                        Forms\Components\TextInput::make('emergency_contact')
                            ->label('اسم جهة الاتصال')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('emergency_phone')
                            ->label('رقم هاتف الطوارئ')
                            ->tel()
                            ->maxLength(255),
                    ])->columns(2),
                    
                Forms\Components\Section::make('الملفات والوثائق')
                    ->description('رفع الملفات الخاصة بالمستأجر (اختياري)')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\FileUpload::make('identity_document_path')
                                    ->label('صورة الهوية')
                                    ->directory('tenants/identity')
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->maxSize(5120) // 5MB
                                    ->downloadable()
                                    ->previewable()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $set('identity_document_name', $state->getClientOriginalName());
                                        }
                                    }),
                                    
                                Forms\Components\FileUpload::make('commercial_register_path')
                                    ->label('السجل التجاري')
                                    ->directory('tenants/commercial')
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->maxSize(5120) // 5MB
                                    ->downloadable()
                                    ->previewable()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $set('commercial_register_name', $state->getClientOriginalName());
                                        }
                                    }),
                            ]),
                            
                        Forms\Components\Fieldset::make('مستندات إضافية')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('additional_document1_label')
                                            ->label('نوع المستند الأول')
                                            ->placeholder('مثال: عقد الشراكة')
                                            ->maxLength(255),
                                            
                                        Forms\Components\TextInput::make('additional_document2_label')
                                            ->label('نوع المستند الثاني') 
                                            ->placeholder('مثال: تفويض')
                                            ->maxLength(255),
                                            
                                        Forms\Components\TextInput::make('additional_document3_label')
                                            ->label('نوع المستند الثالث')
                                            ->placeholder('مثال: شهادة')
                                            ->maxLength(255),
                                    ]),
                                    
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\FileUpload::make('additional_document1_path')
                                            ->label('المستند الأول')
                                            ->directory('tenants/additional')
                                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                                            ->maxSize(5120)
                                            ->downloadable()
                                            ->previewable()
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                if ($state) {
                                                    $set('additional_document1_name', $state->getClientOriginalName());
                                                }
                                            }),
                                            
                                        Forms\Components\FileUpload::make('additional_document2_path')
                                            ->label('المستند الثاني')
                                            ->directory('tenants/additional')
                                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                                            ->maxSize(5120)
                                            ->downloadable()
                                            ->previewable()
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                if ($state) {
                                                    $set('additional_document2_name', $state->getClientOriginalName());
                                                }
                                            }),
                                            
                                        Forms\Components\FileUpload::make('additional_document3_path')
                                            ->label('المستند الثالث')
                                            ->directory('tenants/additional')
                                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                                            ->maxSize(5120)
                                            ->downloadable()
                                            ->previewable()
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                if ($state) {
                                                    $set('additional_document3_name', $state->getClientOriginalName());
                                                }
                                            }),
                                    ]),
                            ]),
                            
                        // حقول مخفية لحفظ أسماء الملفات
                        Forms\Components\Hidden::make('identity_document_name'),
                        Forms\Components\Hidden::make('commercial_register_name'),
                        Forms\Components\Hidden::make('additional_document1_name'),
                        Forms\Components\Hidden::make('additional_document2_name'),
                        Forms\Components\Hidden::make('additional_document3_name'),
                    ]),
                    
                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->company_name ?: 'فرد'),
                    
                Tables\Columns\TextColumn::make('company_name')
                    ->label('اسم الشركة')
                    ->searchable()
                    ->sortable()
                    ->placeholder('فرد')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('commercial_registration')
                    ->label('السجل التجاري')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ السجل التجاري')
                    ->placeholder('غير متوفر')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('national_id')
                    ->label('رقم الهوية')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ رقم الهوية')
                    ->formatStateUsing(fn ($state) => $state ? '****' . substr($state, -4) : 'غير متوفر')
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
                    ->placeholder('غير متوفر'),
                    
                Tables\Columns\TextColumn::make('address')
                    ->label('العنوان')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->address)
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('contracts_count')
                    ->label('عدد العقود')
                    ->counts('contracts')
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('active_contracts_count')
                    ->label('العقود النشطة')
                    ->getStateUsing(fn ($record) => $record->contracts()->where('status', 'active')->count())
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                Tables\Columns\TextColumn::make('documents_count')
                    ->label('الملفات')
                    ->getStateUsing(fn ($record) => count($record->getUploadedDocuments()))
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "{$state} ملف" : 'لا توجد ملفات'),
                    
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
                    
                Tables\Filters\Filter::make('has_contracts')
                    ->label('له عقود')
                    ->query(fn (Builder $query): Builder => $query->has('contracts'))
                    ->indicator('له عقود'),
                    
                Tables\Filters\Filter::make('has_active_contracts')
                    ->label('له عقود نشطة')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('contracts', fn ($q) => $q->where('status', 'active'))
                    )
                    ->indicator('له عقود نشطة'),
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
                    ->label('تصدير جميع المستأجرين')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        // منطق التصدير
                    }),
            ])
            ->emptyStateHeading('لا يوجد مستأجرين')
            ->emptyStateDescription('ابدأ بإضافة مستأجر جديد')
            ->emptyStateIcon('heroicon-o-users');
    }

    public static function getRelations(): array
    {
        return [
            TenantResource\RelationManagers\ContractsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'view' => Pages\ViewTenant::route('/{record}'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
