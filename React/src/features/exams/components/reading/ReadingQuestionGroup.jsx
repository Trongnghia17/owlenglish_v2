import { memo } from 'react';
import ReadingHtmlContent from './ReadingHtmlContent';
import ReadingQuestionRenderer from './ReadingQuestionRenderer';

import {
  containsInlinePlaceholders,
  isReadingDiagramLabelCompletionGroup,
  isReadingFlowChartCompletionGroup,
  isReadingMatchingFeaturesGroup,
  isReadingMatchingHeadingsGroup,
  isReadingMatchingInformationGroup,
  isReadingMatchingSentenceEndingsGroup,
  isReadingMultipleChoiceGroup,
  isReadingNoteCompletionGroup,
  isReadingSentenceCompletionGroup,
  isReadingShortAnswerGroup,
  isReadingSummaryCompletionGroup,
  isReadingTableCompletionGroup,
  isReadingTrueFalseNotGivenGroup,
  isReadingYesNoNotGivenGroup
} from '../../utils/readingTest';

const getRangeText = (group) => {
  const firstNumber =
    group.startNumber ||
    group.questions?.[0]?.number ||
    '';

  const lastQuestion =
    group.questions?.[
      group.questions.length - 1
    ];

  const lastNumber =
    group.endNumber ||
    lastQuestion?.number ||
    firstNumber;

  return firstNumber && lastNumber
    ? `Questions ${firstNumber}-${lastNumber}`
    : 'Questions';
};

const stripHtml = (content) =>
  String(content ?? '')
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

const instructionIncludesQuestionRange = (
  instructions = ''
) =>
  /questions?\s+\d+/i.test(
    stripHtml(instructions)
  );

const getMultipleChoiceInstructions = (
  group
) => {
  if (group.instructions)
    return group.instructions;

  const letters = group.options?.length
    ? group.options
    : ['A', 'B', 'C'];

  const letterText =
    letters.length > 1
      ? `${letters
          .slice(0, -1)
          .join(', ')} or ${
          letters[
            letters.length - 1
          ]
        }`
      : letters[0];

  return `Choose the correct letter, ${letterText}.`;
};

const getTrueFalseInstructions = (
  group
) =>
  group.instructions ||
  'Do the following statements agree with the information given in the passage?<br /><strong>TRUE</strong> if the statement agrees with the information<br /><strong>FALSE</strong> if the statement contradicts the information<br /><strong>NOT GIVEN</strong> if there is no information on this';

const getYesNoInstructions = (
  group
) =>
  group.instructions ||
  'Do the following statements agree with the views of the writer?<br /><strong>YES</strong> if the statement agrees with the views<br /><strong>NO</strong> if the statement contradicts the views<br /><strong>NOT GIVEN</strong> if it is impossible to say what the writer thinks';

const getMatchingInstructions = (
  group
) => {
  if (group.instructions)
    return group.instructions;

  const letters = group.options?.length
    ? group.options
    : ['A', 'B', 'C'];

  const letterText =
    letters.length > 1
      ? `${letters[0]}-${
          letters[
            letters.length - 1
          ]
        }`
      : letters[0];

  return `Choose the correct letter, <strong>${letterText}</strong>, next to ${getRangeText(
    group
  )}.`;
};

const getSentenceCompletionInstructions =
  (group) =>
    group.instructions ||
    'Complete the sentences below.<br /><strong>Write NO MORE THAN TWO WORDS AND/OR A NUMBER</strong> for each answer.';

const getSummaryCompletionInstructions =
  (group) =>
    group.instructions ||
    'Complete the summary below.<br /><strong>Write NO MORE THAN TWO WORDS AND/OR A NUMBER</strong> for each answer.';

const getNoteCompletionInstructions = (
  group
) =>
  group.instructions ||
  'Complete the notes below.<br /><strong>Write NO MORE THAN TWO WORDS AND/OR A NUMBER</strong> for each answer.';

const getTableCompletionInstructions =
  (group) =>
    group.instructions ||
    'Complete the table below.<br /><strong>Write NO MORE THAN TWO WORDS AND/OR A NUMBER</strong> for each answer.';

const getFlowChartInstructions = (
  group
) =>
  group.instructions ||
  'Complete the flow-chart below.<br /><strong>Write NO MORE THAN TWO WORDS AND/OR A NUMBER</strong> for each answer.';

const getDiagramInstructions = (
  group
) =>
  group.instructions ||
  'Label the diagram below.<br /><strong>Write NO MORE THAN TWO WORDS AND/OR A NUMBER</strong> for each answer.';

const getShortAnswerInstructions = (
  group
) =>
  group.instructions ||
  'Answer the questions below.<br /><strong>Write NO MORE THAN TWO WORDS AND/OR A NUMBER</strong> for each answer.';

function ReadingQuestionGroup({
  group,
  showGroupContent = true,
  answers,
  onAnswerChange
}) {
  const isMultipleChoice =
    isReadingMultipleChoiceGroup(
      group
    );

  const isTrueFalse =
    isReadingTrueFalseNotGivenGroup(
      group
    );

  const isYesNo =
    isReadingYesNoNotGivenGroup(
      group
    );

  const isMatching =
    isReadingMatchingInformationGroup(
      group
    ) ||
    isReadingMatchingHeadingsGroup(
      group
    ) ||
    isReadingMatchingFeaturesGroup(
      group
    ) ||
    isReadingMatchingSentenceEndingsGroup(
      group
    );

  const isSentenceCompletion =
    isReadingSentenceCompletionGroup(
      group
    );

  const isSummaryCompletion =
    isReadingSummaryCompletionGroup(
      group
    );

  const isNoteCompletion =
    isReadingNoteCompletionGroup(
      group
    );

  const isTableCompletion =
    isReadingTableCompletionGroup(
      group
    );

  const isFlowChartCompletion =
    isReadingFlowChartCompletionGroup(
      group
    );

  const isDiagramCompletion =
    isReadingDiagramLabelCompletionGroup(
      group
    );

  const isShortAnswer =
    isReadingShortAnswerGroup(
      group
    );

  const shouldShowRange =
    !instructionIncludesQuestionRange(
      group.instructions
    );

  return (
    <div
      id={`question-group-${group.id}`}
      className={`reading-test__question-group
      ${
        isMultipleChoice
          ? 'reading-test__question-group--multiple-choice'
          : ''
      }
      ${
        isTrueFalse || isYesNo
          ? 'reading-test__question-group--choice'
          : ''
      }
      ${
        isMatching
          ? 'reading-test__question-group--matching'
          : ''
      }
      ${
        isSentenceCompletion
          ? 'reading-test__question-group--sentence-completion'
          : ''
      }
      ${
        isSummaryCompletion
          ? 'reading-test__question-group--summary-completion'
          : ''
      }
      ${
        isNoteCompletion
          ? 'reading-test__question-group--note-completion'
          : ''
      }
      ${
        isTableCompletion
          ? 'reading-test__question-group--table-completion'
          : ''
      }
      ${
        isFlowChartCompletion
          ? 'reading-test__question-group--flow-chart'
          : ''
      }
      ${
        isDiagramCompletion
          ? 'reading-test__question-group--diagram'
          : ''
      }
      ${
        isShortAnswer
          ? 'reading-test__question-group--short-answer'
          : ''
      }
    `}
    >
      {(isMultipleChoice ||
        isMatching ||
        isTrueFalse ||
        isYesNo ||
        isSentenceCompletion ||
        isSummaryCompletion ||
        isNoteCompletion ||
        isTableCompletion ||
        isFlowChartCompletion ||
        isDiagramCompletion ||
        isShortAnswer) && (
        <div className="reading-test__group-header">
          {shouldShowRange && (
            <h3>
              {getRangeText(group)}
            </h3>
          )}

          <div
            className="reading-test__group-instructions"
            dangerouslySetInnerHTML={{
              __html:
                isMultipleChoice
                  ? getMultipleChoiceInstructions(
                      group
                    )
                  : isTrueFalse
                  ? getTrueFalseInstructions(
                      group
                    )
                  : isYesNo
                  ? getYesNoInstructions(
                      group
                    )
                  : isMatching
                  ? getMatchingInstructions(
                      group
                    )
                  : isSentenceCompletion
                  ? getSentenceCompletionInstructions(
                      group
                    )
                  : isSummaryCompletion
                  ? getSummaryCompletionInstructions(
                      group
                    )
                  : isNoteCompletion
                  ? getNoteCompletionInstructions(
                      group
                    )
                  : isTableCompletion
                  ? getTableCompletionInstructions(
                      group
                    )
                  : isFlowChartCompletion
                  ? getFlowChartInstructions(
                      group
                    )
                  : isDiagramCompletion
                  ? getDiagramInstructions(
                      group
                    )
                  : isShortAnswer
                  ? getShortAnswerInstructions(
                      group
                    )
                  : group.instructions
            }}
          />
        </div>
      )}

      {!isMultipleChoice &&
        !isMatching &&
        !isTrueFalse &&
        !isYesNo &&
        !isSentenceCompletion &&
        !isSummaryCompletion &&
        !isNoteCompletion &&
        !isTableCompletion &&
        !isFlowChartCompletion &&
        !isDiagramCompletion &&
        !isShortAnswer &&
        group.instructions && (
          <div
            className="reading-test__group-instructions"
            dangerouslySetInnerHTML={{
              __html:
                group.instructions
            }}
          />
        )}

      {showGroupContent &&
        group.groupContent &&
        !containsInlinePlaceholders(
          group.groupContent
        ) && (
          <ReadingHtmlContent
            className="reading-test__group-content"
            html={
              group.groupContent
            }
          />
        )}

      <div
        className={`reading-test__questions-list
        ${
          isMultipleChoice
            ? 'reading-test__questions-list--multiple-choice'
            : ''
        }
        ${
          isMatching
            ? 'reading-test__questions-list--matching'
            : ''
        }
      `}
      >
        <ReadingQuestionRenderer
          group={group}
          answers={answers}
          onAnswerChange={
            onAnswerChange
          }
        />
      </div>
    </div>
  );
}

export default memo(
  ReadingQuestionGroup
);
