<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'commercial_registration',
        'phone',
        'email',
        'national_id',
        'address',
        'emergency_contact',
        'emergency_phone',
        'documents',
        'is_active',
        'identity_document_path',
        'identity_document_name',
        'commercial_register_path',
        'commercial_register_name',
        'additional_document1_path',
        'additional_document1_name',
        'additional_document1_label',
        'additional_document2_path',
        'additional_document2_name',
        'additional_document2_label',
        'additional_document3_path',
        'additional_document3_name',
        'additional_document3_label',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'documents' => 'array',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * وثائق المستأجر
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable')->orderBy('sort_order');
    }

    /**
     * التحقق من وجود صورة الهوية
     */
    public function hasIdentityDocument(): bool
    {
        return !empty($this->identity_document_path);
    }

    /**
     * التحقق من وجود صورة السجل التجاري
     */
    public function hasCommercialRegister(): bool
    {
        return !empty($this->commercial_register_path);
    }

    /**
     * التحقق من وجود المستند الإضافي الأول
     */
    public function hasAdditionalDocument1(): bool
    {
        return !empty($this->additional_document1_path);
    }

    /**
     * التحقق من وجود المستند الإضافي الثاني
     */
    public function hasAdditionalDocument2(): bool
    {
        return !empty($this->additional_document2_path);
    }

    /**
     * التحقق من وجود المستند الإضافي الثالث
     */
    public function hasAdditionalDocument3(): bool
    {
        return !empty($this->additional_document3_path);
    }

    /**
     * الحصول على رابط صورة الهوية
     */
    public function getIdentityDocumentUrl(): ?string
    {
        return $this->hasIdentityDocument() ? asset('storage/' . $this->identity_document_path) : null;
    }

    /**
     * الحصول على رابط صورة السجل التجاري
     */
    public function getCommercialRegisterUrl(): ?string
    {
        return $this->hasCommercialRegister() ? asset('storage/' . $this->commercial_register_path) : null;
    }

    /**
     * الحصول على رابط المستند الإضافي الأول
     */
    public function getAdditionalDocument1Url(): ?string
    {
        return $this->hasAdditionalDocument1() ? asset('storage/' . $this->additional_document1_path) : null;
    }

    /**
     * الحصول على رابط المستند الإضافي الثاني
     */
    public function getAdditionalDocument2Url(): ?string
    {
        return $this->hasAdditionalDocument2() ? asset('storage/' . $this->additional_document2_path) : null;
    }

    /**
     * الحصول على رابط المستند الإضافي الثالث
     */
    public function getAdditionalDocument3Url(): ?string
    {
        return $this->hasAdditionalDocument3() ? asset('storage/' . $this->additional_document3_path) : null;
    }

    /**
     * الحصول على جميع المستندات المرفوعة
     */
    public function getUploadedDocuments(): array
    {
        $documents = [];
        
        if ($this->hasIdentityDocument()) {
            $documents['identity'] = [
                'label' => 'صورة الهوية',
                'url' => $this->getIdentityDocumentUrl(),
                'name' => $this->identity_document_name
            ];
        }
        
        if ($this->hasCommercialRegister()) {
            $documents['commercial_register'] = [
                'label' => 'السجل التجاري',
                'url' => $this->getCommercialRegisterUrl(),
                'name' => $this->commercial_register_name
            ];
        }
        
        if ($this->hasAdditionalDocument1()) {
            $documents['additional1'] = [
                'label' => $this->additional_document1_label ?: 'مستند إضافي 1',
                'url' => $this->getAdditionalDocument1Url(),
                'name' => $this->additional_document1_name
            ];
        }
        
        if ($this->hasAdditionalDocument2()) {
            $documents['additional2'] = [
                'label' => $this->additional_document2_label ?: 'مستند إضافي 2',
                'url' => $this->getAdditionalDocument2Url(),
                'name' => $this->additional_document2_name
            ];
        }
        
        if ($this->hasAdditionalDocument3()) {
            $documents['additional3'] = [
                'label' => $this->additional_document3_label ?: 'مستند إضافي 3',
                'url' => $this->getAdditionalDocument3Url(),
                'name' => $this->additional_document3_name
            ];
        }
        
        return $documents;
    }
}
