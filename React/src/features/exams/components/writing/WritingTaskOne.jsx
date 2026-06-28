import WritingAnswerSections from './WritingAnswerSections';
import WritingTaskHeader from './WritingTaskHeader';

const WritingTaskOne = ({ group, answers, onAnswerChange }) => (
  <div
    id={`question-group-${group.id}`}
    className="writing-test__task writing-test__task--task-1"
  >
    <WritingTaskHeader group={group} />

    <div className="writing-test__task-left">
      {group.timeInstructionHtml && (
        <div
          className="writing-test__task-instruction"
          dangerouslySetInnerHTML={{ __html: group.timeInstructionHtml }}
        />
      )}

      {group.promptHtml && (
        <div
          className="writing-test__prompt-body"
          dangerouslySetInnerHTML={{ __html: group.promptHtml }}
        />
      )}

      <div className="writing-test__word-target">
        {group.wordTargetInstruction}
      </div>
    </div>

    <WritingAnswerSections
      group={group}
      answers={answers}
      onAnswerChange={onAnswerChange}
    />
  </div>
);

export default WritingTaskOne;
