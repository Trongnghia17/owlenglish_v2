import { memo, useMemo, useState } from 'react';

const MATCHING_DRAG_TYPE = 'application/x-reading-headings-answer';
const normalizeLabel = (value) => String(value ?? '').trim().toLowerCase();
const displayLabel = (value) => String(value ?? '').trim().toUpperCase();

function MatchingHeadingsQuestions({ group, answers = {} }) {
  const [draggingLetter, setDraggingLetter] = useState('');

  const options = useMemo(
    () =>
      (group.optionsWithContent || []).map((opt, idx) => ({
        letter: opt.letter || String.fromCharCode(65 + idx),
        content: opt.content || '',
      })),
    [group.optionsWithContent]
  );

  const selectedLetters = useMemo(
    () => new Set(group.questions.map((q) => normalizeLabel(answers[q.id])).filter(Boolean)),
    [answers, group.questions]
  );

  return (
    <section className="reading-test__mh-heading-list">
      <h4 className="reading-test__mh-heading-list-title">List of Heading</h4>
      <div className="reading-test__mh-options">
        {options.map((opt) => {
          const isUsed = selectedLetters.has(normalizeLabel(opt.letter));
          return (
            <button
              key={`${opt.letter}-${opt.content}`}
              type="button"
              className={[
                'reading-test__mh-option-card',
                isUsed ? 'is-used' : '',
                draggingLetter === opt.letter ? 'is-dragging' : '',
              ]
                .filter(Boolean)
                .join(' ')}
              draggable={!isUsed}
              aria-disabled={isUsed}
              onDragStart={(event) => {
                if (isUsed) return;
                setDraggingLetter(opt.letter);
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData(MATCHING_DRAG_TYPE, opt.letter);
                event.dataTransfer.setData('text/plain', opt.letter);
              }}
              onDragEnd={() => setDraggingLetter('')}
            >
              <span className="reading-test__mh-option-letter">
                {displayLabel(opt.letter)}
              </span>
              <span
                className="reading-test__mh-option-text"
                dangerouslySetInnerHTML={{ __html: opt.content }}
              />
              <span className="reading-test__mh-option-handle" aria-hidden="true" />
            </button>
          );
        })}
      </div>
    </section>
  );
}

export default memo(MatchingHeadingsQuestions);
