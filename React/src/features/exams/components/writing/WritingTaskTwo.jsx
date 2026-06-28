import WritingAnswerSections from './WritingAnswerSections';
import WritingTaskHeader from './WritingTaskHeader';
import { memo, useMemo } from 'react';
const processTimeInstructionHtml = (html) => {
  if (!html) return '';

  const parser = new DOMParser();
  const doc = parser.parseFromString(html, 'text/html');

  const paragraphs = doc.querySelectorAll('p');

  for (const p of paragraphs) {
    if (p.querySelector('strong')) {
      p.classList.add('first-paragraph-has-strong');
      break; // dừng ngay sau khi add lần đầu
    }
  }

  return doc.body.innerHTML;
};
const WritingTaskTwo = ({ group, answers, onAnswerChange }) => {
  const timeInstructionHtml = useMemo(
    () => processTimeInstructionHtml(group.timeInstructionHtml),
    [group.timeInstructionHtml]
  );
  return (
    <div
    id={`question-group-${group.id}`}
    className="writing-test__task writing-test__task--task-2"
  >
    <WritingTaskHeader group={group} />

    <div className="writing-test__task-left">
      <div className="writing-test__answer-header">
          You should spend about 40 minutes on this task.
        </div>
      {group.timeInstructionHtml && (
        <div
          className="writing-test__task-instruction"
          dangerouslySetInnerHTML={{ __html: timeInstructionHtml }}
        />
      )}

      {/* <p className="writing-test__prompt-intro">{group.promptIntro}</p> */}

      {/* {group.topicHtml && (
        <div className="writing-test__essay-topic">
          <div
            className="writing-test__prompt-body writing-test__prompt-body--task-2"
            dangerouslySetInnerHTML={{ __html: group.topicHtml }}
          />
        </div>
      )} */}

      {/* <p className="writing-test__supporting-instruction">
        {group.supportingInstruction}
      </p> */}

      {/* <div className="writing-test__word-target writing-test__word-target--task-2">
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

export default memo(WritingTaskTwo);
