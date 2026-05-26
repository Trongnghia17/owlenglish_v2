import { memo } from 'react';
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
        {block.questions.map((question) => (
          <label key={question.id} className="listening-test__short-answer-row">
            <span className="listening-test__short-answer-number">{question.number}</span>
            <input
              type="text"
              className="listening-test__short-answer-input"
              aria-label={`Answer ${question.number}`}
              value={answers[question.id] || ''}
              onChange={(event) => onAnswerChange(question.id, event.target.value)}
              maxLength={100}
            />
          </label>
        ))}
      </div>
    </div>
  ));
}

export default memo(ShortAnswerQuestions);
