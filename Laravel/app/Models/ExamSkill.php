<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamSkill extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'exam_test_id',
        'skill_type',
        'name',
        'description',
        'image',
        'time_limit',
        'is_active',
        'is_online',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        'time_limit' => 'integer',
    ];

    /**
     * Một skill thuộc về một test
     */
    public function examTest(): BelongsTo
    {
        return $this->belongsTo(ExamTest::class);
    }

    /**
     * Một skill có nhiều sections (Part 1, Part 2, Part 3)
     */
    public function sections(): HasMany
    {
        return $this->hasMany(ExamSection::class)->orderBy('id');
    }

    /**
     * Lấy các sections đang active
     */
    public function activeSections(): HasMany
    {
        return $this->hasMany(ExamSection::class)->where('is_active', true);
    }

    /**
     * Scope: Lọc theo loại skill
     */
    public function scopeOfType($query, string $skillType)
    {
        return $query->where('skill_type', $skillType);
    }

    /**
     * Scope: Chỉ lấy skill đang active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if this is a reading skill
     */
    public function isReading(): bool
    {
        return $this->skill_type === 'reading';
    }

    /**
     * Check if this is a listening skill
     */
    public function isListening(): bool
    {
        return $this->skill_type === 'listening';
    }

    /**
     * Check if this is a writing skill
     */
    public function isWriting(): bool
    {
        return $this->skill_type === 'writing';
    }

    /**
     * Check if this is a speaking skill
     */
    public function isSpeaking(): bool
    {
        return $this->skill_type === 'speaking';
    }
}
