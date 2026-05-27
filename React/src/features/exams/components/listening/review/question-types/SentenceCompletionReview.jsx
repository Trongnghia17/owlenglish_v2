import ListeningReviewAnswerGroup from './ListeningReviewAnswerGroup';

export default function SentenceCompletionReview(props) {
  return (
    <ListeningReviewAnswerGroup
      {...props}
      typeClass="sentence-completion"
      title="Your answer"
    />
  );
}
