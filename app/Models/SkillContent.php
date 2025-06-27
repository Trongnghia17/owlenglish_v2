<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'skill_id',
        'title',
        'text',
        'media_url',
        'sort_order',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Get the skill that owns this content
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    /**
     * Get all general questions for this content
     */
    public function generalQuestions(): HasMany
    {
        return $this->hasMany(GeneralQuestion::class);
    }

    /**
     * Get active general questions only
     */
    public function activeGeneralQuestions(): HasMany
    {
        return $this->hasMany(GeneralQuestion::class)->where('status', 1);
    }

    /**
     * Scope for active contents
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