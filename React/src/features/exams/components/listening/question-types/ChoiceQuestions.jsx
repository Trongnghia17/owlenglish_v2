import { memo } from 'react';

function ChoiceQuestions({ group, answers, onAnswerChange }) {
  return group.questions.map((question) => (
    <div key={question.id} className="listening-test__question-item">
      <div className="listening-test__question-row">
        <div className="listening-test__question-number">{question.number}</div>
        <div className="listening-test__question-text" dangerouslySetInnerHTML={{ __html: question.content }} />
      </div>
      {group.options && group.options.length > 0 && (
        <div className="listening-test__options">
          {group.options.map((option) => (
            <label key={option} className={`listening-test__option ${answers[question.id] === option ? 'selected' : ''}`}>
              <input
                type="radio"
                name={`question-${question.id}`}
                value={option}
                checked={answers[question.id] === option}
                onChange={() => onAnswerChange(question.id, option)}
              />
              <span className="listening-test__option-text">{option}</span>
            </label>
          ))}
        </div>
      )}
    </div>
  ));
}

export default memo(ChoiceQuestions);
