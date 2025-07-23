<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Building;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    /**
     * عرض جميع الوثائق
     */
    public function index(Request $request): JsonResponse
    {
        $query = Document::with(['documentable', 'uploader'])
            ->where('is_active', true);

        // فلترة حسب نوع الوثيقة
        if ($request->has('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        // فلترة حسب نوع المرفق (مبنى أو محل)
        if ($request->has('documentable_type')) {
            $query->where('documentable_type', $request->documentable_type);
        }

        // فلترة حسب المرفق المحدد
        if ($request->has('documentable_id')) {
            $query->where('documentable_id', $request->documentable_id);
        }

        $documents = $query->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $documents->items(),
            'pagination' => [
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'per_page' => $documents->perPage(),
                'total' => $documents->total(),
            ]
        ]);
    }

    /**
     * عرض وثائق مبنى معين
     */
    public function buildingDocuments($buildingId): JsonResponse
    {
        $building = Building::findOrFail($buildingId);
        
        $documents = $building->documents()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'building' => [
                'id' => $building->id,
                'name' => $building->name,
                'address' => $building->address,
            ],
            'documents' => $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'description' => $doc->description,
                    'document_type' => $doc->document_type,
                    'document_type_label' => Document::getDocumentTypes()[$doc->document_type] ?? $doc->document_type,
                    'file_name' => $doc->file_name,
                    'file_url' => $doc->getFileUrl(),
                    'file_size' => $doc->formatted_file_size,
                    'mime_type' => $doc->mime_type,
                    'is_image' => $doc->isImage(),
                    'is_pdf' => $doc->isPdf(),
                    'uploaded_by' => $doc->uploader?->name,
                    'created_at' => $doc->created_at->format('d/m/Y H:i'),
                ];
            }),
            'total_documents' => $documents->count(),
        ]);
    }

    /**
     * عرض وثائق محل معين
     */
    public function shopDocuments($shopId): JsonResponse
    {
        $shop = Shop::with('building')->findOrFail($shopId);
        
        $documents = $shop->documents()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'shop' => [
                'id' => $shop->id,
                'shop_number' => $shop->shop_number,
                'building_name' => $shop->building?->name,
                'floor' => $shop->floor,
            ],
            'documents' => $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'description' => $doc->description,
                    'document_type' => $doc->document_type,
                    'document_type_label' => Document::getDocumentTypes()[$doc->document_type] ?? $doc->document_type,
                    'file_name' => $doc->file_name,
                    'file_url' => $doc->getFileUrl(),
                    'file_size' => $doc->formatted_file_size,
                    'mime_type' => $doc->mime_type,
                    'is_image' => $doc->isImage(),
                    'is_pdf' => $doc->isPdf(),
                    'uploaded_by' => $doc->uploader?->name,
                    'created_at' => $doc->created_at->format('d/m/Y H:i'),
                ];
            }),
            'total_documents' => $documents->count(),
        ]);
    }

    /**
     * إحصائيات الوثائق
     */
    public function statistics(): JsonResponse
    {
        $totalDocuments = Document::where('is_active', true)->count();
        $buildingDocuments = Document::where('documentable_type', Building::class)
            ->where('is_active', true)->count();
        $shopDocuments = Document::where('documentable_type', Shop::class)
            ->where('is_active', true)->count();

        // إحصائية أنواع الوثائق
        $documentTypeStats = Document::where('is_active', true)
            ->selectRaw('document_type, COUNT(*) as count')
            ->groupBy('document_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    Document::getDocumentTypes()[$item->document_type] ?? $item->document_type => $item->count
                ];
            });

        // حجم الملفات الإجمالي
        $totalFileSize = Document::where('is_active', true)->sum('file_size');
        $formattedSize = $this->formatBytes($totalFileSize);

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_documents' => $totalDocuments,
                'building_documents' => $buildingDocuments,
                'shop_documents' => $shopDocuments,
                'document_types' => $documentTypeStats,
                'total_file_size' => $formattedSize,
                'total_file_size_bytes' => $totalFileSize,
            ]
        ]);
    }

    /**
     * عرض وثيقة معينة
     */
    public function show($id): JsonResponse
    {
        $document = Document::with(['documentable', 'uploader'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'document' => [
                'id' => $document->id,
                'title' => $document->title,
                'description' => $document->description,
                'document_type' => $document->document_type,
                'document_type_label' => Document::getDocumentTypes()[$document->document_type] ?? $document->document_type,
                'file_name' => $document->file_name,
                'file_url' => $document->getFileUrl(),
                'file_size' => $document->formatted_file_size,
                'mime_type' => $document->mime_type,
                'is_image' => $document->isImage(),
                'is_pdf' => $document->isPdf(),
                'uploaded_by' => $document->uploader?->name,
                'created_at' => $document->created_at->format('d/m/Y H:i'),
                'documentable_type' => $document->documentable_type,
                'documentable_id' => $document->documentable_id,
                'documentable_name' => $this->getDocumentableName($document),
            ]
        ]);
    }

    /**
     * الحصول على اسم المرفق
     */
    private function getDocumentableName($document): string
    {
        if ($document->documentable_type === Building::class) {
            return $document->documentable?->name ?? 'مبنى غير محدد';
        } elseif ($document->documentable_type === Shop::class) {
            return "محل رقم {$document->documentable?->shop_number}" ?? 'محل غير محدد';
        }
        
        return 'غير محدد';
    }

    /**
     * تنسيق حجم الملف
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
