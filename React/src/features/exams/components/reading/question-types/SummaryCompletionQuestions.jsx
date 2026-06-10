import { memo, useEffect, useMemo, useRef } from 'react';
import { containsInlinePlaceholders, normalizeHtmlMediaSources } from '../../../utils/readingTest';

const PLACEHOLDER_REGEX = /\{\{\s*([\w-]+)\s*\}\}/g;

const escapeHtml = (value = '') =>
  String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const stripHtmlToText = (content = '') =>
  String(content ?? '')
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

const hasHtml = (content = '') => /<\/?[a-z][\s\S]*>/i.test(content);

const buildFallbackSummaryContent = (group) =>
  [
    group.groupContent || '',
    ...(group.questions || []).map((question) => `${question.content || ''} {{${question.number}}}`)
  ]
    .filter(Boolean)
    .join('\n\n');

const splitTitleAndBody = (content = '') => {
  const normalizedContent = String(content ?? '').replace(/\r\n/g, '\n').trim();
  if (!normalizedContent) {
    return { title: '', body: '' };
  }

  if (hasHtml(normalizedContent)) {
    const firstBlockMatch = normalizedContent.match(/^\s*((?:<h[1-6]\b[^>]*>|<p\b[^>]*>|<div\b[^>]*>)[\s\S]*?(?:<\/h[1-6]>|<\/p>|<\/div>))/i);
    const firstBlock = firstBlockMatch?.[1] || '';

    if (firstBlock && !containsInlinePlaceholders(firstBlock) && stripHtmlToText(firstBlock).length <= 100) {
      const body = normalizedContent.slice(firstBlockMatch.index + firstBlock.length).trim();
      if (containsInlinePlaceholders(body)) {
        return {
          title: stripHtmlToText(firstBlock),
          body
        };
      }
    }

    return { title: '', body: normalizedContent };
  }

  const parts = normalizedContent.split(/\n\s*\n/).map((part) => part.trim()).filter(Boolean);

  if (parts.length > 1 && !containsInlinePlaceholders(parts[0]) && stripHtmlToText(parts[0]).length <= 100) {
    return {
      title: stripHtmlToText(parts[0]),
      body: parts.slice(1).join('\n\n')
    };
  }

  return { title: '', body: normalizedContent };
};

const textToSummaryListHtml = (content = '') => {
  if (hasHtml(content)) return content;

  const items = String(content ?? '')
    .split(/\n\s*\n/)
    .map((item) => item.replace(/\s*\n\s*/g, ' ').trim())
    .filter(Boolean);

  return `<ul>${items.map((item) => `<li>${escapeHtml(item)}</li>`).join('')}</ul>`;
};

const buildSummaryData = (group) => {
  const source = containsInlinePlaceholders(group.groupContent)
    ? group.groupContent
    : buildFallbackSummaryContent(group);
  const { title, body } = splitTitleAndBody(source);

  return {
    title,
    bodyHtml: normalizeHtmlMediaSources(textToSummaryListHtml(body))
  };
};

function SummaryCompletionQuestions({ group, answers, onAnswerChange }) {
  const contentRef = useRef(null);
  const placeholdersRef = useRef([]);
  const latestAnswersRef = useRef(answers);
  const latestHandlerRef = useRef(onAnswerChange);
  const summaryData = useMemo(() => buildSummaryData(group), [group]);

  useEffect(() => {
    latestAnswersRef.current = answers;
  }, [answers]);

  useEffect(() => {
    latestHandlerRef.current = onAnswerChange;
  }, [onAnswerChange]);

  useEffect(() => {
    const container = contentRef.current;
    if (!container) return undefined;

    const questionByNumber = new Map(
      (group.questions || []).map((question) => [String(question.number), question])
    );
    let sequentialIndex = 0;
    const placeholderMeta = [];

    const processedHtml = summaryData.bodyHtml.replace(PLACEHOLDER_REGEX, (match, token) => {
      const question = questionByNumber.get(String(token)) || group.questions?.[sequentialIndex];
      sequentialIndex += 1;

      if (!question) return match;

      const placeholderId = `summary-placeholder-${question.id}-${placeholderMeta.length}`;
      placeholderMeta.push({ placeholderId, question });
      return `<span class="reading-test__summary-completion-placeholder" data-placeholder-id="${placeholderId}"></span>`;
    });

    container.innerHTML = processedHtml;

    placeholderMeta.forEach((meta) => {
      const placeholderElement = container.querySelector(`[data-placeholder-id="${meta.placeholderId}"]`);
      if (!placeholderElement) return;

      const wrapper = document.createElement('span');
      wrapper.className = 'reading-test__summary-completion-blank';

      const numberChip = document.createElement('span');
      numberChip.className = 'reading-test__summary-completion-blank-number';
      numberChip.textContent = meta.question.number?.toString() || '';
      wrapper.appendChild(numberChip);

      const input = document.createElement('input');
      input.type = 'text';
      input.className = 'reading-test__summary-completion-input';
      input.placeholder = '............';
      input.autocomplete = 'off';
      input.size = 12;
      input.value = latestAnswersRef.current?.[meta.question.id] || '';
      input.setAttribute('aria-label', `Question ${meta.question.number || ''} answer`.trim());

      const syncState = (value) => {
        input.size = Math.max(12, String(value || input.placeholder).length);
        wrapper.classList.toggle('is-filled', String(value ?? '').trim() !== '');
      };

      const handleInput = (event) => {
        const nextValue = event.target.value;
        syncState(nextValue);
        latestHandlerRef.current?.(meta.question.id, nextValue);
      };

      input.addEventListener('input', handleInput);
      syncState(input.value);

      wrapper.appendChild(input);
      placeholderElement.replaceWith(wrapper);

      meta.input = input;
      meta.wrapper = wrapper;
      meta.cleanup = () => input.removeEventListener('input', handleInput);
    });

    placeholdersRef.current = placeholderMeta;

    return () => {
      placeholderMeta.forEach((meta) => meta.cleanup?.());
    };
  }, [group.questions, summaryData.bodyHtml]);

  useEffect(() => {
    placeholdersRef.current.forEach(({ question, input, wrapper }) => {
      if (!input) return;
      const nextValue = answers?.[question.id] || '';
      if (input.value !== nextValue) {
        input.value = nextValue;
      }
      input.size = Math.max(12, String(nextValue || input.placeholder).length);
      wrapper?.classList.toggle('is-filled', String(nextValue ?? '').trim() !== '');
    });
  }, [answers]);

  return (
    <div className="reading-test__summary-completion">
      <div className="reading-test__summary-completion-card">
        {summaryData.title && (
          <div className="reading-test__summary-completion-card-head">
            {summaryData.title}
          </div>
        )}

        <div
          ref={contentRef}
          className="reading-test__summary-completion-content"
        />
      </div>
    </div>
  );
}

export default memo(SummaryCompletionQuestions);
