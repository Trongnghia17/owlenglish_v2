<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamTest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'exam_id',
        'name',
        'description',
        'image',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Một test thuộc về một exam
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Một test có nhiều skills (Reading, Writing, Speaking, Listening)
     */
    public function skills(): HasMany
    {
        return $this->hasMany(ExamSkill::class)->orderBy('order');
    }

    /**
     * Lấy các skills đang active
     */
    public function activeSkills(): HasMany
    {
        return $this->hasMany(ExamSkill::class)->where('is_active', true)->orderBy('order');
    }

    /**
     * Scope: Chỉ lấy test đang active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
