import { useMemo } from 'react';
import { getQuestionAnswerOptions } from '../../../../utils/readingTest';
import { getReviewAnswerData, getReviewCorrectAnswer, isCorrectResultValue, stripHtmlToText, getQuestionExplanation, getQuestionLocateText } from '../readingReviewUtils';

function MultipleChoiceReviewItem({ group, question, userAnswers, expandedExplanations, activeQuestionId, onToggleExplanation, onQuestionFocus, onLocate }) {
  const answerData = getReviewAnswerData(userAnswers, question);
  const userAnswer = stripHtmlToText(answerData.userAnswer || '');
  const correctAnswer = stripHtmlToText(answerData.correctAnswer || question?.correctAnswer || '');
  const isCorrect = isCorrectResultValue(answerData.isCorrect);
  const isUnanswered = !userAnswer;

  const questionOptions = getQuestionAnswerOptions(question, group.optionsWithContent || []);
  const selectedLetter = (userAnswer || '').toUpperCase().slice(0, 1);
  const correctLetter = (correctAnswer || '').toUpperCase().slice(0, 1);

  const isExpanded = Boolean(expandedExplanations[question.id]);
  const isActive = String(activeQuestionId ?? '') === String(question.id);
  const explanation = getQuestionExplanation(question);
  const locateText = getQuestionLocateText(question);

  return (
    <div className={`reading-review__mc-question-block ${isUnanswered ? 'is-unanswered' : isCorrect ? 'is-correct' : 'is-incorrect'} ${isExpanded ? 'is-expanded' : ''} ${isActive ? 'is-active' : ''}`}>
      <div className="reading-review__mc-question-head">
        <span className="reading-review__answer-number">{question.number}.</span>
        <div className="reading-review__mc-statuses">
          {isUnanswered ? (
            <span className="reading-review__mc-status-badge is-unanswered">Bỏ qua</span>
          ) : (
            <span className={`reading-review__mc-status-badge ${isCorrect ? 'is-correct' : 'is-incorrect'}`}>
              {isCorrect ? 'Đúng' : 'Sai'}
            </span>
          )}
        </div>
      </div>
      <div className="reading-review__mc-question-text" dangerouslySetInnerHTML={{ __html: question.content || '' }} />
      <div className="reading-review__mc-options">
        {questionOptions.map((option) => {
          const isSelected = option.letter.toUpperCase() === selectedLetter;
          const isCorrectOpt = option.letter.toUpperCase() === correctLetter;
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
      <div className="reading-review__mc-actions">
        {explanation && (
          <button type="button" className="reading-review__mc-action reading-review__mc-action--detail" onClick={() => { onQuestionFocus(question); onToggleExplanation(question.id); }}>
            {isExpanded ? 'Thu gọn' : 'Chi tiết'}
          </button>
        )}
        {!isCorrect && !isUnanswered && (
          <span className="reading-review__mc-correct-answer-inline">
            Đáp án đúng: <strong>{correctLetter}</strong>
          </span>
        )}
      </div>
      {isExpanded && (
        <div className="reading-review__mc-detail">
          {explanation ? <div dangerouslySetInnerHTML={{ __html: explanation }} /> : <p>Chưa có giải thích.</p>}
          {locateText && (
            <button type="button" className="reading-review__locate-button" onClick={(e) => { e.stopPropagation(); onLocate(locateText); }}>
              Locate
            </button>
          )}
        </div>
      )}
    </div>
  );
}

export default function MultipleChoiceReview({ group, userAnswers, expandedExplanations, activeQuestionId, onToggleExplanation, onQuestionFocus, onLocate }) {
  return (
    <section className="reading-review__answer-group reading-review__answer-group--multiple-choice">
      <div className="reading-review__answer-range">Questions {group.startNumber} – {group.endNumber}</div>
      <div className="reading-review__mc-list">
        {group.questions?.map((question) => (
          <MultipleChoiceReviewItem key={question.id} group={group} question={question} userAnswers={userAnswers} expandedExplanations={expandedExplanations} activeQuestionId={activeQuestionId} onToggleExplanation={onToggleExplanation} onQuestionFocus={onQuestionFocus} onLocate={onLocate} />
        ))}
      </div>
    </section>
  );
}
