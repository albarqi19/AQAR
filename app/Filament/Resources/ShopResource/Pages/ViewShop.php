<?php

namespace App\Filament\Resources\ShopResource\Pages;

use App\Filament\Resources\ShopResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewShop extends ViewRecord
{
    protected static string $resource = ShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل المحل'),
            Actions\DeleteAction::make()
                ->label('حذف المحل'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'عرض المحل: ' . $this->record->shop_number;
    }
}
