import { memo } from 'react';

const normalizeLetterAnswer = (value) =>
  String(value ?? '').trim().toUpperCase().slice(0, 1);

function PlanMapDiagramLabellingQuestions({ group, answers, onAnswerChange }) {
  return (
    <aside className="listening-test__map-answer-card">
      <div className="listening-test__map-answer-list">
        {group.questions.map((question) => {
          const answer = answers[question.id] || '';
          const inputId = `listening-map-answer-${question.id}`;

          return (
            <div
              key={question.id}
              id={`listening-map-question-${question.id}`}
              className={`listening-test__map-answer-row ${answer ? 'is-filled' : ''}`}
            >
              <label
                className="listening-test__map-answer-number"
                htmlFor={inputId}
              >
                {question.number}
              </label>
              <div
                className="listening-test__map-answer-text"
                dangerouslySetInnerHTML={{ __html: question.content || '' }}
              />
              <div className="listening-test__map-answer-dropzone">
                <input
                  id={inputId}
                  type="text"
                  className="listening-test__map-answer-input"
                  placeholder="Type answer here"
                  value={answer}
                  onChange={(event) => onAnswerChange(question.id, normalizeLetterAnswer(event.target.value))}
                  maxLength={1}
                  autoComplete="off"
                />
                {answer && (
                  <button
                    type="button"
                    className="listening-test__map-answer-clear"
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

export default memo(PlanMapDiagramLabellingQuestions);
