# User Notes Feature - Tính năng Ghi chú

## 📝 Tổng quan
Tính năng cho phép người dùng tạo, xem, sửa, xóa ghi chú khi làm bài test. Dữ liệu được lưu vào **database** thông qua API.

## 🗄️ Database Schema

### Bảng: `user_notes`
```sql
- id (bigint, primary key)
- user_id (bigint, foreign key -> users)
- test_type (string) - 'exam', 'skill', 'section', 'test'
- test_id (bigint) - ID của bài test
- title (string, nullable) - Tiêu đề ghi chú
- content (text) - Nội dung ghi chú
- selected_text (text, nullable) - Text được bôi đen khi tạo note
- created_at (timestamp)
- updated_at (timestamp)
- Index: (user_id, test_type, test_id)
```

## 🔌 API Endpoints

### Base URL: `/api/user-notes`

**⚠️ Tất cả endpoints yêu cầu authentication (`auth:sanctum`)**

### 1. Lấy danh sách notes
```
GET /api/user-notes
Query params:
  - test_type: 'exam' | 'skill' | 'section' | 'test' (required)
  - test_id: number (required)

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "test_type": "exam",
      "test_id": 5,
      "title": "Grammar note",
      "content": "Remember this grammar rule...",
      "selected_text": "important phrase",
      "created_at": "2026-01-13T19:42:21.000000Z",
      "updated_at": "2026-01-13T19:42:21.000000Z"
    }
  ]
}
```

### 2. Tạo note mới
```
POST /api/user-notes
Body:
{
  "test_type": "exam",        // required
  "test_id": 5,               // required
  "title": "My note",         // optional
  "content": "Note content",  // required
  "selected_text": "..."      // optional
}

Response:
{
  "success": true,
  "message": "Note created successfully",
  "data": { /* note object */ }
}
```

### 3. Xem chi tiết note
```
GET /api/user-notes/{id}

Response:
{
  "success": true,
  "data": { /* note object */ }
}
```

### 4. Cập nhật note
```
PUT /api/user-notes/{id}
Body:
{
  "title": "Updated title",      // optional
  "content": "Updated content",  // optional
  "selected_text": "..."         // optional
}

Response:
{
  "success": true,
  "message": "Note updated successfully",
  "data": { /* updated note object */ }
}
```

### 5. Xóa note
```
DELETE /api/user-notes/{id}

Response:
{
  "success": true,
  "message": "Note deleted successfully"
}
```

## 🎨 Frontend Implementation

### API Service (`React/src/features/exams/api/notes.api.js`)
```javascript
import api from '@/lib/axios';

export const getNotes = (testType, testId) => 
  api.get('/api/user-notes', { params: { test_type: testType, test_id: testId } });

export const createNote = (noteData) => 
  api.post('/api/user-notes', noteData);

export const updateNote = (id, noteData) => 
  api.put(`/api/user-notes/${id}`, noteData);

export const deleteNote = (id) => 
  api.delete(`/api/user-notes/${id}`);
```

### Component Usage (`TestLayout.jsx`)
```javascript
import { getNotes, createNote, updateNote, deleteNote } from '../api/notes.api';

// Tự động load notes khi component mount
useEffect(() => {
  if (!testId) return;
  
  const loadNotes = async () => {
    setNotesLoading(true);
    try {
      const response = await getNotes(testType, testId);
      if (response.data.success) {
        setNotes(response.data.data);
      }
    } catch (error) {
      console.error('Error loading notes:', error);
    } finally {
      setNotesLoading(false);
    }
  };

  loadNotes();
}, [testId, testType]);
```

## 🚀 Cách sử dụng

### 1. Tạo note mới
- Bôi đen text trong bài test
- Nhấn nút "Note" xuất hiện
- Nhập nội dung và save

### 2. Xem danh sách notes
- Nhấn icon "📝" ở header
- Panel notes sẽ hiện bên phải

### 3. Sửa note
- Click vào note trong danh sách
- Sửa nội dung và save

### 4. Xóa note
- Click icon 🗑️ ở mỗi note

## 🔄 Migration

Chạy migration để tạo bảng:
```bash
php artisan migrate
```

## 🔒 Security

- ✅ Authentication required cho tất cả endpoints
- ✅ Users chỉ có thể xem/sửa/xóa notes của chính họ
- ✅ Validation cho tất cả input
- ✅ Foreign key constraint với cascade delete

## 📊 Data Flow

```
User selects text → Click "Note" button → Enter content → Save
                                                            ↓
Frontend (TestLayout) → API Service (notes.api.js) → Laravel API
                                                            ↓
                                        UserNoteController validates & saves
                                                            ↓
                                                Database (user_notes table)
                                                            ↓
                                                Return saved note
                                                            ↓
Frontend updates state ← ← ← ← ← ← ← ← ← ← ← ← ← ← ← ← ← ←
```

## 🎯 Features

✅ Lưu notes vào database  
✅ Authentication required  
✅ CRUD operations đầy đủ  
✅ Loading states  
✅ Error handling  
✅ Fallback to localStorage nếu API fails  
✅ Auto-load notes khi mở test  
✅ Real-time UI updates  

## 🐛 Error Handling

- API errors sẽ hiển thị alert cho user
- Nếu load notes thất bại, fallback sang localStorage
- Console log errors để debug

## 📝 Notes

- Notes được lưu theo `(test_type, test_id)` để phân biệt các bài test khác nhau
- Mỗi user chỉ thấy notes của chính họ
- Khi user bị xóa, notes cũng bị xóa theo (cascade)
