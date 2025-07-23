<?php

namespace App\Filament\Resources\LandlordResource\Pages;

use App\Filament\Resources\LandlordResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLandlord extends CreateRecord
{
    protected static string $resource = LandlordResource::class;
    
    protected static ?string $title = 'إضافة مكتب عقاري جديد';
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء المكتب العقاري بنجاح';
    }
}
