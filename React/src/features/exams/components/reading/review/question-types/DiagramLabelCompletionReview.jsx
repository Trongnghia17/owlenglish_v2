import {
  getQuestionExplanation,
  getQuestionLocateText,
  getReviewAnswerData,
  getReviewCorrectAnswer,
  isCorrectResultValue,
  stripHtmlToText
} from '../readingReviewUtils';

function DiagramLabelCompletionReviewItem({
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
  const questionText = stripHtmlToText(question.content || '');
  const explanation = getQuestionExplanation(question);
  const keywordText = getQuestionLocateText(question);
  const hasStructuredExplanation = /<(ul|ol|li)\b|keyword|từ khoá|từ khóa|giải thích/i.test(explanation);

  const handleToggle = () => {
    onQuestionFocus?.(isExpanded ? null : question);
    onToggleExplanation?.(question.id);
  };

  return (
    <div className={`reading-review__diagram-row ${stateClass} ${isExpanded ? 'is-expanded' : ''} ${isActive ? 'is-active' : ''}`}>
      <div className="reading-review__diagram-question-line">
        <span className="reading-review__diagram-question-number">
          {question.number}
        </span>

        <div className="reading-review__diagram-dropzone">
          <div className={`reading-review__diagram-answer-box ${stateClass}`}>
            {userAnswer || 'Chưa trả lời'}
          </div>
        </div>
      </div>

      <div className="reading-review__diagram-feedback">
        <div className="reading-review__diagram-feedback-head">
          <span className={`reading-review__diagram-status ${stateClass}`}>
            {isUnanswered ? 'Bỏ qua' : isCorrect ? 'Đúng' : 'Sai'}
          </span>

          <span className="reading-review__diagram-correct-answer">
            Answer: <strong>{correctAnswer || '...'}</strong>
          </span>

          <div className="reading-review__diagram-feedback-action">
            <button
              type="button"
              className="reading-review__diagram-detail-button"
              onClick={handleToggle}
              aria-expanded={isExpanded}
            >
              {isExpanded ? 'Thu gọn' : 'Chi tiết'}
            </button>
          </div>
        </div>

        {isExpanded && (
          <div className="reading-review__diagram-detail">
            {questionText && (
              <>
                <ul className="reading-review__diagram-detail-list">
                  <li><strong>Question:</strong></li>
                </ul>
                <ul className="reading-review__diagram-detail-list reading-review__diagram-detail-list--nested">
                  <li>{questionText}</li>
                </ul>
              </>
            )}

            {keywordText && (
              <>
                <ul className="reading-review__diagram-detail-list">
                  <li><strong>Keyword:</strong></li>
                </ul>
                <ul className="reading-review__diagram-detail-list reading-review__diagram-detail-list--nested">
                  <li>{keywordText}</li>
                </ul>
              </>
            )}

            {explanation && hasStructuredExplanation ? (
              <div
                className="reading-review__diagram-detail-text"
                dangerouslySetInnerHTML={{ __html: explanation }}
              />
            ) : explanation ? (
              <>
                <ul className="reading-review__diagram-detail-list">
                  <li><strong>Giải thích:</strong></li>
                </ul>
                <ul className="reading-review__diagram-detail-list reading-review__diagram-detail-list--nested">
                  <li>
                    <div
                      className="reading-review__diagram-detail-text"
                      dangerouslySetInnerHTML={{ __html: explanation }}
                    />
                  </li>
                </ul>
              </>
            ) : (
              !keywordText && (
                <ul className="reading-review__diagram-detail-list">
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

function DiagramLabelCompletionReview({
  group,
  userAnswers,
  expandedExplanations,
  activeQuestionId,
  onToggleExplanation,
  onQuestionFocus
}) {
  return (
    <section className="reading-review__diagram-answer-group">
      <div className="reading-review__diagram-instructions">
        <p>
          <strong>Questions {group.startNumber}-{group.endNumber}</strong>
        </p>
      </div>

      <div className="reading-review__diagram-card">
        <div className="reading-review__diagram-card-head">
          Your answer
        </div>

        <div className="reading-review__diagram-list">
          {group.questions?.map((question) => (
            <DiagramLabelCompletionReviewItem
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

export default DiagramLabelCompletionReview;
