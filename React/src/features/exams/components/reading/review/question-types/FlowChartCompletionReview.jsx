import {
  getQuestionExplanation,
  getQuestionLocateText,
  getReviewAnswerData,
  getReviewCorrectAnswer,
  isCorrectResultValue,
  stripHtmlToText
} from '../readingReviewUtils';

function FlowChartCompletionReviewItem({
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
  const keywordText = getQuestionLocateText(question);
  const hasStructuredExplanation = /<(ul|ol|li)\b|keyword|từ khoá|từ khóa|giải thích/i.test(explanation);

  const handleToggle = () => {
    onQuestionFocus?.(isExpanded ? null : question);
    onToggleExplanation?.(question.id);
  };

  return (
    <div className={`reading-review__flow-chart-row ${stateClass} ${isExpanded ? 'is-expanded' : ''} ${isActive ? 'is-active' : ''}`}>
      <div className="reading-review__flow-chart-question-line">
        <span className="reading-review__flow-chart-question-number">
          {question.number}
        </span>

        <div className="reading-review__flow-chart-dropzone">
          <div className={`reading-review__flow-chart-answer-box ${stateClass}`}>
            {userAnswer || 'Chưa trả lời'}
          </div>
        </div>
      </div>

      <div className="reading-review__flow-chart-feedback">
        <div className="reading-review__flow-chart-feedback-head">
          <span className={`reading-review__flow-chart-status ${stateClass}`}>
            {isUnanswered ? 'Bỏ qua' : isCorrect ? 'Đúng' : 'Sai'}
          </span>

          <span className="reading-review__flow-chart-correct-answer">
            Answer: <strong>{correctAnswer || '...'}</strong>
          </span>

          <div className="reading-review__flow-chart-feedback-action">
            <button
              type="button"
              className="reading-review__flow-chart-detail-button"
              onClick={handleToggle}
              aria-expanded={isExpanded}
            >
              {isExpanded ? 'Thu gọn' : 'Chi tiết'}
            </button>
          </div>
        </div>

        {isExpanded && (
          <div className="reading-review__flow-chart-detail">
            {keywordText && (
              <>
                <ul className="reading-review__flow-chart-detail-list">
                  <li><strong>Keyword:</strong></li>
                </ul>
                <ul className="reading-review__flow-chart-detail-list reading-review__flow-chart-detail-list--nested">
                  <li>{keywordText}</li>
                </ul>
              </>
            )}

            {explanation && hasStructuredExplanation ? (
              <div
                className="reading-review__flow-chart-detail-text"
                dangerouslySetInnerHTML={{ __html: explanation }}
              />
            ) : explanation ? (
              <>
                <ul className="reading-review__flow-chart-detail-list">
                  <li><strong>Giải thích:</strong></li>
                </ul>
                <ul className="reading-review__flow-chart-detail-list reading-review__flow-chart-detail-list--nested">
                  <li>
                    <div
                      className="reading-review__flow-chart-detail-text"
                      dangerouslySetInnerHTML={{ __html: explanation }}
                    />
                  </li>
                </ul>
              </>
            ) : (
              !keywordText && (
                <ul className="reading-review__flow-chart-detail-list">
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

function FlowChartCompletionReview({
  group,
  userAnswers,
  expandedExplanations,
  activeQuestionId,
  onToggleExplanation,
  onQuestionFocus
}) {
  return (
    <section className="reading-review__flow-chart-answer-group">
      <div className="reading-review__flow-chart-instructions">
        <p>
          <strong>Questions {group.startNumber}-{group.endNumber}</strong>
        </p>
      </div>

      <div className="reading-review__flow-chart-card">
        <div className="reading-review__flow-chart-card-head">
          Your answer
        </div>

        <div className="reading-review__flow-chart-list">
          {group.questions?.map((question) => (
            <FlowChartCompletionReviewItem
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

export default FlowChartCompletionReview;
