import WritingAnswerSections from './WritingAnswerSections';
import WritingTaskHeader from './WritingTaskHeader';

const WritingTaskTwo = ({ group, answers, onAnswerChange }) => (
  <div
    id={`question-group-${group.id}`}
    className="writing-test__task writing-test__task--task-2"
  >
    <WritingTaskHeader group={group} />

    <div className="writing-test__task-left">
      {group.timeInstructionHtml && (
        <div
          className="writing-test__task-instruction"
          dangerouslySetInnerHTML={{ __html: group.timeInstructionHtml }}
        />
      )}

      <p className="writing-test__prompt-intro">{group.promptIntro}</p>

      {group.topicHtml && (
        <div className="writing-test__essay-topic">
          <div
            className="writing-test__prompt-body writing-test__prompt-body--task-2"
            dangerouslySetInnerHTML={{ __html: group.topicHtml }}
          />
        </div>
      )}

      <p className="writing-test__supporting-instruction">
        {group.supportingInstruction}
      </p>

      <div className="writing-test__word-target writing-test__word-target--task-2">
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

export default WritingTaskTwo;
