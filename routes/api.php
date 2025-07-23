<?php

use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\DocumentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// مجموعة مسارات الوثائق والملفات
Route::prefix('documents')->group(function () {
    
    // جميع الوثائق
    Route::get('/', [DocumentController::class, 'index']);
    
    // إحصائيات الوثائق
    Route::get('/statistics', [DocumentController::class, 'statistics']);
    
    // وثائق مبنى معين
    Route::get('/buildings/{buildingId}', [DocumentController::class, 'buildingDocuments']);
    
    // وثائق محل معين
    Route::get('/shops/{shopId}', [DocumentController::class, 'shopDocuments']);
    
    // عرض وثيقة معينة
    Route::get('/{id}', [DocumentController::class, 'show']);
    
});

// مجموعة مسارات العقود
Route::prefix('contracts')->group(function () {
    
    // صورة عقد معين
    Route::get('/{contractId}/document', function ($contractId) {
        $contract = \App\Models\Contract::findOrFail($contractId);
        
        if (!$contract->hasContractDocument()) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد صورة لهذا العقد'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'contract' => [
                'id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'document' => [
                    'file_name' => $contract->contract_document_name,
                    'file_url' => $contract->getContractDocumentUrl(),
                    'file_size' => $contract->getFormattedContractDocumentSize(),
                    'mime_type' => $contract->contract_document_mime_type,
                    'is_image' => $contract->isContractDocumentImage(),
                    'is_pdf' => $contract->isContractDocumentPdf(),
                ]
            ]
        ]);
    });
    
});

// مجموعة مسارات المستأجرين
Route::prefix('tenants')->group(function () {
    
    // ملفات مستأجر معين
    Route::get('/{tenantId}/documents', function ($tenantId) {
        $tenant = \App\Models\Tenant::findOrFail($tenantId);
        $documents = $tenant->getUploadedDocuments();
        
        return response()->json([
            'success' => true,
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'company_name' => $tenant->company_name,
            ],
            'documents' => $documents,
            'total_documents' => count($documents),
        ]);
    });
    
});

// مجموعة مسارات الإحصائيات
Route::prefix('statistics')->group(function () {
    
    // الإحصائيات العامة
    Route::get('/overview', [StatisticsController::class, 'overview']);
    
    // إحصائيات الإشغال
    Route::get('/occupancy', [StatisticsController::class, 'occupancy']);
    
    // الإيرادات الشهرية
    Route::get('/monthly-revenue', [StatisticsController::class, 'monthlyRevenue']);
    
    // معدلات التحصيل
    Route::get('/collection-rates', [StatisticsController::class, 'collectionRates']);
    
    // مقارنة الأداء الشهري
    Route::get('/monthly-comparison', [StatisticsController::class, 'monthlyComparison']);
    
    // أداء المباني
    Route::get('/building-performance', [StatisticsController::class, 'buildingPerformance']);
    
    // الأداء المالي السنوي
    Route::get('/annual-financial', [StatisticsController::class, 'annualFinancialPerformance']);
    
    // النشاطات الحديثة
    Route::get('/recent-activities', [StatisticsController::class, 'recentActivities']);
    
    // تقرير شامل لوحة التحكم
    Route::get('/dashboard', [StatisticsController::class, 'dashboard']);
    
});
