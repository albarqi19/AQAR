<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Shop extends Model
{
    protected $fillable = [
        'building_id',
        'shop_number',
        'floor',
        'area',
        'shop_type',
        'status',
        'description',
        'documents',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'documents' => 'array',
        'area' => 'decimal:2',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * وثائق المحل
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable')->orderBy('sort_order');
    }

    /**
     * صيانة المحل
     */
    public function maintenances(): MorphMany
    {
        return $this->morphMany(Maintenance::class, 'maintainable')->orderBy('maintenance_date', 'desc');
    }

    /**
     * مصروفات المحل
     */
    public function expenses(): MorphMany
    {
        return $this->morphMany(Expense::class, 'expensable')->orderBy('expense_date', 'desc');
    }

    public function activeContract()
    {
        return $this->contracts()->where('status', 'active')->first();
    }

    public function getFormattedNameAttribute()
    {
        return "محل رقم {$this->shop_number} - الطابق {$this->floor}";
    }
}
