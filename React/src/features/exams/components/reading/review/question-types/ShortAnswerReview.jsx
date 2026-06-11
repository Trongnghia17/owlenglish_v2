import {
  getQuestionExplanation,
  getQuestionLocateText,
  getReviewAnswerData,
  getReviewCorrectAnswer,
  isCorrectResultValue,
  stripHtmlToText
} from '../readingReviewUtils';

function ShortAnswerReviewItem({
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
  const hasStructuredExplanation = /<(ul|ol|li)\b|keyword|từ khoá|từ khóa|vị trí|giải thích/i.test(explanation);

  const handleToggle = () => {
    onQuestionFocus?.(isExpanded ? null : question);
    onToggleExplanation?.(question.id);
  };

  return (
    <div className={`reading-review__short-answer-row ${stateClass} ${isExpanded ? 'is-expanded' : ''} ${isActive ? 'is-active' : ''}`}>
      <div className="reading-review__short-answer-question-line">
        <span className="reading-review__short-answer-question-number">
          {question.number}
        </span>

        <div className="reading-review__short-answer-dropzone">
          <div className={`reading-review__short-answer-answer-box ${stateClass}`}>
            {userAnswer || 'Chưa trả lời'}
          </div>
        </div>
      </div>

      <div className="reading-review__short-answer-feedback">
        <div className="reading-review__short-answer-feedback-head">
          <span className={`reading-review__short-answer-status ${stateClass}`}>
            {isUnanswered ? 'Bỏ qua' : isCorrect ? 'Đúng' : 'Sai'}
          </span>

          <span className="reading-review__short-answer-correct-answer">
            Answer: <strong>{correctAnswer || '...'}</strong>
          </span>

          <div className="reading-review__short-answer-feedback-action">
            <button
              type="button"
              className="reading-review__short-answer-detail-button"
              onClick={handleToggle}
              aria-expanded={isExpanded}
            >
              {isExpanded ? 'Thu gọn' : 'Chi tiết'}
            </button>
          </div>
        </div>

        {isExpanded && (
          <div className="reading-review__short-answer-detail">
            {locateText && (
              <>
                <ul className="reading-review__short-answer-detail-list">
                  <li><strong>Keyword:</strong></li>
                </ul>
                <ul className="reading-review__short-answer-detail-list reading-review__short-answer-detail-list--nested">
                  <li>{locateText}</li>
                </ul>
              </>
            )}

            {explanation && hasStructuredExplanation ? (
              <div
                className="reading-review__short-answer-detail-text"
                dangerouslySetInnerHTML={{ __html: explanation }}
              />
            ) : explanation ? (
              <>
                <ul className="reading-review__short-answer-detail-list">
                  <li><strong>Giải thích:</strong></li>
                </ul>
                <ul className="reading-review__short-answer-detail-list reading-review__short-answer-detail-list--nested">
                  <li>
                    <div
                      className="reading-review__short-answer-detail-text"
                      dangerouslySetInnerHTML={{ __html: explanation }}
                    />
                  </li>
                </ul>
              </>
            ) : (
              !locateText && (
                <ul className="reading-review__short-answer-detail-list">
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

function ShortAnswerReview({
  group,
  userAnswers,
  expandedExplanations,
  activeQuestionId,
  onToggleExplanation,
  onQuestionFocus
}) {
  return (
    <section className="reading-review__short-answer-answer-group">
      <div className="reading-review__short-answer-instructions">
        <p>
          <strong>Questions {group.startNumber}-{group.endNumber}</strong>
        </p>
      </div>

      <div className="reading-review__short-answer-card">
        <div className="reading-review__short-answer-card-head">
          Your answer
        </div>

        <div className="reading-review__short-answer-list">
          {group.questions?.map((question) => (
            <ShortAnswerReviewItem
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

export default ShortAnswerReview;
