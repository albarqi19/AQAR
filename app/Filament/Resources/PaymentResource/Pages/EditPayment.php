<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;
    
    protected static ?string $title = 'تعديل المدفوعة';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('عرض'),
            Actions\DeleteAction::make()
                ->label('حذف')
                ->successNotificationTitle('تم حذف المدفوعة بنجاح'),
            Actions\Action::make('mark_paid')
                ->label('تسديد كامل')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status !== 'paid')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'paid_amount' => $this->record->invoice_amount,
                        'remaining_amount' => 0,
                        'status' => 'paid',
                        'payment_date' => now(),
                    ]);
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث المدفوعة بنجاح';
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // حساب المبلغ المتبقي
        $data['remaining_amount'] = $data['invoice_amount'] - ($data['paid_amount'] ?? 0);
        
        // تحديد حالة الدفع
        if ($data['remaining_amount'] <= 0) {
            $data['status'] = 'paid';
        } elseif (($data['paid_amount'] ?? 0) > 0) {
            $data['status'] = 'partial';
        } else {
            $data['status'] = 'pending';
        }
        
        // التحقق من التأخير
        if (isset($data['due_date']) && \Carbon\Carbon::parse($data['due_date'])->isPast() && $data['status'] !== 'paid') {
            $data['status'] = 'overdue';
        }
        
        return $data;
    }
}
