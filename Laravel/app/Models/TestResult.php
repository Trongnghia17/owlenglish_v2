<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestResult extends Model
{
    protected $fillable = [
        'user_id',
        'exam_skill_id',
        'exam_section_id',
        'exam_test_id',
        'total_questions',
        'answered_questions',
        'correct_answers',
        'score',
        'time_spent',
        'status',
        'answers',
    ];

    protected $casts = [
        'answers' => 'array',
        'score' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(ExamSkill::class, 'exam_skill_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ExamSection::class, 'exam_section_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(ExamTest::class, 'exam_test_id');
    }
}
