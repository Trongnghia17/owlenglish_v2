import { useEffect, useRef } from 'react';
import { normalizeHtmlMediaSources } from '../../utils/listeningTest';

export default function InlineAnswerContent({
  content,
  questions = [],
  answers = {},
  onAnswerChange,
  onQuestionFocus,
  variant = 'default'
}) {
  const containerRef = useRef(null);
  const placeholdersMetaRef = useRef([]);
  const latestHandlerRef = useRef(onAnswerChange);
  const latestQuestionFocusHandlerRef = useRef(onQuestionFocus);
  const latestAnswersRef = useRef(answers);
  const isFlowVariant = variant === 'flow';
  const isSentenceVariant = variant === 'sentence';
  const isSummaryVariant = variant === 'summary';
  const isPillVariant = isFlowVariant || isSentenceVariant || isSummaryVariant;

  useEffect(() => {
    latestHandlerRef.current = onAnswerChange;
  }, [onAnswerChange]);

  useEffect(() => {
    latestQuestionFocusHandlerRef.current = onQuestionFocus;
  }, [onQuestionFocus]);

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

    const normalizedContent = normalizeHtmlMediaSources(content);
    const processedContent = normalizedContent.replace(placeholderRegex, (match) => {
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
      wrapper.className = [
        'listening-test__inline-input-wrapper',
        isPillVariant ? 'listening-test__inline-input-wrapper--sentence' : '',
        isFlowVariant ? 'listening-test__inline-input-wrapper--flow' : '',
        isSummaryVariant ? 'listening-test__inline-input-wrapper--summary' : ''
      ].filter(Boolean).join(' ');

      if (isPillVariant) {
        const numberChip = document.createElement('span');
        numberChip.className = 'listening-test__inline-input-number';
        numberChip.textContent = meta.question.number?.toString() || '';
        wrapper.appendChild(numberChip);
      }

      const input = document.createElement('input');
      input.type = 'text';
      input.className = [
        'listening-test__inline-input',
        isPillVariant ? 'listening-test__inline-input--sentence' : '',
        isFlowVariant ? 'listening-test__inline-input--flow' : '',
        isSummaryVariant ? 'listening-test__inline-input--summary' : ''
      ].filter(Boolean).join(' ');
      input.placeholder = isSummaryVariant ? '............' : isPillVariant ? '' : meta.question.number?.toString() || '';
      input.maxLength = isFlowVariant ? 1 : 50;
      input.dataset.questionId = meta.question.id;
      input.autocomplete = 'off';
      input.setAttribute('aria-label', `Answer ${meta.question.number || ''}`.trim());
      input.value = latestAnswersRef.current?.[meta.question.id] || '';

      const syncValueState = (value) => {
        wrapper.classList.toggle('has-value', String(value ?? '').trim() !== '');
      };
      syncValueState(input.value);

      const handleInput = (event) => {
        const nextValue = isFlowVariant
          ? event.target.value.toUpperCase().slice(0, 1)
          : event.target.value;
        event.target.value = nextValue;
        syncValueState(nextValue);
        latestHandlerRef.current?.(meta.question.id, nextValue);
      };

      const handleFocus = (event) => {
        event.stopPropagation();
        latestQuestionFocusHandlerRef.current?.(meta.question.id);
      };

      const handleDragOver = (event) => {
        event.preventDefault();
        event.stopPropagation();
        event.dataTransfer.dropEffect = 'copy';
        wrapper.classList.add('is-drop-target');
        latestQuestionFocusHandlerRef.current?.(meta.question.id);
      };

      const handleDragLeave = (event) => {
        if (event.relatedTarget && wrapper.contains(event.relatedTarget)) return;
        wrapper.classList.remove('is-drop-target');
      };

      const handleDrop = (event) => {
        event.preventDefault();
        event.stopPropagation();

        const droppedValue = (
          event.dataTransfer.getData('application/x-flow-chart-answer') ||
          event.dataTransfer.getData('text/plain')
        ).trim().toUpperCase().slice(0, 1);

        wrapper.classList.remove('is-drop-target');

        if (!droppedValue) return;

        input.value = droppedValue;
        syncValueState(droppedValue);
        latestQuestionFocusHandlerRef.current?.(meta.question.id);
        latestHandlerRef.current?.(meta.question.id, droppedValue);
      };

      input.addEventListener('input', handleInput);
      input.addEventListener('focus', handleFocus);
      input.addEventListener('click', handleFocus);

      if (isFlowVariant) {
        wrapper.addEventListener('dragenter', handleDragOver);
        wrapper.addEventListener('dragover', handleDragOver);
        wrapper.addEventListener('dragleave', handleDragLeave);
        wrapper.addEventListener('drop', handleDrop);
      }

      wrapper.appendChild(input);
      placeholderElement.replaceWith(wrapper);

      meta.input = input;
      meta.wrapper = wrapper;
      meta.cleanup = () => {
        input.removeEventListener('input', handleInput);
        input.removeEventListener('focus', handleFocus);
        input.removeEventListener('click', handleFocus);
        wrapper.removeEventListener('dragenter', handleDragOver);
        wrapper.removeEventListener('dragover', handleDragOver);
        wrapper.removeEventListener('dragleave', handleDragLeave);
        wrapper.removeEventListener('drop', handleDrop);
      };
    });

    placeholdersMetaRef.current = placeholderMeta;

    return () => {
      placeholderMeta.forEach((meta) => meta.cleanup?.());
    };
  }, [content, questions, isFlowVariant, isPillVariant, isSummaryVariant]);

  useEffect(() => {
    placeholdersMetaRef.current.forEach(({ question, input, wrapper }) => {
      if (!input) return;
      const nextValue = answers?.[question.id] || '';
      if (input.value !== nextValue) {
        input.value = nextValue;
      }
      wrapper?.classList.toggle('has-value', String(nextValue ?? '').trim() !== '');
    });
  }, [answers]);

  return (
    <div
      className={[
        'listening-test__group-content-parsed',
        isPillVariant ? 'listening-test__group-content-parsed--sentence' : '',
        isFlowVariant ? 'listening-test__group-content-parsed--flow' : '',
        isSummaryVariant ? 'listening-test__group-content-parsed--summary' : ''
      ].filter(Boolean).join(' ')}
      ref={containerRef}
    />
  );
}
