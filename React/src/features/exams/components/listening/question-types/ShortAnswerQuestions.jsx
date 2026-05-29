import { memo } from 'react';
import clsx from 'clsx';
import { createQuestionBlocks } from './textQuestionUtils';

function ShortAnswerQuestions({ group, answers, onAnswerChange }) {
  return createQuestionBlocks(group.questions).map((block) => (
    <div key={block.id} className="listening-test__short-answer-card">
      <div className="listening-test__short-answer-card-header">
        <div
          className="listening-test__short-answer-question-text"
          dangerouslySetInnerHTML={{ __html: block.content }}
        />
      </div>

      <div className="listening-test__short-answer-list">
        {block.questions.map((question) => {
          const value = answers[question.id] || '';

          return (
            <label
              key={question.id}
              className={clsx(
                'listening-test__short-answer-row',
                {
                  'is-filled': value.trim(),
                }
              )}
            >
              <span className="listening-test__short-answer-number">
                {question.number}
              </span>

              <input
                type="text"
                className="listening-test__short-answer-input"
                aria-label={`Answer ${question.number}`}
                value={value}
                onChange={(event) =>
                  onAnswerChange(question.id, event.target.value)
                }
                onFocus={(event) => {
                  event.currentTarget
                    .closest('.listening-test__short-answer-row')
                    ?.classList.add('is-focus');
                }}
                onBlur={(event) => {
                  event.currentTarget
                    .closest('.listening-test__short-answer-row')
                    ?.classList.remove('is-focus');
                }}
                maxLength={100}
              />
            </label>
          );
        })}
      </div>
    </div>
  ));
}

export default memo(ShortAnswerQuestions);