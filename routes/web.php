<?php

use Illuminate\Support\Facades\Route;
use App\Services\ReportService;
use App\Services\TCPDFReportService;

Route::get('/', function () {
    return redirect('/admin');
})->name('home');

Route::get('/welcome', function () {
    return response()->json([
        'message' => 'مرحباً بك في نظام إدارة العقارات',
        'status' => 'يعمل بنجاح',
        'admin_url' => '/admin',
        'version' => '1.0.0'
    ]);
})->name('welcome');

Route::get('/features', function () {
    return view('features');
})->name('features');

// إضافة route للتشخيص
Route::get('/debug', function () {
    try {
        return response()->json([
            'database' => [
                'connection' => config('database.default'),
                'status' => 'متصل'
            ],
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'key' => config('app.key') ? 'موجود' : 'غير موجود'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/test-report', function () {
    try {
        $tcpdfService = new TCPDFReportService();
        return $tcpdfService->generateReport('monthly');
    } catch (\Exception $e) {
        return 'خطأ: ' . $e->getMessage();
    }
});
