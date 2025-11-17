# Trang Thư viện Đề thi Online

## Mô tả
Trang này hiển thị danh sách các **Skills** từ các **Exams có type = "online"** với UI giống như thiết kế được cung cấp.

## Cấu trúc dữ liệu
```
Exam (type: online) 
  └── ExamTest 
       └── ExamSkill (Reading/Writing/Listening/Speaking)
            └── ExamSection
                 └── ExamQuestionGroup
                      └── ExamQuestion
```

## Các tính năng đã triển khai

### 1. **UI Components**
- ✅ Sidebar với:
  - Danh sách tasks (Tất cả, Writing, Reading, Listening, Speaking)
  - Recent Actual Tests
  - Filters (Skill Type, Độ khó)
- ✅ Main content với:
  - Header với tiêu đề và sort options
  - Search bar
  - Grid hiển thị skill cards

### 2. **Tích hợp Backend API**
- ✅ Kết nối với API endpoint: `/public/skills`
- ✅ Filter tự động theo exam type = 'online'
- ✅ Xử lý hình ảnh từ backend (Laravel storage)
- ✅ Fallback placeholder theo skill type với màu khác nhau

### 3. **Chức năng**
- ✅ Tìm kiếm theo tên skill
- ✅ Sắp xếp (Mới nhất, Cũ nhất, Tên A-Z)
- ✅ Filter theo skill type (Reading, Writing, Listening, Speaking)
- ✅ Filter theo task trong sidebar
- ✅ Click vào skill card để navigate đến trang chi tiết
- ✅ Hiển thị thời gian làm bài (time_limit)

## Cấu trúc Files

```
React/src/features/exams/
├── api/
│   └── exams.api.js          # API calls cho exams
├── pages/
│   ├── PagesTest.jsx         # Component chính
│   └── PagesTest.css         # Styles
```

## API Endpoints được sử dụng

### GET `/public/skills`
Lấy danh sách skills với filter theo exam type

**Parameters:**
- `exam_type`: string - Filter theo loại exam (ielts, toeic, **online**)
- `skill_type`: string - Filter theo loại skill (reading, writing, listening, speaking)
- `is_active`: boolean - Chỉ lấy skills active (mặc định: true)
- `search`: string - Tìm kiếm theo tên

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "exam_test_id": 1,
        "skill_type": "reading",
        "name": "TOEIC Reading",
        "description": "...",
        "time_limit": 75,
        "is_active": true,
        "exam_test": {
          "id": 1,
          "exam_id": 1,
          "name": "Test 1",
          "image": "tests/test1.jpg",
          "exam": {
            "id": 1,
            "name": "OWL Online Test",
            "type": "online",
            "image": "exams/exam1.jpg"
          }
        }
      }
    ]
  }
}
```

## Cách chạy

1. **Đảm bảo backend đang chạy:**
```bash
cd Laravel
php artisan serve
```

2. **Đảm bảo frontend đang chạy:**
```bash
cd React
npm run dev
```

3. **Truy cập trang:**
```
http://localhost:5173/de-thi-online
```

## Cấu hình

File `.env` trong React:
```env
VITE_API_BASE_URL=http://127.0.0.1:8000
```

## Responsive Design
- ✅ Desktop (> 1024px)
- ✅ Tablet (768px - 1024px)
- ✅ Mobile (< 768px)

## To-Do / Cải tiến
- [ ] Tạo trang Skill Detail khi click vào card
- [ ] Implement pagination
- [ ] Add loading skeleton
- [ ] Show skill progress/completion status
- [ ] Add favorite/bookmark feature
- [ ] Display number of questions per skill

## Thay đổi quan trọng
- ✅ **Chuyển từ hiển thị Tests sang Skills**
- ✅ **Chỉ lấy skills từ exams có type = 'online'**
- ✅ **Thêm API endpoint mới `/public/skills` trong backend**
- ✅ **Filter theo skill_type thay vì exam_type**
- ✅ **Placeholder có màu sắc khác nhau theo skill type:**
  - Reading: Blue (#3b82f6)
  - Writing: Green (#10b981)
  - Listening: Orange (#f59e0b)
  - Speaking: Red (#ef4444)

## Notes
- Hình ảnh từ backend được load từ Laravel storage
- Nếu không có hình ảnh, sẽ hiển thị placeholder
- Route được cấu hình trong `/app/routes.jsx`
