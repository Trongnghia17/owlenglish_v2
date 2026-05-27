import ListeningReviewAnswerGroup from './ListeningReviewAnswerGroup';

export default function SummaryCompletionReview(props) {
  return (
    <ListeningReviewAnswerGroup
      {...props}
      typeClass="summary-completion"
      title="Your answer"
    />
  );
}
