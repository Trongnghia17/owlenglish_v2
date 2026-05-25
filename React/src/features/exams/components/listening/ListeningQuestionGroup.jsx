import { memo } from 'react';
import ListeningAudioPlayer from './ListeningAudioPlayer';
import ListeningHtmlContent from './ListeningHtmlContent';
import ListeningQuestionRenderer from './ListeningQuestionRenderer';
import { containsInlinePlaceholders, isMultipleChoiceGroup } from '../../utils/listeningTest';

const getRangeText = (group) => {
  const firstNumber = group.startNumber || group.questions?.[0]?.number || '';
  const lastQuestion = group.questions?.[group.questions.length - 1];
  const lastNumber = group.endNumber || lastQuestion?.number || firstNumber;

  return firstNumber && lastNumber
    ? `Questions ${firstNumber} - ${lastNumber}`
    : 'Questions';
};

const getMultipleChoiceInstructions = (group) => {
  if (group.instructions) return group.instructions;

  const letters = group.options?.length ? group.options : ['A', 'B', 'C'];
  const letterText = letters.length > 1
    ? `${letters.slice(0, -1).join(', ')} or ${letters[letters.length - 1]}`
    : letters[0];

  return `Choose the correct letter, ${letterText}.`;
};

function ListeningQuestionGroup({
  group,
  currentPartTab,
  audioUrl,
  showAudio = false,
  showPartTitle = true,
  showGroupContent = true,
  answers,
  onAnswerChange
}) {
  const isMultipleChoice = isMultipleChoiceGroup(group);

  return (
    <div
      id={`question-group-${group.id}`}
      className={`listening-test__question-group ${isMultipleChoice ? 'listening-test__question-group--multiple-choice' : ''}`}
    >
      {showPartTitle && <h2>Listening Part {currentPartTab}</h2>}
      {showAudio && <ListeningAudioPlayer audioUrl={audioUrl} />}

      {isMultipleChoice && (
        <div className="listening-test__group-header">
          <h3>{getRangeText(group)}</h3>
          <div
            className="listening-test__group-instructions"
            dangerouslySetInnerHTML={{ __html: getMultipleChoiceInstructions(group) }}
          />
        </div>
      )}

      {!isMultipleChoice && group.instructions && (
        <div
          className="listening-test__group-instructions"
          dangerouslySetInnerHTML={{ __html: group.instructions }}
        />
      )}

      {showGroupContent && !isMultipleChoice && group.groupContent && !containsInlinePlaceholders(group.groupContent) && (
        <ListeningHtmlContent
          className="listening-test__group-content"
          html={group.groupContent}
        />
      )}

      <div className={`listening-test__questions-list ${isMultipleChoice ? 'listening-test__questions-list--multiple-choice' : ''}`}>
        <ListeningQuestionRenderer
          group={group}
          answers={answers}
          onAnswerChange={onAnswerChange}
        />
      </div>
    </div>
  );
}

export default memo(ListeningQuestionGroup);
