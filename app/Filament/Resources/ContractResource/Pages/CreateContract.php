<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateContract extends CreateRecord
{
    protected static string $resource = ContractResource::class;
    
    public function getTitle(): string
    {
        return 'إضافة عقد جديد';
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء العقد بنجاح';
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // تحديث حالة المحل إلى مؤجر
        if (isset($data['shop_id'])) {
            \App\Models\Shop::find($data['shop_id'])->update(['status' => 'occupied']);
        }
        
        // توليد رقم عقد تلقائي إذا لم يكن موجود
        if (empty($data['contract_number'])) {
            $data['contract_number'] = 'CON-' . date('Y') . '-' . str_pad(\App\Models\Contract::count() + 1, 4, '0', STR_PAD_LEFT);
        }
        
        return $data;
    }
}
