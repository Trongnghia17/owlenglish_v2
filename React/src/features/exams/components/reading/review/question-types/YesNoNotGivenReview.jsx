import { useMemo } from 'react';
import { getReviewAnswerData, getReviewCorrectAnswer, isCorrectResultValue, stripHtmlToText, getQuestionExplanation, getQuestionLocateText } from '../readingReviewUtils';

function ReviewItem({ group, question, userAnswers, expandedExplanations, activeQuestionId, onToggleExplanation, onQuestionFocus, onLocate, options }) {
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
        <span className="reading-review__answer-number">{question.number}.</span>
        <span className={`reading-review__choice-badge ${isUnanswered ? 'is-unanswered' : isCorrect ? 'is-correct' : 'is-incorrect'}`}>
          {isUnanswered ? 'Bỏ qua' : isCorrect ? 'Đúng' : 'Sai'}
        </span>
      </div>
      <div className="reading-review__choice-question-text" dangerouslySetInnerHTML={{ __html: question.content || '' }} />
      <div className="reading-review__choice-options">
        {options.map((opt) => {
          const isSelected = opt.toUpperCase() === userAnswer.toUpperCase();
          const isCorrectOpt = opt.toUpperCase() === correctAnswer.toUpperCase();
          let stateClass = '';
          if (isCorrectOpt) stateClass = 'is-correct';
          else if (isSelected && !isCorrect) stateClass = 'is-incorrect';
          return (
            <span key={opt} className={`reading-review__choice-option ${stateClass} ${isSelected ? 'is-selected' : ''}`}>
              {opt}
            </span>
          );
        })}
      </div>
      {!isCorrect && !isUnanswered && (
        <div className="reading-review__choice-correct-answer">Đáp án đúng: <strong>{correctAnswer}</strong></div>
      )}
      {(explanation || locateText) && (
        <div className="reading-review__choice-actions">
          {explanation && (
            <button type="button" className="reading-review__choice-action-btn" onClick={() => onToggleExplanation(question.id)}>
              {isExpanded ? 'Thu gọn' : 'Giải thích'}
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

export default function YesNoNotGivenReview({ group, userAnswers, expandedExplanations, activeQuestionId, onToggleExplanation, onQuestionFocus, onLocate }) {
  const options = group.optionsWithContent?.length
    ? group.optionsWithContent.map(o => o.content || o.letter).filter(Boolean)
    : ['Yes', 'No', 'Not Given'];
  return (
    <section className="reading-review__answer-group reading-review__answer-group--ynng">
      <div className="reading-review__answer-range">Questions {group.startNumber} – {group.endNumber}</div>
      <div className="reading-review__choice-list">
        {group.questions?.map((question) => (
          <ReviewItem key={question.id} group={group} question={question} userAnswers={userAnswers} expandedExplanations={expandedExplanations} activeQuestionId={activeQuestionId} onToggleExplanation={onToggleExplanation} onQuestionFocus={onQuestionFocus} onLocate={onLocate} options={options} />
        ))}
      </div>
    </section>
  );
}
