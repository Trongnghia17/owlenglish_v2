import { memo, useMemo, useState, useCallback } from 'react';
import { stripHtml } from '../../listening/question-types/textQuestionUtils';

const MATCHING_DRAG_TYPE = 'application/x-reading-features-answer';
const normalizeLetter = (value) => String(value ?? '').trim().toUpperCase().slice(0, 1);

function MatchingFeaturesQuestions({ group, answers, onAnswerChange }) {
  const [draggingLetter, setDraggingLetter] = useState('');
  const [activeLetter, setActiveLetter] = useState('');
  const [dropTargetId, setDropTargetId] = useState(null);

  const options = useMemo(
    () =>
      (group.optionsWithContent || []).map((opt, idx) => ({
        letter: opt.letter || String.fromCharCode(65 + idx),
        content: opt.content || '',
      })),
    [group.optionsWithContent]
  );

  const selectedLetters = useMemo(
    () => new Set(group.questions.map((q) => normalizeLetter(answers[q.id])).filter(Boolean)),
    [answers, group.questions]
  );

  const assignAnswer = useCallback(
    (questionId, letter) => {
      const normalized = normalizeLetter(letter);
      if (!normalized) return;
      const prev = group.questions.find((q) => q.id !== questionId && normalizeLetter(answers[q.id]) === normalized);
      if (prev) onAnswerChange(prev.id, '');
      onAnswerChange(questionId, normalized);
      setActiveLetter('');
      setDropTargetId(null);
    },
    [group.questions, answers, onAnswerChange]
  );

  const handleDragStart = (e, option) => {
    setDraggingLetter(option.letter);
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData(MATCHING_DRAG_TYPE, option.letter);
    e.dataTransfer.setData('text/plain', option.letter);
  };

  const handleDrop = (e, questionId) => {
    e.preventDefault();
    e.stopPropagation();
    const letter = e.dataTransfer.getData(MATCHING_DRAG_TYPE) || e.dataTransfer.getData('text/plain');
    assignAnswer(questionId, letter);
  };

  return (
    <div className="reading-test__matching-layout">
      {/* Features / options panel */}
      <aside className="reading-test__matching-card reading-test__matching-card--options">
        <h4 className="reading-test__matching-card-title">{group.optionTitle || 'Features'}</h4>
        <div className="reading-test__matching-options-list">
          {options.map((opt) => {
            const isUsed = selectedLetters.has(opt.letter);
            const isActive = activeLetter === opt.letter;
            return (
              <button
                key={opt.letter}
                type="button"
                className={[
                  'reading-test__matching-option',
                  isUsed ? 'is-used' : '',
                  isActive ? 'is-active' : '',
                  draggingLetter === opt.letter ? 'is-dragging' : '',
                ]
                  .filter(Boolean)
                  .join(' ')}
                draggable={!isUsed}
                aria-pressed={isActive}
                aria-disabled={isUsed}
                onClick={() => !isUsed && setActiveLetter(isActive ? '' : opt.letter)}
                onDragStart={(e) => !isUsed && handleDragStart(e, opt)}
                onDragEnd={() => {
                  setDraggingLetter('');
                  setDropTargetId(null);
                }}
              >
                <span className="reading-test__matching-option-letter">{opt.letter}</span>
                <span
                  className="reading-test__matching-option-text"
                  dangerouslySetInnerHTML={{ __html: opt.content }}
                />
              </button>
            );
          })}
        </div>
      </aside>

      {/* Questions column */}
      <section className="reading-test__matching-card">
        <h4 className="reading-test__matching-card-title">Questions</h4>
        <div className="reading-test__matching-question-list">
          {group.questions.map((question) => {
            const answer = normalizeLetter(answers[question.id]);
            const answerContent = options.find((o) => o.letter === answer)?.content || '';
            const isDropTarget = dropTargetId === question.id;
            return (
              <div
                key={question.id}
                className={`reading-test__matching-question-row ${answer ? 'is-filled' : ''}`}
              >
                <span className="reading-test__matching-question-number">{question.number}</span>
                <div
                  className="reading-test__matching-question-text"
                  dangerouslySetInnerHTML={{ __html: question.content || '' }}
                />
                <div
                  className={`reading-test__matching-dropzone ${isDropTarget ? 'is-drop-target' : ''} ${activeLetter && !answer ? 'is-ready' : ''}`}
                  role="button"
                  tabIndex={0}
                  onClick={() => activeLetter && assignAnswer(question.id, activeLetter)}
                  onKeyDown={(e) => {
                    if ((e.key === 'Enter' || e.key === ' ') && activeLetter) {
                      e.preventDefault();
                      assignAnswer(question.id, activeLetter);
                    }
                  }}
                  onDragEnter={(e) => {
                    e.preventDefault();
                    setDropTargetId(question.id);
                  }}
                  onDragOver={(e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    setDropTargetId(question.id);
                  }}
                  onDragLeave={(e) => {
                    if (e.currentTarget.contains(e.relatedTarget)) return;
                    setDropTargetId(null);
                  }}
                  onDrop={(e) => handleDrop(e, question.id)}
                >
                  {answer ? (
                    <span className="reading-test__matching-answer-chip">
                      <span className="reading-test__matching-answer-letter">{answer}</span>
                      <span className="reading-test__matching-answer-text">
                        {stripHtml(answerContent)}
                      </span>
                      <button
                        type="button"
                        className="reading-test__matching-answer-clear"
                        aria-label={`Clear answer ${question.number}`}
                        onClick={(e) => {
                          e.stopPropagation();
                          onAnswerChange(question.id, '');
                        }}
                      />
                    </span>
                  ) : (
                    <span className="reading-test__matching-placeholder">
                      Drop answer here
                    </span>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      </section>
    </div>
  );
}

export default memo(MatchingFeaturesQuestions);
