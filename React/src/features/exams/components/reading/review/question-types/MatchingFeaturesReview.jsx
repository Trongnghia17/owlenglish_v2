import { useMemo } from 'react';
import { stripHtml } from '../../../listening/question-types/textQuestionUtils';
import { getReviewAnswerData, getReviewCorrectAnswer, isCorrectResultValue, stripHtmlToText, getQuestionExplanation, getQuestionLocateText } from '../readingReviewUtils';

const normalizeLetter = (v) => String(v ?? '').trim().toUpperCase().slice(0, 1);

function MatchingReviewItem({ group, question, userAnswers, expandedExplanations, activeQuestionId, onToggleExplanation, onQuestionFocus, onLocate, label }) {
  const answerData = getReviewAnswerData(userAnswers, question);
  const userAnswer = stripHtmlToText(answerData.userAnswer || '');
  const correctAnswer = getReviewCorrectAnswer(answerData, question);
  const isCorrect = isCorrectResultValue(answerData.isCorrect);
  const isUnanswered = !userAnswer;

  const options = useMemo(
    () => (group.optionsWithContent || []).map((o, i) => ({ letter: o.letter || String.fromCharCode(65 + i), content: o.content || '' })),
    [group.optionsWithContent]
  );

  const userLetter = normalizeLetter(userAnswer);
  const correctLetter = normalizeLetter(correctAnswer);
  const matchedOption = options.find(o => o.letter === userLetter);
  const correctOption = options.find(o => o.letter === correctLetter);

  const isExpanded = Boolean(expandedExplanations[question.id]);
  const explanation = getQuestionExplanation(question);
  const locateText = getQuestionLocateText(question);

  return (
    <div className={`reading-review__matching-row ${isUnanswered ? 'is-unanswered' : isCorrect ? 'is-correct' : 'is-incorrect'} ${isExpanded ? 'is-expanded' : ''}`}>
      <div className="reading-review__matching-question-card">
        <span className="reading-review__answer-number">{question.number}.</span>
        <div className="reading-review__matching-question-text" dangerouslySetInnerHTML={{ __html: question.content || '' }} />
        <div className={`reading-review__matching-answer-card ${isUnanswered ? 'is-unanswered' : isCorrect ? 'is-correct' : 'is-incorrect'}`}>
          {userLetter ? (
            <>
              <span className="reading-review__matching-answer-letter">{userLetter}</span>
              {matchedOption && <span className="reading-review__matching-answer-text">{stripHtml(matchedOption.content)}</span>}
            </>
          ) : (
            <span className="reading-review__matching-placeholder">Chưa trả lời</span>
          )}
        </div>
      </div>
      <div className="reading-review__matching-feedback">
        <div className="reading-review__matching-feedback-line">
          <span className={`reading-review__matching-status-badge ${isUnanswered ? 'is-unanswered' : isCorrect ? 'is-correct' : 'is-incorrect'}`}>
            {isUnanswered ? 'Bỏ qua' : isCorrect ? 'Đúng' : 'Sai'}
          </span>
          {!isCorrect && (
            <span className="reading-review__matching-correct-answer">
              Đáp án: <strong>{correctLetter}{correctOption ? ` — ${stripHtml(correctOption.content)}` : ''}</strong>
            </span>
          )}
          <button type="button" className="reading-review__matching-detail-btn" onClick={(e) => { e.stopPropagation(); onQuestionFocus(question); onToggleExplanation(question.id); }} aria-expanded={isExpanded}>
            {isExpanded ? 'Thu gọn' : 'Chi tiết'}
          </button>
        </div>
        {isExpanded && (
          <div className="reading-review__matching-detail">
            {explanation ? <div dangerouslySetInnerHTML={{ __html: explanation }} /> : <p>Chưa có giải thích.</p>}
            {locateText && (
              <button type="button" className="reading-review__locate-button" onClick={(e) => { e.stopPropagation(); onLocate(locateText); }}>Locate</button>
            )}
          </div>
        )}
      </div>
    </div>
  );
}

export default function MatchingFeaturesReview({ group, userAnswers, expandedExplanations, activeQuestionId, onToggleExplanation, onQuestionFocus, onLocate }) {
  return (
    <section className="reading-review__answer-group reading-review__answer-group--matching">
      <div className="reading-review__answer-range">Questions {group.startNumber} – {group.endNumber}</div>
      <div className="reading-review__matching-card">
        {group.questions?.map((q) => (
          <MatchingReviewItem key={q.id} group={group} question={q} userAnswers={userAnswers} expandedExplanations={expandedExplanations} activeQuestionId={activeQuestionId} onToggleExplanation={onToggleExplanation} onQuestionFocus={onQuestionFocus} onLocate={onLocate} />
        ))}
      </div>
    </section>
  );
}
