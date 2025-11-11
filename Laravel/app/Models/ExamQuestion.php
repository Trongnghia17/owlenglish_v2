<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamQuestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'exam_question_group_id',
        'content',
        'answer_content',
        'is_correct',
        'point',
        'feedback',
        'hint',
        'image',
        'audio_file',
        'metadata',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_correct' => 'boolean',
        'order' => 'integer',
        'point' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Một question thuộc về một question group
     */
    public function questionGroup(): BelongsTo
    {
        return $this->belongsTo(ExamQuestionGroup::class, 'exam_question_group_id');
    }

    /**
     * Scope: Chỉ lấy question đang active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Chỉ lấy câu trả lời đúng
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Check if question has image
     */
    public function hasImage(): bool
    {
        return !empty($this->image);
    }

    /**
     * Check if question has audio
     */
    public function hasAudio(): bool
    {
        return !empty($this->audio_file);
    }

    /**
     * Check if question has hint
     */
    public function hasHint(): bool
    {
        return !empty($this->hint);
    }

    /**
     * Check if question has feedback
     */
    public function hasFeedback(): bool
    {
        return !empty($this->feedback);
    }
}
