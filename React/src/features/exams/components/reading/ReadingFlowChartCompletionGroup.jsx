import { memo } from 'react';
import FlowChartCompletionQuestions from './question-types/FlowChartCompletionQuestions';

const getRangeText = (group) => {
  const firstNumber = group.startNumber || group.questions?.[0]?.number || '';
  const lastQuestion = group.questions?.[group.questions.length - 1];
  const lastNumber = group.endNumber || lastQuestion?.number || firstNumber;

  return firstNumber && lastNumber ? `Questions ${firstNumber} - ${lastNumber}` : 'Questions';
};

const createDefaultInstructions = (group) => `
  <strong>${getRangeText(group)}</strong><br />
  Complete the flow-chart below.<br />
  Choose <strong>NO MORE THAN TWO WORDS</strong> from the text for each answer.
`;

function ReadingFlowChartCompletionGroup({ group, answers, onAnswerChange }) {
  const instructionsHtml = group.instructions || createDefaultInstructions(group);

  return (
    <section
      id={`question-group-${group.id}`}
      className="reading-test__flow-chart-completion"
    >
      <div
        className="reading-test__flow-chart-instructions"
        dangerouslySetInnerHTML={{ __html: instructionsHtml }}
      />

      <FlowChartCompletionQuestions
        group={group}
        answers={answers}
        onAnswerChange={onAnswerChange}
      />
    </section>
  );
}

export default memo(ReadingFlowChartCompletionGroup);
