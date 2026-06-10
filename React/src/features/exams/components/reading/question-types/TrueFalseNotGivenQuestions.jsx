import { memo } from 'react';

const TRUE_FALSE_NOT_GIVEN_OPTIONS = [
  { letter: 'A', value: 'TRUE' },
  { letter: 'B', value: 'FALSE' },
  { letter: 'C', value: 'NOT GIVEN' }
];

const normalizeAnswer = (answer) =>
  String(answer ?? '').trim().toUpperCase();

function TrueFalseNotGivenQuestions({ group, answers, onAnswerChange, showTips = false }) {
  return (
    <div className={`reading-test__mc-layout ${showTips ? '' : 'reading-test__mc-layout--single'}`}>
      <div className="reading-test__mc-questions">
        {group.questions.map((question) => {
          const selectedAnswer = normalizeAnswer(answers[question.id]);

          return (
            <div key={question.id} className="reading-test__mc-card">
              <div className="reading-test__mc-card-header">
                <div className="reading-test__mc-number">{question.number}</div>
                <div className="reading-test__mc-question-text" dangerouslySetInnerHTML={{ __html: question.content || '' }} />
              </div>
              <div className="reading-test__mc-options">
                {TRUE_FALSE_NOT_GIVEN_OPTIONS.map((option) => {
                  const isSelected = selectedAnswer === option.value || selectedAnswer === option.letter;

                  return (
                    <label key={option.letter} className={`reading-test__mc-option ${isSelected ? 'selected' : ''}`}>
                      <input
                        className="reading-test__mc-input"
                        type="radio"
                        name={`question-${question.id}`}
                        value={option.value}
                        checked={isSelected}
                        onChange={() => onAnswerChange(question.id, option.value)}
                      />
                      <span className="reading-test__mc-option-body">
                        <span className="reading-test__mc-letter">{option.letter}</span>
                        <span className="reading-test__mc-option-text">{option.value}</span>
                      </span>
                    </label>
                  );
                })}
              </div>
            </div>
          );
        })}
      </div>
      {showTips && (
        <aside className="reading-test__mc-tips">
          <div className="reading-test__mc-tips-title">
            <span className="reading-test__mc-tips-icon" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="18" viewBox="0 0 14 18" fill="none">
                <path d="M10.6251 16.877C10.6251 17.0428 10.5593 17.2017 10.442 17.3189C10.3248 17.4361 10.1659 17.502 10.0001 17.502H3.7501C3.58434 17.502 3.42537 17.4361 3.30816 17.3189C3.19095 17.2017 3.1251 17.0428 3.1251 16.877ZM13.7501 6.877C13.7528 7.9189 13.5174 8.94766 13.0619 9.88474 12.6065 10.8218 11.9429 11.6425 11.122 12.284C10.9685 12.4017 10.8439 12.5529 10.7578 12.7261 10.6717 12.8993 10.6263 13.0898 10.6251 13.2832V13.752C10.6251 14.0835 10.4934 14.4015 10.259 14.6359 10.0246 14.8703 9.70662 15.002 9.3751 15.002H4.3751C4.04358 15.002 3.72564 14.8703 3.49122 14.6359C3.2568 14.4015 3.1251 14.0835 3.1251 13.752V13.2832C3.12497 13.0921 3.08103 12.9036 2.99666 12.7322 2.91228 12.5607 2.78972 12.4109 2.63838 12.2942 1.81948 11.6564 1.15639 10.8407 0.699304 9.9088 0.24222 8.9769 0.00312013 7.95323 0.000102226 6.91528-0.0202103 3.19184 2.98916 0.091058 6.70948 0.00199546C7.62616 -0.0200946 8.53799 0.141412 9.39132 0.477009 10.2446 0.812607 11.0222 1.31551 11.6783 1.95613 12.3343 2.59674 12.8556 3.36213 13.2114 4.20722 13.5672 5.05232 13.7504 5.96005 13.7501 6.877ZM12.5001 6.877C12.5003 6.12673 12.3504 5.384 12.0593 4.69253 11.7682 4.00106 11.3416 3.37482 10.8048 2.85068 10.268 2.32653 9.63175 1.91507 8.93353 1.64052 8.2353 1.36596 7.48921 1.23386 6.73917 1.252 3.69229 1.32387 1.2337 3.86059 1.2501 6.90746 1.25296 7.75636 1.44885 8.59349 1.82296 9.35551 2.19706 10.1175 2.73959 10.7845 3.40948 11.3059 3.7106 11.54 3.95418 11.8399 4.12155 12.1826 4.28892 12.5253 4.37565 12.9018 4.3751 13.2832V13.752H9.3751V13.2832C9.37597 12.9007 9.46419 12.5235 9.63302 12.1803 9.80186 11.837 10.0469 11.5369 10.3493 11.3028 11.0213 10.7776 11.5644 10.1059 11.9371 9.33889 12.3099 8.57186 12.5024 7.7298 12.5001 6.877ZM11.2415 6.14731C11.0794 5.24207 10.6439 4.40822 9.99357 3.75801 9.34323 3.1078 8.50928 2.67246 7.60401 2.51059 7.52306 2.49694 7.44022 2.49938 7.36021 2.51775 7.2802 2.53612 7.2046 2.57007 7.13771 2.61766 7.07082 2.66525 7.01396 2.72555 6.97038 2.79511 6.9268 2.86467 6.89734 2.94214 6.8837 3.02309 6.87005 3.10404 6.87248 3.18688 6.89085 3.26689 6.90922 3.34689 6.94317 3.4225 6.99076 3.48939 7.03835 3.55628 7.09865 3.61313 7.16822 3.65672 7.23778 3.7003 7.31525 3.72976 7.3962 3.7434 8.69073 3.96137 9.78917 5.05981 10.0087 6.35668 10.0334 6.50225 10.1089 6.63436 10.2217 6.72959 10.3345 6.82483 10.4775 6.87705 10.6251 6.877C10.6604 6.87678 10.6957 6.87391 10.7306 6.8684 10.8939 6.84051 11.0395 6.74888 11.1354 6.61365 11.2312 6.47843 11.2694 6.31068 11.2415 6.14731Z" fill="#1F2937" />
              </svg>
            </span>
            IELTS Tips
          </div>
          <p>+ <strong>TRUE</strong>: Thông tin trong câu hỏi khớp hoàn toàn với thông tin trong bài đọc.</p>
          <p>+ <strong>FALSE</strong>: Thông tin trong câu hỏi trái ngược/contradicts với bài đọc.</p>
          <p>+ <strong>NOT GIVEN</strong>: Bài đọc không đề cập đến thông tin đó — không thể kết luận đúng hay sai.</p>
          <p>+ Lưu ý: FALSE ≠ NOT GIVEN. Chỉ chọn FALSE khi bài viết NÓI NGƯỢC lại câu hỏi.</p>
        </aside>
      )}
    </div>
  );
}

export default memo(TrueFalseNotGivenQuestions);
