import ListeningReviewAnswerGroup from './ListeningReviewAnswerGroup';

export default function ShortAnswerReview(props) {
  return (
    <ListeningReviewAnswerGroup
      {...props}
      typeClass="short-answer"
      title="Your answer"
    />
  );
}
