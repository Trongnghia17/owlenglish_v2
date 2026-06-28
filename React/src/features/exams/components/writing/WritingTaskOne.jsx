import WritingAnswerSections from './WritingAnswerSections';
import WritingTaskHeader from './WritingTaskHeader';
import { memo, useMemo } from 'react';

const processTimeInstructionHtml = (html) => {
  if (!html) return '';

  const parser = new DOMParser();
  const doc = parser.parseFromString(html, 'text/html');
  const strongTags = doc.querySelectorAll('strong');

  if (strongTags.length > 2) {
    strongTags[0].classList.add('first-strong-highlight');
  }

  return doc.body.innerHTML;
};

const WritingTaskOne = ({ group, answers, onAnswerChange }) => {
  const timeInstructionHtml = useMemo(
    () => processTimeInstructionHtml(group.timeInstructionHtml),
    [group.timeInstructionHtml]
  );

  console.count("WritingTaskOne render");
  return (
    <div
      id={`question-group-${group.id}`}
      className="writing-test__task writing-test__task--task-1"
    >
      <WritingTaskHeader group={group} />

      <div className="writing-test__task-left">

        <div className="writing-test__answer-header">
          You should spend about 20 minutes on this task.
        </div>

        {group.timeInstructionHtml && (
          <div
            className="writing-test__task-instruction"
            dangerouslySetInnerHTML={{
              __html: processTimeInstructionHtml(group.timeInstructionHtml),
            }}
          />
        )}

        {/* {group.promptHtml && (
        <div
          className="writing-test__prompt-body"
          dangerouslySetInnerHTML={{ __html: group.promptHtml }}
        />
      )} */}

        {/* <div className="writing-test__word-target">
        {group.wordTargetInstruction}
      </div> */}
      </div>

      <WritingAnswerSections
        group={group}
        answers={answers}
        onAnswerChange={onAnswerChange}
      />
    </div>
  );
};

export default memo(WritingTaskOne);
