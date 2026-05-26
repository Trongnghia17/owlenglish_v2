import { memo } from 'react';

function TableCompletionQuestions({ group, answers, onAnswerChange }) {
  return (
    <aside className="listening-test__table-answer-card">
      <div className="listening-test__table-answer-title">Your answer</div>

      <div className="listening-test__table-answer-list">
        {group.questions.map((question) => {
          const answer = answers[question.id] || '';
          const inputId = `listening-table-answer-${question.id}`;

          return (
            <div
              key={question.id}
              id={`listening-table-question-${question.id}`}
              className={`listening-test__table-answer-row ${answer ? 'is-filled' : ''}`}
            >
              <label
                className="listening-test__table-answer-number"
                htmlFor={inputId}
              >
                {question.number}
              </label>
              <div className="listening-test__table-answer-dropzone">
                <input
                  id={inputId}
                  type="text"
                  className="listening-test__table-answer-input"
                  placeholder="Type answer here"
                  value={answer}
                  onChange={(event) => onAnswerChange(question.id, event.target.value)}
                  maxLength={100}
                  autoComplete="off"
                />
                {answer && (
                  <button
                    type="button"
                    className="listening-test__table-answer-clear"
                    aria-label={`Clear answer ${question.number}`}
                    onClick={() => onAnswerChange(question.id, '')}
                  />
                )}
              </div>
            </div>
          );
        })}
      </div>
    </aside>
  );
}

export default memo(TableCompletionQuestions);
