<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamCollection extends Model
{
    protected $fillable = [
        'name',
        'type',
        'status'
    ];

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'exam_collection_id');
    }
}
