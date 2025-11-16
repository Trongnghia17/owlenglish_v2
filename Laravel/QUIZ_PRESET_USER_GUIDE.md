# Hướng dẫn sử dụng Quiz Preset mới

## 📋 Tổng quan

Từ bây giờ, Quiz Preset được tổ chức theo 2 luồng khác nhau tùy theo loại skill:

### 🎯 Speaking & Writing
```
Skill → Section → Question (trực tiếp)
```
- **Không có Group Question**
- Questions được tạo trực tiếp trong Section
- Phù hợp cho các câu hỏi tự luận, nói

### 🎧📖 Listening & Reading  
```
Skill → Section → Question Group → Question
```
- **Có Group Question** (như cũ)
- Questions được nhóm theo Question Group
- Phù hợp cho các câu hỏi trắc nghiệm, nhóm câu hỏi

---

## 🆕 Thay đổi giao diện

### 1. Navigation (Thanh điều hướng bên trái)

**Speaking/Writing:**
```
📄 Section 1
  ❓ Question 1
  ❓ Question 2
📄 Section 2
  ❓ Question 1
```

**Listening/Reading:**
```
📄 Section 1
  📊 Group 1
    ❓ Question 1
    ❓ Question 2
  📊 Group 2
    ❓ Question 1
```

### 2. Content Builder (Vùng chỉnh sửa chính)

**Speaking/Writing:**
- Sau phần "Answer Inputs Inside Content", sẽ thấy:
  - **Questions** (thay vì Question Groups)
  - Nút "Add Question" màu xanh lá
  - Các trường:
    - Question Content
    - Points
    - Sample Answer / Marking Criteria
    - Feedback
    - Hint

**Listening/Reading:**
- Giữ nguyên như cũ:
  - Question Groups
  - Nút "Add Question Group"
  - Các trường như cũ

---

## ✨ Cách sử dụng

### Tạo Quiz Speaking/Writing

1. **Tạo Section:**
   - Click "Add Section"
   - Điền Section Title, Content
   - Upload Image nếu cần

2. **Thêm Questions:**
   - Click "Add Question" (màu xanh lá)
   - Điền:
     - **Question Content**: Nội dung câu hỏi
     - **Points**: Điểm số (mặc định 1)
     - **Sample Answer**: Câu trả lời mẫu hoặc tiêu chí chấm điểm
     - **Feedback**: Nhận xét cho học sinh
     - **Hint**: Gợi ý (tùy chọn)

3. **Lưu:**
   - Click nút "Update Skill" ở cuối trang

### Tạo Quiz Listening/Reading

- **Giữ nguyên như cũ**
- Tạo Section → Question Group → Question

---

## 🔄 Migration Data

### Database đã được cập nhật:

✅ Bảng `exam_questions` đã thêm:
- `exam_section_id` (nullable) - cho Speaking/Writing
- `exam_question_group_id` (nullable) - cho Listening/Reading

### Quy tắc:
- **Speaking/Writing**: `exam_section_id` có giá trị, `exam_question_group_id` = NULL
- **Listening/Reading**: `exam_question_group_id` có giá trị, `exam_section_id` = NULL

---

## ⚠️ Lưu ý quan trọng

1. **Không thể chuyển đổi skill type sau khi đã có data:**
   - Nếu đã tạo questions cho Speaking, không nên đổi sang Reading
   - Phải xóa hết questions cũ trước khi đổi

2. **Validation:**
   - Mỗi question phải thuộc về Section (Speaking/Writing) HOẶC Group (Listening/Reading)
   - Không thể có cả hai cùng lúc

3. **UI sẽ tự động thay đổi:**
   - Khi chọn skill type là Speaking/Writing → hiện Direct Questions
   - Khi chọn skill type là Listening/Reading → hiện Question Groups

---

## 🐛 Troubleshooting

**Câu hỏi: Tôi không thấy nút "Add Question" ở Speaking/Writing?**
- Kiểm tra xem skill type đã đúng chưa
- Refresh lại trang

**Câu hỏi: Questions cũ của tôi biến đâu?**
- Questions cũ vẫn còn, nhưng hiển thị ở vị trí mới
- Listening/Reading: Trong Question Groups (như cũ)
- Speaking/Writing: Trực tiếp trong Section

**Câu hỏi: Làm sao để chuyển đổi từ cấu trúc cũ sang mới?**
- Hệ thống tự động nhận diện
- Không cần làm gì thêm

---

## 📞 Hỗ trợ

Nếu gặp vấn đề, vui lòng liên hệ team development.

---

**Ngày cập nhật**: 16/11/2025  
**Version**: 2.0.0
