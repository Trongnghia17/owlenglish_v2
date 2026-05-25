import { useEffect, useRef } from 'react';

export default function InlineAnswerContent({
  content,
  questions = [],
  answers = {},
  onAnswerChange,
  variant = 'default'
}) {
  const containerRef = useRef(null);
  const placeholdersMetaRef = useRef([]);
  const latestHandlerRef = useRef(onAnswerChange);
  const latestAnswersRef = useRef(answers);
  const isSentenceVariant = variant === 'sentence';

  useEffect(() => {
    latestHandlerRef.current = onAnswerChange;
  }, [onAnswerChange]);

  useEffect(() => {
    latestAnswersRef.current = answers;
  }, [answers]);

  useEffect(() => {
    const container = containerRef.current;
    if (!container) return;

    if (!content) {
      container.innerHTML = '';
      placeholdersMetaRef.current = [];
      return;
    }

    const placeholderRegex = /\{\{\s*([\w-]+)\s*\}\}/g;
    const placeholderMeta = [];
    let questionIndex = 0;

    const processedContent = content.replace(placeholderRegex, (match) => {
      const question = questions[questionIndex];
      questionIndex += 1;

      if (!question) {
        return match;
      }

      const placeholderId = `inline-placeholder-${question.id}`;
      placeholderMeta.push({ placeholderId, question });
      return `<span class="listening-test__inline-placeholder" data-placeholder-id="${placeholderId}"></span>`;
    });

    container.innerHTML = processedContent;

    placeholderMeta.forEach((meta) => {
      const placeholderElement = container.querySelector(`[data-placeholder-id="${meta.placeholderId}"]`);
      if (!placeholderElement) return;

      const wrapper = document.createElement('span');
      wrapper.className = `listening-test__inline-input-wrapper ${isSentenceVariant ? 'listening-test__inline-input-wrapper--sentence' : ''}`.trim();

      if (isSentenceVariant) {
        const numberChip = document.createElement('span');
        numberChip.className = 'listening-test__inline-input-number';
        numberChip.textContent = meta.question.number?.toString() || '';
        wrapper.appendChild(numberChip);
      }

      const input = document.createElement('input');
      input.type = 'text';
      input.className = `listening-test__inline-input ${isSentenceVariant ? 'listening-test__inline-input--sentence' : ''}`.trim();
      input.placeholder = isSentenceVariant ? '' : meta.question.number?.toString() || '';
      input.maxLength = 50;
      input.dataset.questionId = meta.question.id;
      input.autocomplete = 'off';
      input.setAttribute('aria-label', `Answer ${meta.question.number || ''}`.trim());
      input.value = latestAnswersRef.current?.[meta.question.id] || '';

      const handleInput = (event) => {
        latestHandlerRef.current?.(meta.question.id, event.target.value);
      };

      const stopPropagation = (event) => event.stopPropagation();

      input.addEventListener('input', handleInput);
      input.addEventListener('focus', stopPropagation);
      input.addEventListener('click', stopPropagation);

      wrapper.appendChild(input);
      placeholderElement.replaceWith(wrapper);

      meta.input = input;
      meta.cleanup = () => {
        input.removeEventListener('input', handleInput);
        input.removeEventListener('focus', stopPropagation);
        input.removeEventListener('click', stopPropagation);
      };
    });

    placeholdersMetaRef.current = placeholderMeta;

    return () => {
      placeholderMeta.forEach((meta) => meta.cleanup?.());
    };
  }, [content, questions, isSentenceVariant]);

  useEffect(() => {
    placeholdersMetaRef.current.forEach(({ question, input }) => {
      if (!input) return;
      const nextValue = answers?.[question.id] || '';
      if (input.value !== nextValue) {
        input.value = nextValue;
      }
    });
  }, [answers]);

  return (
    <div
      className={`listening-test__group-content-parsed ${isSentenceVariant ? 'listening-test__group-content-parsed--sentence' : ''}`.trim()}
      ref={containerRef}
    />
  );
}
