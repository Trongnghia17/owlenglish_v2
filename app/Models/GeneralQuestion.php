<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeneralQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'skill_content_id',
        'title',
        'text',
        'sort_order',
        'type',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Get the skill content that owns this question
     */
    public function skillContent(): BelongsTo
    {
        return $this->belongsTo(SkillContent::class);
    }

    /**
     * Get all sub questions for this general question
     */
    public function subQuestions(): HasMany
    {
        return $this->hasMany(SubQuestion::class);
    }

    /**
     * Get active sub questions only
     */
    public function activeSubQuestions(): HasMany
    {
        return $this->hasMany(SubQuestion::class)->where('status', 1);
    }

    /**
     * Scope for active questions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for specific question type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for ordering by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
} 