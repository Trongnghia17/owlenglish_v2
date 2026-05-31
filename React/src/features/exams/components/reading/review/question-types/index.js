import MultipleChoiceReview from './MultipleChoiceReview';
import TrueFalseNotGivenReview from './TrueFalseNotGivenReview';
import YesNoNotGivenReview from './YesNoNotGivenReview';
import MatchingInformationReview from './MatchingInformationReview';
import MatchingHeadingsReview from './MatchingHeadingsReview';
import MatchingFeaturesReview from './MatchingFeaturesReview';
import MatchingSentenceEndingsReview from './MatchingSentenceEndingsReview';
import SentenceCompletionReview from './SentenceCompletionReview';
import SummaryCompletionReview from './SummaryCompletionReview';
import NoteCompletionReview from './NoteCompletionReview';
import TableCompletionReview from './TableCompletionReview';
import FlowChartCompletionReview from './FlowChartCompletionReview';
import DiagramLabelCompletionReview from './DiagramLabelCompletionReview';
import ShortAnswerReview from './ShortAnswerReview';
import DefaultReadingReview from './DefaultReadingReview';

const REVIEW_RENDERERS = {
  multiple_choice: MultipleChoiceReview,
  true_false_not_given: TrueFalseNotGivenReview,
  yes_no_not_given: YesNoNotGivenReview,
  matching_information: MatchingInformationReview,
  matching_headings: MatchingHeadingsReview,
  matching_features: MatchingFeaturesReview,
  matching_sentence_endings: MatchingSentenceEndingsReview,
  sentence_completion: SentenceCompletionReview,
  summary_completion: SummaryCompletionReview,
  note_completion: NoteCompletionReview,
  table_completion: TableCompletionReview,
  flow_chart_completion: FlowChartCompletionReview,
  diagram_label_completion: DiagramLabelCompletionReview,
  short_answer_questions: ShortAnswerReview,
};

export const getReadingReviewRenderer = (questionType) =>
  REVIEW_RENDERERS[(questionType || '').toLowerCase()] || DefaultReadingReview;

export {
  MultipleChoiceReview,
  TrueFalseNotGivenReview,
  YesNoNotGivenReview,
  MatchingInformationReview,
  MatchingHeadingsReview,
  MatchingFeaturesReview,
  MatchingSentenceEndingsReview,
  SentenceCompletionReview,
  SummaryCompletionReview,
  NoteCompletionReview,
  TableCompletionReview,
  FlowChartCompletionReview,
  DiagramLabelCompletionReview,
  ShortAnswerReview,
  DefaultReadingReview,
};
