import ListeningReviewAnswerGroup from './ListeningReviewAnswerGroup';

export default function FlowChartCompletionReview(props) {
  return (
    <ListeningReviewAnswerGroup
      {...props}
      typeClass="flow-chart-completion"
      title="Your answer"
    />
  );
}
