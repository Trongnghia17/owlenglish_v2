import { memo, useMemo, useState } from 'react';
import { containsInlinePlaceholders } from '../../../utils/listeningTest';
import InlineAnswerContent from '../InlineAnswerContent';
import { buildInlineCompletionContent, stripHtml } from './textQuestionUtils';

const ensureInlinePlaceholder = (content, questionNumber) => {
  const safeContent = String(content ?? '').trim();

  if (containsInlinePlaceholders(safeContent)) {
    return safeContent;
  }

  const placeholder = ` {{${questionNumber}}}`;

  if (!safeContent) {
    return `<p>${placeholder.trim()}</p>`;
  }

  if (/<\/p>\s*$/i.test(safeContent)) {
    return safeContent.replace(/<\/p>\s*$/i, `${placeholder}</p>`);
  }

  return `<p>${safeContent}${placeholder}</p>`;
};

const getFirstTextBlock = (html = '') => {
  const headingMatch = html.match(/<h[1-4][^>]*>([\s\S]*?)<\/h[1-4]>/i);
  if (headingMatch && !containsInlinePlaceholders(headingMatch[0])) {
    return stripHtml(headingMatch[1]);
  }

  const paragraphMatch = html.match(/<p[^>]*>([\s\S]*?)<\/p>/i);
  if (paragraphMatch && !containsInlinePlaceholders(paragraphMatch[0])) {
    return stripHtml(paragraphMatch[1]);
  }

  return '';
};

const getFlowTitle = (group) =>
  getFirstTextBlock(group.groupContent) || 'Flow chart';

const getFlowOptions = (group) =>
  (group.optionsWithContent || []).filter((option) => option.content || option.letter);

const FLOW_ANSWER_TRANSFER_TYPE = 'application/x-flow-chart-answer';

const normalizeFlowLetter = (value) => String(value || '').trim().toUpperCase().slice(0, 1);

function FlowChartCompletionQuestions({ group, answers, onAnswerChange }) {
  const [activeQuestionId, setActiveQuestionId] = useState(group.questions?.[0]?.id || null);
  const [draggingLetter, setDraggingLetter] = useState('');
  const [dropTargetQuestionId, setDropTargetQuestionId] = useState(null);
  const options = useMemo(() => getFlowOptions(group), [group]);
  const shouldRenderGroupContent = containsInlinePlaceholders(group.groupContent);
  const hasQuestionContent = group.questions.some((question) => stripHtml(question.content));
  const activeAnswerValues = new Set(
    group.questions
      .map((question) => String(answers[question.id] || '').trim().toUpperCase())
      .filter(Boolean)
  );

  const handleOptionSelect = (option) => {
    const selectedLetter = normalizeFlowLetter(option.letter);
    const targetQuestion =
      group.questions.find((question) => question.id === activeQuestionId) ||
      group.questions.find((question) => !String(answers[question.id] || '').trim()) ||
      group.questions[0];

    if (!targetQuestion || !selectedLetter) return;

    setActiveQuestionId(targetQuestion.id);
    onAnswerChange(targetQuestion.id, selectedLetter);
  };

  const handleOptionDragStart = (event, option) => {
    const selectedLetter = normalizeFlowLetter(option.letter);

    if (!selectedLetter) {
      event.preventDefault();
      return;
    }

    event.dataTransfer.effectAllowed = 'copy';
    event.dataTransfer.setData(FLOW_ANSWER_TRANSFER_TYPE, selectedLetter);
    event.dataTransfer.setData('text/plain', selectedLetter);
    setDraggingLetter(selectedLetter);
  };

  const handleOptionDragEnd = () => {
    setDraggingLetter('');
    setDropTargetQuestionId(null);
  };

  const handleRowDragOver = (event, questionId) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'copy';
    setActiveQuestionId(questionId);
    setDropTargetQuestionId(questionId);
  };

  const handleRowDragLeave = (event, questionId) => {
    if (event.currentTarget.contains(event.relatedTarget)) return;
    setDropTargetQuestionId((currentQuestionId) =>
      currentQuestionId === questionId ? null : currentQuestionId
    );
  };

  const handleRowDrop = (event, question) => {
    event.preventDefault();

    const droppedLetter = normalizeFlowLetter(
      event.dataTransfer.getData(FLOW_ANSWER_TRANSFER_TYPE) ||
      event.dataTransfer.getData('text/plain')
    );

    setDraggingLetter('');
    setDropTargetQuestionId(null);

    if (!droppedLetter) return;

    setActiveQuestionId(question.id);
    onAnswerChange(question.id, droppedLetter);
  };

  return (
    <div className="listening-test__flow-chart-layout">
      <section className="listening-test__flow-chart-card">
        {shouldRenderGroupContent ? (
          <InlineAnswerContent
            content={buildInlineCompletionContent(group)}
            questions={group.questions}
            answers={answers}
            onAnswerChange={onAnswerChange}
            onQuestionFocus={setActiveQuestionId}
            variant="flow"
          />
        ) : hasQuestionContent ? (
          <>
            <div className="listening-test__flow-chart-title">{getFlowTitle(group)}</div>
            <div className="listening-test__flow-chart-rows">
              {group.questions.map((question) => (
                <div
                  key={question.id}
                  role="button"
                  tabIndex={0}
                  className={[
                    'listening-test__flow-chart-row',
                    activeQuestionId === question.id ? 'is-active' : '',
                    dropTargetQuestionId === question.id ? 'is-drop-target' : ''
                  ].filter(Boolean).join(' ')}
                  onClick={() => setActiveQuestionId(question.id)}
                  onDragEnter={(event) => handleRowDragOver(event, question.id)}
                  onDragOver={(event) => handleRowDragOver(event, question.id)}
                  onDragLeave={(event) => handleRowDragLeave(event, question.id)}
                  onDrop={(event) => handleRowDrop(event, question)}
                  onKeyDown={(event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                      setActiveQuestionId(question.id);
                    }
                  }}
                >
                  <InlineAnswerContent
                    content={ensureInlinePlaceholder(question.content, question.number)}
                    questions={[question]}
                    answers={answers}
                    onAnswerChange={onAnswerChange}
                    onQuestionFocus={setActiveQuestionId}
                    variant="flow"
                  />
                  <span className="listening-test__flow-chart-arrow" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                      <path d="M8 3.333v9.334M4.667 9.333 8 12.667l3.333-3.334" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                  </span>
                </div>
              ))}
            </div>
          </>
        ) : (
          <InlineAnswerContent
            content={buildInlineCompletionContent(group)}
            questions={group.questions}
            answers={answers}
            onAnswerChange={onAnswerChange}
            onQuestionFocus={setActiveQuestionId}
            variant="flow"
          />
        )}
      </section>

      {options.length > 0 && (
        <aside className="listening-test__flow-options-card">
          <div className="listening-test__flow-options-title">{group.optionTitle || 'Options'}</div>
          <div className="listening-test__flow-options-list">
            {options.map((option) => {
              const letter = normalizeFlowLetter(option.letter);

              return (
                <button
                  key={`${option.letter}-${option.content}`}
                  type="button"
                  draggable={Boolean(letter)}
                  className={[
                    'listening-test__flow-option',
                    activeAnswerValues.has(letter) ? 'is-selected' : '',
                    draggingLetter === letter ? 'is-dragging' : ''
                  ].filter(Boolean).join(' ')}
                  aria-grabbed={draggingLetter === letter}
                  onClick={() => handleOptionSelect(option)}
                  onDragStart={(event) => handleOptionDragStart(event, option)}
                  onDragEnd={handleOptionDragEnd}
                >
                  <span className="listening-test__flow-option-letter">{option.letter}</span>
                  <span
                    className="listening-test__flow-option-text"
                    dangerouslySetInnerHTML={{ __html: option.content }}
                  />
                </button>
              );
            })}
          </div>
        </aside>
      )}
    </div>
  );
}

export default memo(FlowChartCompletionQuestions);
