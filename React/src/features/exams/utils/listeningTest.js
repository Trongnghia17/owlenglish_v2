const API_BASE_URL = import.meta.env.VITE_API_BASE_URL?.replace(/\/$/, '') || '';

export const containsInlinePlaceholders = (text) => /\{\{\s*[\w-]+\s*\}\}/.test(text || '');

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
  'table_selection'
]);

const MULTI_SLOT_TEXT_QUESTION_TYPES = new Set([
  'short_answer_questions',
  'summary_completion',
  'sentence_completion'
]);

export const isNoteCompletionGroup = (group) =>
  (group?.type || '').toLowerCase() === 'note_completion';

export const isFormCompletionGroup = (group) =>
  (group?.type || '').toLowerCase() === 'form_completion';

export const isTableCompletionGroup = (group) =>
  (group?.type || '').toLowerCase() === 'table_completion';

export const isPlanMapDiagramLabellingGroup = (group) =>
  (group?.type || '').toLowerCase() === 'plan_map_diagram_labelling';

export const isMatchingGroup = (group) =>
  (group?.type || '').toLowerCase() === 'matching';

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
      if (url.pathname.startsWith('/api/public/media/')) {
        return path;
      }
      if (url.pathname.startsWith('/storage/')) {
        return toPublicMediaUrl(url.pathname);
      }
    } catch {
      return path;
    }

    return path;
  }

  if (/^\/?api\/public\/media\//i.test(path)) {
    return `${API_BASE_URL}/${path.replace(/^\/+/, '')}`;
  }

  return toPublicMediaUrl(path);
};

export const normalizeHtmlMediaSources = (html = '') => {
  if (!html) return '';

  return String(html).replace(
    /(<img\b[^>]*?\bsrc=)(["'])([^"']+)\2/gi,
    (match, prefix, quote, value) => {
      const src = value.trim();

      if (!src || /^(data:|blob:)/i.test(src)) {
        return match;
      }

      if (/^https?:\/\//i.test(src)) {
        try {
          const url = new URL(src);
          if (!url.pathname.startsWith('/storage/')) {
            return match;
          }
        } catch {
          return match;
        }
      } else if (!/^\/?storage\//i.test(src)) {
        return match;
      }

      return `${prefix}${quote}${toStorageUrl(src)}${quote}`;
    }
  );
};

export const usesTwoColumnLayout = (groups = []) =>
  groups.some((group) => TWO_COLUMN_QUESTION_TYPES.has((group.type || '').toLowerCase()));

export const usesNoteCompletionLayout = (groups = []) =>
  groups.some(isNoteCompletionGroup);

export const isMultipleChoiceGroup = (group) =>
  (group?.type || '').toLowerCase() === 'multiple_choice';

export const isFlowChartCompletionGroup = (group) =>
  (group?.type || '').toLowerCase() === 'flow_chart_completion';

export const isShortAnswerGroup = (group) =>
  (group?.type || '').toLowerCase() === 'short_answer_questions';

export const isSummaryCompletionGroup = (group) =>
  (group?.type || '').toLowerCase() === 'summary_completion';

export const isSentenceCompletionGroup = (group) =>
  (group?.type || '').toLowerCase() === 'sentence_completion';

export const parseMetadata = (metadata) => {
  if (!metadata) return {};
  if (typeof metadata !== 'string') return metadata;

  try {
    return JSON.parse(metadata) || {};
  } catch {
    return {};
  }
};

export const isCorrectAnswerOption = (answer) =>
  answer?.is_correct === '1' ||
  answer?.is_correct === 1 ||
  answer?.is_correct === true;

export const hasAnswerValue = (answer) => {
  if (Array.isArray(answer)) {
    return answer.some((value) => String(value ?? '').trim() !== '');
  }

  return String(answer ?? '').trim() !== '';
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

export const getCorrectAnswerOptionCount = (question) => {
  const metadata = parseMetadata(question?.metadata);
  const answers = Array.isArray(metadata.answers) ? metadata.answers : [];

  return answers.filter(isCorrectAnswerOption).length;
};

const normalizeAnswerOption = (answer, index) => {
  const answerObject = typeof answer === 'string' ? { content: answer } : answer || {};
  const content = stripParagraphWrapper(answerObject.content || answerObject.text || '');

  return {
    letter: answerObject.letter || answerObject.label || String.fromCharCode(65 + index),
    content
  };
};

const getQuestionAnswerSlots = (question, questionType) => {
  const metadata = parseMetadata(question?.metadata);

  if (MULTI_SLOT_TEXT_QUESTION_TYPES.has(questionType) &&
    Array.isArray(metadata.answers) &&
    metadata.answers.length > 0
  ) {
    return metadata.answers;
  }

  return [null];
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
    optionsWithContent: metadata.answers.map(normalizeAnswerOption)
  };
};

const getFlowChartOptions = (questions = []) => {
  const firstQuestion = questions[0];
  const firstMetadata = parseMetadata(firstQuestion?.metadata);
  const firstAnswers = Array.isArray(firstMetadata.answers) ? firstMetadata.answers : [];
  const answersSource = firstAnswers.length > 1
    ? firstAnswers
    : questions.flatMap((question) => {
      const metadata = parseMetadata(question?.metadata);
      return Array.isArray(metadata.answers) ? metadata.answers : [];
    });
  const optionsWithContent = answersSource
    .map(normalizeAnswerOption)
    .filter((option) => option.content || option.letter);
  const uniqueOptions = optionsWithContent.filter((option, index, options) =>
    options.findIndex((candidate) =>
      candidate.letter === option.letter && candidate.content === option.content
    ) === index
  );

  return {
    options: uniqueOptions.map((option) => option.letter),
    optionsWithContent: uniqueOptions,
    optionTitle: firstMetadata.answer_label || 'Options'
  };
};

const getMatchingOptions = (questions = []) => {
  const firstQuestion = questions[0];
  const firstMetadata = parseMetadata(firstQuestion?.metadata);
  const firstAnswers = Array.isArray(firstMetadata.answers) ? firstMetadata.answers : [];
  const answersSource = firstAnswers.length > 1
    ? firstAnswers
    : questions.flatMap((question) => {
      const metadata = parseMetadata(question?.metadata);
      return Array.isArray(metadata.answers) ? metadata.answers : [];
    });
  const optionsWithContent = answersSource
    .map(normalizeAnswerOption)
    .filter((option) => option.content || option.letter);
  const uniqueOptions = optionsWithContent.filter((option, index, options) =>
    options.findIndex((candidate) =>
      candidate.letter === option.letter && candidate.content === option.content
    ) === index
  );

  return {
    options: uniqueOptions.map((option) => option.letter),
    optionsWithContent: uniqueOptions,
    optionTitle: firstMetadata.answer_label || 'Options'
  };
};

const getGroupAnswerOptions = (group) => {
  const questionType = (group.question_type || '').toLowerCase();
  const groupOptions = normalizeOptions(group.options);

  switch (questionType) {
    case 'multiple_choice':
      return getMultipleChoiceOptions(group.questions || []);
    case 'matching':
      return getMatchingOptions(group.questions || []);
    case 'flow_chart_completion':
      return getFlowChartOptions(group.questions || []);
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
  const sectionAudioUrl = section?.audio_url || section?.audio_file || section?.media?.audio;

  (section?.question_groups || []).forEach((group) => {
    const questionType = (group.question_type || '').toLowerCase();
    const questions = (group.questions || []).flatMap((question) => {
      const answerSlots = getQuestionAnswerSlots(question, questionType);
      const shouldUseAnswerSlotIds = MULTI_SLOT_TEXT_QUESTION_TYPES.has(questionType) &&
        answerSlots.length > 0 &&
        answerSlots[0] !== null;

      return answerSlots.map((answerSlot, answerIndex) => ({
        id: shouldUseAnswerSlotIds
          ? `${question.id}:${answerIndex}`
          : question.id,
        sourceQuestionId: question.id,
        answerIndex: shouldUseAnswerSlotIds ? answerIndex : null,
        number: questionNumber++,
        content: question.content,
        correctAnswer: answerSlot?.content ?? question.answer_content,
        imageUrl: toStorageUrl(question.image_url || question.image || question.media?.image),
        metadata: question.metadata
      }));
    });

    const { options, optionsWithContent, optionTitle } = getGroupAnswerOptions(group);
    const fallbackNumber = questions[0]?.number || startQuestionNumber;

    groups.push({
      id: group.id,
      part: partNumber,
      type: group.question_type || 'TRUE_FALSE_NOT_GIVEN',
      instructions: group.instructions,
      groupContent: group.content,
      imageUrl: toStorageUrl(group.image_url || group.image || group.media?.image) ||
        questions.find((question) => question.imageUrl)?.imageUrl ||
        null,
      audioUrl: group.audio_url || group.audio_file || group.audio || sectionAudioUrl,
      options,
      optionsWithContent,
      optionTitle,
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

export const getCurrentPartAudio = ({ skillData, sectionData, currentPartGroups }) => {
  const skillAudio = skillData?.audio_file || skillData?.audio_url || skillData?.media?.audio;
  const sectionAudio = sectionData?.audio_url ||
    sectionData?.audio_file ||
    sectionData?.media?.audio ||
    sectionData?.exam_skill?.audio_file ||
    sectionData?.examSkill?.audio_file ||
    sectionData?.skill?.audio_file;
  const groupAudio = currentPartGroups.find((group) => group.audioUrl)?.audioUrl;
  const audioSource = sectionData
    ? groupAudio || sectionAudio || skillAudio
    : skillAudio || groupAudio;

  return toStorageUrl(audioSource);
};

export const getAnsweredCount = (answers = {}) =>
  Object.values(answers).filter(hasAnswerValue).length;
