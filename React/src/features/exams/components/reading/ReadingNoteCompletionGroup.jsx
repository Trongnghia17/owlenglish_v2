import { memo, useMemo } from 'react';
import NoteCompletionQuestions from './question-types/NoteCompletionQuestions';

const PLACEHOLDER_PATTERN =
  /\{\{\s*([a-zA-Z0-9_-]+)\s*\}\}/g;

const getRangeText = (group) => {
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

  return firstNumber && lastNumber
    ? `Questions ${firstNumber} - ${lastNumber}`
    : 'Questions';
};

const createPlaceholderHtml = (question) => `
  <span class="reading-test__note-placeholder">
    <span class="reading-test__note-placeholder-number">${question.number}</span>
    <span class="reading-test__note-placeholder-line">.......</span>
  </span>
`;

const createFallbackNoteHtml = (
  questions = []
) =>
  questions
    .map(
      (question) => `
        <div class="reading-test__note-line">
          ${question.content || ''}
          ${createPlaceholderHtml(question)}
        </div>
      `
    )
    .join('');

const createNoteHtml = (
  content,
  questions = []
) => {
  if (!content) {
    return createFallbackNoteHtml(questions);
  }

  let questionIndex = 0;

  return content.replace(
    PLACEHOLDER_PATTERN,
    (match) => {
      const question =
        questions[questionIndex];
      questionIndex += 1;

      return question
        ? createPlaceholderHtml(question)
        : match;
    }
  );
};

const createDefaultInstructions = (group) => `
  <strong>${getRangeText(group)}</strong><br />
  Complete the notes below.<br />
  Write <strong>ONE WORD ONLY</strong> for each answer.
`;

function ReadingNoteCompletionGroup({
  group,
  answers,
  onAnswerChange
}) {
  const noteHtml = useMemo(
    () =>
      createNoteHtml(
        group.groupContent,
        group.questions
      ),
    [group.groupContent, group.questions]
  );

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

      <div className="reading-test__note-workspace">
        <div className="reading-test__note-card">
          <div
            className="reading-test__note-content"
            dangerouslySetInnerHTML={{
              __html: noteHtml
            }}
          />
        </div>

        <NoteCompletionQuestions
          group={group}
          answers={answers}
          onAnswerChange={onAnswerChange}
        />
      </div>
    </section>
  );
}

export default memo(ReadingNoteCompletionGroup);
