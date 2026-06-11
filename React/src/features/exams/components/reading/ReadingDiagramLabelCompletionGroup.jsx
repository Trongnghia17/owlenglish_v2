import { memo } from 'react';
import DiagramLabelCompletionQuestions from './question-types/DiagramLabelCompletionQuestions';

const getRangeText = (group) => {
  const firstNumber = group.startNumber || group.questions?.[0]?.number || '';
  const lastQuestion = group.questions?.[group.questions.length - 1];
  const lastNumber = group.endNumber || lastQuestion?.number || firstNumber;

  return firstNumber && lastNumber ? `Questions ${firstNumber} - ${lastNumber}` : 'Questions';
};

const createDefaultInstructions = (group) => `
  <strong>${getRangeText(group)}</strong><br />
  Label the diagram below.<br />
  Choose <strong>ONE WORD</strong> from the passage for each answer.
`;

function ReadingDiagramLabelCompletionGroup({ group, answers, onAnswerChange }) {
  const instructionsHtml = group.instructions || createDefaultInstructions(group);

  return (
    <section
      id={`question-group-${group.id}`}
      className="reading-test__diagram-completion"
    >
      <div
        className="reading-test__diagram-instructions"
        dangerouslySetInnerHTML={{ __html: instructionsHtml }}
      />

      <DiagramLabelCompletionQuestions
        group={group}
        answers={answers}
        onAnswerChange={onAnswerChange}
      />
    </section>
  );
}

export default memo(ReadingDiagramLabelCompletionGroup);
