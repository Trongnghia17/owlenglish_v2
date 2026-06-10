import { memo, useCallback, useMemo, useState } from 'react';

const normalizeLetter = (value) =>
  String(value ?? '').trim().toUpperCase().slice(0, 1);

function MatchingFeaturesQuestions({ group, answers, onAnswerChange }) {
  const [activeLetter, setActiveLetter] = useState('');
  const optionTitle =
    !group.optionTitle || group.optionTitle === 'Options'
      ? 'List of People'
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

  const assignAnswer = useCallback(
    (questionId, value) => {
      const normalized = normalizeLetter(value);

      if (!normalized) {
        onAnswerChange(questionId, '');
        return;
      }

      if (optionLetters.size > 0 && !optionLetters.has(normalized)) return;

      onAnswerChange(questionId, normalized);
      setActiveLetter('');
    },
    [onAnswerChange, optionLetters]
  );

  return (
    <div className="reading-test__matching-features">
      <aside className="reading-test__matching-features-card reading-test__matching-features-card--people">
        <h4 className="reading-test__matching-features-card-title">
          {optionTitle}
        </h4>
        <div className="reading-test__matching-features-people-grid">
          {options.map((option) => {
            const isActive = activeLetter === option.letter;

            return (
              <button
                key={`${option.letter}-${option.content}`}
                type="button"
                className={`reading-test__matching-features-person ${isActive ? 'is-active' : ''}`}
                aria-pressed={isActive}
                onClick={() => setActiveLetter(isActive ? '' : option.letter)}
              >
                <span className="reading-test__matching-features-badge">
                  {option.letter}
                </span>
                <span
                  className="reading-test__matching-features-person-name"
                  dangerouslySetInnerHTML={{ __html: option.content }}
                />
              </button>
            );
          })}
        </div>
      </aside>

      <section className="reading-test__matching-features-card reading-test__matching-features-card--questions">
        <div className="reading-test__matching-features-question-list">
          {group.questions.map((question) => {
            const answer = normalizeLetter(answers[question.id]);

            return (
              <div
                key={question.id}
                className="reading-test__matching-features-question-row"
              >
                <span className="reading-test__matching-features-question-number">
                  {question.number}
                </span>
                <div
                  className="reading-test__matching-features-question-text"
                  dangerouslySetInnerHTML={{ __html: question.content || '' }}
                />
                <label
                  className={[
                    'reading-test__matching-features-dropzone',
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

export default memo(MatchingFeaturesQuestions);
