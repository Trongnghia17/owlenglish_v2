import { containsInlinePlaceholders } from '../../../utils/listeningTest';

export const stripHtml = (content) =>
  String(content ?? '').replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ').replace(/\s+/g, ' ').trim();

const normalizePrompt = (content = '') => stripHtml(content).toLowerCase();

export const createQuestionBlocks = (questions = []) =>
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

export const buildInlineCompletionContent = (group) => {
  const groupContent = group.groupContent || '';

  if (containsInlinePlaceholders(groupContent)) {
    return groupContent;
  }

  const fallbackBlocks = createQuestionBlocks(group.questions)
    .map((block) => appendPlaceholdersToContent(block.content, block.questions))
    .join('');

  return `${groupContent || ''}${fallbackBlocks}`;
};
