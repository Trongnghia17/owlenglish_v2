import { memo, useMemo } from 'react';
import FlowChartCompletionQuestions from './question-types/FlowChartCompletionQuestions';

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
  <span class="reading-test__flow-chart-placeholder">
    <span class="reading-test__flow-chart-placeholder-number">${question.number}</span>
    <span class="reading-test__flow-chart-placeholder-line">.......</span>
  </span>
`;

const createFallbackFlowHtml = (
  questions = []
) =>
  questions
    .map(
      (question, index) => `
        <p>
          ${question.content || ''}
          ${createPlaceholderHtml(question)}
        </p>
        ${
          index < questions.length - 1
            ? '<p>&darr;</p>'
            : ''
        }
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

const createFlowHtml = (
  content,
  questions = []
) => {
  if (!content) {
    return createFallbackFlowHtml(questions);
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
  Complete the flow-chart below.<br />
  Write <strong>NO MORE THAN TWO WORDS</strong> for each answer.
`;

function ReadingFlowChartCompletionGroup({
  group,
  answers,
  onAnswerChange
}) {
  const flowHtml = useMemo(
    () =>
      createFlowHtml(
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
      className="reading-test__flow-chart-completion"
    >
      <div
        className="reading-test__flow-chart-instructions"
        dangerouslySetInnerHTML={{
          __html: instructionsHtml
        }}
      />

      <div className="reading-test__flow-chart-workspace">
        <div className="reading-test__flow-chart-card">
          <div
            className="reading-test__flow-chart-content"
            dangerouslySetInnerHTML={{
              __html: flowHtml
            }}
          />
        </div>

        <FlowChartCompletionQuestions
          group={group}
          answers={answers}
          onAnswerChange={onAnswerChange}
        />
      </div>
    </section>
  );
}

export default memo(
  ReadingFlowChartCompletionGroup
);
