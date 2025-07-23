<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use App\Services\ReportService;
use App\Services\TCPDFReportService;
use Filament\Notifications\Notification;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.reports';
    
    protected static ?string $title = 'التقارير والإحصائيات';
    
    protected static ?string $navigationLabel = 'التقارير';
    
    protected static ?int $navigationSort = 10;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateReport')
                ->label('إصدار تقرير')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->form([
                    Select::make('period')
                        ->label('نوع التقرير')
                        ->required()
                        ->options([
                            'monthly' => 'شهري',
                            'quarterly' => 'ربع سنوي', 
                            'semi_annual' => 'نصف سنوي',
                            'annual' => 'سنوي',
                        ])
                        ->default('monthly')
                        ->selectablePlaceholder(false)
                ])
                ->action(function (array $data) {
                    try {
                        $tcpdfService = new TCPDFReportService();
                        return $tcpdfService->generateReport($data['period']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('خطأ في إنشاء التقرير')
                            ->body('حدث خطأ أثناء إنشاء التقرير: ' . $e->getMessage())
                            ->danger()
                            ->send();
                        
                        return null;
                    }
                })
                ->modalHeading('إصدار تقرير شامل')
                ->modalDescription('اختر نوع التقرير الذي ترغب في إصداره')
                ->modalSubmitActionLabel('إصدار التقرير')
                ->modalCancelActionLabel('إلغاء')
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ReportsStatsWidget::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\MonthlyComparisonChart::class,
            \App\Filament\Widgets\BuildingPerformanceChart::class,
            \App\Filament\Widgets\FinancialPerformanceChart::class,
            \App\Filament\Widgets\RecentActivitiesWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }

    public function getWidgetsColumns(): int | array
    {
        return 1;
    }
    
    public function getTitle(): string
    {
        return 'التقارير والإحصائيات المتقدمة';
    }
}
