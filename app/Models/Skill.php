<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'description',
        'duration_minutes',
        'skill_type',
        'part',
        'view_count',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'duration_minutes' => 'integer',
        'part' => 'integer',
        'view_count' => 'integer'
    ];

    /**
     * Get all skill contents for this skill
     */
    public function skillContents(): HasMany
    {
        return $this->hasMany(SkillContent::class);
    }

    /**
     * Get active skill contents only
     */
    public function activeSkillContents(): HasMany
    {
        return $this->hasMany(SkillContent::class)->where('status', 1);
    }

    /**
     * Scope for active skills
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for specific skill type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('skill_type', $type);
    }
} 