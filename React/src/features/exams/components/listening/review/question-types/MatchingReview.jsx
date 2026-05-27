import ListeningReviewAnswerGroup from './ListeningReviewAnswerGroup';

export default function MatchingReview(props) {
  return (
    <ListeningReviewAnswerGroup
      {...props}
      typeClass="matching"
      title="Your answer"
    />
  );
}
