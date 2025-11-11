<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamSection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'exam_skill_id',
        'title',
        'content',
        'feedback',
        'content_format',
        'audio_file',
        'video_file',
        'metadata',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Một section thuộc về một skill
     */
    public function examSkill(): BelongsTo
    {
        return $this->belongsTo(ExamSkill::class);
    }

    /**
     * Một section có nhiều question groups
     */
    public function questionGroups(): HasMany
    {
        return $this->hasMany(ExamQuestionGroup::class)->orderBy('order');
    }

    /**
     * Lấy các question groups đang active
     */
    public function activeQuestionGroups(): HasMany
    {
        return $this->hasMany(ExamQuestionGroup::class)->where('is_active', true)->orderBy('order');
    }

    /**
     * Scope: Chỉ lấy section đang active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if section has audio content
     */
    public function hasAudio(): bool
    {
        return $this->content_format === 'audio' && !empty($this->audio_file);
    }

    /**
     * Check if section has video content
     */
    public function hasVideo(): bool
    {
        return $this->content_format === 'video' && !empty($this->video_file);
    }
}
