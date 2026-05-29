import {
  getQuestionExplanation,
  getQuestionLocateText,
  getReviewAnswerData,
  getReviewCorrectAnswer,
  isCorrectResultValue,
  stripHtmlToText
} from '../listeningReviewUtils';

const normalizeLetter = (value = '') => {
  const normalizedValue = stripHtmlToText(value).trim();

  return /^[A-Z]$/i.test(normalizedValue)
    ? normalizedValue.toUpperCase()
    : '';
};

const normalizeComparableText = (value = '') =>
  stripHtmlToText(value).toLowerCase();

const getQuestionAnswerOptions = (question) => {
  try {
    const metadata = typeof question?.metadata === 'string'
      ? JSON.parse(question.metadata)
      : question?.metadata;

    return Array.isArray(metadata?.answers) ? metadata.answers : [];
  } catch {
    return [];
  }
};

const getMatchingOptions = (group, question) => {
  const groupOptions = group.optionsWithContent?.length
    ? group.optionsWithContent
    : group.options;
  const sourceOptions = groupOptions?.length
    ? groupOptions
    : getQuestionAnswerOptions(question);

  return (sourceOptions || [])
    .map((option, index) => {
      if (typeof option === 'string') {
        return {
          letter: option.length === 1 ? option.toUpperCase() : String.fromCharCode(65 + index),
          content: option.length === 1 ? '' : option
        };
      }

      return {
        letter: String(option?.letter || option?.label || String.fromCharCode(65 + index)).toUpperCase(),
        content: option?.content || option?.text || ''
      };
    })
    .filter((option) => option.letter || option.content);
};

const findOptionByAnswer = (options, answer) => {
  const answerText = stripHtmlToText(answer || '');
  const answerLetter = normalizeLetter(answerText);

  if (answerLetter) {
    return options.find((option) => option.letter === answerLetter) || null;
  }

  const comparableAnswer = normalizeComparableText(answerText);

  return options.find((option) =>
    normalizeComparableText(option.content) === comparableAnswer
  ) || null;
};

const formatCorrectAnswer = (option, fallbackAnswer) => {
  const fallbackText = stripHtmlToText(fallbackAnswer || '');

  if (!option) {
    return fallbackText || 'N/A';
  }

  const optionText = stripHtmlToText(option.content || '');

  return optionText ? `${option.letter} — ${optionText}` : option.letter;
};

function MatchingReviewItem({
  group,
  question,
  userAnswers,
  expandedExplanations,
  activeQuestionId,
  onToggleExplanation,
  onQuestionFocus,
  onLocate
}) {
  const options = getMatchingOptions(group, question);
  const answerData = getReviewAnswerData(userAnswers, question);
  const userAnswer = stripHtmlToText(answerData.userAnswer || '');
  const correctAnswer = getReviewCorrectAnswer(answerData, question);
  const selectedOption = findOptionByAnswer(options, userAnswer);
  const correctOption = findOptionByAnswer(options, correctAnswer);
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
  const displayedUserLetter = selectedOption?.letter || normalizeLetter(userAnswer);
  const displayedUserText = selectedOption ? stripHtmlToText(selectedOption.content || '') : '';

  return (
    <div className={`listening-review__map-row listening-review__matching-row ${stateClass} ${isExpanded ? 'is-expanded' : ''} ${isActive ? 'is-active' : ''}`}>
      <div className="listening-review__map-question-card">
        <span className="listening-review__answer-number">{question.number}</span>
        <div
          className="listening-review__map-question-text"
          dangerouslySetInnerHTML={{ __html: question.content || '' }}
        />
        <div className="listening-review__map-dropzone">
          <div className="listening-review__map-answer-card listening-review__matching-answer-card">
            {displayedUserLetter ? (
              <>
                <span className="listening-review__matching-answer-letter">{displayedUserLetter}</span>
                {displayedUserText && (
                  <span className="listening-review__matching-answer-text">{displayedUserText}</span>
                )}
              </>
            ) : (
              'Chưa trả lời'
            )}
          </div>
        </div>
      </div>

      <div className="listening-review__map-feedback">
        <div className="listening-review__map-feedback-line">
          <span className="listening-review__map-status-badge">{statusLabel}</span>
          <span className="listening-review__map-correct-answer">
            Answer: <strong>{formatCorrectAnswer(correctOption, correctAnswer)}</strong>
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

export default function MatchingReview({
  group,
  userAnswers,
  expandedExplanations,
  activeQuestionId,
  onToggleExplanation,
  onQuestionFocus,
  onLocate
}) {
  return (
    <section className="listening-review__answer-group listening-review__answer-group--matching">
      <div className="listening-review__answer-range">
        Question {group.startNumber} - {group.endNumber}
      </div>
      <div className="listening-review__map-card listening-review__matching-card">
        {group.questions?.map((question) => (
          <MatchingReviewItem
            key={question.id}
            group={group}
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
