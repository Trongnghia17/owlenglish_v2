import { getQuestionAnswerOptions } from '../../../../utils/readingTest';
import { getReviewAnswerData, isCorrectResultValue, stripHtmlToText, getQuestionExplanation, getQuestionLocateText } from '../readingReviewUtils';
import './MultipleChoiceReview.css';

const getAnswerLetters = (answer) =>
  stripHtmlToText(answer || '')
    .split(/[\s,;|/]+/)
    .map((value) => value.trim().toUpperCase().charAt(0))
    .filter((value) => /^[A-Z]$/.test(value));

function MultipleChoiceReviewItem({ group, question, userAnswers, expandedExplanations, activeQuestionId, onToggleExplanation, onQuestionFocus, onLocate }) {
  const answerData = getReviewAnswerData(userAnswers, question);
  const userAnswer = stripHtmlToText(answerData.userAnswer || '');
  const correctAnswer = stripHtmlToText(answerData.correctAnswer || question?.correctAnswer || '');
  const isCorrect = isCorrectResultValue(answerData.isCorrect);
  const isUnanswered = !userAnswer;

  const questionOptions = getQuestionAnswerOptions(question, group.optionsWithContent || []);
  const selectedLetters = getAnswerLetters(userAnswer);
  const correctLetters = getAnswerLetters(correctAnswer);

  const isExpanded = Boolean(expandedExplanations[question.id]);
  const isActive = String(activeQuestionId ?? '') === String(question.id);
  const explanation = getQuestionExplanation(question);
  const locateText = getQuestionLocateText(question);
  const hasDetailActions = Boolean(explanation || locateText);

  return (
    <div className={`reading-review__mc-question-block ${isUnanswered ? 'is-unanswered' : isCorrect ? 'is-correct' : 'is-incorrect'} ${isExpanded ? 'is-expanded' : ''} ${isActive ? 'is-active' : ''}`}>
      <div className="reading-review__mc-question-head">
        <div className="reading-review__mc-number-row">
          <span className="reading-review__mc-number">{question.number}</span>
        </div>
        <div className="reading-review__mc-question-text" dangerouslySetInnerHTML={{ __html: question.content || '' }} />
      </div>
      <div className="reading-review__mc-options">
        {questionOptions.map((option) => {
          const optionLetter = option.letter.toUpperCase();
          const isSelected = selectedLetters.includes(optionLetter);
          const isCorrectOpt = correctLetters.includes(optionLetter);
          let stateClass = '';
          if (isCorrectOpt) stateClass = 'is-correct';
          else if (isSelected && !isCorrect) stateClass = 'is-incorrect';

          return (
            <div key={option.letter} className={`reading-review__mc-option ${stateClass} ${isSelected ? 'is-selected' : ''}`}>
              <span className="reading-review__mc-option-letter">{option.letter}</span>
              <span className="reading-review__mc-option-text" dangerouslySetInnerHTML={{ __html: option.content }} />
            </div>
          );
        })}
      </div>

      {hasDetailActions && (
        <div className="reading-review__mc-detail-panel">
          <div className="reading-review__mc-detail-head">
            {explanation && (
              <button type="button" className="reading-review__mc-detail-button" onClick={() => { onQuestionFocus(question); onToggleExplanation(question.id); }}>
                Chi tiết
              </button>
            )}
            {locateText && (
              <button type="button" className="reading-review__mc-locate-button" onClick={(e) => { e.stopPropagation(); onLocate(locateText); }}>
                <span>Vị trí: {locateText}</span>
                <svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
                  <path d="M11 11.6a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" stroke="currentColor" strokeWidth="1.5" />
                  <path d="M17.1 9.35c0 4.55-6.1 8.8-6.1 8.8s-6.1-4.25-6.1-8.8a6.1 6.1 0 1 1 12.2 0Z" stroke="currentColor" strokeWidth="1.5" />
                </svg>
              </button>
            )}
          </div>
          {isExpanded && (
            <div className="reading-review__mc-detail">
              {explanation ? <div dangerouslySetInnerHTML={{ __html: explanation }} /> : <p>Chưa có giải thích.</p>}
            </div>
          )}
        </div>
      )}
      {!hasDetailActions && (
        <div className="reading-review__mc-detail-panel">
          <button type="button" className="reading-review__mc-detail-button" onClick={() => { onQuestionFocus(question); onToggleExplanation(question.id); }}>
            Chi tiết
          </button>
          {isExpanded && (
            <div className="reading-review__mc-detail">
              <p>Chưa có giải thích.</p>
            </div>
          )}
        </div>
      )}
    </div>
  );
}

export default function MultipleChoiceReview({ group, userAnswers, expandedExplanations, activeQuestionId, onToggleExplanation, onQuestionFocus, onLocate }) {
  return (
    <section className="reading-review__answer-group reading-review__answer-group--multiple-choice">
      <div className="reading-review__answer-range">
        <div>Questions {group.startNumber}-{group.endNumber}</div>
        {group.instructions && (
          <div
            className="reading-review__mc-instructions"
            dangerouslySetInnerHTML={{ __html: group.instructions }}
          />
        )}
      </div>
      <div className="reading-review__mc-list">
        {group.questions?.map((question) => (
          <MultipleChoiceReviewItem key={question.id} group={group} question={question} userAnswers={userAnswers} expandedExplanations={expandedExplanations} activeQuestionId={activeQuestionId} onToggleExplanation={onToggleExplanation} onQuestionFocus={onQuestionFocus} onLocate={onLocate} />
        ))}
      </div>
    </section>
  );
}
