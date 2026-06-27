import {
  countWritingWords,
  getWritingAnswerKey,
  getWritingAnswerText
} from '../../utils/writingAnswers';

const WritingAnswerSections = ({ group, answers, onAnswerChange }) => {
  const wordCount = countWritingWords(getWritingAnswerText(answers, group));

  return (
    <div className="writing-test__answer-section">
      <div className="writing-test__answer-list">
        {group.answerSections.map((section, sectionIndex) => {
          const inputId = `writing-answer-${group.id}-${section.key}`;

          return (
            <section className="writing-test__answer-block" key={section.key}>
              <div className="writing-test__answer-header">
                <label className="writing-test__answer-label" htmlFor={inputId}>
                  {sectionIndex + 1}. {section.label}
                </label>
              </div>

              <textarea
                id={inputId}
                className="writing-test__textarea"
                style={{ minHeight: `${section.minHeight}px` }}
                placeholder="Input caption"
                value={answers[getWritingAnswerKey(group.id, section.key)] || ''}
                onChange={(event) => onAnswerChange(group.id, section.key, event.target.value)}
              />
            </section>
          );
        })}
      </div>

      <div className="writing-test__word-count-footer">
        <span>{wordCount} từ: </span>
        <span className={wordCount < group.wordTarget ? 'text-red' : 'text-green'}>
          {wordCount}
        </span>
        <span>/{group.wordTarget}</span>
      </div>
    </div>
  );
};

export default WritingAnswerSections;
