import { useMemo } from 'react';
import { stripHtml } from '../../../listening/question-types/textQuestionUtils';
import {
  getReviewAnswerData,
  getReviewCorrectAnswer,
  isCorrectResultValue,
  stripHtmlToText,
  getQuestionExplanation,
  getQuestionLocateText
} from '../readingReviewUtils';

const normalizeLetter = (v) =>
  String(v ?? '').trim().toUpperCase().slice(0, 1);

const displayLetter = (v) =>
  String(v ?? '').trim().toUpperCase();

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
  const answerData = getReviewAnswerData(userAnswers, question);

  const userAnswer = stripHtmlToText(answerData.userAnswer || '');
  const correctAnswer = getReviewCorrectAnswer(answerData, question);

  const isCorrect = isCorrectResultValue(answerData.isCorrect);
  const isUnanswered = !userAnswer;

  const options = useMemo(
    () =>
      (group.optionsWithContent || []).map((option, index) => ({
        letter: option.letter || String.fromCharCode(65 + index),
        content: option.content || ''
      })),
    [group.optionsWithContent]
  );

  const userLetter = normalizeLetter(userAnswer);
  const correctLetter = normalizeLetter(correctAnswer);

  const matchedOption = options.find(
    (option) => option.letter === userLetter
  );

  const correctOption = options.find(
    (option) => option.letter === correctLetter
  );

  const isExpanded = Boolean(expandedExplanations[question.id]);
  const isActive =
    String(activeQuestionId ?? '') === String(question.id);

  const explanation = getQuestionExplanation(question);
  const locateText = getQuestionLocateText(question);

  const stateClass = isUnanswered
    ? 'is-unanswered'
    : isCorrect
    ? 'is-correct'
    : 'is-incorrect';

  const handleToggle = () => {
    onQuestionFocus(question);
    onToggleExplanation(question.id);
  };

  return (
    <div className='reading-review__matching-list'>
    <div
      className={`reading-review__matching-row ${stateClass} ${
        isExpanded ? 'is-expanded' : ''
      } ${isActive ? 'is-active' : ''}`}
    >
      <div className="reading-review__matching-question-line">
        <span className="reading-review__matching-question-number">
          {question.number}
        </span>

        <div
          className={`reading-review__matching-answer-box ${stateClass}`}
        >
          {userLetter ? displayLetter(userLetter) : ''}
        </div>
      </div>

      <div className="reading-review__matching-feedback">
        <div className="reading-review__matching-feedback-head">
          <span
            className={`reading-review__matching-status-badge ${stateClass}`}
          >
            {isUnanswered
              ? 'Bỏ qua'
              : isCorrect
              ? 'Đúng'
              : 'Sai'}
          </span>

          <span className="reading-review__matching-correct-answer">
            Answer:{' '}
            <strong>
              {correctOption
                ? stripHtml(correctOption.content)
                : displayLetter(correctLetter)}
            </strong>
          </span>

          <button
            type="button"
            className="reading-review__matching-detail-btn"
            onClick={handleToggle}
            aria-expanded={isExpanded}
          >
            {isExpanded ? 'Thu gọn' : 'Chi tiết'}
          </button>
        </div>

        {isExpanded && (
          <div className="reading-review__matching-detail">
            <div className="reading-review__matching-detail-title">
              Giải thích:
            </div>

            {explanation ? (
              <div
                className="reading-review__matching-detail-text"
                dangerouslySetInnerHTML={{
                  __html: explanation
                }}
              />
            ) : (
              <p className="reading-review__matching-detail-text">
                Chưa có giải thích.
              </p>
            )}

            {locateText && (
              <button
                type="button"
                className="reading-review__locate-button"
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
    </div>
  );
}

export default function MatchingInformationReview({
  group,
  userAnswers,
  expandedExplanations,
  activeQuestionId,
  onToggleExplanation,
  onQuestionFocus,
  onLocate
}) {
  return (
    <section className="reading-review__answer-group reading-review__answer-group--matching">
      <div className="reading-review__answer-range">
        Questions {group.startNumber}-{group.endNumber}
      </div>

      <div className="reading-review__matching-card">
        <div className="reading-review__matching-card-head">Your answer</div>
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