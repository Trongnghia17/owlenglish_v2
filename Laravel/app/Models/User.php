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

    // New role constants for updated system
    const ROLE_SUPER_ADMIN = 0;
    const ROLE_ORG_ADMIN = 1;
    const ROLE_ACADEMIC_MANAGER = 2;
    const ROLE_ACP = 3;
    const ROLE_TEACHING = 4;
    const ROLE_STUDENT = 5;
    const ROLE_PARENT = 6;
    const ROLE_CONTENT_AUTHOR = 7;
    const ROLE_FINANCE = 8;
    const ROLE_MARKETING = 9;


    const ROLE_NAMES = [
        self::ROLE_SUPER_ADMIN => 'Super Admin (Chủ hệ thống)',
        self::ROLE_ORG_ADMIN => 'Org Admin (Quản trị hệ thống)',
        self::ROLE_ACADEMIC_MANAGER => 'Academic Manager (Giáo vụ)',
        self::ROLE_ACP => 'Assessment & Curriculum Planning',
        self::ROLE_TEACHING => 'Teaching (Giáo viên)',
        self::ROLE_STUDENT => 'Student (Học viên)',
        self::ROLE_PARENT => 'Parent/Guardian (Phụ huynh)',
        self::ROLE_CONTENT_AUTHOR => 'Content Author (Biên soạn nội dung)',
        self::ROLE_FINANCE => 'Finance (Kế toán)',
        self::ROLE_MARKETING => 'Marketing',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [

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
