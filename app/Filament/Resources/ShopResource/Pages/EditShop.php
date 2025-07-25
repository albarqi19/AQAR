<?php

namespace App\Filament\Resources\ShopResource\Pages;

use App\Filament\Resources\ShopResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShop extends EditRecord
{
    protected static string $resource = ShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف المحل'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'تعديل المحل: ' . $this->record->shop_number;
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم حفظ التغييرات بنجاح';
    }
}
