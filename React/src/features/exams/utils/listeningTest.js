const API_BASE_URL = import.meta.env.VITE_API_BASE_URL?.replace(/\/$/, '') || '';

export const containsInlinePlaceholders = (text) => /\{\{\s*[a-zA-Z0-9]+\s*\}\}/.test(text || '');

const TEXT_ANSWER_QUESTION_TYPES = new Set([
  'short_text',
  'form_completion',
  'table_completion',
  'flow_chart_completion',
  'summary_completion',
  'sentence_completion',
  'short_answer_questions',
  'plan_map_diagram_labelling'
]);

const CHOICE_ANSWER_QUESTION_TYPES = new Set([
  'multiple_choice',
  'matching'
]);

const TWO_COLUMN_QUESTION_TYPES = new Set([
  'table_selection',
  'table_completion',
  'flow_chart_completion',
  'plan_map_diagram_labelling'
]);

export const isNoteCompletionGroup = (group) =>
  (group?.type || '').toLowerCase() === 'note_completion';

export const isTextAnswerQuestionType = (questionType) =>
  TEXT_ANSWER_QUESTION_TYPES.has((questionType || '').toLowerCase());

export const isChoiceAnswerQuestionType = (questionType) =>
  CHOICE_ANSWER_QUESTION_TYPES.has((questionType || '').toLowerCase());

const encodeStoragePath = (path) =>
  path.split('/').map((segment) => encodeURIComponent(segment)).join('/');

const toPublicMediaUrl = (path) => {
  const storagePath = path
    .replace(/^\/storage\//, '')
    .replace(/^storage\//, '')
    .replace(/^\/+/, '');

  return `${API_BASE_URL}/api/public/media/${encodeStoragePath(storagePath)}`;
};

export const toStorageUrl = (path) => {
  if (!path) return null;

  if (/^https?:\/\//i.test(path)) {
    try {
      const url = new URL(path);
      if (url.pathname.startsWith('/storage/')) {
        return toPublicMediaUrl(url.pathname);
      }
    } catch {
      return path;
    }

    return path;
  }

  return toPublicMediaUrl(path);
};

export const usesTwoColumnLayout = (groups = []) =>
  groups.some((group) => TWO_COLUMN_QUESTION_TYPES.has((group.type || '').toLowerCase()));

export const usesNoteCompletionLayout = (groups = []) =>
  groups.some(isNoteCompletionGroup);

export const isMultipleChoiceGroup = (group) =>
  (group?.type || '').toLowerCase() === 'multiple_choice';

export const parseMetadata = (metadata) => {
  if (!metadata) return {};
  if (typeof metadata !== 'string') return metadata;

  try {
    return JSON.parse(metadata) || {};
  } catch {
    return {};
  }
};

export const stripParagraphWrapper = (content = '') =>
  content.replace(/^<p[^>]*>|<\/p>$/gi, '').trim();

export const getQuestionAnswerOptions = (question, fallbackOptions = []) => {
  const metadata = parseMetadata(question?.metadata);

  if (Array.isArray(metadata.answers) && metadata.answers.length > 0) {
    return metadata.answers.map((answer, index) => ({
      letter: String.fromCharCode(65 + index),
      content: stripParagraphWrapper(answer.content || '')
    }));
  }

  return fallbackOptions;
};

const normalizeOptions = (options) => {
  if (Array.isArray(options)) return options;
  if (typeof options !== 'string') return [];

  try {
    const parsedOptions = JSON.parse(options);
    return Array.isArray(parsedOptions) ? parsedOptions : [];
  } catch {
    return [];
  }
};

const getMultipleChoiceOptions = (questions = []) => {
  const firstQuestion = questions[0];
  const metadata = parseMetadata(firstQuestion?.metadata);

  if (!Array.isArray(metadata.answers)) {
    return { options: [], optionsWithContent: null };
  }

  return {
    options: metadata.answers.map((_, index) => String.fromCharCode(65 + index)),
    optionsWithContent: metadata.answers.map((answer, index) => ({
      letter: String.fromCharCode(65 + index),
      content: stripParagraphWrapper(answer.content || '')
    }))
  };
};

const getGroupAnswerOptions = (group) => {
  const questionType = (group.question_type || '').toLowerCase();
  const groupOptions = normalizeOptions(group.options);

  switch (questionType) {
    case 'multiple_choice':
    case 'matching':
      return getMultipleChoiceOptions(group.questions || []);
    case 'yes_no_not_given':
      return {
        options: groupOptions.length > 0 ? groupOptions : ['Yes', 'No', 'Not Given'],
        optionsWithContent: null
      };
    case 'true_false_not_given':
      return {
        options: groupOptions.length > 0 ? groupOptions : ['True', 'False', 'Not Given'],
        optionsWithContent: null
      };
    case 'short_text':
    case 'note_completion':
    case 'form_completion':
    case 'table_completion':
    case 'flow_chart_completion':
    case 'summary_completion':
    case 'sentence_completion':
    case 'short_answer_questions':
    case 'plan_map_diagram_labelling':
      return { options: [], optionsWithContent: null };
    default:
      return {
        options: groupOptions,
        optionsWithContent: null
      };
  }
};

export const normalizeListeningSection = (section, partNumber = 1, startQuestionNumber = 1) => {
  const groups = [];
  let questionNumber = startQuestionNumber;

  (section?.question_groups || []).forEach((group) => {
    const questions = (group.questions || []).map((question) => ({
      id: question.id,
      number: questionNumber++,
      content: question.content,
      correctAnswer: question.answer_content,
      metadata: question.metadata
    }));

    const { options, optionsWithContent } = getGroupAnswerOptions(group);
    const fallbackNumber = questions[0]?.number || startQuestionNumber;

    groups.push({
      id: group.id,
      part: partNumber,
      type: group.question_type || 'TRUE_FALSE_NOT_GIVEN',
      instructions: group.instructions,
      groupContent: group.content,
      audioUrl: group.audio_url,
      options,
      optionsWithContent,
      questions,
      startNumber: questions[0]?.number || fallbackNumber,
      endNumber: questions[questions.length - 1]?.number || fallbackNumber
    });
  });

  return {
    groups,
    nextQuestionNumber: questionNumber
  };
};

export const createPartTitle = (partNumber, groups = [], fallbackStart = 1) => {
  const firstNumber = groups[0]?.startNumber || fallbackStart;
  const lastGroup = groups[groups.length - 1];
  const lastNumber = lastGroup?.endNumber || Math.max(0, fallbackStart - 1);

  return `Part ${partNumber} (${firstNumber}-${lastNumber})`;
};

export const getCurrentPartAudio = ({ skillData, sectionData, currentPartGroups }) =>
  toStorageUrl(
    skillData?.audio_file ||
    skillData?.audio_url ||
    skillData?.media?.audio ||
    sectionData?.exam_skill?.audio_file ||
    sectionData?.skill?.audio_file ||
    currentPartGroups[0]?.audioUrl
  );

export const getAnsweredCount = (answers = {}) =>
  Object.values(answers).filter((answer) => String(answer ?? '').trim() !== '').length;
