import { memo, useMemo, useState } from 'react';
import ReadingHtmlContent from './ReadingHtmlContent';
import { isReadingMatchingHeadingsGroup, normalizeHtmlMediaSources } from '../../utils/readingTest';

const MATCHING_HEADINGS_DRAG_TYPE = 'application/x-reading-headings-answer';

const normalizeLabel = (value) => String(value ?? '').trim().toLowerCase();
const displayLabel = (value) => String(value ?? '').trim().toUpperCase();

const stripHtmlToText = (value = '') =>
  String(value)
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

const escapeHtml = (value = '') =>
  String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const getQuestionSectionLabel = (question, index) => {
  const text = stripHtmlToText(question?.content || '');
  const explicitMatch = text.match(/\b(?:section|paragraph|para)\s+([A-Z])\b/i);
  if (explicitMatch) return explicitMatch[1].toUpperCase();

  const simpleMatch = text.match(/^([A-Z])(?:\b|[\s.:)-])/i);
  return simpleMatch ? simpleMatch[1].toUpperCase() : String.fromCharCode(65 + index);
};

function PassageHtmlChunk({ html }) {
  const normalizedHtml = useMemo(() => normalizeHtmlMediaSources(html), [html]);
  if (!normalizedHtml) return null;

  return (
    <div
      className="reading-test__mh-html-chunk"
      dangerouslySetInnerHTML={{ __html: normalizedHtml }}
    />
  );
}

const getParagraphInnerHtml = (paragraphHtml = '') => {
  const match = String(paragraphHtml).match(/^<p\b[^>]*>([\s\S]*?)<\/p>$/i);
  return match ? match[1] : paragraphHtml;
};

const getLeadingTargetParagraph = (paragraphHtml, targetByLabel) => {
  const innerHtml = getParagraphInnerHtml(paragraphHtml);
  const plainText = stripHtmlToText(paragraphHtml);
  const exactLabel = /^[A-Z]$/i.test(plainText) ? plainText.toUpperCase() : '';
  const exactTarget = exactLabel ? targetByLabel.get(exactLabel) : null;

  if (exactTarget) {
    return { label: exactLabel, target: exactTarget, restHtml: '' };
  }

  const leadingLabelMatch = innerHtml.match(
    /^\s*(?:<(?:strong|b|em|i|span)\b[^>]*>\s*)*([A-Z])\s*(?:<\/(?:strong|b|em|i|span)>\s*)*(?:<br\s*\/?>|\r?\n)/i
  );

  if (!leadingLabelMatch) return null;

  const label = leadingLabelMatch[1].toUpperCase();
  const target = targetByLabel.get(label);
  if (!target) return null;

  const restInnerHtml = innerHtml.slice(leadingLabelMatch[0].length).trim();
  return {
    label,
    target,
    restHtml: restInnerHtml ? `<p>${restInnerHtml}</p>` : '',
  };
};

function MatchingHeadingsTarget({ question, answer, isDropTarget, onDropAnswer, onClearAnswer }) {
  const answerLabel = answer ? displayLabel(answer) : '.......';

  return (
    <button
      type="button"
      className={[
        'reading-test__mh-passage-target',
        answer ? 'is-filled' : '',
        isDropTarget ? 'is-drop-target' : '',
      ].filter(Boolean).join(' ')}
      aria-label={`Question ${question.number}${answer ? `, selected ${answerLabel}` : ''}`}
      onClick={() => {
        if (answer) onClearAnswer(question.id);
      }}
      onDragEnter={(event) => {
        event.preventDefault();
        onDropAnswer(question.id, null, true);
      }}
      onDragOver={(event) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
        onDropAnswer(question.id, null, true);
      }}
      onDragLeave={(event) => {
        if (event.currentTarget.contains(event.relatedTarget)) return;
        onDropAnswer(null, null, true);
      }}
      onDrop={(event) => {
        event.preventDefault();
        event.stopPropagation();
        const droppedAnswer =
          event.dataTransfer.getData(MATCHING_HEADINGS_DRAG_TYPE) ||
          event.dataTransfer.getData('text/plain');
        onDropAnswer(question.id, droppedAnswer);
      }}
    >
      <span className="reading-test__mh-passage-target-number">{question.number}</span>
      <span className="reading-test__mh-passage-target-value">{answerLabel}</span>
    </button>
  );
}

function MatchingHeadingsPassageContent({ html, group, answers = {}, onAnswerChange }) {
  const [dropTargetId, setDropTargetId] = useState(null);

  const options = useMemo(
    () =>
      (group.optionsWithContent || []).map((option, index) => ({
        letter: option.letter || String.fromCharCode(65 + index),
        content: option.content || '',
      })),
    [group.optionsWithContent]
  );

  const targets = useMemo(
    () =>
      (group.questions || []).map((question, index) => ({
        label: getQuestionSectionLabel(question, index),
        question,
      })),
    [group.questions]
  );

  const targetByLabel = useMemo(
    () =>
      new Map(
        targets.map((target) => [target.label, target])
      ),
    [targets]
  );

  const segments = useMemo(() => {
    const sourceHtml = html || '';
    const parts = [];
    const usedLabels = new Set();
    const paragraphRegex = /<p\b[^>]*>[\s\S]*?<\/p>/gi;
    let lastIndex = 0;
    let match;
    let hasParagraphMatches = false;
    let hasTitleParagraph = false;

    while ((match = paragraphRegex.exec(sourceHtml)) !== null) {
      hasParagraphMatches = true;
      const paragraphHtml = match[0];
      const plainText = stripHtmlToText(paragraphHtml);
      const leadingTarget = getLeadingTargetParagraph(paragraphHtml, targetByLabel);

      if (match.index > lastIndex) {
        parts.push({ type: 'html', html: sourceHtml.slice(lastIndex, match.index) });
      }

      if (leadingTarget) {
        usedLabels.add(leadingTarget.label);
        parts.push({
          type: 'target-heading',
          label: leadingTarget.label,
          target: leadingTarget.target,
        });
        if (leadingTarget.restHtml) {
          parts.push({ type: 'html', html: leadingTarget.restHtml });
        }
      } else if (!hasTitleParagraph && plainText) {
        hasTitleParagraph = true;
        parts.push({ type: 'html', html: `<h1>${escapeHtml(plainText)}</h1>` });
      } else {
        parts.push({ type: 'html', html: paragraphHtml });
      }

      lastIndex = paragraphRegex.lastIndex;
    }

    if (!hasParagraphMatches) {
      const textParts = [];
      const textUsedLabels = new Set();
      const lines = String(sourceHtml).split(/\r?\n/);
      let buffer = [];
      let hasTitle = false;

      const flushBuffer = () => {
        const text = buffer.join(' ').replace(/\s+/g, ' ').trim();
        buffer = [];
        if (!text) return;

        if (!hasTitle) {
          hasTitle = true;
          textParts.push({ type: 'html', html: `<h1>${escapeHtml(text)}</h1>` });
          return;
        }

        textParts.push({ type: 'html', html: `<p>${escapeHtml(text)}</p>` });
      };

      lines.forEach((line) => {
        const plainLine = stripHtmlToText(line);
        const label = /^[A-Z]$/i.test(plainLine) ? plainLine.toUpperCase() : '';
        const target = label ? targetByLabel.get(label) : null;

        if (target) {
          flushBuffer();
          textUsedLabels.add(label);
          textParts.push({ type: 'target-heading', label, target });
          return;
        }

        if (!plainLine) {
          flushBuffer();
          return;
        }

        buffer.push(plainLine);
      });

      flushBuffer();

      return { parts: textParts, usedLabels: textUsedLabels };
    }

    if (lastIndex < sourceHtml.length) {
      parts.push({ type: 'html', html: sourceHtml.slice(lastIndex) });
    }

    return { parts, usedLabels };
  }, [html, targetByLabel]);

  const assignAnswer = (questionId, droppedAnswer, dragStateOnly = false) => {
    if (dragStateOnly) {
      setDropTargetId(questionId);
      return;
    }

    setDropTargetId(null);
    const normalizedAnswer = normalizeLabel(droppedAnswer);
    if (!questionId || !normalizedAnswer || !onAnswerChange) return;

    const selectedOption = options.find((option) => normalizeLabel(option.letter) === normalizedAnswer);
    if (!selectedOption) return;

    const previousQuestion = (group.questions || []).find(
      (question) =>
        String(question.id) !== String(questionId) &&
        normalizeLabel(answers[question.id]) === normalizedAnswer
    );

    if (previousQuestion) onAnswerChange(previousQuestion.id, '');
    onAnswerChange(questionId, selectedOption.letter);
  };

  const clearAnswer = (questionId) => {
    if (onAnswerChange) onAnswerChange(questionId, '');
  };

  return (
    <div className="reading-test__passage-content reading-test__passage-content--matching-headings">
      {segments.parts.map((part, index) => {
        if (part.type === 'target-heading') {
          const { label, target } = part;
          return (
            <div key={`${label}-${target.question.id}`} className="reading-test__mh-passage-heading-row">
              <span className="reading-test__mh-passage-section-label">{label}</span>
              <MatchingHeadingsTarget
                question={target.question}
                answer={answers[target.question.id]}
                isDropTarget={dropTargetId === target.question.id}
                onDropAnswer={assignAnswer}
                onClearAnswer={clearAnswer}
              />
            </div>
          );
        }

        return <PassageHtmlChunk key={`chunk-${index}`} html={part.html} />;
      })}
    </div>
  );
}

function ReadingPassagePanel({ passage, groups, answers = {}, onAnswerChange }) {
  const passageHtml = passage || groups?.[0]?.passage || '';
  const imageUrl = groups?.[0]?.imageUrl;
  const matchingHeadingsGroup = groups?.find(isReadingMatchingHeadingsGroup);

  return (
    <div className="reading-test__passage-panel">
      {imageUrl && (
        <div className="reading-test__passage-image-wrapper">
          <img
            src={imageUrl}
            alt="Passage illustration"
            className="reading-test__passage-image"
          />
        </div>
      )}
      {passageHtml && matchingHeadingsGroup ? (
        <MatchingHeadingsPassageContent
          html={passageHtml}
          group={matchingHeadingsGroup}
          answers={answers}
          onAnswerChange={onAnswerChange}
        />
      ) : passageHtml ? (
        <ReadingHtmlContent
          className="reading-test__passage-content"
          html={passageHtml}
        />
      ) : null}
    </div>
  );
}

export default memo(ReadingPassagePanel);
