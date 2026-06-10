import { memo } from 'react';
import { stripParagraphWrapper } from '../../../utils/readingTest';

const INPUT_TOKEN = '__READING_SENTENCE_COMPLETION_INPUT__';

const stripHtmlToText = (content = '') =>
  String(content ?? '')
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

const PLACEHOLDER_REGEX = /\{\{\s*[\w-]+\s*\}\}|_{3,}|\.{4,}|…{2,}|-{4,}/g;

const normalizeContent = (content = '') =>
  stripParagraphWrapper(String(content ?? '').replace(/&nbsp;/g, ' '));

const extractPlaceholderFragment = (content = '', placeholderIndex = 0) => {
  const normalizedContent = normalizeContent(content);
  const paragraphs = normalizedContent.match(/<p\b[^>]*>[\s\S]*?<\/p>/gi) || [];
  let currentPlaceholderIndex = 0;

  for (const paragraph of paragraphs) {
    const placeholders = Array.from(paragraph.matchAll(PLACEHOLDER_REGEX));
    if (placeholderIndex < currentPlaceholderIndex + placeholders.length) {
      return normalizeContent(paragraph);
    }
    currentPlaceholderIndex += placeholders.length;
  }

  return Array.from(normalizedContent.matchAll(PLACEHOLDER_REGEX)).length > placeholderIndex
    ? normalizedContent
    : '';
};

const getQuestionContent = (group, question, questionIndex) => {
  const content = normalizeContent(question.content || '');
  if (stripHtmlToText(content)) return content;

  return extractPlaceholderFragment(group.groupContent, questionIndex);
};

const buildSegments = (content, question) => {
  const matches = Array.from(content.matchAll(PLACEHOLDER_REGEX));

  if (matches.length === 0) {
    return [
      { type: 'html', value: content },
      { type: 'input' }
    ];
  }

  const targetIndex = Number.isInteger(question.answerIndex)
    ? Math.min(question.answerIndex, matches.length - 1)
    : 0;

  const output = [];
  let cursor = 0;

  matches.forEach((match, index) => {
    if (match.index > cursor) {
      output.push({
        type: 'html',
        value: content.slice(cursor, match.index)
      });
    }

    output.push({ type: index === targetIndex ? 'input' : 'blank' });
    cursor = match.index + match[0].length;
  });

  if (cursor < content.length) {
    output.push({ type: 'html', value: content.slice(cursor) });
  }

  return output;
};

function SentenceCompletionQuestions({ group, answers, onAnswerChange }) {
  return (
    <div className="reading-test__sentence-completion">
      <div className="reading-test__sentence-completion-card">
        <div className="reading-test__sentence-completion-list">
          {group.questions.map((question) => {
            const answer = String(answers[question.id] ?? '');
            const content = getQuestionContent(group, question, question.answerIndex ?? question.number - group.startNumber);
            const segments = buildSegments(content, question);

            return (
              <label
                key={question.id}
                className={[
                  'reading-test__sentence-completion-row',
                  answer.trim() ? 'is-filled' : ''
                ].filter(Boolean).join(' ')}
              >
                <span className="reading-test__sentence-completion-number">
                  {question.number}
                </span>

                <span className="reading-test__sentence-completion-text">
                  {segments.map((segment, index) => {
                    if (segment.type === 'input') {
                      return (
                        <span
                          key={`${INPUT_TOKEN}-${question.id}-${index}`}
                          className={[
                            'reading-test__sentence-completion-blank',
                            answer.trim() ? 'is-filled' : ''
                          ].filter(Boolean).join(' ')}
                        >
                          <input
                            type="text"
                            value={answer}
                            placeholder="..................."
                            aria-label={`Question ${question.number} answer`}
                            autoComplete="off"
                            onChange={(event) => onAnswerChange(question.id, event.target.value)}
                          />
                        </span>
                      );
                    }

                    if (segment.type === 'blank') {
                      return (
                        <span
                          key={`blank-${question.id}-${index}`}
                          className="reading-test__sentence-completion-blank reading-test__sentence-completion-blank--static"
                        >
                          ...................
                        </span>
                      );
                    }

                    return (
                      <span
                        key={`text-${question.id}-${index}`}
                        className="reading-test__sentence-completion-html"
                        dangerouslySetInnerHTML={{ __html: segment.value }}
                      />
                    );
                  })}
                </span>
              </label>
            );
          })}
        </div>
      </div>
    </div>
  );
}

export default memo(SentenceCompletionQuestions);
