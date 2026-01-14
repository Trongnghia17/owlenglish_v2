<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExamFilter extends Model
{
    use HasFactory;

    protected $table = 'exam_filters';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    /* ======================
     |  RELATIONSHIPS
     |======================*/

    // Cha
    public function parent()
    {
        return $this->belongsTo(ExamFilter::class, 'parent_id');
    }

    // Con
    public function children()
    {
        return $this->hasMany(ExamFilter::class, 'parent_id')
                    ->orderBy('sort_order');
    }

    // Con (chỉ lấy active)
    public function activeChildren()
    {
        return $this->children()->where('is_active', true);
    }

    // Các đề thi thuộc filter này
    public function examSections()
    {
        return $this->hasMany(ExamSection::class, 'exam_filter_id');
    }

    /* ======================
     |  SCOPES
     |======================*/

    // Filter gốc (Listening, Reading...)
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // Chỉ lấy active
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Theo type
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /* ======================
     |  HELPERS
     |======================*/

    // Kiểm tra có con không
    public function isLeaf()
    {
        return !$this->children()->exists();
    }

    public function sections()
    {
        return $this->belongsToMany(
            ExamSection::class,
            'exam_section_filter',
            'exam_filter_id',
            'exam_section_id'
        )->withTimestamps();
    }
}
