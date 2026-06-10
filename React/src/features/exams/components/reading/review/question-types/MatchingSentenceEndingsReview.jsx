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

const normalizeLetter = (value) =>
  String(value ?? '').trim().toUpperCase().slice(0, 1);

const normalizeText = (value) =>
  stripHtmlToText(value).toLowerCase();

function MatchingSentenceEndingReviewItem({
  group,
  question,
  userAnswers,
  expandedExplanations,
  activeQuestionId,
  onToggleExplanation,
  onQuestionFocus
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
        letter: normalizeLetter(option.letter) || String.fromCharCode(65 + index),
        content: option.content || ''
      })),
    [group.optionsWithContent]
  );

  const findOption = (value) => {
    const letter = normalizeLetter(value);
    const text = normalizeText(value);

    return (
      options.find((option) => option.letter === letter) ||
      options.find((option) => normalizeText(option.content) === text) ||
      null
    );
  };

  const userOption = findOption(userAnswer);
  const correctOption = findOption(correctAnswer);
  const userLetter = userOption?.letter || normalizeLetter(userAnswer);
  const correctLetter = correctOption?.letter || normalizeLetter(correctAnswer);
  const correctText = stripHtml(correctOption?.content || correctAnswer);
  const stateClass = isUnanswered ? 'is-unanswered' : isCorrect ? 'is-correct' : 'is-incorrect';
  const explanation = getQuestionExplanation(question);
  const locateText = getQuestionLocateText(question);
  const hasStructuredExplanation = /<(ul|ol|li)\b|vị trí|từ khoá|từ khóa|giải thích/i.test(explanation);

  const handleToggle = () => {
    onQuestionFocus(question);
    onToggleExplanation(question.id);
  };

  return (
    <div className={`reading-review__mse-row ${stateClass} ${isExpanded ? 'is-expanded' : ''} ${isActive ? 'is-active' : ''}`}>
      <div className="reading-review__mse-question-line">
        <span className="reading-review__mse-question-number">{question.number}</span>
        <div className="reading-review__mse-dropzone">
          <div className={`reading-review__mse-answer-box ${stateClass}`}>
            {userLetter || ''}
          </div>
        </div>
      </div>

      <div className="reading-review__mse-feedback">
        <div className="reading-review__mse-feedback-head">
          <span className={`reading-review__mse-status ${stateClass}`}>
            {isUnanswered ? 'Bỏ qua' : isCorrect ? 'Đúng' : 'Sai'}
          </span>
          <span className="reading-review__mse-correct-answer">
            Answer:{' '}
            <strong>
              {correctLetter}
              {correctText ? ` — ${correctText}` : ''}
            </strong>
          </span>
          <div className="reading-review__mse-feedback-action">
            <button
              type="button"
              className="reading-review__mse-detail-button"
              onClick={handleToggle}
              aria-expanded={isExpanded}
            >
              {isExpanded ? 'Thu gọn' : 'Chi tiết'}
            </button>
          </div>
        </div>

        {isExpanded && (
          <div className="reading-review__mse-detail">
            {locateText && (
              <>
                <ul className="reading-review__mse-detail-list">
                  <li><strong>Vị trí:</strong></li>
                </ul>
                <ul className="reading-review__mse-detail-list reading-review__mse-detail-list--nested">
                  <li>{locateText}</li>
                </ul>
              </>
            )}

            {explanation && hasStructuredExplanation ? (
              <div
                className="reading-review__mse-detail-text"
                dangerouslySetInnerHTML={{ __html: explanation }}
              />
            ) : explanation ? (
              <>
                <ul className="reading-review__mse-detail-list">
                  <li><strong>Giải thích đáp án:</strong></li>
                </ul>
                <ul className="reading-review__mse-detail-list reading-review__mse-detail-list--nested">
                  <li>
                    <div
                      className="reading-review__mse-detail-text"
                      dangerouslySetInnerHTML={{ __html: explanation }}
                    />
                  </li>
                </ul>
              </>
            ) : (
              !locateText && (
                <ul className="reading-review__mse-detail-list">
                  <li>Chưa có giải thích.</li>
                </ul>
              )
            )}
          </div>
        )}
      </div>
    </div>
  );
}

export default function MatchingSentenceEndingsReview({
  group,
  userAnswers,
  expandedExplanations,
  activeQuestionId,
  onToggleExplanation,
  onQuestionFocus
}) {
  return (
    <section className="reading-review__mse-answer-group">
      <div className="reading-review__mse-instructions">
        <p>
          <strong>Questions {group.startNumber}-{group.endNumber}</strong>
        </p>
      </div>

      <div className="reading-review__mse-card">
        <div className="reading-review__mse-card-head">Your answer</div>
        <div className="reading-review__mse-list">
          {group.questions?.map((question) => (
            <MatchingSentenceEndingReviewItem
              key={question.id}
              group={group}
              question={question}
              userAnswers={userAnswers}
              expandedExplanations={expandedExplanations}
              activeQuestionId={activeQuestionId}
              onToggleExplanation={onToggleExplanation}
              onQuestionFocus={onQuestionFocus}
            />
          ))}
        </div>
      </div>
    </section>
  );
}
