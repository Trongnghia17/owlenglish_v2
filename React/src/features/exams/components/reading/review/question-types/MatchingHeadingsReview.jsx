import { useMemo } from 'react';
import { stripHtml } from '../../../listening/question-types/textQuestionUtils';
import {
  getQuestionExplanation,
  getQuestionLocateText,
  getReviewAnswerData,
  getReviewCorrectAnswer,
  isCorrectResultValue,
  stripHtmlToText
} from '../readingReviewUtils';

const normalizeLabel = (value) => String(value ?? '').trim().toLowerCase();
const displayLabel = (value) => String(value ?? '').trim().toUpperCase();

function MatchingHeadingReviewItem({
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
  const isExpanded = Boolean(expandedExplanations[question.id]);
  const isActive = String(activeQuestionId ?? '') === String(question.id);

  const options = useMemo(
    () =>
      (group.optionsWithContent || []).map((option, index) => ({
        letter: option.letter || String.fromCharCode(65 + index),
        content: option.content || ''
      })),
    [group.optionsWithContent]
  );

  const userOption = options.find((option) => normalizeLabel(option.letter) === normalizeLabel(userAnswer));
  const correctOption =
    options.find((option) => normalizeLabel(option.letter) === normalizeLabel(correctAnswer)) ||
    options.find((option) => normalizeLabel(stripHtml(option.content)) === normalizeLabel(correctAnswer));

  const userLabel = userOption?.letter || userAnswer;
  const correctLabel = correctOption?.letter || correctAnswer;
  const correctText = stripHtml(correctOption?.content || correctAnswer);
  const explanation = getQuestionExplanation(question);
  const locateText = getQuestionLocateText(question);
  const stateClass = isUnanswered ? 'is-unanswered' : isCorrect ? 'is-correct' : 'is-incorrect';

  const handleToggle = () => {
    onQuestionFocus(question);
    onToggleExplanation(question.id);
  };

  return (
    <div className={`reading-review__mh-row ${stateClass} ${isExpanded ? 'is-expanded' : ''} ${isActive ? 'is-active' : ''}`}>
      <div className="reading-review__mh-question-line">
        <span className="reading-review__mh-question-number">{question.number}</span>
        <div className={`reading-review__mh-answer-box ${stateClass}`}>
          {userLabel ? displayLabel(userLabel) : ''}
        </div>
      </div>

      <div className="reading-review__mh-feedback">
        <div className="reading-review__mh-feedback-head">
          <span className={`reading-review__mh-status ${stateClass}`}>
            {isUnanswered ? 'Bỏ qua' : isCorrect ? 'Đúng' : 'Sai'}
          </span>
          <span className="reading-review__mh-correct-answer">
            Answer: <strong>{correctText || displayLabel(correctLabel)}</strong>
          </span>
          <button
            type="button"
            className="reading-review__mh-detail-button"
            onClick={handleToggle}
            aria-expanded={isExpanded}
          >
            {isExpanded ? 'Thu gọn' : 'Chi tiết'}
          </button>
        </div>

        {isExpanded && (
          <div className="reading-review__mh-detail">
            <div className="reading-review__mh-detail-title">Giải thích:</div>
            {explanation ? (
              <div
                className="reading-review__mh-detail-text"
                dangerouslySetInnerHTML={{ __html: explanation }}
              />
            ) : (
              <p className="reading-review__mh-detail-text">Chưa có giải thích.</p>
            )}
            {locateText && (
              <button
                type="button"
                className="reading-review__mh-locate-button"
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

export default function MatchingHeadingsReview({
  group,
  userAnswers,
  expandedExplanations,
  activeQuestionId,
  onToggleExplanation,
  onQuestionFocus,
  onLocate
}) {
  const instructionText = stripHtmlToText(group.instructions || '');
  const shouldShowRange = !/questions?\s+\d+/i.test(instructionText);

  return (
    <section className="reading-review__mh-answer-group">
      <div className="reading-review__mh-instructions">
        {shouldShowRange && (
          <p>
            <strong>Questions {group.startNumber}-{group.endNumber}</strong>
          </p>
        )}
        {group.instructions && (
          <div dangerouslySetInnerHTML={{ __html: group.instructions }} />
        )}
      </div>

      <div className="reading-review__mh-card">
        <div className="reading-review__mh-card-head">Your answer</div>
        <div className="reading-review__mh-list">
          {group.questions?.map((question) => (
            <MatchingHeadingReviewItem
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
      </div>
    </section>
  );
}
