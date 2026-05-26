import { memo } from 'react';
import InlineAnswerContent from '../InlineAnswerContent';
import { buildInlineCompletionContent } from './textQuestionUtils';

function SummaryCompletionQuestions({ group, answers, onAnswerChange }) {
  return (
    <div className="listening-test__summary-card">
      <InlineAnswerContent
        content={buildInlineCompletionContent(group)}
        questions={group.questions}
        answers={answers}
        onAnswerChange={onAnswerChange}
        variant="summary"
      />
    </div>
  );
}

export default memo(SummaryCompletionQuestions);
