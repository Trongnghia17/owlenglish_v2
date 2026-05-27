import DefaultListeningReview from './DefaultListeningReview';
import FlowChartCompletionReview from './FlowChartCompletionReview';
import MatchingReview from './MatchingReview';
import MultipleChoiceReview from './MultipleChoiceReview';
import SentenceCompletionReview from './SentenceCompletionReview';
import ShortAnswerReview from './ShortAnswerReview';
import SummaryCompletionReview from './SummaryCompletionReview';
import TextCompletionReview from './TextCompletionReview';

const REVIEW_RENDERERS = {
  sentence_completion: SentenceCompletionReview,
  summary_completion: SummaryCompletionReview,
  short_answer_questions: ShortAnswerReview,
  flow_chart_completion: FlowChartCompletionReview,
  matching: MatchingReview,
  multiple_choice: MultipleChoiceReview,
  short_text: TextCompletionReview,
  note_completion: TextCompletionReview,
  form_completion: TextCompletionReview,
  table_completion: TextCompletionReview,
  plan_map_diagram_labelling: TextCompletionReview
};

export const getListeningReviewRenderer = (questionType) =>
  REVIEW_RENDERERS[(questionType || '').toLowerCase()] || DefaultListeningReview;
