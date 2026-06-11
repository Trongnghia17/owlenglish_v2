import { memo } from 'react';
import NoteCompletionQuestions from './question-types/NoteCompletionQuestions';

const getQuestionRange = (group) => {
  const firstNumber =
    group.startNumber ||
    group.questions?.[0]?.number ||
    '';

  const lastQuestion =
    group.questions?.[
      group.questions.length - 1
    ];

  const lastNumber =
    group.endNumber ||
    lastQuestion?.number ||
    firstNumber;

  if (!firstNumber) return '';

  return firstNumber === lastNumber
    ? String(firstNumber)
    : `${firstNumber}-${lastNumber}`;
};

const createDefaultInstructions = (group) => {
  const questionRange = getQuestionRange(group);
  const questionLabel = questionRange
    ? `Question ${questionRange}`
    : 'Question';

  return `
    <strong>${questionLabel}</strong><br />
    Complete the notes below.<br />
    Choose <strong>ONE WORD ONLY</strong> from the passage for each answer.<br />
    Write your answers in boxes ${questionRange || ''} on your answer sheet.
  `;
};

function ReadingNoteCompletionGroup({
  group,
  answers,
  onAnswerChange
}) {
  const instructionsHtml =
    group.instructions ||
    createDefaultInstructions(group);

  return (
    <section
      id={`question-group-${group.id}`}
      className="reading-test__note-completion"
    >
      <div
        className="reading-test__note-instructions"
        dangerouslySetInnerHTML={{
          __html: instructionsHtml
        }}
      />

      <NoteCompletionQuestions
        group={group}
        answers={answers}
        onAnswerChange={onAnswerChange}
      />
    </section>
  );
}

export default memo(ReadingNoteCompletionGroup);
