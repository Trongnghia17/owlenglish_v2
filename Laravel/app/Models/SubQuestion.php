<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'general_question_id',
        'text',
        'sort_order',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Get the general question that owns this sub question
     */
    public function generalQuestion(): BelongsTo
    {
        return $this->belongsTo(GeneralQuestion::class);
    }

    /**
     * Get all answers for this sub question
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Get active answers only
     */
    public function activeAnswers(): HasMany
    {
        return $this->hasMany(Answer::class)->where('status', 1);
    }

    /**
     * Get correct answers only
     */
    public function correctAnswers(): HasMany
    {
        return $this->hasMany(Answer::class)->where('is_correct', true);
    }

    /**
     * Scope for active sub questions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for ordering by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
} 