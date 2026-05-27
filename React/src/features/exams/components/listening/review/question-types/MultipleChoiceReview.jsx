import ListeningReviewAnswerGroup from './ListeningReviewAnswerGroup';

export default function MultipleChoiceReview(props) {
  return (
    <ListeningReviewAnswerGroup
      {...props}
      typeClass="multiple-choice"
      title="Your answer"
    />
  );
}
