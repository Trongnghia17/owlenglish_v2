<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

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
        'email_verified_at',
        'phone_verified_at',
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

    public function getLoginMethodsAttribute()
    {
        return $this->identities->pluck('provider')->toArray();
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

    // Role relationship and methods
    public function role(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Role::class);
    }

    public function hasRole($roleId): bool
    {
        return $this->role_id == $roleId;
    }

    public function hasAnyRole(array $roleIds): bool
    {
        return in_array($this->role_id, $roleIds);
    }


    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->permissions()
            ->whereIn('name', $permissions)
            ->exists();
    }

    /** Mutators (giữ email chuẩn hoá, phone bạn có thể E164 hoá nếu cần) */
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $value ? mb_strtolower(trim($value)) : null;
    }
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withTimestamps()
            ->withPivot(['granted', 'expires_at', 'deleted_at']);
    }

    public function hasPermission(string $permissionName): bool
    {
        // Check role permissions
        if ($this->role && $this->role->hasPermission($permissionName)) {
            return true;
        }

        // Check direct user permissions
        return $this->permissions()
            ->where('name', $permissionName)
            ->wherePivot('granted', true)
            ->where(function ($query) {
                $query->whereNull('user_permissions.expires_at')
                    ->orWhere('user_permissions.expires_at', '>', now());
            })
            ->exists();
    }

    public static function getRoleOptions(): array
    {
        return [
            1 => 'Super Admin (Chủ hệ thống)',
            2 => 'Org Admin (Quản trị hệ thống)',
            3 => 'Academic Manager (Giáo vụ)',
            4 => 'Assessment & Curriculum Planning (Chấm & giáo trình)',
            5 => 'Teaching (Giáo viên)',
            6 => 'Student (Học viên)',
            7 => 'Parent/Guardian (Phụ huynh)',
            8 => 'Content Author (Biên soạn nội dung)',
            9 => 'Finance (Kế toán)',
            10 => 'Marketing'
        ];
    }

    public function getRoleInfo(): array
    {
        $roleColors = [
            1 => 'danger',    // Super Admin
            2 => 'warning',   // Org Admin
            3 => 'primary',   // Academic Manager
            4 => 'info',      // ACP
            5 => 'success',   // Teaching
            6 => 'secondary', // Student
            7 => 'light',     // Parent
            8 => 'dark',      // Content Author
            9 => 'warning',   // Finance
            10 => 'info'      // Marketing
        ];

        $roleOptions = self::getRoleOptions();

        return [
            'name' => $roleOptions[$this->role_id] ?? 'Unknown',
            'color' => $roleColors[$this->role_id] ?? 'secondary'
        ];
    }
}
