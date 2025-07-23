<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;

class ViewTenant extends ViewRecord
{
    protected static string $resource = TenantResource::class;

    protected static ?string $title = 'عرض المستأجر';

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\EditAction::make()
                ->label('تعديل المستأجر'),
        ];

        // إضافة أزرار الملفات حسب ما هو متوفر
        $documents = $this->record->getUploadedDocuments();
        
        foreach ($documents as $key => $document) {
            $actions[] = Actions\Action::make("view_document_{$key}")
                ->label($document['label'])
                ->icon('heroicon-o-document')
                ->color('info')
                ->url($document['url'])
                ->openUrlInNewTab()
                ->tooltip("عرض {$document['label']}");
        }

        $actions[] = Actions\DeleteAction::make()
            ->label('حذف المستأجر');
            
        $actions[] = Actions\Action::make('toggle_status')
            ->label(fn () => $this->record->is_active ? 'إلغاء التفعيل' : 'تفعيل')
            ->icon(fn () => $this->record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
            ->color(fn () => $this->record->is_active ? 'danger' : 'success')
            ->requiresConfirmation()
            ->action(function () {
                $this->record->update(['is_active' => !$this->record->is_active]);
                $this->redirect(request()->header('Referer'));
            });

        return $actions;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('المعلومات الأساسية')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('الاسم')
                                    ->weight(FontWeight::Bold)
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                    
                                Infolists\Components\TextEntry::make('is_active')
                                    ->label('الحالة')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'نشط' : 'غير نشط'),
                                    
                                Infolists\Components\TextEntry::make('company_name')
                                    ->label('اسم الشركة')
                                    ->placeholder('فرد')
                                    ->weight(FontWeight::Medium),
                                    
                                Infolists\Components\TextEntry::make('commercial_registration')
                                    ->label('السجل التجاري')
                                    ->placeholder('غير متوفر')
                                    ->copyable()
                                    ->copyMessage('تم نسخ السجل التجاري'),
                                    
                                Infolists\Components\TextEntry::make('national_id')
                                    ->label('رقم الهوية')
                                    ->placeholder('غير متوفر')
                                    ->copyable()
                                    ->copyMessage('تم نسخ رقم الهوية'),
                            ]),
                    ])
                    ->icon('heroicon-o-identification'),

                Infolists\Components\Section::make('معلومات الاتصال')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('phone')
                                    ->label('رقم الهاتف')
                                    ->icon('heroicon-m-phone')
                                    ->copyable()
                                    ->copyMessage('تم نسخ رقم الهاتف'),
                                    
                                Infolists\Components\TextEntry::make('email')
                                    ->label('البريد الإلكتروني')
                                    ->icon('heroicon-m-envelope')
                                    ->copyable()
                                    ->copyMessage('تم نسخ البريد الإلكتروني')
                                    ->placeholder('غير متوفر'),
                                    
                                Infolists\Components\TextEntry::make('address')
                                    ->label('العنوان')
                                    ->columnSpanFull()
                                    ->placeholder('غير متوفر'),
                            ]),
                    ])
                    ->icon('heroicon-o-phone'),

                Infolists\Components\Section::make('جهة الاتصال في حالات الطوارئ')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('emergency_contact')
                                    ->label('اسم جهة الاتصال')
                                    ->placeholder('غير متوفر'),
                                    
                                Infolists\Components\TextEntry::make('emergency_phone')
                                    ->label('رقم هاتف الطوارئ')
                                    ->icon('heroicon-m-phone')
                                    ->copyable()
                                    ->copyMessage('تم نسخ رقم هاتف الطوارئ')
                                    ->placeholder('غير متوفر'),
                            ]),
                    ])
                    ->icon('heroicon-o-exclamation-triangle')
                    ->collapsible(),

                Infolists\Components\Section::make('إحصائيات العقود')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_contracts')
                                    ->label('إجمالي العقود')
                                    ->state(fn () => $this->record->contracts()->count())
                                    ->badge()
                                    ->color('info'),
                                    
                                Infolists\Components\TextEntry::make('active_contracts')
                                    ->label('العقود النشطة')
                                    ->state(fn () => $this->record->contracts()->where('status', 'active')->count())
                                    ->badge()
                                    ->color('success'),
                                    
                                Infolists\Components\TextEntry::make('expired_contracts')
                                    ->label('العقود المنتهية')
                                    ->state(fn () => $this->record->contracts()->where('status', 'expired')->count())
                                    ->badge()
                                    ->color('danger'),
                            ]),
                    ])
                    ->icon('heroicon-o-document-text'),

                Infolists\Components\Section::make('الملفات والوثائق')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema($this->getDocumentEntries()),
                    ])
                    ->icon('heroicon-o-folder')
                    ->visible(fn () => count($this->record->getUploadedDocuments()) > 0),

                Infolists\Components\Section::make('معلومات النظام')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('تاريخ التسجيل')
                                    ->dateTime('d/m/Y H:i'),
                                    
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('آخر تحديث')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    /**
     * إنشاء عناصر عرض الملفات
     */
    protected function getDocumentEntries(): array
    {
        $documents = $this->record->getUploadedDocuments();
        $entries = [];

        foreach ($documents as $key => $document) {
            $entries[] = Infolists\Components\Actions::make([
                Infolists\Components\Actions\Action::make("view_{$key}")
                    ->label($document['label'])
                    ->icon('heroicon-o-document')
                    ->color('info')
                    ->url($document['url'])
                    ->openUrlInNewTab()
                    ->tooltip("فتح {$document['label']}")
                    ->button()
                    ->outlined(),
            ]);
        }

        // إضافة رسالة إذا لم توجد ملفات
        if (empty($entries)) {
            $entries[] = Infolists\Components\TextEntry::make('no_documents')
                ->label('')
                ->state('لا توجد ملفات مرفوعة')
                ->color('gray')
                ->columnSpanFull();
        }

        return $entries;
    }
}
