<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'description',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Một exam có nhiều tests (Test 1, Test 2, Test 3)
     */
    public function tests(): HasMany
    {
        return $this->hasMany(ExamTest::class);
    }

    /**
     * Lấy các tests đang active
     */
    public function activeTests(): HasMany
    {
        return $this->hasMany(ExamTest::class)->where('is_active', true);
    }

    /**
     * Scope: Lọc theo loại exam
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Chỉ lấy exam đang active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
