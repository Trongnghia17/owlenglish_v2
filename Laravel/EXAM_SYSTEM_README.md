# 📚 HỆ THỐNG QUẢN LÝ ĐỀ THI IELTS & TOEIC

## 🎯 TỔNG QUAN

Hệ thống được thiết kế để quản lý các bài thi tiếng Anh (IELTS, TOEIC, Online) với cấu trúc phân cấp rõ ràng và linh hoạt.

## 📊 CẤU TRÚC DATABASE

```
exams (Kỳ thi)
 └── exam_tests (Bộ đề: Test 1, Test 2, Test 3)
      └── exam_skills (Kỹ năng: Reading, Writing, Speaking, Listening)
           └── exam_sections (Phần: Part 1, Part 2, Part 3, Part 4)
                └── exam_question_groups (Nhóm câu hỏi)
                     └── exam_questions (Câu hỏi cụ thể)
```

---

## 📋 CHI TIẾT CÁC BẢNG

### 1️⃣ **EXAMS** - Kỳ thi

Quản lý các loại kỳ thi (IELTS, TOEIC, Online Test)

**Cột:**
- `id`: ID kỳ thi
- `name`: Tên kỳ thi (VD: "IELTS Academic", "TOEIC Listening & Reading")
- `type`: Loại (online, ielts, toeic)
- `description`: Mô tả về kỳ thi
- `image`: Ảnh đại diện
- `is_active`: Trạng thái hoạt động
- `deleted_at`: Soft delete

**Ví dụ:**
```
ID: 1
Name: IELTS Academic
Type: ielts
Description: Bài thi IELTS Academic dành cho mục đích học tập
```

---

### 2️⃣ **EXAM_TESTS** - Bộ đề thi

Mỗi exam có nhiều test (Test 1, Test 2, Mock Test...)

**Cột:**
- `id`: ID test
- `exam_id`: Liên kết với exam
- `name`: Tên test (VD: "Test 1", "Mock Test Full")
- `description`: Mô tả
- `image`: Ảnh đại diện
- `order`: Thứ tự hiển thị
- `is_active`: Trạng thái

**Ví dụ:**
```
Exam: IELTS Academic
  ├── Test 1
  ├── Test 2
  ├── Test 3
  └── Mock Test
```

---

### 3️⃣ **EXAM_SKILLS** - Kỹ năng

Mỗi test có 4 kỹ năng (Reading, Writing, Speaking, Listening)

**Cột:**
- `id`: ID skill
- `exam_test_id`: Liên kết với test
- `skill_type`: Loại kỹ năng (reading, writing, speaking, listening)
- `name`: Tên hiển thị
- `description`: Mô tả
- `time_limit`: Giới hạn thời gian (phút)
- `order`: Thứ tự
- `is_active`: Trạng thái

**Ví dụ:**
```
Test 1
  ├── Reading (60 phút)
  ├── Writing (60 phút)
  ├── Speaking (15 phút)
  └── Listening (40 phút)
```

---

### 4️⃣ **EXAM_SECTIONS** - Phần của kỹ năng

Mỗi skill có nhiều sections (Part 1, 2, 3...)

**Cột:**
- `id`: ID section
- `exam_skill_id`: Liên kết với skill
- `title`: Tiêu đề (VD: "Part 1: Matching Headings")
- `content`: Nội dung chung (đoạn văn Reading, mô tả Listening)
- `feedback`: Hướng dẫn/phản hồi
- `content_format`: Định dạng (text, audio, video, image)
- `audio_file`: File audio (cho Listening)
- `video_file`: File video (nếu có)
- `metadata`: Thông tin bổ sung (JSON)
- `order`: Thứ tự
- `is_active`: Trạng thái

**Đặc điểm theo kỹ năng:**

#### 📖 **READING**
```
Reading
  ├── Part 1: Matching Headings
  │   └── content: Đoạn văn dài về chủ đề X
  ├── Part 2: True/False/Not Given
  │   └── content: Đoạn văn về chủ đề Y
  └── Part 3: Multiple Choice
      └── content: Đoạn văn phức tạp
```

#### 🎧 **LISTENING**
```
Listening
  ├── Part 1: Form Completion
  │   ├── content: Mô tả bài nghe
  │   └── audio_file: listening_part1.mp3
  ├── Part 2: Multiple Choice
  │   └── audio_file: listening_part2.mp3
  └── Part 3: Matching
      └── audio_file: listening_part3.mp3
```

#### ✍️ **WRITING**
```
Writing
  └── Part 1: Essay Task
      └── content: Đề bài viết luận
```

#### 🗣️ **SPEAKING**
```
Speaking
  └── Part 1: Introduction
      └── content: Câu hỏi giới thiệu
```

---

### 5️⃣ **EXAM_QUESTION_GROUPS** - Nhóm câu hỏi

Mỗi section có nhiều question groups với các kiểu câu hỏi khác nhau

**Cột:**
- `id`: ID question group
- `exam_section_id`: Liên kết với section
- `content`: Nội dung nhóm (hình ảnh, bảng biểu chung)
- `question_type`: Loại câu hỏi
- `answer_layout`: Bố cục trả lời
- `instructions`: Hướng dẫn làm bài
- `options`: Cấu hình đặc biệt (JSON)
- `order`: Thứ tự
- `is_active`: Trạng thái

**Các loại Question Type:**
1. `multiple_choice` - Trắc nghiệm
2. `yes_no_not_given` - Yes/No/Not Given (IELTS Reading)
3. `true_false_not_given` - True/False/Not Given (IELTS Reading)
4. `short_text` - Điền từ ngắn
5. `fill_in_blank` - Điền vào chỗ trống
6. `matching` - Nối
7. `table_selection` - Chọn trong bảng
8. `essay` - Viết luận (Writing)
9. `speaking` - Nói (Speaking)

**Các loại Answer Layout:**
1. `inline` - Trả lời đầu vào bên trong nội dung
2. `side_by_side` - Chia nội dung và câu hỏi cạnh nhau
3. `drag_drop` - Cho phép kéo thả câu trả lời
4. `standard` - Hiển thị tiêu chuẩn

**Ví dụ:**
```json
{
  "question_type": "matching",
  "answer_layout": "drag_drop",
  "options": {
    "choices": ["A. Option 1", "B. Option 2", "C. Option 3"]
  }
}
```

---

### 6️⃣ **EXAM_QUESTIONS** - Câu hỏi cụ thể

Mỗi question group có nhiều questions

**Cột:**
- `id`: ID câu hỏi
- `exam_question_group_id`: Liên kết với question group
- `content`: Nội dung câu hỏi
- `answer_content`: Nội dung đáp án
- `is_correct`: Đáp án đúng (cho multiple choice)
- `point`: Điểm số
- `feedback`: Phản hồi/giải thích
- `hint`: Gợi ý
- `image`: Hình ảnh
- `audio_file`: File audio
- `metadata`: Thông tin bổ sung (JSON)
- `order`: Thứ tự
- `is_active`: Trạng thái

**Ví dụ Multiple Choice:**
```
Question Group (Multiple Choice)
  ├── Question 1: "What is the main idea?"
  │   ├── Option A (is_correct: false)
  │   ├── Option B (is_correct: true) ✓
  │   ├── Option C (is_correct: false)
  │   └── Option D (is_correct: false)
  └── Question 2: "According to the passage..."
      └── (4 options)
```

**Ví dụ Fill in Blank:**
```
Question Group (Fill in Blank)
  ├── Question 1: "The capital of France is _____"
  │   └── answer_content: "Paris"
  └── Question 2: "Water freezes at _____ degrees"
      └── answer_content: "0"
```

---

## 🔗 RELATIONSHIPS (Quan hệ giữa các bảng)

```php
Exam (1) ──→ (N) ExamTest
ExamTest (1) ──→ (N) ExamSkill
ExamSkill (1) ──→ (N) ExamSection
ExamSection (1) ──→ (N) ExamQuestionGroup
ExamQuestionGroup (1) ──→ (N) ExamQuestion
```

---

## 💡 VÍ DỤ CẤU TRÚC HOÀN CHỈNH

```
📚 IELTS Academic
  ├── 📄 Test 1
  │    ├── 📖 Reading (60 phút)
  │    │    ├── Part 1: Passage về "Climate Change"
  │    │    │    ├── Question Group 1: Matching Headings (inline)
  │    │    │    │    ├── Question 1: Match paragraph A
  │    │    │    │    ├── Question 2: Match paragraph B
  │    │    │    │    └── Question 3: Match paragraph C
  │    │    │    └── Question Group 2: True/False/Not Given (standard)
  │    │    │         ├── Question 4: Statement about CO2
  │    │    │         └── Question 5: Statement about temperature
  │    │    ├── Part 2: Passage về "Technology"
  │    │    └── Part 3: Passage về "Education"
  │    │
  │    ├── 🎧 Listening (40 phút)
  │    │    ├── Part 1: Form Completion (audio: part1.mp3)
  │    │    │    └── Question Group: Fill in blank (inline)
  │    │    │         ├── Question 1: Name: _____
  │    │    │         ├── Question 2: Phone: _____
  │    │    │         └── Question 3: Address: _____
  │    │    ├── Part 2: Multiple Choice (audio: part2.mp3)
  │    │    ├── Part 3: Matching (audio: part3.mp3)
  │    │    └── Part 4: Sentence Completion (audio: part4.mp3)
  │    │
  │    ├── ✍️ Writing (60 phút)
  │    │    ├── Part 1: Essay Task 1
  │    │    │    └── Question Group: Essay
  │    │    │         └── Question: "Describe the chart..."
  │    │    └── Part 2: Essay Task 2 (mặc định chỉ 1 part)
  │    │
  │    └── 🗣️ Speaking (15 phút)
  │         └── Part 1: Introduction (mặc định chỉ 1 part)
  │              └── Question Group: Speaking
  │                   ├── Question 1: "Tell me about your hometown"
  │                   ├── Question 2: "What do you do?"
  │                   └── Question 3: "Do you like sports?"
  │
  ├── 📄 Test 2
  └── 📄 Test 3
```

---

## 🚀 CÁCH SỬ DỤNG MODELS

### Tạo một bài thi mới

```php
use App\Models\Exam;
use App\Models\ExamTest;
use App\Models\ExamSkill;
use App\Models\ExamSection;
use App\Models\ExamQuestionGroup;
use App\Models\ExamQuestion;

// 1. Tạo Exam
$exam = Exam::create([
    'name' => 'IELTS Academic',
    'type' => 'ielts',
    'description' => 'Bài thi IELTS Academic chính thức',
    'is_active' => true
]);

// 2. Tạo Test
$test = $exam->tests()->create([
    'name' => 'Test 1',
    'description' => 'Đề thi mẫu số 1',
    'order' => 1
]);

// 3. Tạo Reading Skill
$reading = $test->skills()->create([
    'skill_type' => 'reading',
    'name' => 'Reading',
    'time_limit' => 60,
    'order' => 1
]);

// 4. Tạo Section cho Reading
$section = $reading->sections()->create([
    'title' => 'Part 1: Climate Change',
    'content' => 'Đoạn văn dài về biến đổi khí hậu...',
    'content_format' => 'text',
    'order' => 1
]);

// 5. Tạo Question Group
$questionGroup = $section->questionGroups()->create([
    'question_type' => 'multiple_choice',
    'answer_layout' => 'standard',
    'instructions' => 'Choose the correct answer A, B, C or D',
    'order' => 1
]);

// 6. Tạo Questions
$questionGroup->questions()->create([
    'content' => 'What is the main idea of the passage?',
    'answer_content' => 'A. Climate change is dangerous',
    'is_correct' => true,
    'point' => 1.0,
    'order' => 1
]);

$questionGroup->questions()->create([
    'content' => 'What is the main idea of the passage?',
    'answer_content' => 'B. Climate is stable',
    'is_correct' => false,
    'point' => 1.0,
    'order' => 2
]);
```

### Truy vấn dữ liệu

```php
// Lấy tất cả tests của một exam
$exam = Exam::with('tests')->find(1);

// Lấy tất cả skills của một test
$test = ExamTest::with('skills')->find(1);

// Lấy toàn bộ cấu trúc của một exam (eager loading)
$exam = Exam::with([
    'tests.skills.sections.questionGroups.questions'
])->find(1);

// Lấy chỉ Reading skill
$readingSkill = ExamSkill::where('skill_type', 'reading')
    ->with('sections.questionGroups.questions')
    ->first();

// Đếm tổng số câu hỏi trong một test
$totalQuestions = ExamTest::find(1)
    ->skills()
    ->with('sections.questionGroups.questions')
    ->get()
    ->pluck('sections')
    ->flatten()
    ->pluck('questionGroups')
    ->flatten()
    ->pluck('questions')
    ->flatten()
    ->count();

// Tính tổng điểm của một exam skill
$totalPoints = $examSkill->sections()
    ->with('questionGroups.questions')
    ->get()
    ->pluck('questionGroups')
    ->flatten()
    ->sum(function($group) {
        return $group->getTotalPoints();
    });
```

---

## 🎨 ĐẶC ĐIỂM THEO TỪNG KỸ NĂNG

### 📖 READING
- **Sections**: Part 1, Part 2, Part 3
- **Content Format**: Text
- **Question Types**: 
  - Multiple Choice
  - Yes/No/Not Given
  - True/False/Not Given
  - Matching Headings
  - Fill in Blank

### 🎧 LISTENING
- **Sections**: Part 1, Part 2, Part 3, Part 4
- **Content Format**: Audio
- **Audio File**: Required
- **Question Types**:
  - Multiple Choice
  - Form Completion
  - Matching
  - Sentence Completion

### ✍️ WRITING
- **Sections**: Mặc định 1 (hoặc 2 tasks)
- **Content Format**: Text
- **Question Types**: Essay only
- **Answer Layout**: Standard

### 🗣️ SPEAKING
- **Sections**: Mặc định 1 (hoặc chia Part 1, 2, 3)
- **Content Format**: Text
- **Question Types**: Speaking only
- **Answer Layout**: Standard

---

## 🔧 CHẠY MIGRATION

```bash
# Chạy migration
php artisan migrate

# Rollback
php artisan migrate:rollback

# Refresh (xóa và tạo lại)
php artisan migrate:refresh
```

---

## 📌 LƯU Ý

1. **Soft Delete**: Tất cả các bảng đều sử dụng soft delete để có thể khôi phục
2. **Order Field**: Mỗi bảng có trường `order` để sắp xếp thứ tự hiển thị
3. **Active Status**: Trường `is_active` để kiểm soát hiển thị
4. **Metadata/Options**: Sử dụng JSON để lưu cấu hình linh hoạt
5. **Foreign Keys**: Cascade delete - khi xóa parent thì xóa tất cả children

---

## 🎯 TÍNH NĂNG MỞ RỘNG

Có thể thêm các bảng sau để mở rộng hệ thống:

1. **exam_attempts** - Lưu lịch sử làm bài của học viên
2. **exam_answers** - Lưu câu trả lời của học viên
3. **exam_results** - Lưu kết quả thi
4. **exam_categories** - Phân loại đề thi
5. **exam_tags** - Gắn thẻ cho đề thi

---

Hệ thống này đã sẵn sàng để sử dụng! 🚀
