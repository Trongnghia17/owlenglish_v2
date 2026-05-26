import { memo, useMemo, useState } from 'react';

const MATCHING_DRAG_TYPE = 'application/x-listening-matching-answer';

const normalizeLetter = (value) =>
  String(value ?? '').trim().toUpperCase().slice(0, 1);

const stripHtml = (content = '') =>
  String(content)
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

const getTextBlocks = (html = '') => {
  const content = String(html);
  const matches = [
    ...content.matchAll(/<(?:p|h[1-6]|div|li)[^>]*>([\s\S]*?)<\/(?:p|h[1-6]|div|li)>/gi)
  ];
  const blocks = matches.map((match) => stripHtml(match[1])).filter(Boolean);

  return blocks.length > 0 ? blocks : [stripHtml(content)].filter(Boolean);
};

const getPanelTitles = (group) => {
  const titleBlocks = getTextBlocks(group.groupContent)
    .filter((block) => block.length <= 80);

  return {
    questionTitle: titleBlocks[0] || 'Questions',
    optionTitle: titleBlocks[1] || group.optionTitle || 'Options'
  };
};

const getOptionContent = (options, letter) =>
  options.find((option) => option.letter === letter)?.content || '';

const getFallbackOptions = (group) =>
  (group.options || []).map((option, index) => {
    if (typeof option === 'string') {
      return {
        letter: option.length === 1 ? option.toUpperCase() : String.fromCharCode(65 + index),
        content: option
      };
    }

    return {
      letter: option?.letter || option?.label || String.fromCharCode(65 + index),
      content: option?.content || option?.text || ''
    };
  });

function MatchingQuestions({ group, answers, onAnswerChange }) {
  const [draggingLetter, setDraggingLetter] = useState('');
  const [activeLetter, setActiveLetter] = useState('');
  const [dropTargetQuestionId, setDropTargetQuestionId] = useState(null);
  const panelTitles = useMemo(() => getPanelTitles(group), [group]);

  const options = useMemo(() => {
    const answerOptions = group.optionsWithContent?.length
      ? group.optionsWithContent
      : getFallbackOptions(group);

    return answerOptions.map((option, index) => ({
      letter: normalizeLetter(option.letter || String.fromCharCode(65 + index)),
      content: option.content || ''
    })).filter((option) => option.letter && option.content);
  }, [group]);

  const selectedLetters = useMemo(
    () => new Set(group.questions.map((question) => normalizeLetter(answers[question.id])).filter(Boolean)),
    [answers, group.questions]
  );

  const assignAnswer = (questionId, letter) => {
    const normalizedLetter = normalizeLetter(letter);
    if (!normalizedLetter) return;

    const previousQuestion = group.questions.find((question) =>
      question.id !== questionId && normalizeLetter(answers[question.id]) === normalizedLetter
    );

    if (previousQuestion) {
      onAnswerChange(previousQuestion.id, '');
    }

    onAnswerChange(questionId, normalizedLetter);
    setActiveLetter('');
    setDropTargetQuestionId(null);
  };

  const handleDragStart = (event, option) => {
    setDraggingLetter(option.letter);
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData(MATCHING_DRAG_TYPE, option.letter);
    event.dataTransfer.setData('text/plain', option.letter);
  };

  const handleDrop = (event, questionId) => {
    event.preventDefault();
    event.stopPropagation();
    const letter = event.dataTransfer.getData(MATCHING_DRAG_TYPE) ||
      event.dataTransfer.getData('text/plain');

    assignAnswer(questionId, letter);
  };

  const handleDropzoneKeyDown = (event, questionId) => {
    if ((event.key === 'Enter' || event.key === ' ') && activeLetter) {
      event.preventDefault();
      assignAnswer(questionId, activeLetter);
    }
  };

  return (
    <div className="listening-test__matching-layout">
      <section className="listening-test__matching-card">
        <h4 className="listening-test__matching-card-title">{panelTitles.questionTitle}</h4>
        <div className="listening-test__matching-question-list">
          {group.questions.map((question) => {
            const answer = normalizeLetter(answers[question.id]);
            const answerContent = getOptionContent(options, answer);
            const isDropTarget = dropTargetQuestionId === question.id;

            return (
              <div
                key={question.id}
                className={`listening-test__matching-question-row ${answer ? 'is-filled' : ''}`}
              >
                <span className="listening-test__matching-question-number">
                  {question.number}
                </span>
                <div
                  className="listening-test__matching-question-text"
                  dangerouslySetInnerHTML={{ __html: question.content || '' }}
                />
                <div
                  className={`listening-test__matching-dropzone ${isDropTarget ? 'is-drop-target' : ''} ${activeLetter && !answer ? 'is-ready' : ''}`}
                  role="button"
                  tabIndex={0}
                  onClick={() => activeLetter && assignAnswer(question.id, activeLetter)}
                  onKeyDown={(event) => handleDropzoneKeyDown(event, question.id)}
                  onDragEnter={(event) => {
                    event.preventDefault();
                    setDropTargetQuestionId(question.id);
                  }}
                  onDragOver={(event) => {
                    event.preventDefault();
                    event.dataTransfer.dropEffect = 'move';
                    setDropTargetQuestionId(question.id);
                  }}
                  onDragLeave={(event) => {
                    if (event.currentTarget.contains(event.relatedTarget)) return;
                    setDropTargetQuestionId(null);
                  }}
                  onDrop={(event) => handleDrop(event, question.id)}
                >
                  {answer ? (
                    <span className="listening-test__matching-answer-chip">
                      <span className="listening-test__matching-answer-letter">{answer}</span>
                      <span
                        className="listening-test__matching-answer-text"
                        dangerouslySetInnerHTML={{ __html: answerContent }}
                      />
                      <button
                        type="button"
                        className="listening-test__matching-answer-clear"
                        aria-label={`Clear answer ${question.number}`}
                        onClick={(event) => {
                          event.stopPropagation();
                          onAnswerChange(question.id, '');
                        }}
                      />
                    </span>
                  ) : (
                    <span className="listening-test__matching-placeholder">Drop answer here</span>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      </section>

      <aside className="listening-test__matching-card listening-test__matching-card--options">
        <h4 className="listening-test__matching-card-title">{panelTitles.optionTitle}</h4>
        <div className="listening-test__matching-options-list">
          {options.map((option) => {
            const isUsed = selectedLetters.has(option.letter);
            const isActive = activeLetter === option.letter;

            return (
              <button
                key={`${option.letter}-${stripHtml(option.content)}`}
                type="button"
                className={[
                  'listening-test__matching-option',
                  isUsed ? 'is-used' : '',
                  isActive ? 'is-active' : '',
                  draggingLetter === option.letter ? 'is-dragging' : ''
                ].filter(Boolean).join(' ')}
                draggable={!isUsed}
                aria-pressed={isActive}
                aria-disabled={isUsed}
                onClick={() => !isUsed && setActiveLetter(isActive ? '' : option.letter)}
                onDragStart={(event) => !isUsed && handleDragStart(event, option)}
                onDragEnd={() => {
                  setDraggingLetter('');
                  setDropTargetQuestionId(null);
                }}
              >
                <span className="listening-test__matching-option-letter">{option.letter}</span>
                <span
                  className="listening-test__matching-option-text"
                  dangerouslySetInnerHTML={{ __html: option.content }}
                />
                <span className="listening-test__matching-option-handle" aria-hidden="true" />
              </button>
            );
          })}
        </div>
      </aside>
    </div>
  );
}

export default memo(MatchingQuestions);
