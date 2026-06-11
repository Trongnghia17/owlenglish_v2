import { getReviewAnswerData, isCorrectResultValue, stripHtmlToText, getQuestionExplanation, getQuestionLocateText } from '../readingReviewUtils';

const YES_NO_NOT_GIVEN_OPTIONS = [
  { letter: 'A', content: 'YES' },
  { letter: 'B', content: 'NO' },
  { letter: 'C', content: 'NOT GIVEN' }
];

const matchesOption = (answer, option) => {
  const normalizedAnswer = stripHtmlToText(answer || '').trim().toUpperCase();
  return normalizedAnswer === option.letter || normalizedAnswer === option.content;
};

function ReviewItem({ question, userAnswers, expandedExplanations, onToggleExplanation, onLocate, options }) {
  const answerData = getReviewAnswerData(userAnswers, question);
  const userAnswer = stripHtmlToText(answerData.userAnswer || '');
  const correctAnswer = stripHtmlToText(answerData.correctAnswer || question?.correctAnswer || '');
  const isCorrect = isCorrectResultValue(answerData.isCorrect);
  const isUnanswered = !userAnswer;

  const isExpanded = Boolean(expandedExplanations[question.id]);
  const explanation = getQuestionExplanation(question);
  const locateText = getQuestionLocateText(question);

  return (
    <div className={`reading-review__choice-question-block ${isUnanswered ? 'is-unanswered' : isCorrect ? 'is-correct' : 'is-incorrect'} ${isExpanded ? 'is-expanded' : ''}`}>
      <div className="reading-review__choice-question-head">
        <span className="reading-review__answer-number">{question.number}</span>
        {/* <span className={`reading-review__choice-badge ${isUnanswered ? 'is-unanswered' : isCorrect ? 'is-correct' : 'is-incorrect'}`}>
          {isUnanswered ? 'Bỏ qua' : isCorrect ? 'Đúng' : 'Sai'}
        </span> */}
      </div>
      <div className="reading-review__choice-question-text" dangerouslySetInnerHTML={{ __html: question.content || '' }} />
      <div className="reading-review__choice-options">
  {options.map((opt) => {
    const isSelected = matchesOption(userAnswer, opt);
    const isCorrectOpt = matchesOption(correctAnswer, opt);

    let stateClass = '';

    if (isCorrectOpt) stateClass = 'is-correct';
    else if (isSelected && !isCorrect) stateClass = 'is-incorrect';

    return (
      <div
        key={opt.letter}
        className={`reading-review__choice-option ${stateClass} ${
          isSelected ? 'is-selected' : ''
        }`}
      >
        <span className="reading-review__choice-option-letter">
          {opt.letter}
        </span>

        <span className="reading-review__choice-option-text">
          {opt.content}
        </span>
      </div>
    );
  })}
</div>
      
      {(explanation || locateText) && (
        <div className="reading-review__choice-actions">
          {explanation && (
            <button type="button" className="reading-review__choice-action-btn" onClick={() => onToggleExplanation(question.id)}>
              {isExpanded ? 'Thu gọn' : 'Chi tiết'}
            </button>
          )}
          {locateText && (
            <button type="button" className="reading-review__choice-action-btn reading-review__choice-action-btn--locate" onClick={() => onLocate(locateText)}>
              Locate
            </button>
          )}
        </div>
      )}
      {isExpanded && explanation && (
        <div className="reading-review__choice-detail" dangerouslySetInnerHTML={{ __html: explanation }} />
      )}
    </div>
  );
}

export default function YesNoNotGivenReview({ group, userAnswers, expandedExplanations, onToggleExplanation, onLocate }) {
  return (
    <section className="reading-review__answer-group reading-review__answer-group--ynng">
      <div className="reading-review__answer-range">Questions {group.startNumber} – {group.endNumber}</div>
      <div className="reading-review__choice-list">
        {group.questions?.map((question) => (
          <ReviewItem key={question.id} question={question} userAnswers={userAnswers} expandedExplanations={expandedExplanations} onToggleExplanation={onToggleExplanation} onLocate={onLocate} options={YES_NO_NOT_GIVEN_OPTIONS} />
        ))}
      </div>
    </section>
  );
}
