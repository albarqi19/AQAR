<?php

namespace App\Filament\Resources\LandlordResource\Pages;

use App\Filament\Resources\LandlordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditLandlord extends EditRecord
{
    protected static string $resource = LandlordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('عرض')
                ->icon('heroicon-o-eye')
                ->color('info'),
            Actions\DeleteAction::make()
                ->label('حذف')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('حذف المكتب العقاري')
                ->modalDescription('هل أنت متأكد من حذف هذا المكتب العقاري؟ سيتم حذف جميع البيانات المرتبطة به.')
                ->modalSubmitActionLabel('حذف')
                ->modalCancelActionLabel('إلغاء'),
        ];
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('حفظ التعديلات')
            ->icon('heroicon-o-check');
    }

    protected function getCancelFormAction(): Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('إلغاء');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم حفظ التعديلات بنجاح')
            ->body('تم تحديث بيانات المكتب العقاري بنجاح.');
    }

    public function getTitle(): string
    {
        return 'تعديل المكتب العقاري: ' . $this->record->name;
    }
}
