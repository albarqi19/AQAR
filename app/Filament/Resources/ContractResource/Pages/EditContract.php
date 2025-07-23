<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContract extends EditRecord
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('عرض العقد'),
            Actions\Action::make('renew')
                ->label('تجديد العقد')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->visible(fn () => $this->record->status === 'active' && \Carbon\Carbon::parse($this->record->end_date)->diffInDays() <= 60),
            Actions\DeleteAction::make()
                ->label('حذف العقد'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'تعديل العقد: ' . $this->record->contract_number;
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم حفظ التغييرات بنجاح';
    }
}
