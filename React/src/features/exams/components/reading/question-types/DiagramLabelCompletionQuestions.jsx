import { memo } from 'react';

function DiagramLabelCompletionQuestions({
  group,
  answers,
  onAnswerChange
}) {
  return (
    <aside className="reading-test__diagram-answer-card">
      <div className="reading-test__diagram-answer-title">
        Your answer
      </div>

      <div className="reading-test__diagram-answer-list">
        {group.questions.map((question) => {
          const answer =
            answers[question.id] || '';
          const inputId = `reading-diagram-answer-${question.id}`;

          return (
            <div
              key={question.id}
              id={`reading-diagram-question-${question.id}`}
              className={`reading-test__diagram-answer-row ${
                answer ? 'is-filled' : ''
              }`}
            >
              <label
                className="reading-test__diagram-answer-number"
                htmlFor={inputId}
              >
                {question.number}
              </label>

              <input
                id={inputId}
                type="text"
                className="reading-test__diagram-answer-input"
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
                  className="reading-test__diagram-answer-clear"
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

export default memo(
  DiagramLabelCompletionQuestions
);
