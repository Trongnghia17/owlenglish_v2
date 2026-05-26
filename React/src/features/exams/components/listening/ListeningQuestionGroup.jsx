import { memo } from 'react';
import ListeningAudioPlayer from './ListeningAudioPlayer';
import ListeningHtmlContent from './ListeningHtmlContent';
import ListeningQuestionRenderer from './ListeningQuestionRenderer';
import {
  containsInlinePlaceholders,
  isFlowChartCompletionGroup,
  isMatchingGroup,
  isMultipleChoiceGroup,
  isSentenceCompletionGroup,
  isShortAnswerGroup,
  isSummaryCompletionGroup
} from '../../utils/listeningTest';

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

const stripHtml = (content) =>
  String(content ?? '').replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ').replace(/\s+/g, ' ').trim();

const instructionIncludesQuestionRange = (instructions = '') =>
  /questions?\s+\d+/i.test(stripHtml(instructions));

const getShortAnswerInstructions = (group) =>
  group.instructions ||
  'Complete the notes below.<br />Write <strong>NO MORE THAN THREE WORDS AND/OR A NUMBER</strong> for each answer.';

const getSentenceCompletionInstructions = (group) =>
  group.instructions ||
  'Complete the notes below.<br />Write <strong>ONLY ONE WORD</strong> for each answer.';

const getSummaryCompletionInstructions = (group) =>
  group.instructions ||
  'Complete the summary below.<br />Write <strong>ONLY ONE WORD</strong> for each answer.';

const getFlowChartCompletionInstructions = (group) =>
  group.instructions ||
  'Complete the flow-chart below.<br />Choose answers from the box and write the correct letter next to each question.';

const getMatchingInstructions = (group) => {
  if (group.instructions) return group.instructions;

  const letters = group.options?.length ? group.options : ['A', 'B', 'C'];
  const letterText = letters.length > 1
    ? `${letters[0]}-${letters[letters.length - 1]}`
    : letters[0];

  return `Choose answers from the box and write the correct letter, <strong>${letterText}</strong>, next to ${getRangeText(group)}.`;
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
  const isMatching = isMatchingGroup(group);
  const isFlowChartCompletion = isFlowChartCompletionGroup(group);
  const isShortAnswer = isShortAnswerGroup(group);
  const isSummaryCompletion = isSummaryCompletionGroup(group);
  const isSentenceCompletion = isSentenceCompletionGroup(group);
  const shouldShowMatchingRange = isMatching && !instructionIncludesQuestionRange(group.instructions);
  const shouldShowShortAnswerRange = isShortAnswer && !instructionIncludesQuestionRange(group.instructions);
  const shouldShowFlowChartCompletionRange = isFlowChartCompletion && !instructionIncludesQuestionRange(group.instructions);
  const shouldShowSummaryCompletionRange = isSummaryCompletion && !instructionIncludesQuestionRange(group.instructions);
  const shouldShowSentenceCompletionRange = isSentenceCompletion && !instructionIncludesQuestionRange(group.instructions);

  return (
    <div
      id={`question-group-${group.id}`}
      className={`listening-test__question-group ${isMultipleChoice ? 'listening-test__question-group--multiple-choice' : ''} ${isMatching ? 'listening-test__question-group--matching' : ''} ${isFlowChartCompletion ? 'listening-test__question-group--flow-chart' : ''} ${isShortAnswer ? 'listening-test__question-group--short-answer' : ''} ${isSummaryCompletion ? 'listening-test__question-group--summary-completion' : ''} ${isSentenceCompletion ? 'listening-test__question-group--sentence-completion' : ''}`}
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

      {isMatching && (
        <div className="listening-test__group-header listening-test__group-header--matching">
          {shouldShowMatchingRange && <h3>{getRangeText(group)}</h3>}
          <div
            className="listening-test__group-instructions"
            dangerouslySetInnerHTML={{ __html: getMatchingInstructions(group) }}
          />
        </div>
      )}

      {isShortAnswer && (
        <div className="listening-test__group-header listening-test__group-header--short-answer">
          {shouldShowShortAnswerRange && <h3>{getRangeText(group)}</h3>}
          <div
            className="listening-test__group-instructions"
            dangerouslySetInnerHTML={{ __html: getShortAnswerInstructions(group) }}
          />
        </div>
      )}

      {isFlowChartCompletion && (
        <div className="listening-test__group-header listening-test__group-header--flow-chart">
          {shouldShowFlowChartCompletionRange && <h3>{getRangeText(group)}</h3>}
          <div
            className="listening-test__group-instructions"
            dangerouslySetInnerHTML={{ __html: getFlowChartCompletionInstructions(group) }}
          />
        </div>
      )}

      {isSentenceCompletion && (
        <div className="listening-test__group-header listening-test__group-header--sentence-completion">
          {shouldShowSentenceCompletionRange && <h3>{getRangeText(group)}</h3>}
          <div
            className="listening-test__group-instructions"
            dangerouslySetInnerHTML={{ __html: getSentenceCompletionInstructions(group) }}
          />
        </div>
      )}

      {isSummaryCompletion && (
        <div className="listening-test__group-header listening-test__group-header--summary-completion">
          {shouldShowSummaryCompletionRange && <h3>{getRangeText(group)}</h3>}
          <div
            className="listening-test__group-instructions"
            dangerouslySetInnerHTML={{ __html: getSummaryCompletionInstructions(group) }}
          />
        </div>
      )}

      {!isMultipleChoice && !isMatching && !isFlowChartCompletion && !isShortAnswer && !isSummaryCompletion && !isSentenceCompletion && group.instructions && (
        <div
          className="listening-test__group-instructions"
          dangerouslySetInnerHTML={{ __html: group.instructions }}
        />
      )}

      {showGroupContent && !isMultipleChoice && !isMatching && !isFlowChartCompletion && !isSummaryCompletion && !isSentenceCompletion && group.groupContent && !containsInlinePlaceholders(group.groupContent) && (
        <ListeningHtmlContent
          className="listening-test__group-content"
          html={group.groupContent}
        />
      )}

      <div className={`listening-test__questions-list ${isMultipleChoice ? 'listening-test__questions-list--multiple-choice' : ''} ${isMatching ? 'listening-test__questions-list--matching' : ''} ${isFlowChartCompletion ? 'listening-test__questions-list--flow-chart' : ''} ${isShortAnswer ? 'listening-test__questions-list--short-answer' : ''} ${isSummaryCompletion ? 'listening-test__questions-list--summary-completion' : ''} ${isSentenceCompletion ? 'listening-test__questions-list--sentence-completion' : ''}`}>
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
