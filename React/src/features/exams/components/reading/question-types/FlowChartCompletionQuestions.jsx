import { memo } from 'react';

function FlowChartCompletionQuestions({
  group,
  answers,
  onAnswerChange
}) {
  return (
    <aside className="reading-test__flow-chart-answer-card">
      <div className="reading-test__flow-chart-answer-title">
        Your answer
      </div>

      <div className="reading-test__flow-chart-answer-list">
        {group.questions.map((question) => {
          const answer =
            answers[question.id] || '';
          const inputId = `reading-flow-chart-answer-${question.id}`;

          return (
            <div
              key={question.id}
              id={`reading-flow-chart-question-${question.id}`}
              className={`reading-test__flow-chart-answer-row ${
                answer ? 'is-filled' : ''
              }`}
            >
              <label
                className="reading-test__flow-chart-answer-number"
                htmlFor={inputId}
              >
                {question.number}
              </label>

              <input
                id={inputId}
                type="text"
                className="reading-test__flow-chart-answer-input"
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
                  className="reading-test__flow-chart-answer-clear"
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

export default memo(FlowChartCompletionQuestions);
