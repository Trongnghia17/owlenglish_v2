# 🚀 API DOCUMENTATION - EXAM MANAGEMENT SYSTEM

## 📋 TỔNG QUAN

API RESTful để quản lý hệ thống đề thi IELTS, TOEIC và Online Test.

**Base URL**: `http://your-domain.com/api`

**Authentication**: Sử dụng Laravel Sanctum (Bearer Token)

---

## 🔐 AUTHENTICATION

### Public Routes (Không cần auth)
- Lấy danh sách exams public
- Xem chi tiết đề thi để làm bài

### Protected Routes (Cần auth)
- Quản lý exams, tests, skills, sections, questions
- Chỉ dành cho Admin/Giáo viên

**Header cho protected routes:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

---

## 📚 EXAMS API

### 1. Lấy danh sách exams
```
GET /api/exams
GET /api/public/exams (public)
```

**Query Parameters:**
- `type` (optional): `online`, `ielts`, `toeic`
- `is_active` (optional): `true`, `false`
- `search` (optional): Tìm kiếm theo tên
- `with_tests` (optional): `true` để include tests

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "IELTS Academic",
        "type": "ielts",
        "description": "Bài thi IELTS Academic...",
        "image": "exams/ielts.jpg",
        "is_active": true,
        "created_at": "2025-11-11T00:00:00.000000Z"
      }
    ],
    "total": 10
  }
}
```

### 2. Tạo exam mới (Admin)
```
POST /api/exams
```

**Body (multipart/form-data):**
```json
{
  "name": "IELTS Academic",
  "type": "ielts",
  "description": "Mô tả...",
  "image": [file],
  "is_active": true
}
```

### 3. Xem chi tiết exam
```
GET /api/exams/{id}
GET /api/public/exams/{id} (public)
```

**Query Parameters:**
- `with_tests`: `true` để load toàn bộ cấu trúc

### 4. Cập nhật exam
```
PUT /api/exams/{id}
PATCH /api/exams/{id}
```

### 5. Xóa exam
```
DELETE /api/exams/{id}
```

### 6. Restore exam đã xóa
```
POST /api/exams/{id}/restore
```

### 7. Toggle active status
```
POST /api/exams/{id}/toggle-active
```

---

## 📄 EXAM TESTS API

### 1. Lấy tests của exam
```
GET /api/exams/{examId}/tests
```

**Query Parameters:**
- `is_active`: `true`, `false`
- `with_skills`: `true`

### 2. Tạo test mới
```
POST /api/exams/{examId}/tests
```

**Body:**
```json
{
  "name": "Test 1",
  "description": "Đề thi mẫu số 1",
  "image": [file],
  "order": 1,
  "is_active": true
}
```

### 3. Xem chi tiết test
```
GET /api/tests/{id}
GET /api/public/tests/{id} (public)
```

**Query Parameters:**
- `with_skills`: `true`
- `with_full_structure`: `true` (load tất cả skills, sections, questions)

### 4. Cập nhật test
```
PUT /api/tests/{id}
PATCH /api/tests/{id}
```

### 5. Xóa test
```
DELETE /api/tests/{id}
```

### 6. Duplicate test (Copy toàn bộ cấu trúc)
```
POST /api/tests/{id}/duplicate
```

### 7. Sắp xếp lại thứ tự tests
```
POST /api/exams/{examId}/tests/reorder
```

**Body:**
```json
{
  "tests": [
    { "id": 1, "order": 1 },
    { "id": 2, "order": 2 },
    { "id": 3, "order": 3 }
  ]
}
```

---

## 🎯 EXAM SKILLS API

### 1. Lấy skills của test
```
GET /api/tests/{testId}/skills
```

**Query Parameters:**
- `skill_type`: `reading`, `writing`, `speaking`, `listening`
- `with_sections`: `true`

### 2. Tạo skill mới
```
POST /api/tests/{testId}/skills
```

**Body:**
```json
{
  "skill_type": "reading",
  "name": "Reading",
  "description": "Academic Reading Test",
  "time_limit": 60,
  "order": 1,
  "is_active": true
}
```

### 3. Xem chi tiết skill
```
GET /api/skills/{id}
```

### 4. Cập nhật skill
```
PUT /api/skills/{id}
```

### 5. Xóa skill
```
DELETE /api/skills/{id}
```

### 6. Sắp xếp skills
```
POST /api/tests/{testId}/skills/reorder
```

---

## 📖 EXAM SECTIONS API

### 1. Lấy sections của skill
```
GET /api/skills/{skillId}/sections
```

**Query Parameters:**
- `with_questions`: `true`

### 2. Tạo section mới
```
POST /api/skills/{skillId}/sections
```

**Body (multipart/form-data):**
```json
{
  "title": "Part 1: Climate Change",
  "content": "Đoạn văn dài...",
  "feedback": "Hướng dẫn...",
  "content_format": "text",
  "audio_file": [file],
  "video_file": [file],
  "metadata": {},
  "order": 1,
  "is_active": true
}
```

**Content Format Options:**
- `text`: Văn bản (Reading)
- `audio`: File âm thanh (Listening)
- `video`: Video
- `image`: Hình ảnh

### 3. Xem chi tiết section
```
GET /api/sections/{id}
```

### 4. Cập nhật section
```
PUT /api/sections/{id}
```

### 5. Xóa section
```
DELETE /api/sections/{id}
```

### 6. Sắp xếp sections
```
POST /api/skills/{skillId}/sections/reorder
```

---

## ❓ QUESTION GROUPS API

### 1. Lấy question groups của section
```
GET /api/sections/{sectionId}/question-groups
```

**Query Parameters:**
- `with_questions`: `true`

### 2. Tạo question group
```
POST /api/sections/{sectionId}/question-groups
```

**Body:**
```json
{
  "content": "Nội dung nhóm câu hỏi (bảng biểu, hình ảnh chung...)",
  "question_type": "multiple_choice",
  "answer_layout": "standard",
  "instructions": "Choose the correct answer A, B, C or D",
  "options": {
    "choices": ["A", "B", "C", "D"]
  },
  "order": 1,
  "is_active": true
}
```

**Question Types:**
- `multiple_choice`: Trắc nghiệm
- `yes_no_not_given`: Yes/No/Not Given
- `true_false_not_given`: True/False/Not Given
- `short_text`: Điền từ ngắn
- `fill_in_blank`: Điền vào chỗ trống
- `matching`: Nối
- `table_selection`: Chọn trong bảng
- `essay`: Viết luận
- `speaking`: Nói

**Answer Layout:**
- `standard`: Hiển thị tiêu chuẩn
- `inline`: Trả lời trong nội dung
- `side_by_side`: Nội dung và câu hỏi cạnh nhau
- `drag_drop`: Kéo thả

### 3. Xem question group
```
GET /api/question-groups/{id}
```

### 4. Cập nhật question group
```
PUT /api/question-groups/{id}
```

### 5. Xóa question group
```
DELETE /api/question-groups/{id}
```

---

## 📝 QUESTIONS API

### 1. Lấy questions của group
```
GET /api/question-groups/{groupId}/questions
```

### 2. Tạo question
```
POST /api/question-groups/{groupId}/questions
```

**Body (multipart/form-data):**
```json
{
  "content": "What is the main idea?",
  "answer_content": "A. Option A",
  "is_correct": true,
  "point": 1.0,
  "feedback": "Giải thích...",
  "hint": "Gợi ý...",
  "image": [file],
  "audio_file": [file],
  "metadata": {},
  "order": 1,
  "is_active": true
}
```

### 3. Tạo nhiều questions cùng lúc
```
POST /api/question-groups/{groupId}/questions/bulk
```

**Body:**
```json
{
  "questions": [
    {
      "content": "Question 1",
      "answer_content": "A. Answer 1",
      "is_correct": false,
      "point": 1.0,
      "order": 1
    },
    {
      "content": "Question 1",
      "answer_content": "B. Answer 2",
      "is_correct": true,
      "point": 1.0,
      "order": 2
    }
  ]
}
```

### 4. Xem question
```
GET /api/questions/{id}
```

### 5. Cập nhật question
```
PUT /api/questions/{id}
```

### 6. Xóa question
```
DELETE /api/questions/{id}
```

### 7. Sắp xếp questions
```
POST /api/question-groups/{groupId}/questions/reorder
```

---

## 📊 VÍ DỤ WORKFLOW

### Tạo một bài thi IELTS hoàn chỉnh

```javascript
// 1. Tạo Exam
const exam = await fetch('/api/exams', {
  method: 'POST',
  headers: { 
    'Authorization': 'Bearer {token}',
    'Content-Type': 'application/json' 
  },
  body: JSON.stringify({
    name: 'IELTS Academic',
    type: 'ielts',
    description: 'IELTS Academic Test',
    is_active: true
  })
});

// 2. Tạo Test
const test = await fetch(`/api/exams/${exam.id}/tests`, {
  method: 'POST',
  body: JSON.stringify({
    name: 'Test 1',
    order: 1
  })
});

// 3. Tạo Reading Skill
const reading = await fetch(`/api/tests/${test.id}/skills`, {
  method: 'POST',
  body: JSON.stringify({
    skill_type: 'reading',
    name: 'Reading',
    time_limit: 60,
    order: 1
  })
});

// 4. Tạo Section
const section = await fetch(`/api/skills/${reading.id}/sections`, {
  method: 'POST',
  body: JSON.stringify({
    title: 'Passage 1',
    content: 'Long passage text...',
    content_format: 'text',
    order: 1
  })
});

// 5. Tạo Question Group
const questionGroup = await fetch(`/api/sections/${section.id}/question-groups`, {
  method: 'POST',
  body: JSON.stringify({
    question_type: 'multiple_choice',
    answer_layout: 'standard',
    instructions: 'Choose A, B, C or D'
  })
});

// 6. Tạo Questions (bulk)
await fetch(`/api/question-groups/${questionGroup.id}/questions/bulk`, {
  method: 'POST',
  body: JSON.stringify({
    questions: [
      { content: 'Question 1', answer_content: 'A. Ans1', is_correct: false, point: 1, order: 1 },
      { content: 'Question 1', answer_content: 'B. Ans2', is_correct: true, point: 1, order: 2 },
      { content: 'Question 1', answer_content: 'C. Ans3', is_correct: false, point: 1, order: 3 },
      { content: 'Question 1', answer_content: 'D. Ans4', is_correct: false, point: 1, order: 4 }
    ]
  })
});
```

---

## 🎨 RESPONSE FORMAT

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

---

## 🔧 CHẠY MIGRATION & SEEDER

```bash
# Chạy migration
php artisan migrate

# Chạy seeder để tạo dữ liệu mẫu
php artisan db:seed --class=ExamSeeder

# Hoặc refresh toàn bộ database
php artisan migrate:fresh --seed
```

---

## 📁 STORAGE

Files được lưu trong `storage/app/public/`:
- `exams/` - Ảnh exam
- `exam-tests/` - Ảnh test
- `exam-audio/` - File audio (listening)
- `exam-video/` - File video
- `exam-questions/` - Ảnh câu hỏi
- `exam-questions-audio/` - Audio câu hỏi

**Link symbolic:**
```bash
php artisan storage:link
```

Truy cập file: `http://your-domain.com/storage/exams/file.jpg`

---

## 🚀 TIPS

1. **Eager Loading**: Sử dụng `with_*` parameters để giảm số lượng query
2. **Soft Delete**: Tất cả đều dùng soft delete, có thể restore
3. **Order**: Sử dụng `/reorder` endpoints để sắp xếp
4. **Duplicate**: Có thể copy toàn bộ test với `/duplicate`
5. **Bulk Create**: Dùng `/bulk` để tạo nhiều questions cùng lúc

---

Happy Coding! 🎓
