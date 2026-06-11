import { memo } from 'react';

const EMPTY_PLACEHOLDER =
  '..................................................................................................................';

const getTextAnswerValue = (answer) => {
  if (Array.isArray(answer)) {
    return answer.filter(Boolean).join(', ');
  }

  return String(answer ?? '');
};

function ShortAnswerQuestions({ group, answers, onAnswerChange }) {
  return (
    <div className="reading-test__short-answer-list">
      {group.questions.map((question) => {
        const value = getTextAnswerValue(answers?.[question.id]);
        const isFilled = value.trim() !== '';
        const inputId = `reading-short-answer-${question.id}`;

        return (
          <article
            key={question.id}
            className={`reading-test__short-answer-card ${isFilled ? 'is-filled' : ''}`}
          >
            <div className="reading-test__short-answer-card-header">
              <div className="reading-test__short-answer-card-number">
                {question.number}
              </div>

              <div
                className="reading-test__short-answer-question-text"
                dangerouslySetInnerHTML={{ __html: question.content || '' }}
              />
            </div>

            <div className="reading-test__short-answer-field-wrap">
              <label className="reading-test__short-answer-pill" htmlFor={inputId}>
                <input
                  id={inputId}
                  type="text"
                  className="reading-test__short-answer-input"
                  aria-label={`Answer ${question.number}`}
                  placeholder={EMPTY_PLACEHOLDER}
                  value={value}
                  onChange={(event) =>
                    onAnswerChange(question.id, event.target.value)
                  }
                  maxLength={100}
                  autoComplete="off"
                />
              </label>
            </div>
          </article>
        );
      })}
    </div>
  );
}

export default memo(ShortAnswerQuestions);
