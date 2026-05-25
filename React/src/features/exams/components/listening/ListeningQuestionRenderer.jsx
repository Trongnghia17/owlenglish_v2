import { memo } from 'react';
import {
  containsInlinePlaceholders,
  isChoiceAnswerQuestionType,
  isTextAnswerQuestionType
} from '../../utils/listeningTest';
import ChoiceQuestions from './question-types/ChoiceQuestions';
import MultipleChoiceQuestions from './question-types/MultipleChoiceQuestions';
import NoteCompletionQuestions from './question-types/NoteCompletionQuestions';
import ShortTextQuestions from './question-types/ShortTextQuestions';

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
