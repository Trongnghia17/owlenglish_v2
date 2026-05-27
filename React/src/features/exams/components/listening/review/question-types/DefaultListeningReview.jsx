import ListeningReviewAnswerGroup from './ListeningReviewAnswerGroup';

export default function DefaultListeningReview(props) {
  return (
    <ListeningReviewAnswerGroup
      {...props}
      typeClass="default"
      title="Your answer"
    />
  );
}
