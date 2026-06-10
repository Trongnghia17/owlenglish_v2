import { memo, useMemo } from 'react';
import TableCompletionQuestions from './question-types/TableCompletionQuestions';

const PLACEHOLDER_PATTERN =
  /\{\{\s*([\w-]+)\s*\}\}/g;

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
  <span class="reading-test__table-placeholder">
    <span class="reading-test__table-placeholder-number">${question.number}</span>
    <span class="reading-test__table-placeholder-line">.......</span>
  </span>
`;

const createFallbackTableHtml = (
  questions = []
) =>
  questions
    .map(
      (question) => `
        <p>
          ${question.content || ''}
          ${createPlaceholderHtml(question)}
        </p>
      `
    )
    .join('');

const findQuestionForToken = (
  questions,
  token,
  usedQuestionIds
) => {
  const normalizedToken = String(
    token ?? ''
  ).trim();

  if (!normalizedToken) return null;

  return questions.find((question) => {
    if (usedQuestionIds.has(question.id)) {
      return false;
    }

    return [
      question.id,
      question.sourceQuestionId,
      question.number
    ].some(
      (value) =>
        String(value ?? '') ===
        normalizedToken
    );
  });
};

const createTableHtml = (
  content,
  questions = []
) => {
  if (!content) {
    return createFallbackTableHtml(questions);
  }

  let questionIndex = 0;
  const usedQuestionIds = new Set();

  return content.replace(
    PLACEHOLDER_PATTERN,
    (match, token) => {
      const matchedQuestion =
        findQuestionForToken(
          questions,
          token,
          usedQuestionIds
        );

      const sequentialQuestion =
        questions
          .slice(questionIndex)
          .find(
            (question) =>
              !usedQuestionIds.has(
                question.id
              )
          );

      const question =
        matchedQuestion ||
        sequentialQuestion;

      questionIndex += 1;

      if (!question) return match;

      usedQuestionIds.add(question.id);
      return createPlaceholderHtml(question);
    }
  );
};

const createDefaultInstructions = (group) => `
  <strong>${getRangeText(group)}</strong><br />
  Complete the table below.<br />
  Write <strong>ONE WORD ONLY</strong> for each answer.
`;

function ReadingTableCompletionGroup({
  group,
  answers,
  onAnswerChange
}) {
  const tableHtml = useMemo(
    () =>
      createTableHtml(
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
      className="reading-test__table-completion"
    >
      <div
        className="reading-test__table-instructions"
        dangerouslySetInnerHTML={{
          __html: instructionsHtml
        }}
      />

      <div className="reading-test__table-workspace">
        <div className="reading-test__table-card">
          <div
            className="reading-test__table-content"
            dangerouslySetInnerHTML={{
              __html: tableHtml
            }}
          />
        </div>

        <TableCompletionQuestions
          group={group}
          answers={answers}
          onAnswerChange={onAnswerChange}
        />
      </div>
    </section>
  );
}

export default memo(ReadingTableCompletionGroup);
