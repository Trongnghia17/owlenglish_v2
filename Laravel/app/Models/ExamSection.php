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
        'ui_layer',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
     * Một section có nhiều question groups (cho listening và reading)
     */
    public function questionGroups(): HasMany
    {
        return $this->hasMany(ExamQuestionGroup::class)->orderBy('id');
    }

    /**
     * Một section có nhiều questions trực tiếp (cho speaking và writing)
     */
    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('id');
    }

    /**
     * Lấy các question groups đang active
     */
    public function activeQuestionGroups(): HasMany
    {
        return $this->hasMany(ExamQuestionGroup::class)->where('is_active', true);
    }

    /**
     * Lấy các questions đang active (trực tiếp từ section)
     */
    public function activeQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->where('is_active', true);
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
     * Check if section has UI layer
     */
    public function hasUiLayer(): bool
    {
        return !empty($this->ui_layer) && in_array($this->ui_layer, ['1', '2']);
    }
    
    /**
     * Get UI layer value
     */
    public function getUiLayer(): ?string
    {
        return $this->ui_layer;
    }

    /**
     * Check if this section belongs to a speaking or writing skill
     * (which uses direct questions without question groups)
     */
    public function useDirectQuestions(): bool
    {
        return $this->examSkill && 
               ($this->examSkill->isSpeaking() || $this->examSkill->isWriting());
    }

    /**
     * Check if this section uses question groups
     * (listening and reading skills)
     */
    public function useQuestionGroups(): bool
    {
        return $this->examSkill && 
               ($this->examSkill->isListening() || $this->examSkill->isReading());
    }
}
