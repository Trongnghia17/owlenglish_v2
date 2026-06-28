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
        'writing_feedback',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'writing_feedback' => 'array',
        'score' => 'decimal:2',
        'graded_at' => 'datetime',
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

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
