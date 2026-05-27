import { useEffect, useMemo, useRef } from 'react';
import ListeningAudioPlayer from '../ListeningAudioPlayer';
import { getListeningReviewPartContentHtml } from './listeningReviewUtils';

export default function ListeningReviewContentPanel({
  groups,
  currentPartTab,
  audioUrl,
  userAnswers,
  activeQuestionId,
  onQuestionSelect
}) {
  const contentRef = useRef(null);
  const contentHtml = getListeningReviewPartContentHtml(groups, userAnswers);
  const questionById = useMemo(() => {
    const entries = groups
      .flatMap((group) => group.questions || [])
      .map((question) => [String(question.id), question]);

    return new Map(entries);
  }, [groups]);

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

  const handleContentClick = (event) => {
    const anchor = event.target.closest('[data-question-id]');
    if (!anchor) return;

    const question = questionById.get(String(anchor.dataset.questionId));
    if (question) {
      onQuestionSelect(question);
    }
  };

  return (
    <div className="listening-review__passage">
      <h2 className="listening-review__title">Listening Part {currentPartTab}</h2>
      <ListeningAudioPlayer audioUrl={audioUrl} />

      <div className="listening-review__passage-section">
        {contentHtml ? (
          <div
            ref={contentRef}
            className="listening-review__passage-content"
            dangerouslySetInnerHTML={{ __html: contentHtml }}
            onClick={handleContentClick}
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
