import { useMemo } from 'react';
import InlineAnswerContent from '../../../../features/exams/components/listening/InlineAnswerContent';
import { buildInlineCompletionContent, stripHtml } from '../../../../features/exams/components/listening/question-types/textQuestionUtils';
import { getReviewAnswerData, getQuestionExplanation, getQuestionLocateText } from '../readingReviewUtils';

function SentenceCompletionReview({ group, userAnswers, expandedExplanations, activeQuestionId, onToggleExplanation, onQuestionFocus, onLocate }) {
  return (
    <section className="reading-review__answer-group reading-review__answer-group--sentence">
      <div className="reading-review__answer-range">Questions {group.startNumber} – {group.endNumber}</div>
      <div className="reading-review__sentence-card">
        <InlineAnswerContent
          content={buildInlineCompletionContent(group)}
          questions={group.questions}
          answers={Object.fromEntries(
            group.questions.map(q => {
              const d = getReviewAnswerData(userAnswers, q);
              return [q.id, stripHtml(d.userAnswer || d.correctAnswer || '')];
            })
          )}
          onAnswerChange={() => {}}
          onQuestionFocus={onQuestionFocus}
          variant="sentence"
          reviewMode
          userAnswers={userAnswers}
        />
      </div>
    </section>
  );
}

export default SentenceCompletionReview;
