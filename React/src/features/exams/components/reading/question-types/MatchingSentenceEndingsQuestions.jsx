import { memo, useCallback, useMemo, useState } from 'react';

const normalizeLetter = (value) =>
  String(value ?? '').trim().toUpperCase().slice(0, 1);

function MatchingSentenceEndingsQuestions({ group, answers, onAnswerChange }) {
  const [activeLetter, setActiveLetter] = useState('');
  const optionTitle =
    !group.optionTitle ||
    group.optionTitle === 'Options' ||
    /sentence\s+endings?/i.test(group.optionTitle)
      ? 'List of ending'
      : group.optionTitle;

  const options = useMemo(
    () =>
      (group.optionsWithContent || []).map((option, index) => ({
        letter: normalizeLetter(option.letter) || String.fromCharCode(65 + index),
        content: option.content || ''
      })),
    [group.optionsWithContent]
  );

  const optionLetters = useMemo(
    () => new Set(options.map((option) => option.letter)),
    [options]
  );

  const selectedLetters = useMemo(
    () => new Set(group.questions.map((question) => normalizeLetter(answers[question.id])).filter(Boolean)),
    [answers, group.questions]
  );

  const assignAnswer = useCallback(
    (questionId, value) => {
      const normalized = normalizeLetter(value);

      if (!normalized) {
        onAnswerChange(questionId, '');
        return;
      }

      if (optionLetters.size > 0 && !optionLetters.has(normalized)) return;

      const previousQuestion = group.questions.find(
        (question) =>
          question.id !== questionId &&
          normalizeLetter(answers[question.id]) === normalized
      );

      if (previousQuestion) {
        onAnswerChange(previousQuestion.id, '');
      }

      onAnswerChange(questionId, normalized);
      setActiveLetter('');
    },
    [answers, group.questions, onAnswerChange, optionLetters]
  );

  return (
    <div className="reading-test__matching-sentence-endings">
      <aside className="reading-test__matching-sentence-endings-card reading-test__matching-sentence-endings-card--options">
        <h4 className="reading-test__matching-sentence-endings-card-title">
          {optionTitle}
        </h4>
        <div className="reading-test__matching-sentence-endings-options-grid">
          {options.map((option) => {
            const isActive = activeLetter === option.letter;
            const isUsed = selectedLetters.has(option.letter);

            return (
              <button
                key={`${option.letter}-${option.content}`}
                type="button"
                className={[
                  'reading-test__matching-sentence-endings-option',
                  isActive ? 'is-active' : '',
                  isUsed ? 'is-used' : ''
                ]
                  .filter(Boolean)
                  .join(' ')}
                aria-pressed={isActive}
                onClick={() => !isUsed && setActiveLetter(isActive ? '' : option.letter)}
              >
                <span className="reading-test__matching-sentence-endings-badge">
                  {option.letter}
                </span>
                <span
                  className="reading-test__matching-sentence-endings-option-text"
                  dangerouslySetInnerHTML={{ __html: option.content }}
                />
              </button>
            );
          })}
        </div>
      </aside>

      <section className="reading-test__matching-sentence-endings-card reading-test__matching-sentence-endings-card--questions">
        <div className="reading-test__matching-sentence-endings-question-list">
          {group.questions.map((question) => {
            const answer = normalizeLetter(answers[question.id]);

            return (
              <div
                key={question.id}
                className="reading-test__matching-sentence-endings-question-row"
              >
                <span className="reading-test__matching-sentence-endings-question-number">
                  {question.number}
                </span>
                <div
                  className="reading-test__matching-sentence-endings-question-text"
                  dangerouslySetInnerHTML={{ __html: question.content || '' }}
                />
                <label
                  className={[
                    'reading-test__matching-sentence-endings-dropzone',
                    answer ? 'is-filled' : '',
                    activeLetter && !answer ? 'is-ready' : ''
                  ]
                    .filter(Boolean)
                    .join(' ')}
                  onClick={() => {
                    if (activeLetter && !answer) {
                      assignAnswer(question.id, activeLetter);
                    }
                  }}
                >
                  <input
                    type="text"
                    inputMode="text"
                    maxLength={1}
                    value={answer}
                    placeholder="Type Answer"
                    aria-label={`Question ${question.number} answer`}
                    onChange={(event) => assignAnswer(question.id, event.target.value)}
                  />
                </label>
              </div>
            );
          })}
        </div>
      </section>
    </div>
  );
}

export default memo(MatchingSentenceEndingsQuestions);
