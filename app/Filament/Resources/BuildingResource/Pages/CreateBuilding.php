<?php

namespace App\Filament\Resources\BuildingResource\Pages;

use App\Filament\Resources\BuildingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBuilding extends CreateRecord
{
    protected static string $resource = BuildingResource::class;
    
    public function getTitle(): string
    {
        return 'إضافة مبنى جديد';
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء المبنى بنجاح';
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // يمكن إضافة منطق إضافي هنا لمعالجة البيانات قبل الحفظ
        return $data;
    }
}
