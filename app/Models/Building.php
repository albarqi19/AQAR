<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Building extends Model
{
    protected $fillable = [
        'district_id',
        'landlord_id',
        'name',
        'building_number',
        'address',
        'floors_count',
        'total_shops',
        'total_area',
        'construction_year',
        'description',
        'documents',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'documents' => 'array',
        'total_area' => 'decimal:2',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class);
    }

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    /**
     * وثائق المبنى
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable')->orderBy('sort_order');
    }

    /**
     * صيانة المبنى
     */
    public function maintenances(): MorphMany
    {
        return $this->morphMany(Maintenance::class, 'maintainable')->orderBy('maintenance_date', 'desc');
    }

    /**
     * مصروفات المبنى
     */
    public function expenses(): MorphMany
    {
        return $this->morphMany(Expense::class, 'expensable')->orderBy('expense_date', 'desc');
    }
    
    // Accessor للحصول على المدينة عبر الحي
    public function getCityAttribute()
    {
        return $this->district ? $this->district->city : null;
    }
}
