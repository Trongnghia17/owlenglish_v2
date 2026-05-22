import { memo } from 'react';
import { getQuestionAnswerOptions } from '../../../utils/listeningTest';

function MultipleChoiceQuestions({ group, answers, onAnswerChange }) {
  return (
    <div className="listening-test__mc-layout">
      <div className="listening-test__mc-questions">
        {group.questions.map((question) => {
          const questionOptions = getQuestionAnswerOptions(question, group.optionsWithContent || []);

          return (
            <div key={question.id} className="listening-test__mc-card">
              <div className="listening-test__mc-card-header">
                <div className="listening-test__mc-number">{question.number}</div>
                <div
                  className="listening-test__mc-question-text"
                  dangerouslySetInnerHTML={{ __html: question.content || '' }}
                />
              </div>
              <div className="listening-test__mc-options">
                {questionOptions.map((option) => (
                  <label
                    key={option.letter}
                    className={`listening-test__mc-option ${answers[question.id] === option.letter ? 'selected' : ''}`}
                  >
                    <input
                      type="radio"
                      name={`question-${question.id}`}
                      value={option.letter}
                      checked={answers[question.id] === option.letter}
                      onChange={() => onAnswerChange(question.id, option.letter)}
                    />
                    <span className="listening-test__mc-letter">{option.letter}</span>
                    <span
                      className="listening-test__mc-option-text"
                      dangerouslySetInnerHTML={{ __html: option.content }}
                    />
                  </label>
                ))}
              </div>
            </div>
          );
        })}
      </div>

      <aside className="listening-test__mc-tips">
        <div className="listening-test__mc-tips-title">
          <span className="listening-test__mc-tips-icon" aria-hidden="true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M9 21h6M10 17h4M12 3a7 7 0 0 0-4 12.74V17h8v-1.26A7 7 0 0 0 12 3Z" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </span>
          IELTS Tips
        </div>
        <p>+ Câu hỏi hoặc câu chưa hoàn chỉnh: thể hiện thông tin cần nghe.</p>
        <p>+ Các lựa chọn có thể xuất hiện trong bài nghe, có từ khóa trùng hoặc paraphrase. Trong đó có 1 đáp án đúng và các lựa chọn còn lại là thông tin nhiễu.</p>
        <p>+ Hướng dẫn số lượng đáp án: Choose ONE answer, Choose TWO letters. Đọc kỹ để không chọn thiếu hoặc thừa đáp án.</p>
      </aside>
    </div>
  );
}

export default memo(MultipleChoiceQuestions);
