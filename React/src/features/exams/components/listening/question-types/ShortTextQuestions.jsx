import { memo } from 'react';
import InlineAnswerContent from '../InlineAnswerContent';
import {
  containsInlinePlaceholders,
  isSentenceCompletionGroup,
  isShortAnswerGroup
} from '../../../utils/listeningTest';

const stripHtml = (content) =>
  String(content ?? '').replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ').replace(/\s+/g, ' ').trim();

const normalizePrompt = (content = '') => stripHtml(content).toLowerCase();

const createShortAnswerBlocks = (questions = []) =>
  questions.reduce((blocks, question) => {
    const sourceQuestionId = question.sourceQuestionId ?? question.id;
    const promptKey = normalizePrompt(question.content);
    const previousBlock = blocks[blocks.length - 1];
    const shouldAppendToPrevious =
      previousBlock &&
      (previousBlock.sourceQuestionId === sourceQuestionId ||
        !promptKey ||
        previousBlock.promptKey === promptKey);

    if (shouldAppendToPrevious) {
      previousBlock.questions.push(question);
      return blocks;
    }

    blocks.push({
      id: sourceQuestionId,
      sourceQuestionId,
      promptKey,
      content: question.content || '',
      questions: [question]
    });

    return blocks;
  }, []);

const appendPlaceholdersToContent = (content, questions = []) => {
  const placeholders = questions.map((question) => ` {{${question.number}}}`).join('');
  const safeContent = String(content ?? '').trim();

  if (!safeContent) {
    return `<p>${placeholders.trim()}</p>`;
  }

  if (/<\/p>\s*$/i.test(safeContent)) {
    return safeContent.replace(/<\/p>\s*$/i, `${placeholders}</p>`);
  }

  return `<p>${safeContent}${placeholders}</p>`;
};

const buildSentenceCompletionContent = (group) => {
  const groupContent = group.groupContent || '';

  if (containsInlinePlaceholders(groupContent)) {
    return groupContent;
  }

  const fallbackBlocks = createShortAnswerBlocks(group.questions)
    .map((block) => appendPlaceholdersToContent(block.content, block.questions))
    .join('');

  return `${groupContent || ''}${fallbackBlocks}`;
};

function ShortTextQuestions({ group, answers, onAnswerChange }) {
  if (isSentenceCompletionGroup(group)) {
    return (
      <div className="listening-test__sentence-card">
        <InlineAnswerContent
          content={buildSentenceCompletionContent(group)}
          questions={group.questions}
          answers={answers}
          onAnswerChange={onAnswerChange}
          variant="sentence"
        />
      </div>
    );
  }

  const hasPlaceholders = containsInlinePlaceholders(group.groupContent);

  if (hasPlaceholders) {
    return (
      <div className="listening-test__question-group-with-inputs">
        <InlineAnswerContent
          content={group.groupContent}
          questions={group.questions}
          answers={answers}
          onAnswerChange={onAnswerChange}
        />
      </div>
    );
  }

  if (isShortAnswerGroup(group)) {
    return createShortAnswerBlocks(group.questions).map((block) => (
      <div key={block.id} className="listening-test__short-answer-card">
        <div className="listening-test__short-answer-card-header">
          <div
            className="listening-test__short-answer-question-text"
            dangerouslySetInnerHTML={{ __html: block.content }}
          />
        </div>
        <div className="listening-test__short-answer-list">
          {block.questions.map((question) => (
            <label key={question.id} className="listening-test__short-answer-row">
              <span className="listening-test__short-answer-number">{question.number}</span>
              <input
                type="text"
                className="listening-test__short-answer-input"
                aria-label={`Answer ${question.number}`}
                value={answers[question.id] || ''}
                onChange={(event) => onAnswerChange(question.id, event.target.value)}
                maxLength={100}
              />
            </label>
          ))}
        </div>
      </div>
    ));
  }

  return group.questions.map((question) => (
    <div key={question.id} className="listening-test__question-item listening-test__question-item--input">
      <div className="listening-test__question-row">
        <div className="listening-test__question-number">
          {question.number}
        </div>
        <div
          className="listening-test__question-text"
          dangerouslySetInnerHTML={{ __html: question.content }}
        />
      </div>
      <div className="listening-test__answer-input-wrapper">
        <input
          type="text"
          className="listening-test__answer-input"
          placeholder="Type your answer here..."
          value={answers[question.id] || ''}
          onChange={(event) => onAnswerChange(question.id, event.target.value)}
          maxLength={100}
        />
      </div>
    </div>
  ));
}

export default memo(ShortTextQuestions);
