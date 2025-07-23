<?php

use Illuminate\Support\Facades\Route;
use App\Services\ReportService;
use App\Services\TCPDFReportService;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/features', function () {
    return view('features');
})->name('features');

Route::get('/test-report', function () {
    try {
        $tcpdfService = new TCPDFReportService();
        return $tcpdfService->generateReport('monthly');
    } catch (\Exception $e) {
        return 'خطأ: ' . $e->getMessage();
    }
});
