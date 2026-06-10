import { memo } from 'react';
import {
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
  isReadingYesNoNotGivenGroup,
  isReadingTextAnswerQuestionType,
  isReadingChoiceAnswerQuestionType,
} from '../../utils/readingTest';
import MultipleChoiceQuestions from './question-types/MultipleChoiceQuestions';
import TrueFalseNotGivenQuestions from './question-types/TrueFalseNotGivenQuestions';
import YesNoNotGivenQuestions from './question-types/YesNoNotGivenQuestions';
import MatchingInformationQuestions from './question-types/MatchingInformationQuestions';
import MatchingHeadingsQuestions from './question-types/MatchingHeadingsQuestions';
import MatchingFeaturesQuestions from './question-types/MatchingFeaturesQuestions';
import MatchingSentenceEndingsQuestions from './question-types/MatchingSentenceEndingsQuestions';
import SentenceCompletionQuestions from './question-types/SentenceCompletionQuestions';
import SummaryCompletionQuestions from './question-types/SummaryCompletionQuestions';
import NoteCompletionQuestions from './question-types/NoteCompletionQuestions';
import TableCompletionQuestions from './question-types/TableCompletionQuestions';
import FlowChartCompletionQuestions from './question-types/FlowChartCompletionQuestions';
import DiagramLabelCompletionQuestions from './question-types/DiagramLabelCompletionQuestions';
import ShortAnswerQuestions from './question-types/ShortAnswerQuestions';

function ReadingQuestionRenderer({ group, answers, onAnswerChange }) {
  const questionType = (group.type || '').toLowerCase();

  if (isReadingMultipleChoiceGroup(group)) {
    return (
      <MultipleChoiceQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  if (isReadingTrueFalseNotGivenGroup(group)) {
    return (
      <TrueFalseNotGivenQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  if (isReadingYesNoNotGivenGroup(group)) {
    return (
      <YesNoNotGivenQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  if (isReadingMatchingInformationGroup(group)) {
    return (
      <MatchingInformationQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  if (isReadingMatchingHeadingsGroup(group)) {
    return (
      <MatchingHeadingsQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  if (isReadingMatchingFeaturesGroup(group)) {
    return (
      <MatchingFeaturesQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  if (isReadingMatchingSentenceEndingsGroup(group)) {
    return (
      <MatchingSentenceEndingsQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  if (isReadingSentenceCompletionGroup(group)) {
    return (
      <SentenceCompletionQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  if (isReadingSummaryCompletionGroup(group)) {
    return (
      <SummaryCompletionQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  if (isReadingNoteCompletionGroup(group)) {
    return (
      <NoteCompletionQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  if (isReadingTableCompletionGroup(group)) {
    return (
      <TableCompletionQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  if (isReadingFlowChartCompletionGroup(group)) {
    return (
      <FlowChartCompletionQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  if (isReadingDiagramLabelCompletionGroup(group)) {
    return (
      <DiagramLabelCompletionQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  if (isReadingShortAnswerGroup(group)) {
    return (
      <ShortAnswerQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  if (isReadingTextAnswerQuestionType(questionType)) {
    return (
      <ShortAnswerQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  if (isReadingChoiceAnswerQuestionType(questionType)) {
    return (
      <MultipleChoiceQuestions group={group} answers={answers} onAnswerChange={onAnswerChange} />
    );
  }

  return null;
}

export default memo(ReadingQuestionRenderer);
