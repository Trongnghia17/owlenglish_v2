# Tính năng: Xem giải thích chi tiết kết quả bài thi

## 📋 Tổng quan

Tính năng này cho phép người dùng xem lại chi tiết kết quả bài thi với:
- **UI giống hệt khi làm bài** (passage bên trái, câu hỏi bên phải)
- Từng câu hỏi hiển thị đáp án của user vs đáp án đúng
- **Giải thích chi tiết (feedback)** hiển thị ngay bên dưới mỗi câu
- **Gợi ý (hint)** nếu có
- Tất cả input đều bị disable (chỉ xem, không sửa)
- Badge màu sắc: ✓ Đúng (xanh), ✗ Sai (đỏ), - Bỏ qua (vàng)

## 🏗️ Cấu trúc Database liên quan

```
Exam → ExamTest → ExamSkill → ExamSection → ExamQuestionGroup → ExamQuestion
```

### Các trường quan trọng:

**exam_questions:**
- `content`: Nội dung câu hỏi
- `answer_content`: Đáp án
- `feedback`: **Giải thích/phản hồi cho câu hỏi** ⭐
- `hint`: Gợi ý
- `image`: Hình ảnh đi kèm
- `audio_file`: File audio
- `metadata`: Thông tin bổ sung

**exam_question_groups:**
- `content`: Passage/đoạn văn cho nhóm câu hỏi

**exam_sections:**
- `content`: Nội dung passage chính
- `feedback`: Phản hồi cho section

## 🔧 Các thay đổi đã thực hiện

### 1. Backend (Laravel)

#### File: `app/Http/Controllers/Api/TestResultController.php`

**Cập nhật method `show($id)`:**
```php
public function show($id)
{
    // Lấy kết quả bài thi
    $result = TestResult::with(['skill', 'section', 'test', 'user'])
        ->where('user_id', auth()->id())
        ->findOrFail($id);

    // Làm giàu dữ liệu answers với feedback, hint, passage content
    $enrichedAnswers = collect($result->answers)->map(function ($answer) {
        $question = ExamQuestion::find($answer['question_id']);
        
        if (!$question) {
            return $answer;
        }

        // Lấy passage content từ section hoặc group
        $passageContent = null;
        if ($question->exam_section_id) {
            $section = $question->examSection;
            $passageContent = $section ? $section->content : null;
        } elseif ($question->exam_question_group_id) {
            $group = $question->questionGroup;
            if ($group) {
                $passageContent = $group->content;
                if (!$passageContent && $group->exam_section_id) {
                    $section = $group->examSection;
                    $passageContent = $section ? $section->content : null;
                }
            }
        }

        return array_merge($answer, [
            'feedback' => $question->feedback,
            'hint' => $question->hint,
            'question_content' => $question->content,
            'passage_content' => $passageContent,
            'image' => $question->image,
            'audio_file' => $question->audio_file,
        ]);
    })->toArray();

    $resultData = $result->toArray();
    $resultData['answers'] = $enrichedAnswers;

    return response()->json([
        'success' => true,
        'data' => $resultData,
    ]);
}
```

**Những gì được thêm:**
- ✅ `feedback`: Giải thích chi tiết cho câu hỏi
- ✅ `hint`: Gợi ý (nếu có)
- ✅ `question_content`: Nội dung câu hỏi
- ✅ `passage_content`: Nội dung passage (từ section hoặc group)
- ✅ `image`: Hình ảnh câu hỏi
- ✅ `audio_file`: File audio

### 2. Frontend (React)

#### File: `React/src/features/exams/pages/TestReview.jsx`

Component wrapper nhận `resultId`, fetch kết quả từ API, sau đó pass props vào `ReadingTest`:

```jsx
export default function TestReview() {
  const { resultId } = useParams();
  const [result, setResult] = useState(null);
  
  // Fetch result từ API
  useEffect(() => {
    const fetchResult = async () => {
      const response = await getTestResult(resultId);
      setResult(response.data.data);
    };
    fetchResult();
  }, [resultId]);

  // Pass vào ReadingTest với review mode
  return (
    <ReadingTest 
      reviewMode={true}
      reviewData={result}
      preloadedSkillId={result.exam_skill_id}
      preloadedSectionId={result.exam_section_id}
    />
  );
}
```

#### File: `React/src/features/exams/pages/ReadingTest.jsx`

**Cập nhật để support Review Mode:**

1. **Thêm props:**
```jsx
export default function ReadingTest({ 
  reviewMode = false, 
  reviewData = null,
  preloadedSkillId = null,
  preloadedSectionId = null
})
```

2. **Thêm FeedbackSection Component:**
```jsx
const FeedbackSection = ({ questionId, reviewAnswersMap }) => {
  const reviewData = reviewAnswersMap[questionId];
  if (!reviewData) return null;

  return (
    <div className="reading-test__review-section">
      {/* Answer comparison */}
      <div className="reading-test__answer-comparison">
        <span>Câu trả lời của bạn: {userAnswer}</span>
        <span>Đáp án đúng: {correctAnswer}</span>
      </div>

      {/* Feedback */}
      {feedback && (
        <div className="reading-test__feedback">
          <h4>📘 Giải thích</h4>
          <div dangerouslySetInnerHTML={{ __html: feedback }} />
        </div>
      )}

      {/* Hint */}
      {hint && (
        <div className="reading-test__hint">
          <h4>💡 Gợi ý</h4>
          <div dangerouslySetInnerHTML={{ __html: hint }} />
        </div>
      )}
    </div>
  );
};
```

3. **Process review data:**
```jsx
useEffect(() => {
  if (reviewMode && reviewData) {
    // Tạo map từ question_id -> review data
    const answerMap = {};
    reviewData.answers.forEach(answer => {
      answerMap[answer.question_id] = {
        userAnswer: answer.user_answer,
        correctAnswer: answer.correct_answer,
        isCorrect: answer.is_correct,
        feedback: answer.feedback,
        hint: answer.hint
      };
    });
    setReviewAnswersMap(answerMap);
    
    // Set user's answers để hiển thị trong input
    const userAnswers = {};
    reviewData.answers.forEach(answer => {
      userAnswers[answer.question_id] = answer.user_answer;
    });
    setAnswers(userAnswers);
  }
}, [reviewMode, reviewData]);
```

4. **Update render functions:**
```jsx
// Thêm FeedbackSection vào mỗi câu hỏi
return group.questions.map((question) => (
  <div key={question.id} className="reading-test__question-item">
    {/* Question content */}
    <div className="reading-test__question-row">...</div>
    
    {/* Options/Input (disabled in review mode) */}
    <input disabled={reviewMode} ... />
    
    {/* Feedback section (chỉ hiển thị khi reviewMode) */}
    {reviewMode && (
      <FeedbackSection 
        questionId={question.id} 
        reviewAnswersMap={reviewAnswersMap} 
      />
    )}
  </div>
));
```

5. **Disable inputs và hide timer:**
```jsx
// Disable tất cả inputs
<input disabled={reviewMode} ... />
<input type="radio" disabled={reviewMode} ... />

// Hide timer
<TestLayout
  timeRemaining={reviewMode ? null : timeRemaining}
  ...
/>
```

#### File: `React/src/features/exams/pages/ReadingTest.css`

**Thêm CSS cho Review:**
```css
/* Question Review Item */
.reading-test__question-review-item {
  background: white;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 16px;
  border: 1px solid #E5E7EB;
}

/* Answer Badges */
.reading-test__answer-badge.correct {
  background: #D1FAE5;
  color: #065F46;
}

.reading-test__answer-badge.incorrect {
  background: #FEE2E2;
  color: #991B1B;
}

.reading-test__answer-badge.unanswered {
  background: #FEF3C7;
  color: #92400E;
}

/* Feedback Section */
.reading-test__feedback {
  background: #EFF6FF;
  border-left: 3px solid #3B82F6;
  border-radius: 6px;
  padding: 16px;
}

/* Hint Section */
.reading-test__hint {
  background: #FFFBEB;
  border-left: 3px solid #F59E0B;
  border-radius: 6px;
  padding: 16px;
}
```

#### File: `React/src/app/routes.jsx`

**Thêm route mới:**
```jsx
import TestReview from '../features/exams/pages/TestReview';

// ...

{ path: '/test-review/:resultId', element: <TestReview /> },
```

#### File: `React/src/features/exams/pages/TestResult.jsx`

**Cập nhật button "Giải thích chi tiết":**
```jsx
<button className="test-result__review-btn" onClick={() => {
  // Navigate to review page
  navigate(`/test-review/${resultId}`);
}}>
  Giải thích chi tiết
</button>
```

## 🎯 Luồng hoạt động

1. **User xem kết quả bài thi** tại `/test-result/:resultId`
2. **Click "Giải thích chi tiết"**
3. **Navigate đến** `/test-review/:resultId`
4. **Backend trả về** kết quả với đầy đủ:
   - Passage content
   - Question content
   - Feedback
   - Hint
   - User answer vs Correct answer
5. **Frontend hiển thị** layout tương tự khi làm bài:
   - Passage bên trái
   - Questions với giải thích bên phải
   - Badge màu sắc cho từng câu
   - Tab navigation theo Part

## 🎨 UI/UX

### Màu sắc:
- **Đúng**: Xanh lá (`#D1FAE5` / `#065F46`)
- **Sai**: Đỏ (`#FEE2E2` / `#991B1B`)
- **Bỏ qua**: Vàng (`#FEF3C7` / `#92400E`)
- **Feedback**: Xanh dương (`#EFF6FF` / `#1E40AF`)
- **Hint**: Cam (`#FFFBEB` / `#92400E`)

### Layout:
```
┌─────────────────────────────────────────────────────┐
│  Header (Logo + Test Name + Close Button)          │
├──────────────────┬──────────────────────────────────┤
│                  │  Part 1 │ Part 2 │ Part 3        │
│  William Henry   ├──────────────────────────────────┤
│  Perkin          │  Question 1 - 7 Choose TRUE/...  │
│                  │  ┌────────────────────────────┐  │
│  The man who     │  │ 1. Michael Faraday was ... │  │
│  invented        │  │ □ True  ☑ False            │  │
│  synthetic dyes  │  │ ─────────────────────────  │  │
│                  │  │ Câu trả lời: False    ✗Sai│  │
│  William Henry   │  │ Đáp án đúng: True          │  │
│  Perkin was      │  │ 📘 Giải thích:             │  │
│  born on March   │  │ According to Dr Richter... │  │
│  12,1838...      │  └────────────────────────────┘  │
│                  │  ┌────────────────────────────┐  │
│  (Passage        │  │ 2. Michael Faraday was ... │  │
│   content        │  │ ☑ True  □ False            │  │
│   như khi        │  │ ─────────────────────────  │  │
│   làm bài)       │  │ Câu trả lời: True    ✓Đúng │  │
│                  │  │ Đáp án đúng: True          │  │
│                  │  │ 📘 Giải thích: ...         │  │
│                  │  │ 💡 Gợi ý: ...              │  │
│                  │  └────────────────────────────┘  │
└──────────────────┴──────────────────────────────────┘
```

## ✅ Testing Checklist

- [ ] Kiểm tra hiển thị passage content đúng
- [ ] Kiểm tra badge đúng/sai/bỏ qua hiển thị chính xác
- [ ] Kiểm tra feedback hiển thị (nếu có)
- [ ] Kiểm tra hint hiển thị (nếu có)
- [ ] Kiểm tra navigation giữa các Part
- [ ] Kiểm tra responsive trên mobile
- [ ] Kiểm tra với câu hỏi không có feedback
- [ ] Kiểm tra với section không có passage
- [ ] Kiểm tra performance với nhiều câu hỏi

## 🚀 Cách sử dụng

1. Làm bài thi
2. Nộp bài và xem kết quả
3. Click "Giải thích chi tiết"
4. Xem lại passage và giải thích từng câu
5. Navigate qua các Part nếu có nhiều Part

## 📝 Notes

- Tái sử dụng `TestLayout` component để giữ consistency
- Tái sử dụng CSS từ `ReadingTest.css`
- Backend API đã được optimize để trả về đầy đủ thông tin cần thiết
- Frontend chỉ cần call API một lần để lấy toàn bộ dữ liệu
- Support cả câu hỏi thuộc section hoặc question group
- Fallback cho trường hợp không có feedback/hint

## 🔮 Future Enhancements

- [ ] Thêm filter: chỉ xem câu sai, chỉ xem câu đúng
- [ ] Thêm export PDF kết quả
- [ ] Thêm video giải thích (nếu có)
- [ ] Thêm discussion/comment cho mỗi câu
- [ ] Thêm bookmark câu hỏi khó
- [ ] Thêm statistics chi tiết theo topic
