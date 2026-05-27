import ListeningReviewAnswerGroup from './ListeningReviewAnswerGroup';

export default function TextCompletionReview(props) {
  const typeClass = (props.group?.type || 'text-completion')
    .toLowerCase()
    .replaceAll('_', '-');

  return (
    <ListeningReviewAnswerGroup
      {...props}
      typeClass={typeClass}
      title="Your answer"
    />
  );
}
