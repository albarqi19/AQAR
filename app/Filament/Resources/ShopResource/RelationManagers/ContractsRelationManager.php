<?php

namespace App\Filament\Resources\ShopResource\RelationManagers;

use App\Models\Contract;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                // هذا للعرض فقط - لن نحتاج نموذج تعديل
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
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('المستأجر')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('تاريخ البداية')
                    ->date('Y/m/d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('تاريخ النهاية')
                    ->date('Y/m/d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_months')
                    ->label('المدة (شهر)')
                    ->numeric()
                    ->alignCenter()
                    ->suffix(' شهر'),
                Tables\Columns\TextColumn::make('annual_rent')
                    ->label('الإيجار السنوي')
                    ->money('SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_frequency')
                    ->label('دورية السداد')
                    ->badge()
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'monthly' => 'شهري',
                            'quarterly' => 'ربع سنوي',
                            'semi_annual' => 'نصف سنوي',
                            'annual' => 'سنوي',
                            default => $state,
                        };
                    })
                    ->colors([
                        'success' => 'monthly',
                        'warning' => 'quarterly',
                        'info' => 'semi_annual',
                        'primary' => 'annual',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('حالة العقد')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'expired',
                        'warning' => 'terminated',
                        'info' => 'renewal_pending',
                    ])
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'active' => 'نشط',
                            'expired' => 'منتهي',
                            'terminated' => 'ملغي',
                            'renewal_pending' => 'في انتظار التجديد',
                            default => $state,
                        };
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة العقد')
                    ->options([
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'terminated' => 'ملغي',
                        'renewal_pending' => 'في انتظار التجديد',
                    ])
                    ->multiple(),
            ])
            ->headerActions([
                // لن نضيف إنشاء عقد هنا لأن المطلوب العرض فقط
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض')
                    ->url(fn (Contract $record): string => route('filament.admin.resources.contracts.view', $record)),
            ])
            ->bulkActions([
                // لا حاجة لإجراءات جماعية في العرض
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا يوجد عقد مرفق')
            ->emptyStateDescription('لا توجد عقود مرتبطة بهذا المحل حالياً.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->paginated([10, 25, 50])
            ->poll('30s'); // تحديث تلقائي كل 30 ثانية
    }
    
    public function isReadOnly(): bool
    {
        return true; // جعل الجدول للقراءة فقط
    }
}
