<?php

use App\Services\ReportService;

Route::get('/test-report', function () {
    try {
        $reportService = new ReportService();
        return $reportService->generatePdfReport('monthly');
    } catch (\Exception $e) {
        return 'خطأ: ' . $e->getMessage();
    }
});
