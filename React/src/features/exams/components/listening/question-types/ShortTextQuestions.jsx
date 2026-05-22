import { memo } from 'react';
import InlineAnswerContent from '../InlineAnswerContent';
import { containsInlinePlaceholders } from '../../../utils/listeningTest';

function ShortTextQuestions({ group, answers, onAnswerChange }) {
  const hasPlaceholders = containsInlinePlaceholders(group.groupContent);

  if (hasPlaceholders) {
    return (
      <div className="listening-test__question-group-with-inputs">
        <InlineAnswerContent
          content={group.groupContent}
          questions={group.questions}
          answers={answers}
          onAnswerChange={onAnswerChange}
        />
      </div>
    );
  }

  return group.questions.map((question) => (
    <div key={question.id} className="listening-test__question-item listening-test__question-item--input">
      <div className="listening-test__question-row">
        <div className="listening-test__question-number">
          {question.number}
        </div>
        <div
          className="listening-test__question-text"
          dangerouslySetInnerHTML={{ __html: question.content }}
        />
      </div>
      <div className="listening-test__answer-input-wrapper">
        <input
          type="text"
          className="listening-test__answer-input"
          placeholder="Type your answer here..."
          value={answers[question.id] || ''}
          onChange={(event) => onAnswerChange(question.id, event.target.value)}
          maxLength={100}
        />
      </div>
    </div>
  ));
}

export default memo(ShortTextQuestions);
