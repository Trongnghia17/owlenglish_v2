import { memo } from 'react';

function TableCompletionQuestions({
  group,
  answers,
  onAnswerChange
}) {
  return (
    <aside className="reading-test__table-answer-card">
      <div className="reading-test__table-answer-title">
        Your answer
      </div>

      <div className="reading-test__table-answer-list">
        {group.questions.map((question) => {
          const answer =
            answers[question.id] || '';
          const inputId = `reading-table-answer-${question.id}`;

          return (
            <div
              key={question.id}
              id={`reading-table-question-${question.id}`}
              className={`reading-test__table-answer-row ${
                answer ? 'is-filled' : ''
              }`}
            >
              <label
                className="reading-test__table-answer-number"
                htmlFor={inputId}
              >
                {question.number}
              </label>

              <input
                id={inputId}
                type="text"
                className="reading-test__table-answer-input"
                placeholder="Type answer here"
                value={answer}
                onChange={(event) =>
                  onAnswerChange(
                    question.id,
                    event.target.value
                  )
                }
                maxLength={100}
                autoComplete="off"
              />

              {answer && (
                <button
                  type="button"
                  className="reading-test__table-answer-clear"
                  aria-label={`Clear answer ${question.number}`}
                  onClick={() =>
                    onAnswerChange(question.id, '')
                  }
                />
              )}
            </div>
          );
        })}
      </div>
    </aside>
  );
}

export default memo(TableCompletionQuestions);
