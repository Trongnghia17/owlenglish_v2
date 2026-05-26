import { memo } from 'react';
import InlineAnswerContent from '../InlineAnswerContent';
import { buildInlineCompletionContent } from './textQuestionUtils';

function SentenceCompletionQuestions({ group, answers, onAnswerChange }) {
  return (
    <div className="listening-test__sentence-card">
      <InlineAnswerContent
        content={buildInlineCompletionContent(group)}
        questions={group.questions}
        answers={answers}
        onAnswerChange={onAnswerChange}
        variant="sentence"
      />
    </div>
  );
}

export default memo(SentenceCompletionQuestions);
