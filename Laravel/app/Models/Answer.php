<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_question_id',
        'text',
        'is_correct',
        'sort_order',
        'status'
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'status' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Get the sub question that owns this answer
     */
    public function subQuestion(): BelongsTo
    {
        return $this->belongsTo(SubQuestion::class);
    }

    /**
     * Scope for active answers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for correct answers
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope for ordering by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
} 