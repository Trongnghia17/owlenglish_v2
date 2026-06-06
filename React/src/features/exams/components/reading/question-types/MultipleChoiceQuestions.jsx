import { memo } from 'react';
import { getQuestionAnswerOptions } from '../../../utils/readingTest';
import './MultipleChoiceQuestions.css';

const WORD_NUMBER_MAP = { one: 1, two: 2, three: 3, four: 4, five: 5, six: 6 };

const getInstructionAnswerCount = (text = '') => {
  const normalizedText = String(text).toLowerCase();
  const digitMatch = normalizedText.match(/choose\s+(\d+)/i);
  if (digitMatch) return Number(digitMatch[1]);
  const wordMatch = normalizedText.match(/choose\s+(one|two|three|four|five|six)/i);
  return wordMatch ? WORD_NUMBER_MAP[wordMatch[1]] : 1;
};

const getSelectedAnswers = (answer) => {
  if (Array.isArray(answer)) {
    return answer.filter((value) => String(value ?? '').trim() !== '');
  }
  return String(answer ?? '').trim() ? [answer] : [];
};

function MultipleChoiceQuestions({ group, answers, onAnswerChange }) {
  const groupInstructionText = [group.instructions, group.groupContent].filter(Boolean).join(' ');
  const questionCount = group.questions?.length;
  const instructionAnswerCount = questionCount === 1 ? getInstructionAnswerCount(groupInstructionText) : questionCount;

  const handleMultiAnswerChange = (questionId, optionLetter, selectedAnswers, maxSelections) => {
    const isSelected = selectedAnswers.includes(optionLetter);
    if (isSelected) {
      onAnswerChange(questionId, selectedAnswers.filter((a) => a !== optionLetter));
      return;
    }
    if (maxSelections && selectedAnswers.length >= maxSelections) return;
    onAnswerChange(questionId, [...selectedAnswers, optionLetter]);
  };

  return (
    <div className="reading-test__mc-layout">
      <div className="reading-test__mc-questions">
        {group.questions.map((question) => {
          const questionOptions = getQuestionAnswerOptions(question, group.optionsWithContent || []);
          const maxSelections = Math.max(instructionAnswerCount, 1);
          const allowsMultipleAnswers = maxSelections > 1;
          const selectedAnswers = getSelectedAnswers(answers[question.id]);

          return (
            <div key={question.id} className="reading-test__mc-card">
              <div className="reading-test__mc-card-header">
                <div className="reading-test__mc-number">{question.number}</div>
                <div className="reading-test__mc-question-text" dangerouslySetInnerHTML={{ __html: question.content || '' }} />
              </div>
              <div className="reading-test__mc-options">
                {questionOptions.map((option) => {
                  const isSelected = selectedAnswers.includes(option.letter);

                  return (
                    <label key={option.letter} className={`reading-test__mc-option ${isSelected ? 'selected' : ''}`}>
                      <input
                        className="reading-test__mc-input"
                        type={allowsMultipleAnswers ? 'checkbox' : 'radio'}
                        name={`question-${question.id}`}
                        value={option.letter}
                        checked={isSelected}
                        onChange={() => {
                          if (allowsMultipleAnswers) {
                            handleMultiAnswerChange(question.id, option.letter, selectedAnswers, maxSelections);
                            return;
                          }
                          onAnswerChange(question.id, option.letter);
                        }}
                      />
                      <span className="reading-test__mc-option-body">
                        <span className="reading-test__mc-letter">{option.letter}</span>
                        <span className="reading-test__mc-option-text" dangerouslySetInnerHTML={{ __html: option.content }} />
                      </span>
                    </label>
                  );
                })}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}

export default memo(MultipleChoiceQuestions);
