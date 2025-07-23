<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintainable_type',
        'maintainable_id',
        'maintenance_date',
        'maintenance_type',
        'description',
        'status',
        'notes',
        'cost',
        'contractor_name',
        'contractor_phone',
        'scheduled_date',
        'completed_date',
        'created_by',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2',
    ];

    /**
     * علاقة polymorphic - يمكن ربط الصيانة بأي نموذج (مبنى، محل، إلخ)
     */
    public function maintainable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * المستخدم الذي أنشأ سجل الصيانة
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * أنواع الصيانة المختلفة
     */
    public static function getMaintenanceTypes(): array
    {
        return [
            'electrical' => 'كهرباء',
            'plumbing' => 'سباكة',
            'cleaning' => 'تنظيف',
            'painting' => 'دهانات',
            'operation' => 'تشغيل',
            'new_installation' => 'تأسيس جديد',
            'renovation' => 'ترميم',
            'repair' => 'إصلاح',
            'construction' => 'إنشاء',
            'air_conditioning' => 'تكييف',
            'flooring' => 'أرضيات',
            'doors_windows' => 'أبواب ونوافذ',
            'elevator' => 'مصاعد',
            'security_system' => 'أنظمة أمنية',
            'fire_safety' => 'أنظمة إطفاء',
            'garden_landscape' => 'حدائق وتنسيق',
            'other' => 'أخرى',
        ];
    }

    /**
     * حالات الصيانة
     */
    public static function getStatuses(): array
    {
        return [
            'pending' => 'لم يتم البدء',
            'in_progress' => 'جاري التنفيذ',
            'completed' => 'تم التنفيذ',
            'cancelled' => 'ملغي',
        ];
    }

    /**
     * الحصول على اسم نوع الصيانة
     */
    public function getMaintenanceTypeNameAttribute(): string
    {
        return self::getMaintenanceTypes()[$this->maintenance_type] ?? $this->maintenance_type;
    }

    /**
     * الحصول على اسم الحالة
     */
    public function getStatusNameAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * التحقق من اكتمال الصيانة
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * التحقق من تقدم الصيانة
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * الحصول على اسم المكان
     */
    public function getLocationNameAttribute(): string
    {
        if ($this->maintainable_type === Building::class) {
            return $this->maintainable?->name ?? 'مبنى غير محدد';
        } elseif ($this->maintainable_type === Shop::class) {
            return "محل رقم {$this->maintainable?->shop_number}" ?? 'محل غير محدد';
        }
        
        return 'غير محدد';
    }
}
