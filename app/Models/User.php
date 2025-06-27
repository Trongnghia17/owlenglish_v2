<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable ,SoftDeletes;

    const ROLE_ADMIN = 0;
    const ROLE_TEACHER_TEACHING = 1;
    const ROLE_TEACHER_GRADING = 2;
    const ROLE_TEACHER_CONTENT = 3;
    const ROLE_STUDENT_CARE = 4;
    const ROLE_ASSISTANT_CONTENT = 5;
    const ROLE_STUDENT_CENTER = 6;
    const ROLE_STUDENT_VISITOR = 7;

    const ROLE_NAMES = [
        self::ROLE_ADMIN => 'Quản lý tài khoản Admin',
        self::ROLE_TEACHER_TEACHING => 'Giáo viên giảng dạy',
        self::ROLE_TEACHER_GRADING => 'Giáo viên chấm sửa bài',
        self::ROLE_TEACHER_CONTENT => 'Giáo viên làm đề, chủ đề',
        self::ROLE_STUDENT_CARE => 'Chăm sóc học viên',
        self::ROLE_ASSISTANT_CONTENT => 'Trợ lý chuyên môn làm đề, chủ đề',
        self::ROLE_STUDENT_CENTER => 'Học viên trung tâm',
        self::ROLE_STUDENT_VISITOR => 'Học viên vãng lai',
    ];

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
        'role',
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
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => 'integer',
        ];
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'phone';
    }

    /**
     * Get the username field name.
     *
     * @return string
     */
    public function username()
    {
        return 'phone';
    }

    // Role checking methods
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isTeacher(): bool
    {
        return in_array($this->role, [
            self::ROLE_TEACHER_TEACHING,
            self::ROLE_TEACHER_GRADING,
            self::ROLE_TEACHER_CONTENT
        ]);
    }

    public function isAssistant(): bool
    {
        return in_array($this->role, [
            self::ROLE_STUDENT_CARE,
            self::ROLE_ASSISTANT_CONTENT
        ]);
    }

    public function isStudent(): bool
    {
        return in_array($this->role, [
            self::ROLE_STUDENT_CENTER,
            self::ROLE_STUDENT_VISITOR
        ]);
    }

    public function hasRole(int $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function canEditRole(): bool
    {
        // Chỉ admin mới có thể sửa vai trò
        return $this->role === self::ROLE_ADMIN;
    }

    public function canDeleteUser(): bool
    {
        // Admin không thể xóa chính mình
        return $this->role === self::ROLE_ADMIN && $this->id !== auth()->id();
    }

    public static function getMainRoleGroups(): array
    {
        return [
            'admin' => [self::ROLE_ADMIN],
            'teacher' => [self::ROLE_TEACHER_TEACHING, self::ROLE_TEACHER_GRADING, self::ROLE_TEACHER_CONTENT],
            'assistant' => [self::ROLE_STUDENT_CARE, self::ROLE_ASSISTANT_CONTENT],
            'student' => [self::ROLE_STUDENT_CENTER, self::ROLE_STUDENT_VISITOR]
        ];
    }

    public function getRoleGroup(): string
    {
        $groups = self::getMainRoleGroups();
        foreach ($groups as $group => $roles) {
            if (in_array($this->role, $roles)) {
                return $group;
            }
        }
        return 'unknown';
    }

    public function getRoleNameAttribute(): string
    {
        return self::ROLE_NAMES[$this->role] ?? 'Không xác định';
    }

    public static function getRoleOptions(): array
    {
        return self::ROLE_NAMES;
    }

    public static function getTeacherRoles(): array
    {
        return [
            self::ROLE_TEACHER_TEACHING,
            self::ROLE_TEACHER_GRADING,
            self::ROLE_TEACHER_CONTENT
        ];
    }

    public static function getAssistantRoles(): array
    {
        return [
            self::ROLE_STUDENT_CARE,
            self::ROLE_ASSISTANT_CONTENT
        ];
    }

    public static function getStudentRoles(): array
    {
        return [
            self::ROLE_STUDENT_CENTER,
            self::ROLE_STUDENT_VISITOR
        ];
    }
    public function getRoleInfo(): array
{
    $roleGroups = [
        'admin' => ['color' => 'danger', 'name' => 'Admin'],
        'teacher' => ['color' => 'success', 'name' => 'Giáo viên'], 
        'assistant' => ['color' => 'warning', 'name' => 'Trợ lý'],
        'student' => ['color' => 'primary', 'name' => 'Học viên']
    ];

    $group = $this->getRoleGroup();
    $roleName = self::ROLE_NAMES[$this->role] ?? 'Không xác định';

    return [
        'name' => $roleName,
        'color' => $roleGroups[$group]['color'] ?? 'secondary',
        'group' => $roleGroups[$group]['name'] ?? 'Không xác định'
    ];
}
}