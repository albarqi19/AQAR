<?php

namespace App\Filament\Resources\TenantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'contracts';

    protected static ?string $title = 'العقود';

    protected static ?string $modelLabel = 'عقد';

    protected static ?string $pluralModelLabel = 'العقود';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات العقد')
                    ->schema([
                        Forms\Components\TextInput::make('contract_number')
                            ->label('رقم العقد')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\Select::make('shop_id')
                            ->label('المحل')
                            ->options(function () {
                                return \App\Models\Shop::with('building')
                                    ->get()
                                    ->mapWithKeys(function ($shop) {
                                        return [$shop->id => "محل {$shop->shop_number} - {$shop->building->name}"];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                            
                        Forms\Components\DatePicker::make('start_date')
                            ->label('تاريخ البداية')
                            ->required(),
                            
                        Forms\Components\DatePicker::make('end_date')
                            ->label('تاريخ النهاية')
                            ->required(),
                            
                        Forms\Components\TextInput::make('annual_rent')
                            ->label('الإيجار السنوي')
                            ->numeric()
                            ->prefix('ر.س')
                            ->required(),
                            
                        Forms\Components\TextInput::make('payment_amount')
                            ->label('مبلغ الدفعة')
                            ->numeric()
                            ->prefix('ر.س')
                            ->required(),
                            
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'active' => 'نشط',
                                'expired' => 'منتهي',
                                'pending' => 'في الانتظار',
                                'draft' => 'مسودة',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('contract_number')
            ->columns([
                Tables\Columns\TextColumn::make('contract_number')
                    ->label('رقم العقد')
                    ->searchable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('تم نسخ رقم العقد'),
                    
                Tables\Columns\TextColumn::make('shop.building.name')
                    ->label('المبنى')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('shop.shop_number')
                    ->label('رقم المحل')
                    ->formatStateUsing(fn ($record) => "محل {$record->shop->shop_number}")
                    ->badge()
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('start_date')
                    ->label('تاريخ البداية')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('end_date')
                    ->label('تاريخ النهاية')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => \Carbon\Carbon::parse($record->end_date)->isPast() ? 'danger' : null),
                    
                Tables\Columns\TextColumn::make('annual_rent')
                    ->label('الإيجار السنوي')
                    ->money('SAR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('payment_amount')
                    ->label('مبلغ الدفعة')
                    ->money('SAR')
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'expired',
                        'warning' => 'pending',
                        'gray' => 'draft',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'active' => 'نشط',
                            'expired' => 'منتهي',
                            'pending' => 'في الانتظار',
                            'draft' => 'مسودة',
                            default => $state
                        };
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة العقد')
                    ->options([
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'pending' => 'في الانتظار',
                        'draft' => 'مسودة',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة عقد جديد'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض')
                    ->url(fn ($record) => route('filament.admin.resources.contracts.view', $record)),
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->url(fn ($record) => route('filament.admin.resources.contracts.edit', $record)),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->emptyStateHeading('لا توجد عقود')
            ->emptyStateDescription('ابدأ بإضافة عقد جديد لهذا المستأجر')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
