<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'phone',
        'tenant_id',
        'landlord_id',
        'last_login_at',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * علاقة مع المستأجر
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * علاقة مع المالك
     */
    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class);
    }

    /**
     * التحقق من نوع المستخدم
     */
    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isLandlord(): bool
    {
        return $this->user_type === 'landlord';
    }

    public function isTenant(): bool
    {
        return $this->user_type === 'tenant';
    }

    /**
     * الحصول على البيانات المرتبطة حسب نوع المستخدم
     */
    public function getProfileData()
    {
        switch ($this->user_type) {
            case 'tenant':
                return $this->tenant;
            case 'landlord':
                return $this->landlord;
            default:
                return null;
        }
    }

    /**
     * تحديث آخر تسجيل دخول
     */
    public function updateLastLogin()
    {
        $this->last_login_at = now();
        $this->save();
    }
}
