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

const decodeHtmlEntities = (content = '') =>
  String(content ?? '')
    .replace(/&nbsp;/g, ' ')
    .replace(/&#039;/g, "'")
    .replace(/&quot;/g, '"')
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>');

const htmlToTextLines = (content = '') =>
  decodeHtmlEntities(
    String(content ?? '')
      .replace(/<br\s*\/?>/gi, '\n')
      .replace(/<\/p>\s*<p[^>]*>/gi, '\n\n')
      .replace(/<\/?(?:p|div|span|strong|b|em|i)[^>]*>/gi, '')
      .replace(/<[^>]*>/g, ' ')
  )
    .replace(/[ \t]+\n/g, '\n')
    .replace(/\n[ \t]+/g, '\n')
    .trim();

const buildFallbackNoteContent = (group) =>
  [
    group.groupContent || '',
    ...(group.questions || []).map((question) => `${question.content || ''} {{${question.number}}}`)
  ]
    .filter(Boolean)
    .join('\n');

const splitTitleAndBody = (content = '') => {
  const normalizedContent = String(content ?? '').replace(/\r\n/g, '\n').trim();
  if (!normalizedContent) {
    return { title: '', body: '' };
  }

  if (hasHtml(normalizedContent)) {
    const firstBlockMatch = normalizedContent.match(/^\s*((?:<h[1-6]\b[^>]*>|<p\b[^>]*>|<div\b[^>]*>)[\s\S]*?(?:<\/h[1-6]>|<\/p>|<\/div>))/i);
    const firstBlock = firstBlockMatch?.[1] || '';

    if (firstBlock && !containsInlinePlaceholders(firstBlock) && stripHtmlToText(firstBlock).length <= 100) {
      return {
        title: stripHtmlToText(firstBlock),
        body: normalizedContent.slice(firstBlockMatch.index + firstBlock.length).trim()
      };
    }

    return { title: '', body: normalizedContent };
  }

  const lines = normalizedContent
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean);

  if (lines.length > 1 && !containsInlinePlaceholders(lines[0]) && stripHtmlToText(lines[0]).length <= 100) {
    return {
      title: stripHtmlToText(lines[0]),
      body: lines.slice(1).join('\n')
    };
  }

  return { title: '', body: normalizedContent };
};

const isHeadingLine = (line = '') =>
  !containsInlinePlaceholders(line) &&
  /^[A-ZÀ-Ỵ]/.test(line.trim()) &&
  !/[.:;!?]$/.test(line.trim()) &&
  stripHtmlToText(line).length <= 90;

const isBulletLine = (line = '') => /^(?:[•*]|-)\s+/.test(line.trim());

const isDashLine = (line = '') => /^[–—]\s*/.test(line.trim());

const stripBulletMarker = (line = '') => line.trim().replace(/^(?:[•*]|-)\s+/, '');

const textToNoteHtml = (content = '') => {
  if (hasHtml(content)) return content;

  const lines = String(content ?? '')
    .replace(/\r\n/g, '\n')
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean);

  const chunks = [];
  let currentItems = [];

  const flushItems = () => {
    if (currentItems.length === 0) return;
    chunks.push(`<ul>${currentItems.map((item) => `<li>${escapeHtml(item)}</li>`).join('')}</ul>`);
    currentItems = [];
  };

  lines.forEach((line) => {
    if (isHeadingLine(line)) {
      flushItems();
      chunks.push(`<p class="reading-test__note-completion-heading">${escapeHtml(line)}</p>`);
      return;
    }

    if (isDashLine(line)) {
      flushItems();
      chunks.push(`<p class="reading-test__note-completion-dash">${escapeHtml(line)}</p>`);
      return;
    }

    currentItems.push(isBulletLine(line) ? stripBulletMarker(line) : line);
  });

  flushItems();
  return chunks.join('');
};

const buildNoteData = (group) => {
  const rawSource = containsInlinePlaceholders(group.groupContent)
    ? group.groupContent
    : buildFallbackNoteContent(group);
  const source = hasHtml(rawSource) && /<br\s*\/?>/i.test(rawSource)
    ? htmlToTextLines(rawSource)
    : rawSource;
  const { title, body } = splitTitleAndBody(source);

  return {
    title,
    bodyHtml: normalizeHtmlMediaSources(textToNoteHtml(body))
  };
};

function NoteCompletionQuestions({ group, answers, onAnswerChange }) {
  const contentRef = useRef(null);
  const placeholdersRef = useRef([]);
  const latestAnswersRef = useRef(answers);
  const latestHandlerRef = useRef(onAnswerChange);
  const noteData = useMemo(() => buildNoteData(group), [group]);

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

    const processedHtml = noteData.bodyHtml.replace(PLACEHOLDER_REGEX, (match, token) => {
      const question = questionByNumber.get(String(token)) || group.questions?.[sequentialIndex];
      sequentialIndex += 1;

      if (!question) return match;

      const placeholderId = `note-placeholder-${question.id}-${placeholderMeta.length}`;
      placeholderMeta.push({ placeholderId, question });
      return `<span class="reading-test__note-completion-placeholder" data-placeholder-id="${placeholderId}"></span>`;
    });

    container.innerHTML = processedHtml;

    placeholderMeta.forEach((meta) => {
      const placeholderElement = container.querySelector(`[data-placeholder-id="${meta.placeholderId}"]`);
      if (!placeholderElement) return;

      const wrapper = document.createElement('span');
      wrapper.className = 'reading-test__note-completion-blank';

      const numberChip = document.createElement('span');
      numberChip.className = 'reading-test__note-completion-blank-number';
      numberChip.textContent = meta.question.number?.toString() || '';
      wrapper.appendChild(numberChip);

      const input = document.createElement('input');
      input.type = 'text';
      input.className = 'reading-test__note-completion-input';
      input.placeholder = '';
      input.autocomplete = 'off';
      input.maxLength = 100;
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
  }, [group.questions, noteData.bodyHtml]);

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
    <div className="reading-test__note-completion-card">
      {noteData.title && (
        <div className="reading-test__note-completion-card-head">
          {noteData.title}
        </div>
      )}

      <div
        ref={contentRef}
        className="reading-test__note-completion-content"
      />
    </div>
  );
}

export default memo(NoteCompletionQuestions);
