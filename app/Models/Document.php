<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'title',
        'description',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_by',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * علاقة polymorphic - يمكن ربط الوثيقة بأي نموذج (مبنى، محل، عقد، إلخ)
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * المستخدم الذي رفع الوثيقة
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * أنواع الوثائق المختلفة
     */
    public static function getDocumentTypes(): array
    {
        return [
            'deed' => 'صك الملكية',
            'soil_test' => 'فحص التربة',
            'building_image' => 'صورة المبنى',
            'autocad_design' => 'تصميم أوتوكاد',
            '3d_design' => 'تصميم ثلاثي الأبعاد',
            'license' => 'رخصة البناء',
            'insurance' => 'وثيقة التأمين',
            'maintenance_report' => 'تقرير الصيانة',
            'inspection_report' => 'تقرير الفحص',
            'contract_document' => 'وثيقة العقد',
            'identity_document' => 'وثيقة هوية',
            'commercial_register' => 'السجل التجاري',
            'other' => 'أخرى',
        ];
    }

    /**
     * الحصول على حجم الملف بتنسيق قابل للقراءة
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * التحقق من نوع الملف إذا كان صورة
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * التحقق من نوع الملف إذا كان PDF
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * الحصول على URL الملف
     */
    public function getFileUrl(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
