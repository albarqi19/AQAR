<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Landlord extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'commercial_registration',
        'license_number',
        'phone',
        'email',
        'address',
        'contact_person',
        'commission_rate',
        'documents',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'documents' => 'array',
        'commission_rate' => 'decimal:2',
    ];

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }
}
