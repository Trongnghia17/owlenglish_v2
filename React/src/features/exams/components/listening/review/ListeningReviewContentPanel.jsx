import { useEffect, useRef } from 'react';
import { getListeningReviewPartContentHtml } from './listeningReviewUtils';

export default function ListeningReviewContentPanel({
  groups,
  userAnswers,
  activeQuestionId
}) {
  const contentRef = useRef(null);
  const contentHtml = getListeningReviewPartContentHtml(groups, userAnswers);

  useEffect(() => {
    const contentElement = contentRef.current;
    if (!contentElement) return;

    const anchors = contentElement.querySelectorAll('[data-question-id]');
    anchors.forEach((anchor) => {
      anchor.classList.toggle(
        'is-active',
        String(anchor.dataset.questionId) === String(activeQuestionId ?? '')
      );
    });

    if (!activeQuestionId) return;

    const activeAnchor = contentElement.querySelector(
      `[data-question-id="${CSS.escape(String(activeQuestionId))}"]`
    );
    activeAnchor?.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
  }, [activeQuestionId, contentHtml]);

  return (
    <div className="listening-review__passage">
      <div className="listening-review__passage-section">
        {contentHtml ? (
          <div
            ref={contentRef}
            className="listening-review__passage-content"
            dangerouslySetInnerHTML={{ __html: contentHtml }}
          />
        ) : (
          <div className="listening-review__empty-transcript">
            Chưa có transcript cho phần nghe này.
          </div>
        )}
      </div>
    </div>
  );
}
