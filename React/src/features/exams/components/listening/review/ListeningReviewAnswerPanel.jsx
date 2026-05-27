import { getListeningReviewRenderer } from './question-types';

export default function ListeningReviewAnswerPanel({
  groups,
  userAnswers,
  expandedExplanations,
  activeQuestionId,
  onToggleExplanation,
  onQuestionSelect,
  onQuestionFocus,
  onLocate
}) {
  return (
    <div className="listening-review__answers">
      {groups.map((group) => {
        const ReviewRenderer = getListeningReviewRenderer(group.type);

        return (
          <ReviewRenderer
            key={group.id}
            group={group}
            userAnswers={userAnswers}
            expandedExplanations={expandedExplanations}
            activeQuestionId={activeQuestionId}
            onToggleExplanation={onToggleExplanation}
            onQuestionSelect={onQuestionSelect}
            onQuestionFocus={onQuestionFocus}
            onLocate={onLocate}
          />
        );
      })}
    </div>
  );
}
