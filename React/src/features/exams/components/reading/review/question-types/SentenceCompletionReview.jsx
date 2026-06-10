import {
  getQuestionExplanation,
  getQuestionLocateText,
  getReviewAnswerData,
  getReviewCorrectAnswer,
  isCorrectResultValue,
  stripHtmlToText
} from '../readingReviewUtils';

function SentenceCompletionReviewItem({
  question,
  userAnswers,
  expandedExplanations = {},
  activeQuestionId,
  onToggleExplanation,
  onQuestionFocus
}) {
  const answerData = getReviewAnswerData(userAnswers, question);
  const userAnswer = stripHtmlToText(answerData.userAnswer || '');
  const correctAnswer = getReviewCorrectAnswer(answerData, question);
  const isCorrect = isCorrectResultValue(answerData.isCorrect);
  const isUnanswered = !userAnswer;
  const isExpanded = Boolean(expandedExplanations[question.id]);
  const isActive = String(activeQuestionId ?? '') === String(question.id);
  const stateClass = isUnanswered ? 'is-unanswered' : isCorrect ? 'is-correct' : 'is-incorrect';
  const explanation = getQuestionExplanation(question);
  const locateText = getQuestionLocateText(question);
  const hasStructuredExplanation = /<(ul|ol|li)\b|vị trí|từ khoá|từ khóa|giải thích|keywords/i.test(explanation);

  const handleToggle = () => {
    onQuestionFocus?.(question);
    onToggleExplanation?.(question.id);
  };

  return (
    <div className={`reading-review__sc-row ${stateClass} ${isExpanded ? 'is-expanded' : ''} ${isActive ? 'is-active' : ''}`}>
      <div className="reading-review__sc-question-line">
        <span className="reading-review__sc-question-number">
          {question.number}
        </span>

        <div className="reading-review__sc-dropzone">
          <div className={`reading-review__sc-answer-box ${stateClass}`}>
            {userAnswer || 'Chưa trả lời'}
          </div>
        </div>
      </div>

      <div className="reading-review__sc-feedback">
        <div className="reading-review__sc-feedback-head">
          <span className={`reading-review__sc-status ${stateClass}`}>
            {isUnanswered ? 'Bỏ qua' : isCorrect ? 'Đúng' : 'Sai'}
          </span>

          <span className="reading-review__sc-correct-answer">
            Answer: <strong>{correctAnswer || '...'}</strong>
          </span>

          <div className="reading-review__sc-feedback-action">
            <button
              type="button"
              className="reading-review__sc-detail-button"
              onClick={handleToggle}
              aria-expanded={isExpanded}
            >
              {isExpanded ? 'Thu gọn' : 'Chi tiết'}
            </button>
          </div>
        </div>

        {isExpanded && (
          <div className="reading-review__sc-detail">
            {locateText && (
              <>
                <ul className="reading-review__sc-detail-list">
                  <li><strong>Vị trí:</strong></li>
                </ul>
                <ul className="reading-review__sc-detail-list reading-review__sc-detail-list--nested">
                  <li>{locateText}</li>
                </ul>
              </>
            )}

            {explanation && hasStructuredExplanation ? (
              <div
                className="reading-review__sc-detail-text"
                dangerouslySetInnerHTML={{ __html: explanation }}
              />
            ) : explanation ? (
              <>
                <ul className="reading-review__sc-detail-list">
                  <li><strong>Giải thích đáp án:</strong></li>
                </ul>
                <ul className="reading-review__sc-detail-list reading-review__sc-detail-list--nested">
                  <li>
                    <div
                      className="reading-review__sc-detail-text"
                      dangerouslySetInnerHTML={{ __html: explanation }}
                    />
                  </li>
                </ul>
              </>
            ) : (
              !locateText && (
                <ul className="reading-review__sc-detail-list">
                  <li>Chưa có giải thích.</li>
                </ul>
              )
            )}
          </div>
        )}
      </div>
    </div>
  );
}

function SentenceCompletionReview({
  group,
  userAnswers,
  expandedExplanations,
  activeQuestionId,
  onToggleExplanation,
  onQuestionFocus
}) {
  return (
    <section className="reading-review__sc-answer-group">
      <div className="reading-review__sc-instructions">
        <p>
          <strong>Questions {group.startNumber}-{group.endNumber}</strong>
        </p>
      </div>

      <div className="reading-review__sc-card">
        <div className="reading-review__sc-card-head">
          Your answer
        </div>

        <div className="reading-review__sc-list">
          {group.questions?.map((question) => (
            <SentenceCompletionReviewItem
              key={question.id}
              question={question}
              userAnswers={userAnswers}
              expandedExplanations={expandedExplanations}
              activeQuestionId={activeQuestionId}
              onToggleExplanation={onToggleExplanation}
              onQuestionFocus={onQuestionFocus}
            />
          ))}
        </div>
      </div>
    </section>
  );
}

export default SentenceCompletionReview;
