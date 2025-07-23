<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
    
    protected static ?string $title = 'إضافة مدفوعة جديدة';
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء المدفوعة بنجاح';
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
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
