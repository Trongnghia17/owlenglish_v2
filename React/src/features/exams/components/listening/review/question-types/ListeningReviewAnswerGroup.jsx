import {
  getQuestionExplanation,
  getQuestionLocateText,
  getReviewAnswerData,
  getReviewCorrectAnswer,
  isCorrectResultValue,
  stripHtmlToText
} from '../listeningReviewUtils';

function ListeningReviewAnswerItem({
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
    <div
      className={`listening-review__answer-item ${stateClass} ${isExpanded ? 'is-expanded' : ''} ${isActive ? 'is-active' : ''}`}
    >
      <div className="listening-review__answer-input-row">
        <span className="listening-review__answer-number">{question.number}</span>
        <div className="listening-review__answer-input">
          {userAnswer || 'Chưa trả lời'}
        </div>
      </div>

      <div className="listening-review__answer-feedback-row">
        <span className="listening-review__status-badge">{statusLabel}</span>
        <span className="listening-review__correct-answer">
          Answer: <strong>{correctAnswer || 'N/A'}</strong>
        </span>
        <button
          type="button"
          className="listening-review__detail-button"
          onClick={(event) => {
            event.stopPropagation();
            onQuestionFocus(question);
            onToggleExplanation(question.id);
          }}
          aria-expanded={isExpanded}
        >
          {isExpanded ? 'Thu gọn' : 'Thu gọn'}
        </button>
      </div>

      {isExpanded && (
        <div className="listening-review__detail">
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
  );
}

export default function ListeningReviewAnswerGroup({
  group,
  userAnswers,
  expandedExplanations,
  activeQuestionId,
  onToggleExplanation,
  onQuestionFocus,
  onLocate,
  typeClass = 'default',
  title = 'Your answer'
}) {
  return (
    <section
      id={`question-group-${group.id}`}
      className={`listening-review__answer-group listening-review__answer-group--${typeClass}`}
    >
      <div className="listening-review__answer-range">
        Question {group.startNumber}-{group.endNumber}
      </div>
      <div className="listening-review__answer-card">
        <div className="listening-review__answer-card-title">{title}</div>

        {group.questions?.map((question) => (
          <ListeningReviewAnswerItem
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
