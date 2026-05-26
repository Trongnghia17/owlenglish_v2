import { memo } from 'react';
import {
  containsInlinePlaceholders,
  isFlowChartCompletionGroup,
  isChoiceAnswerQuestionType,
  isSentenceCompletionGroup,
  isShortAnswerGroup,
  isSummaryCompletionGroup,
  isTextAnswerQuestionType
} from '../../utils/listeningTest';
import ChoiceQuestions from './question-types/ChoiceQuestions';
import FlowChartCompletionQuestions from './question-types/FlowChartCompletionQuestions';
import MultipleChoiceQuestions from './question-types/MultipleChoiceQuestions';
import NoteCompletionQuestions from './question-types/NoteCompletionQuestions';
import SentenceCompletionQuestions from './question-types/SentenceCompletionQuestions';
import ShortAnswerQuestions from './question-types/ShortAnswerQuestions';
import ShortTextQuestions from './question-types/ShortTextQuestions';
import SummaryCompletionQuestions from './question-types/SummaryCompletionQuestions';

function ListeningQuestionRenderer({ group, answers, onAnswerChange }) {
  const questionType = (group.type || '').toLowerCase();

  if (questionType === 'note_completion') {
    return (
      <NoteCompletionQuestions
        group={group}
        answers={answers}
        onAnswerChange={onAnswerChange}
      />
    );
  }

  if (isShortAnswerGroup(group)) {
    return (
      <ShortAnswerQuestions
        group={group}
        answers={answers}
        onAnswerChange={onAnswerChange}
      />
    );
  }

  if (isFlowChartCompletionGroup(group)) {
    return (
      <FlowChartCompletionQuestions
        group={group}
        answers={answers}
        onAnswerChange={onAnswerChange}
      />
    );
  }

  if (isSummaryCompletionGroup(group)) {
    return (
      <SummaryCompletionQuestions
        group={group}
        answers={answers}
        onAnswerChange={onAnswerChange}
      />
    );
  }

  if (isSentenceCompletionGroup(group)) {
    return (
      <SentenceCompletionQuestions
        group={group}
        answers={answers}
        onAnswerChange={onAnswerChange}
      />
    );
  }

  if (containsInlinePlaceholders(group.groupContent) || isTextAnswerQuestionType(questionType)) {
    return (
      <ShortTextQuestions
        group={group}
        answers={answers}
        onAnswerChange={onAnswerChange}
      />
    );
  }

  if (isChoiceAnswerQuestionType(questionType)) {
    return (
      <MultipleChoiceQuestions
        group={group}
        answers={answers}
        onAnswerChange={onAnswerChange}
        showTips={questionType === 'multiple_choice'}
      />
    );
  }

  return (
    <ChoiceQuestions
      group={group}
      answers={answers}
      onAnswerChange={onAnswerChange}
    />
  );
}

export default memo(ListeningQuestionRenderer);
