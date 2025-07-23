<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    protected $fillable = [
        'shop_id',
        'tenant_id',
        'contract_number',
        'start_date',
        'end_date',
        'duration_months',
        'annual_rent',
        'payment_amount',
        'payment_frequency',
        'tax_rate',
        'tax_amount',
        'fixed_amounts',
        'total_annual_amount',
        'status',
        'terms',
        'documents',
        'contract_document_path',
        'contract_document_name',
        'contract_document_mime_type',
        'contract_document_size',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'annual_rent' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'fixed_amounts' => 'decimal:2',
        'total_annual_amount' => 'decimal:2',
        'documents' => 'array',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * التحقق من وجود صورة للعقد
     */
    public function hasContractDocument(): bool
    {
        return !empty($this->contract_document_path);
    }

    /**
     * الحصول على رابط صورة العقد
     */
    public function getContractDocumentUrl(): ?string
    {
        if (!$this->hasContractDocument()) {
            return null;
        }
        
        return asset('storage/' . $this->contract_document_path);
    }

    /**
     * الحصول على حجم الملف بتنسيق قابل للقراءة
     */
    public function getFormattedContractDocumentSize(): ?string
    {
        if (!$this->contract_document_size) {
            return null;
        }

        $bytes = $this->contract_document_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * التحقق من نوع الملف إذا كان صورة
     */
    public function isContractDocumentImage(): bool
    {
        return $this->contract_document_mime_type && str_starts_with($this->contract_document_mime_type, 'image/');
    }

    /**
     * التحقق من نوع الملف إذا كان PDF
     */
    public function isContractDocumentPdf(): bool
    {
        return $this->contract_document_mime_type === 'application/pdf';
    }
}
