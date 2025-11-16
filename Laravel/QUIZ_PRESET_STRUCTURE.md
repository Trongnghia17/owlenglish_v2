# Quiz Preset Structure Documentation

## Tổng quan

Quiz Preset hiện đã được cấu trúc lại để phù hợp với từng loại skill:

### 1. **Speaking và Writing** (Direct Questions)
```
ExamSkill → ExamSection → ExamQuestion
```
- Questions thuộc trực tiếp về Section
- **KHÔNG** sử dụng QuestionGroup
- `exam_section_id` được sử dụng trong bảng `exam_questions`
- `exam_question_group_id` là **NULL**

### 2. **Listening và Reading** (Group Questions)
```
ExamSkill → ExamSection → ExamQuestionGroup → ExamQuestion
```
- Questions thuộc về QuestionGroup
- QuestionGroup thuộc về Section
- `exam_question_group_id` được sử dụng trong bảng `exam_questions`
- `exam_section_id` là **NULL**

## Database Schema

### exam_questions table
```php
- id
- exam_question_group_id (nullable) // Dùng cho Listening & Reading
- exam_section_id (nullable)        // Dùng cho Speaking & Writing
- content
- answer_content
- is_correct
- point
- feedback
- hint
- image
- audio_file
- metadata
- is_active
```

**Quy tắc:**
- Một trong hai (`exam_question_group_id` hoặc `exam_section_id`) phải có giá trị
- Speaking/Writing: `exam_section_id` có giá trị, `exam_question_group_id` là NULL
- Listening/Reading: `exam_question_group_id` có giá trị, `exam_section_id` là NULL

## Model Relationships

### ExamSection Model
```php
// Listening & Reading
public function questionGroups(): HasMany

// Speaking & Writing
public function questions(): HasMany

// Helper methods
public function useDirectQuestions(): bool  // true cho Speaking & Writing
public function useQuestionGroups(): bool   // true cho Listening & Reading
```

### ExamQuestion Model
```php
// Relationship với QuestionGroup (Listening & Reading)
public function questionGroup(): BelongsTo

// Relationship với Section (Speaking & Writing)
public function examSection(): BelongsTo

// Helper methods
public function belongsToSection(): bool       // true nếu là Speaking/Writing
public function belongsToQuestionGroup(): bool // true nếu là Listening/Reading
```

### ExamSkill Model
```php
// Helper methods để xác định loại skill
public function isReading(): bool
public function isListening(): bool
public function isWriting(): bool
public function isSpeaking(): bool
```

## Ví dụ sử dụng

### 1. Tạo câu hỏi cho Speaking/Writing:
```php
$section = ExamSection::find($sectionId);

if ($section->useDirectQuestions()) {
    ExamQuestion::create([
        'exam_section_id' => $section->id,
        'exam_question_group_id' => null,
        'content' => 'Question content...',
        // ... other fields
    ]);
}
```

### 2. Tạo câu hỏi cho Listening/Reading:
```php
$questionGroup = ExamQuestionGroup::find($groupId);

if ($questionGroup->examSection->useQuestionGroups()) {
    ExamQuestion::create([
        'exam_question_group_id' => $questionGroup->id,
        'exam_section_id' => null,
        'content' => 'Question content...',
        // ... other fields
    ]);
}
```

### 3. Lấy tất cả questions theo skill type:
```php
$section = ExamSection::with('examSkill')->find($sectionId);

if ($section->examSkill->isSpeaking() || $section->examSkill->isWriting()) {
    // Lấy questions trực tiếp từ section
    $questions = $section->activeQuestions;
} else {
    // Lấy questions qua question groups
    $questions = $section->questionGroups()
        ->with('activeQuestions')
        ->get()
        ->pluck('activeQuestions')
        ->flatten();
}
```

## Migration History

### 2025_11_16_000001_modify_exam_questions_add_section_relation.php
- Thêm column `exam_section_id` (nullable) vào bảng `exam_questions`
- Thay đổi `exam_question_group_id` thành nullable
- Thêm indexes: `idx_questions_section_active`
- Giữ nguyên index: `idx_questions_group_active`

## Validation Rules

Khi tạo/update questions, cần validate:
```php
// Một trong hai phải có giá trị
'exam_question_group_id' => 'required_without:exam_section_id|nullable|exists:exam_question_groups,id',
'exam_section_id' => 'required_without:exam_question_group_id|nullable|exists:exam_sections,id',

// Hoặc sử dụng custom validation
public function rules()
{
    return [
        'exam_question_group_id' => 'nullable|exists:exam_question_groups,id',
        'exam_section_id' => 'nullable|exists:exam_sections,id',
        // ... other rules
    ];
}

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        if (!$this->exam_question_group_id && !$this->exam_section_id) {
            $validator->errors()->add('exam_section_id', 
                'Either exam_question_group_id or exam_section_id must be provided.');
        }
        
        if ($this->exam_question_group_id && $this->exam_section_id) {
            $validator->errors()->add('exam_section_id', 
                'Cannot have both exam_question_group_id and exam_section_id.');
        }
    });
}
```

## Controllers cần cập nhật

Các controller sau cần được cập nhật để hỗ trợ cấu trúc mới:
1. `ExamQuestionController` - Logic tạo/sửa questions
2. `ExamSectionController` - Hiển thị questions theo skill type
3. `ExamSkillController` - Quiz preset logic
4. API endpoints liên quan đến exam structure

## Frontend/View cần cập nhật

1. Form tạo/sửa questions: Phải kiểm tra skill type và hiển thị field phù hợp
2. Quiz display: Render questions dựa trên skill type
3. Navigation/Tree view: Hiển thị đúng hierarchy theo skill type
