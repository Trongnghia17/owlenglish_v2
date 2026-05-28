import {
  getQuestionExplanation,
  getQuestionLocateText,
  getReviewAnswerData,
  isCorrectResultValue,
  stripHtmlToText
} from '../listeningReviewUtils';
import {
  getQuestionAnswerOptions,
  isCorrectAnswerOption,
  parseMetadata
} from '../../../../utils/listeningTest';

const getChoiceTokens = (value = '') =>
  String(value)
    .split(/[,;]+/)
    .map((token) => token.trim().toUpperCase())
    .filter(Boolean);

const uniqueTokens = (tokens) => Array.from(new Set(tokens));

const getCorrectLetters = (question, correctAnswer) => {
  const metadata = parseMetadata(question?.metadata);
  const answers = Array.isArray(metadata.answers) ? metadata.answers : [];
  const metadataLetters = answers
    .map((answer, index) => (isCorrectAnswerOption(answer) ? String.fromCharCode(65 + index) : null))
    .filter(Boolean);

  return uniqueTokens(metadataLetters.length > 0 ? metadataLetters : getChoiceTokens(correctAnswer));
};

function MultipleChoiceReviewItem({
  group,
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
  const correctAnswer = stripHtmlToText(answerData.correctAnswer || question?.correctAnswer || '');
  const selectedLetters = uniqueTokens(getChoiceTokens(userAnswer));
  const correctLetters = getCorrectLetters(question, correctAnswer);
  const selectedSet = new Set(selectedLetters);
  const correctSet = new Set(correctLetters);
  const correctSelectedCount = selectedLetters.filter((letter) => correctSet.has(letter)).length;
  const wrongSelectedCount = selectedLetters.filter((letter) => !correctSet.has(letter)).length;
  const isUnanswered = selectedLetters.length === 0;
  const isCorrect = isCorrectResultValue(answerData.isCorrect);
  const stateClass = isUnanswered
    ? 'is-unanswered'
    : isCorrect
      ? 'is-correct'
      : 'is-incorrect';
  const explanation = getQuestionExplanation(question);
  const locateText = getQuestionLocateText(question);
  const isExpanded = Boolean(expandedExplanations[question.id]);
  const isActive = String(activeQuestionId ?? '') === String(question.id);
  const questionOptions = getQuestionAnswerOptions(question, group.optionsWithContent || []);

  const handleExpand = () => {
    if (!isExpanded) {
      onQuestionFocus(question);
      onToggleExplanation(question.id);
    }
  };

  const handleCollapse = () => {
    if (isExpanded) {
      onQuestionFocus(question);
      onToggleExplanation(question.id);
    }
  };

  return (
    <div
      className={`listening-review__mc-question-block ${stateClass} ${isExpanded ? 'is-expanded' : ''} ${isActive ? 'is-active' : ''}`}
    >
      <div className="listening-review__mc-question-head">
        <span className="listening-review__answer-number">{question.number}</span>
        <div className="listening-review__mc-statuses">
          {isUnanswered ? (
            <span className="listening-review__mc-status-badge is-unanswered">Bỏ qua</span>
          ) : (
            <>
              <span className="listening-review__mc-status-badge is-correct">
                Đúng {correctSelectedCount}/{Math.max(correctLetters.length, 1)}
              </span>
              {wrongSelectedCount > 0 && (
                <span className="listening-review__mc-status-badge is-incorrect">
                  Sai {wrongSelectedCount}/{selectedLetters.length}
                </span>
              )}
            </>
          )}
        </div>
      </div>

      <div
        className="listening-review__mc-question-text"
        dangerouslySetInnerHTML={{ __html: question.content || '' }}
      />

      <div className="listening-review__mc-options">
        {questionOptions.map((option) => {
          const isSelected = selectedSet.has(option.letter);
          const isCorrectOption = correctSet.has(option.letter);
          const optionStateClass = isCorrectOption
            ? 'is-correct'
            : isSelected
              ? 'is-incorrect'
              : '';

          return (
            <div
              key={option.letter}
              className={`listening-review__mc-option ${optionStateClass} ${isSelected ? 'is-selected' : ''}`}
            >
              <span className="listening-review__mc-option-letter">{option.letter}</span>
              <span
                className="listening-review__mc-option-text"
                dangerouslySetInnerHTML={{ __html: option.content }}
              />
            </div>
          );
        })}
      </div>

      <div className="listening-review__mc-actions">
        <button
          type="button"
          className="listening-review__mc-action listening-review__mc-action--detail"
          onClick={(event) => {
            event.stopPropagation();
            handleExpand();
          }}
        >
          Chi tiết
        </button>
        <button
          type="button"
          className="listening-review__mc-action listening-review__mc-action--collapse"
          onClick={(event) => {
            event.stopPropagation();
            handleCollapse();
          }}
        >
          Thu gọn
        </button>
      </div>

      {isExpanded && (
        <div className="listening-review__mc-detail">
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

export default function MultipleChoiceReview({
  group,
  userAnswers,
  expandedExplanations,
  activeQuestionId,
  onToggleExplanation,
  onQuestionFocus,
  onLocate
}) {
  return (
    <section className="listening-review__answer-group listening-review__answer-group--multiple-choice">
      <div className="listening-review__answer-range">
        Question {group.startNumber} - {group.endNumber}
      </div>
      <div className="listening-review__mc-list">
        {group.questions?.map((question) => (
          <MultipleChoiceReviewItem
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
