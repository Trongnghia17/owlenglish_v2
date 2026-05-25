import { memo } from 'react';

function NoteCompletionQuestions({ group, answers, onAnswerChange }) {
  return (
    <aside className="listening-test__note-answer-card">
      <div className="listening-test__note-answer-title">Your answer</div>

      <div className="listening-test__note-answer-list">
        {group.questions.map((question) => (
          <label
            key={question.id}
            id={`listening-note-question-${question.id}`}
            className="listening-test__note-answer-row"
          >
            <span className="listening-test__note-answer-number">
              {question.number}
            </span>
            <input
              type="text"
              className="listening-test__note-answer-input"
              placeholder="Type answer here"
              value={answers[question.id] || ''}
              onChange={(event) => onAnswerChange(question.id, event.target.value)}
              maxLength={100}
              autoComplete="off"
            />
          </label>
        ))}
      </div>
    </aside>
  );
}

export default memo(NoteCompletionQuestions);
