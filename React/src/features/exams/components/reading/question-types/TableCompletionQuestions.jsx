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

const buildFallbackTableHtml = (questions = []) => `
  <table>
    <thead>
      <tr>
        <th>Question</th>
        <th>Answer</th>
      </tr>
    </thead>
    <tbody>
      ${questions
        .map(
          (question) => `
            <tr>
              <td>${question.content || escapeHtml(`Question ${question.number}`)}</td>
              <td>{{${question.number}}}</td>
            </tr>
          `
        )
        .join('')}
    </tbody>
  </table>
`;

const findQuestionForToken = (questions, token, usedQuestionIds) => {
  const normalizedToken = String(token ?? '').trim();
  if (!normalizedToken) return null;

  return questions.find((question) => {
    if (usedQuestionIds.has(question.id)) {
      return false;
    }

    return [
      question.id,
      question.sourceQuestionId,
      question.number
    ].some((value) => String(value ?? '') === normalizedToken);
  });
};

const buildTableHtml = (group) => {
  const source = containsInlinePlaceholders(group.groupContent)
    ? group.groupContent
    : buildFallbackTableHtml(group.questions || []);

  return normalizeHtmlMediaSources(source);
};

function TableCompletionQuestions({ group, answers, onAnswerChange }) {
  const contentRef = useRef(null);
  const placeholdersRef = useRef([]);
  const latestAnswersRef = useRef(answers);
  const latestHandlerRef = useRef(onAnswerChange);
  const tableHtml = useMemo(() => buildTableHtml(group), [group]);

  useEffect(() => {
    latestAnswersRef.current = answers;
  }, [answers]);

  useEffect(() => {
    latestHandlerRef.current = onAnswerChange;
  }, [onAnswerChange]);

  useEffect(() => {
    const container = contentRef.current;
    if (!container) return undefined;

    let questionIndex = 0;
    const usedQuestionIds = new Set();
    const placeholderMeta = [];

    const processedHtml = tableHtml.replace(PLACEHOLDER_REGEX, (match, token) => {
      const matchedQuestion = findQuestionForToken(group.questions || [], token, usedQuestionIds);
      const sequentialQuestion = (group.questions || [])
        .slice(questionIndex)
        .find((question) => !usedQuestionIds.has(question.id));
      const question = matchedQuestion || sequentialQuestion;

      questionIndex += 1;

      if (!question) return match;

      usedQuestionIds.add(question.id);
      const placeholderId = `table-placeholder-${question.id}-${placeholderMeta.length}`;
      placeholderMeta.push({ placeholderId, question });
      return `<span class="reading-test__table-completion-placeholder" data-placeholder-id="${placeholderId}"></span>`;
    });

    container.innerHTML = processedHtml;

    placeholderMeta.forEach((meta) => {
      const placeholderElement = container.querySelector(`[data-placeholder-id="${meta.placeholderId}"]`);
      if (!placeholderElement) return;

      const wrapper = document.createElement('span');
      wrapper.className = 'reading-test__table-completion-blank';

      const numberChip = document.createElement('span');
      numberChip.className = 'reading-test__table-completion-blank-number';
      numberChip.textContent = meta.question.number?.toString() || '';
      wrapper.appendChild(numberChip);

      const input = document.createElement('input');
      input.type = 'text';
      input.className = 'reading-test__table-completion-input';
      input.placeholder = '.......';
      input.autocomplete = 'off';
      input.maxLength = 100;
      input.size = 7;
      input.value = latestAnswersRef.current?.[meta.question.id] || '';
      input.setAttribute('aria-label', `Question ${meta.question.number || ''} answer`.trim());

      const syncState = (value) => {
        input.size = Math.max(7, String(value || input.placeholder).length);
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
  }, [group.questions, tableHtml]);

  useEffect(() => {
    placeholdersRef.current.forEach(({ question, input, wrapper }) => {
      if (!input) return;
      const nextValue = answers?.[question.id] || '';
      if (input.value !== nextValue) {
        input.value = nextValue;
      }
      input.size = Math.max(7, String(nextValue || input.placeholder).length);
      wrapper?.classList.toggle('is-filled', String(nextValue ?? '').trim() !== '');
    });
  }, [answers]);

  return (
    <div className="reading-test__table-completion-card">
      <div
        ref={contentRef}
        className="reading-test__table-completion-content"
      />
    </div>
  );
}

export default memo(TableCompletionQuestions);
