function DefaultReadingReview({ group, userAnswers, expandedExplanations, activeQuestionId, onToggleExplanation, onQuestionFocus, onLocate }) {
  return (
    <section className="reading-review__answer-group reading-review__answer-group--default">
      <div className="reading-review__answer-range">Questions {group.startNumber} – {group.endNumber}</div>
      <div className="reading-review__default-list">
        {group.questions?.map((question) => (
          <div key={question.id} className="reading-review__default-item">
            <span className="reading-review__answer-number">{question.number}.</span>
            <div className="reading-review__default-question-text" dangerouslySetInnerHTML={{ __html: question.content || '' }} />
          </div>
        ))}
      </div>
    </section>
  );
}

export default DefaultReadingReview;