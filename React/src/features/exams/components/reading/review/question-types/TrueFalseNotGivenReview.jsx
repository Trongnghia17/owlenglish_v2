import { getReviewAnswerData, isCorrectResultValue, stripHtmlToText, getQuestionExplanation, getQuestionLocateText } from '../readingReviewUtils';
import './TrueFalseNotGivenReview.css';

const TRUE_FALSE_NOT_GIVEN_OPTIONS = [
  { letter: 'A', content: 'TRUE' },
  { letter: 'B', content: 'FALSE' },
  { letter: 'C', content: 'NOT GIVEN' }
];

const getOptionLabel = (option) => stripHtmlToText(option?.content || option?.label || option || '');

const normalizeAnswer = (value) => stripHtmlToText(value || '').trim().toUpperCase();

const matchesOption = (answer, option) => {
  const normalizedAnswer = normalizeAnswer(answer);
  return normalizedAnswer === normalizeAnswer(option.letter) || normalizedAnswer === normalizeAnswer(getOptionLabel(option));
};

function ReviewItem({ question, userAnswers, expandedExplanations, activeQuestionId, onToggleExplanation, onQuestionFocus, onLocate, options }) {
  const answerData = getReviewAnswerData(userAnswers, question);
  const userAnswer = stripHtmlToText(answerData.userAnswer || '');
  const correctAnswer = stripHtmlToText(answerData.correctAnswer || question?.correctAnswer || '');
  const isCorrect = isCorrectResultValue(answerData.isCorrect);
  const isUnanswered = !userAnswer;

  const isExpanded = Boolean(expandedExplanations[question.id]);
  const isActive = String(activeQuestionId ?? '') === String(question.id);
  const explanation = getQuestionExplanation(question);
  const locateText = getQuestionLocateText(question);
  const hasDetailActions = Boolean(explanation || locateText);

  return (
    <div className={`reading-review__tfng-question-block ${isUnanswered ? 'is-unanswered' : isCorrect ? 'is-correct' : 'is-incorrect'} ${isExpanded ? 'is-expanded' : ''} ${isActive ? 'is-active' : ''}`}>
      <div className="reading-review__tfng-question-head">
        <div className="reading-review__tfng-number-row">
          <span className="reading-review__tfng-number">{question.number}</span>
        </div>
        <div className="reading-review__tfng-question-text" dangerouslySetInnerHTML={{ __html: question.content || '' }} />
      </div>
      <div className="reading-review__tfng-options">
        {options.map((option) => {
          const isSelected = matchesOption(userAnswer, option);
          const isCorrectOpt = matchesOption(correctAnswer, option);
          let stateClass = '';
          if (isCorrectOpt) stateClass = 'is-correct';
          else if (isSelected && !isCorrect) stateClass = 'is-incorrect';

          return (
            <div key={option.letter} className={`reading-review__tfng-option ${stateClass} ${isSelected ? 'is-selected' : ''}`}>
              <span className="reading-review__tfng-option-letter">{option.letter}</span>
              <span className="reading-review__tfng-option-text">{getOptionLabel(option)}</span>
            </div>
          );
        })}
      </div>

      {hasDetailActions && (
        <div className="reading-review__tfng-detail-panel">
          <div className="reading-review__tfng-detail-head">
          {explanation && (
            <button type="button" className="reading-review__tfng-detail-button" onClick={() => { onQuestionFocus(question); onToggleExplanation(question.id); }}>
              Chi tiết
            </button>
          )}
          {locateText && (
            <button type="button" className="reading-review__tfng-locate-button" onClick={(event) => { event.stopPropagation(); onLocate(locateText); }}>
              <span>Vị trí: {locateText}</span>
            </button>
          )}
          </div>
          {isExpanded && explanation && (
            <div className="reading-review__tfng-detail" dangerouslySetInnerHTML={{ __html: explanation }} />
          )}
        </div>
      )}
    </div>
  );
}

export default function TrueFalseNotGivenReview({ group, userAnswers, expandedExplanations, activeQuestionId, onToggleExplanation, onQuestionFocus, onLocate }) {
  return (
    <section className="reading-review__answer-group reading-review__answer-group--tfng">
      <div className="reading-review__tfng-range">
        <div>Questions {group.startNumber}-{group.endNumber}</div>
        <div>Choose TRUE/FALSE/NOT GIVEN</div>
      </div>
      <div className="reading-review__tfng-list">
        {group.questions?.map((question) => (
          <ReviewItem key={question.id} question={question} userAnswers={userAnswers} expandedExplanations={expandedExplanations} activeQuestionId={activeQuestionId} onToggleExplanation={onToggleExplanation} onQuestionFocus={onQuestionFocus} onLocate={onLocate} options={TRUE_FALSE_NOT_GIVEN_OPTIONS} />
        ))}
      </div>
    </section>
  );
}
