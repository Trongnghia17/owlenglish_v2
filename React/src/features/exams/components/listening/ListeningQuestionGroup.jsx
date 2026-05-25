import { memo } from 'react';
import ListeningAudioPlayer from './ListeningAudioPlayer';
import ListeningHtmlContent from './ListeningHtmlContent';
import ListeningQuestionRenderer from './ListeningQuestionRenderer';
import { containsInlinePlaceholders, isMultipleChoiceGroup } from '../../utils/listeningTest';

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

      

      {showGroupContent && group.groupContent && !containsInlinePlaceholders(group.groupContent) && (
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
