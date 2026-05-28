import {
  getQuestionExplanation,
  getQuestionLocateText,
  getReviewAnswerData,
  getReviewCorrectAnswer,
  isCorrectResultValue,
  stripHtmlToText
} from '../listeningReviewUtils';

const formatCorrectAnswer = (answer) => {
  const normalizedAnswer = stripHtmlToText(answer || '');

  if (!normalizedAnswer) {
    return 'N/A';
  }

  return /^[A-Z]$/i.test(normalizedAnswer)
    ? `Đáp án: ${normalizedAnswer.toUpperCase()}`
    : normalizedAnswer;
};

function PlanMapDiagramLabellingReviewItem({
  question,
  userAnswers,
  expandedExplanations,
  activeQuestionId,
  onToggleExplanation,
  onQuestionFocus,
  onLocate
}) {
  const answerData = getReviewAnswerData(userAnswers, question);
  const userAnswer = stripHtmlToText(answerData.userAnswer || '');
  const correctAnswer = getReviewCorrectAnswer(answerData, question);
  const isUnanswered = userAnswer === '';
  const isCorrect = isCorrectResultValue(answerData.isCorrect);
  const stateClass = isUnanswered
    ? 'is-unanswered'
    : isCorrect
      ? 'is-correct'
      : 'is-incorrect';
  const statusLabel = isUnanswered ? 'Bỏ qua' : isCorrect ? 'Đúng' : 'Sai';
  const explanation = getQuestionExplanation(question);
  const locateText = getQuestionLocateText(question);
  const isExpanded = Boolean(expandedExplanations[question.id]);
  const isActive = String(activeQuestionId ?? '') === String(question.id);

  return (
    <div className={`listening-review__map-row ${stateClass} ${isExpanded ? 'is-expanded' : ''} ${isActive ? 'is-active' : ''}`}>
      <div className="listening-review__map-question-card">
        <span className="listening-review__answer-number">{question.number}</span>
        <div
          className="listening-review__map-question-text"
          dangerouslySetInnerHTML={{ __html: question.content || '' }}
        />
        <div className="listening-review__map-dropzone">
          <div className="listening-review__map-answer-card">
            {userAnswer || 'Chưa trả lời'}
          </div>
        </div>
      </div>

      <div className="listening-review__map-feedback">
        <div className="listening-review__map-feedback-line">
          <span className="listening-review__map-status-badge">{statusLabel}</span>
          <span className="listening-review__map-correct-answer">
            Answer: <strong>{formatCorrectAnswer(correctAnswer)}</strong>
          </span>
          <button
            type="button"
            className="listening-review__map-detail-button"
            onClick={(event) => {
              event.stopPropagation();
              onQuestionFocus(question);
              onToggleExplanation(question.id);
            }}
            aria-expanded={isExpanded}
          >
            {isExpanded ? 'Thu gọn' : 'Chi tiết'}
          </button>
        </div>

        {isExpanded && (
          <div className="listening-review__map-detail">
            {explanation ? (
              <div dangerouslySetInnerHTML={{ __html: explanation }} />
            ) : (
              <p>Chưa có giải thích cho câu này.</p>
            )}
            {locateText && (
              <button
                type="button"
                className="listening-review__locate-button"
                onClick={(event) => {
                  event.stopPropagation();
                  onLocate(locateText);
                }}
              >
                Locate
              </button>
            )}
          </div>
        )}
      </div>
    </div>
  );
}

export default function PlanMapDiagramLabellingReview({
  group,
  userAnswers,
  expandedExplanations,
  activeQuestionId,
  onToggleExplanation,
  onQuestionFocus,
  onLocate
}) {
  return (
    <section className="listening-review__answer-group listening-review__answer-group--plan-map-diagram-labelling">
      <div className="listening-review__answer-range">
        Question {group.startNumber} - {group.endNumber}
      </div>
      <div className="listening-review__map-card">
        {group.questions?.map((question) => (
          <PlanMapDiagramLabellingReviewItem
            key={question.id}
            question={question}
            userAnswers={userAnswers}
            expandedExplanations={expandedExplanations}
            activeQuestionId={activeQuestionId}
            onToggleExplanation={onToggleExplanation}
            onQuestionFocus={onQuestionFocus}
            onLocate={onLocate}
          />
        ))}
      </div>
    </section>
  );
}
