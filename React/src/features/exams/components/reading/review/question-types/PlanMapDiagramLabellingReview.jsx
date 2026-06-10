import { useMemo } from 'react';
import { getReviewAnswerData, getReviewCorrectAnswer, isCorrectResultValue, stripHtmlToText, getQuestionExplanation, getQuestionLocateText } from '../readingReviewUtils';

const normalizeLetter = (v) => String(v ?? '').trim().toUpperCase().slice(0, 1);

function ReviewItem({ group, question, userAnswers, expandedExplanations, onToggleExplanation, onLocate }) {
  const answerData = getReviewAnswerData(userAnswers, question);
  const userAnswer = stripHtmlToText(answerData.userAnswer || '');
  const correctAnswer = getReviewCorrectAnswer(answerData, question);
  const isCorrect = isCorrectResultValue(answerData.isCorrect);
  const isUnanswered = !userAnswer;
  const isExpanded = Boolean(expandedExplanations[question.id]);
  const explanation = getQuestionExplanation(question);
  const locateText = getQuestionLocateText(question);

  return (
    <div className={`reading-review__text-row ${isUnanswered ? 'is-unanswered' : isCorrect ? 'is-correct' : 'is-incorrect'}`}>
      <span className="reading-review__answer-number">{question.number}.</span>
      <div className="reading-review__text-question-text" dangerouslySetInnerHTML={{ __html: question.content || '' }} />
      <div className={`reading-review__text-answer-field ${isUnanswered ? 'is-unanswered' : isCorrect ? 'is-correct' : 'is-incorrect'}`}>
        {normalizeLetter(userAnswer) || <em>(Chưa trả lời)</em>}
      </div>
      {!isCorrect && !isUnanswered && <div className="reading-review__text-correct-answer">Đáp án đúng: <strong>{correctAnswer}</strong></div>}
      {(explanation || locateText) && (
        <div className="reading-review__text-actions">
          {explanation && <button type="button" className="reading-review__text-action-btn" onClick={() => onToggleExplanation(question.id)}>{isExpanded ? 'Thu gọn' : 'Giải thích'}</button>}
          {locateText && <button type="button" className="reading-review__text-action-btn" onClick={() => onLocate(locateText)}>Locate</button>}
        </div>
      )}
      {isExpanded && explanation && <div className="reading-review__text-detail" dangerouslySetInnerHTML={{ __html: explanation }} />}
    </div>
  );
}

export default function PlanMapDiagramLabellingReview({ group, userAnswers, expandedExplanations, activeQuestionId, onToggleExplanation, onQuestionFocus, onLocate }) {
  return (
    <section className="reading-review__answer-group reading-review__answer-group--plan-map">
      <div className="reading-review__answer-range">Questions {group.startNumber} – {group.endNumber}</div>
      <div className="reading-review__text-list">
        {group.questions?.map((q) => (
          <ReviewItem key={q.id} group={group} question={q} userAnswers={userAnswers} expandedExplanations={expandedExplanations} onToggleExplanation={onToggleExplanation} onLocate={onLocate} />
        ))}
      </div>
    </section>
  );
}
