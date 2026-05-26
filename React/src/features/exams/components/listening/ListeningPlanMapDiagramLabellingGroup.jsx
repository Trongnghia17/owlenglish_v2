import { memo } from 'react';
import ListeningAudioPlayer from './ListeningAudioPlayer';
import ListeningHtmlContent from './ListeningHtmlContent';
import PlanMapDiagramLabellingQuestions from './question-types/PlanMapDiagramLabellingQuestions';

const getRangeText = (group) => {
  const firstNumber = group.startNumber || group.questions?.[0]?.number || '';
  const lastQuestion = group.questions?.[group.questions.length - 1];
  const lastNumber = group.endNumber || lastQuestion?.number || firstNumber;

  return firstNumber && lastNumber
    ? `Questions ${firstNumber} - ${lastNumber}`
    : 'Questions';
};

const createDefaultInstructions = (group) => `
  <strong>${getRangeText(group)}</strong><br />
  Label the map below.<br />
  Write the correct letter, A-I, next to ${getRangeText(group)}.
`;

function ListeningPlanMapDiagramLabellingGroup({
  group,
  currentPartTab,
  audioUrl,
  showPartTitle = true,
  showAudio = false,
  answers,
  onAnswerChange
}) {
  const instructionsHtml = group.instructions || createDefaultInstructions(group);

  return (
    <section
      id={`question-group-${group.id}`}
      className="listening-test__map-labelling"
    >
      {showPartTitle && <h2>Listening Part {currentPartTab}</h2>}
      {showAudio && <ListeningAudioPlayer audioUrl={audioUrl} />}

      <div
        className="listening-test__map-instructions"
        dangerouslySetInnerHTML={{ __html: instructionsHtml }}
      />

      <div className="listening-test__map-workspace">
        <div className="listening-test__map-card">
          <ListeningHtmlContent
            className="listening-test__map-content"
            html={group.groupContent}
          />
        </div>

        <PlanMapDiagramLabellingQuestions
          group={group}
          answers={answers}
          onAnswerChange={onAnswerChange}
        />
      </div>
    </section>
  );
}

export default memo(ListeningPlanMapDiagramLabellingGroup);
