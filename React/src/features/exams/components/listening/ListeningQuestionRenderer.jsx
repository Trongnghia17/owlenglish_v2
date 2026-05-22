import { memo } from 'react';
import { containsInlinePlaceholders } from '../../utils/listeningTest';
import ChoiceQuestions from './question-types/ChoiceQuestions';
import MultipleChoiceQuestions from './question-types/MultipleChoiceQuestions';
import ShortTextQuestions from './question-types/ShortTextQuestions';

function ListeningQuestionRenderer({ group, answers, onAnswerChange }) {
  const questionType = (group.type || '').toLowerCase();

  if (containsInlinePlaceholders(group.groupContent) || questionType === 'short_text') {
    return (
      <ShortTextQuestions
        group={group}
        answers={answers}
        onAnswerChange={onAnswerChange}
      />
    );
  }

  if (questionType === 'multiple_choice') {
    return (
      <MultipleChoiceQuestions
        group={group}
        answers={answers}
        onAnswerChange={onAnswerChange}
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
