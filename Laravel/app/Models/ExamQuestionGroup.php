<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamQuestionGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'exam_section_id',
        'content',
        'question_type',
        'answer_layout',
        'instructions',
        'options',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'options' => 'array',
    ];

    /**
     * Một question group thuộc về một section
     */
    public function examSection(): BelongsTo
    {
        return $this->belongsTo(ExamSection::class);
    }

    /**
     * Một question group có nhiều questions
     */
    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order');
    }

    /**
     * Lấy các questions đang active
     */
    public function activeQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->where('is_active', true)->orderBy('order');
    }

    /**
     * Scope: Chỉ lấy question group đang active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Lọc theo loại câu hỏi
     */
    public function scopeOfType($query, string $questionType)
    {
        return $query->where('question_type', $questionType);
    }

    /**
     * Check if this is multiple choice type
     */
    public function isMultipleChoice(): bool
    {
        return $this->question_type === 'multiple_choice';
    }

    /**
     * Check if this is essay type (for Writing)
     */
    public function isEssay(): bool
    {
        return $this->question_type === 'essay';
    }

    /**
     * Check if this is speaking type
     */
    public function isSpeaking(): bool
    {
        return $this->question_type === 'speaking';
    }

    /**
     * Check if this uses drag and drop layout
     */
    public function isDragDrop(): bool
    {
        return $this->answer_layout === 'drag_drop';
    }

    /**
     * Get total points for this question group
     */
    public function getTotalPoints(): float
    {
        return $this->questions()->sum('point');
    }
}
