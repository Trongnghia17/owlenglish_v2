<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes , HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
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
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role_id' => 'integer',
        ];
    }

    // Relationships
    public function identities(): HasMany
    {
        return $this->hasMany(UserIdentity::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(UserContact::class);
    }

    public function primaryEmail(): HasOne
    {
        return $this->hasOne(UserContact::class)
            ->where('type', 'email')->where('is_primary', true);
    }

    public function primaryPhone(): HasOne
    {
        return $this->hasOne(UserContact::class)
            ->where('type', 'phone')->where('is_primary', true);
    }

    /** Mutators (giữ email chuẩn hoá, phone bạn có thể E164 hoá nếu cần) */
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $value ? mb_strtolower(trim($value)) : null;
    }
}
