<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expensable_type',
        'expensable_id',
        'expense_date',
        'expense_type',
        'description',
        'amount',
        'currency',
        'notes',
        'vendor_name',
        'vendor_phone',
        'invoice_number',
        'receipt_path',
        'status',
        'paid_date',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * علاقة polymorphic - يمكن ربط المصروف بأي نموذج (مبنى، محل، إلخ)
     */
    public function expensable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * المستخدم الذي أنشأ سجل المصروف
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * أنواع المصروفات المختلفة
     */
    public static function getExpenseTypes(): array
    {
        return [
            'utilities_water' => 'فواتير المياه',
            'utilities_electricity' => 'فواتير الكهرباء',
            'utilities_gas' => 'فواتير الغاز',
            'sewage_pumping' => 'شفط صرف صحي',
            'cleaning' => 'تنظيف',
            'security' => 'أمن وحراسة',
            'maintenance' => 'صيانة عامة',
            'repairs' => 'إصلاحات',
            'supplies' => 'مستلزمات',
            'insurance' => 'تأمين',
            'taxes_fees' => 'ضرائب ورسوم',
            'legal_fees' => 'رسوم قانونية',
            'advertising' => 'إعلان وتسويق',
            'office_supplies' => 'مستلزمات مكتبية',
            'transportation' => 'مواصلات',
            'telecommunications' => 'اتصالات',
            'pest_control' => 'مكافحة حشرات',
            'waste_management' => 'إدارة النفايات',
            'landscaping' => 'تنسيق حدائق',
            'other' => 'أخرى',
        ];
    }

    /**
     * حالات المصروف
     */
    public static function getStatuses(): array
    {
        return [
            'pending' => 'في الانتظار',
            'paid' => 'مدفوع',
            'cancelled' => 'ملغي',
        ];
    }

    /**
     * الحصول على اسم نوع المصروف
     */
    public function getExpenseTypeNameAttribute(): string
    {
        return self::getExpenseTypes()[$this->expense_type] ?? $this->expense_type;
    }

    /**
     * الحصول على اسم الحالة
     */
    public function getStatusNameAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * التحقق من دفع المصروف
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * التحقق من وجود إيصال
     */
    public function hasReceipt(): bool
    {
        return !empty($this->receipt_path);
    }

    /**
     * الحصول على رابط الإيصال
     */
    public function getReceiptUrl(): ?string
    {
        return $this->hasReceipt() ? asset('storage/' . $this->receipt_path) : null;
    }

    /**
     * الحصول على اسم المكان
     */
    public function getLocationNameAttribute(): string
    {
        if ($this->expensable_type === Building::class) {
            return $this->expensable?->name ?? 'مبنى غير محدد';
        } elseif ($this->expensable_type === Shop::class) {
            return "محل رقم {$this->expensable?->shop_number}" ?? 'محل غير محدد';
        }
        
        return 'غير محدد';
    }

    /**
     * تنسيق المبلغ بالعملة
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }
}
