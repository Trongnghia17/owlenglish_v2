const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL?.replace(/\/$/, '') || '';

export const containsInlinePlaceholders = (text) =>
  /\{\{\s*[\w-]+\s*\}\}/.test(text || '');

const TEXT_ANSWER_QUESTION_TYPES = new Set([
  'sentence_completion',
  'summary_completion',
  'note_completion',
  'table_completion',
  'flow_chart_completion',
  'diagram_label_completion',
  'short_answer_questions'
]);

const CHOICE_ANSWER_QUESTION_TYPES = new Set([
  'multiple_choice',
  'true_false_not_given',
  'yes_no_not_given',
  'matching_information',
  'matching_headings',
  'matching_features',
  'matching_sentence_endings'
]);

const TWO_COLUMN_QUESTION_TYPES = new Set([
  'multiple_choice',
  'true_false_not_given',
  'yes_no_not_given',
  'matching_information',
  'matching_headings',
  'matching_features',
  'matching_sentence_endings',
  'sentence_completion',
  'summary_completion',
  'note_completion',
  'table_completion',
  'flow_chart_completion',
  'diagram_label_completion',
  'short_answer_questions'
]);

const MULTI_SLOT_TEXT_QUESTION_TYPES = new Set([
  'note_completion',
  'table_completion',
  'flow_chart_completion',
  'diagram_label_completion',
  'summary_completion',
  'sentence_completion',
  'short_answer_questions'
]);

export const isReadingMultipleChoiceGroup = (group) =>
  (group?.type || '').toLowerCase() === 'multiple_choice';

export const isReadingTrueFalseNotGivenGroup = (group) =>
  (group?.type || '').toLowerCase() ===
  'true_false_not_given';

export const isReadingYesNoNotGivenGroup = (group) =>
  (group?.type || '').toLowerCase() ===
  'yes_no_not_given';

export const isReadingMatchingInformationGroup = (
  group
) =>
  (group?.type || '').toLowerCase() ===
  'matching_information';

export const isReadingMatchingHeadingsGroup = (
  group
) =>
  (group?.type || '').toLowerCase() ===
  'matching_headings';

export const isReadingMatchingFeaturesGroup = (
  group
) =>
  (group?.type || '').toLowerCase() ===
  'matching_features';

export const isReadingMatchingSentenceEndingsGroup =
  (group) =>
    (group?.type || '').toLowerCase() ===
    'matching_sentence_endings';

export const isReadingSentenceCompletionGroup = (
  group
) =>
  (group?.type || '').toLowerCase() ===
  'sentence_completion';

export const isReadingSummaryCompletionGroup = (
  group
) =>
  (group?.type || '').toLowerCase() ===
  'summary_completion';

export const isReadingNoteCompletionGroup = (
  group
) =>
  (group?.type || '').toLowerCase() ===
  'note_completion';

export const isReadingTableCompletionGroup = (
  group
) =>
  (group?.type || '').toLowerCase() ===
  'table_completion';

export const isReadingFlowChartCompletionGroup =
  (group) =>
    (group?.type || '').toLowerCase() ===
    'flow_chart_completion';

export const isReadingDiagramLabelCompletionGroup =
  (group) =>
    (group?.type || '').toLowerCase() ===
    'diagram_label_completion';

export const isReadingShortAnswerGroup = (
  group
) =>
  (group?.type || '').toLowerCase() ===
  'short_answer_questions';

export const isReadingTextAnswerQuestionType = (
  questionType
) =>
  TEXT_ANSWER_QUESTION_TYPES.has(
    (questionType || '').toLowerCase()
  );

export const isReadingChoiceAnswerQuestionType =
  (questionType) =>
    CHOICE_ANSWER_QUESTION_TYPES.has(
      (questionType || '').toLowerCase()
    );

const encodeStoragePath = (path) =>
  path
    .split('/')
    .map((segment) =>
      encodeURIComponent(segment)
    )
    .join('/');

const toPublicMediaUrl = (path) => {
  const storagePath = path
    .replace(/^\/storage\//, '')
    .replace(/^storage\//, '')
    .replace(/^\/+/, '');

  return `${API_BASE_URL}/api/public/media/${encodeStoragePath(
    storagePath
  )}`;
};

export const toStorageUrl = (path) => {
  if (!path) return null;

  if (/^https?:\/\//i.test(path)) {
    try {
      const url = new URL(path);

      if (
        url.pathname.startsWith(
          '/api/public/media/'
        )
      ) {
        return path;
      }

      if (
        url.pathname.startsWith('/storage/')
      ) {
        return toPublicMediaUrl(
          url.pathname
        );
      }
    } catch {
      return path;
    }

    return path;
  }

  if (
    /^\/?api\/public\/media\//i.test(path)
  ) {
    return `${API_BASE_URL}/${path.replace(
      /^\/+/,
      ''
    )}`;
  }

  return toPublicMediaUrl(path);
};

export const normalizeHtmlMediaSources = (
  html = ''
) => {
  if (!html) return '';

  return String(html).replace(
    /(<img\b[^>]*?\bsrc=)(["'])([^"']+)\2/gi,
    (match, prefix, quote, value) => {
      const src = value.trim();

      if (
        !src ||
        /^(data:|blob:)/i.test(src)
      ) {
        return match;
      }

      if (/^https?:\/\//i.test(src)) {
        try {
          const url = new URL(src);

          if (
            !url.pathname.startsWith(
              '/storage/'
            )
          ) {
            return match;
          }
        } catch {
          return match;
        }
      } else if (
        !/^\/?storage\//i.test(src)
      ) {
        return match;
      }

      return `${prefix}${quote}${toStorageUrl(
        src
      )}${quote}`;
    }
  );
};

export const usesReadingTwoColumnLayout = (
  groups = []
) =>
  groups.some((group) =>
    TWO_COLUMN_QUESTION_TYPES.has(
      (group.type || '').toLowerCase()
    )
  );

export const usesReadingNoteCompletionLayout =
  (groups = []) =>
    groups.some(
      isReadingNoteCompletionGroup
    );

export const parseMetadata = (
  metadata
) => {
  if (!metadata) return {};

  if (typeof metadata !== 'string') {
    return metadata;
  }

  try {
    return JSON.parse(metadata) || {};
  } catch {
    return {};
  }
};

export const stripParagraphWrapper = (
  content = ''
) =>
  content
    .replace(/^<p[^>]*>|<\/p>$/gi, '')
    .trim();

const normalizeAnswerOption = (
  answer,
  index
) => {
  const answerObject =
    typeof answer === 'string'
      ? { content: answer }
      : answer || {};

  const content =
    stripParagraphWrapper(
      answerObject.content ||
        answerObject.text ||
        ''
    );

  return {
    letter:
      answerObject.letter ||
      answerObject.label ||
      String.fromCharCode(65 + index),

    content
  };
};

export const getQuestionAnswerOptions = (
  question,
  fallbackOptions = []
) => {
  const metadata = parseMetadata(
    question?.metadata
  );

  if (
    Array.isArray(metadata.answers) &&
    metadata.answers.length > 0
  ) {
    return metadata.answers.map(
      (answer, index) => ({
        letter: String.fromCharCode(
          65 + index
        ),

        content: stripParagraphWrapper(
          answer.content || ''
        )
      })
    );
  }

  return fallbackOptions;
};

const normalizeOptions = (options) => {
  if (Array.isArray(options))
    return options;

  if (typeof options !== 'string')
    return [];

  try {
    const parsedOptions =
      JSON.parse(options);

    return Array.isArray(parsedOptions)
      ? parsedOptions
      : [];
  } catch {
    return [];
  }
};

const getQuestionAnswerSlots = (
  question,
  questionType
) => {
  const metadata = parseMetadata(
    question?.metadata
  );

  if (
    MULTI_SLOT_TEXT_QUESTION_TYPES.has(
      questionType
    ) &&
    Array.isArray(metadata.answers) &&
    metadata.answers.length > 0
  ) {
    return metadata.answers;
  }

  return [null];
};

const getMatchingOptions = (
  questions = []
) => {
  const firstQuestion = questions[0];

  const firstMetadata = parseMetadata(
    firstQuestion?.metadata
  );

  const firstAnswers = Array.isArray(
    firstMetadata.answers
  )
    ? firstMetadata.answers
    : [];

  const answersSource =
    firstAnswers.length > 1
      ? firstAnswers
      : questions.flatMap((question) => {
          const metadata =
            parseMetadata(
              question?.metadata
            );

          return Array.isArray(
            metadata.answers
          )
            ? metadata.answers
            : [];
        });

  const optionsWithContent =
    answersSource
      .map(normalizeAnswerOption)
      .filter(
        (option) =>
          option.content ||
          option.letter
      );

  const uniqueOptions =
    optionsWithContent.filter(
      (option, index, options) =>
        options.findIndex(
          (candidate) =>
            candidate.letter ===
              option.letter &&
            candidate.content ===
              option.content
        ) === index
    );

  return {
    options: uniqueOptions.map(
      (option) => option.letter
    ),

    optionsWithContent:
      uniqueOptions,

    optionTitle:
      firstMetadata.answer_label ||
      'Options'
  };
};

const getGroupAnswerOptions = (
  group
) => {
  const questionType = (
    group.question_type || ''
  ).toLowerCase();

  const groupOptions =
    normalizeOptions(group.options);

  switch (questionType) {
    case 'multiple_choice':
    case 'matching_information':
    case 'matching_headings':
    case 'matching_features':
    case 'matching_sentence_endings':
      return getMatchingOptions(
        group.questions || []
      );

    case 'true_false_not_given':
      return {
        options:
          groupOptions.length > 0
            ? groupOptions
            : [
                'True',
                'False',
                'Not Given'
              ],

        optionsWithContent: null
      };

    case 'yes_no_not_given':
      return {
        options:
          groupOptions.length > 0
            ? groupOptions
            : [
                'Yes',
                'No',
                'Not Given'
              ],

        optionsWithContent: null
      };

    case 'sentence_completion':
    case 'summary_completion':
    case 'note_completion':
    case 'table_completion':
    case 'flow_chart_completion':
    case 'diagram_label_completion':
    case 'short_answer_questions':
      return {
        options: [],
        optionsWithContent: null
      };

    default:
      return {
        options: groupOptions,
        optionsWithContent: null
      };
  }
};

export const normalizeReadingSection = (
  section,
  partNumber = 1,
  startQuestionNumber = 1
) => {
  const groups = [];

  let questionNumber =
    startQuestionNumber;

  (section?.question_groups || []).forEach(
    (group) => {
      const questionType = (
        group.question_type || ''
      ).toLowerCase();

      const questions = (
        group.questions || []
      ).flatMap((question) => {
        const answerSlots =
          getQuestionAnswerSlots(
            question,
            questionType
          );

        const shouldUseAnswerSlotIds =
          MULTI_SLOT_TEXT_QUESTION_TYPES.has(
            questionType
          ) &&
          answerSlots.length > 0 &&
          answerSlots[0] !== null;

        return answerSlots.map(
          (
            answerSlot,
            answerIndex
          ) => ({
            id: shouldUseAnswerSlotIds
              ? `${question.id}:${answerIndex}`
              : question.id,

            sourceQuestionId:
              question.id,

            answerIndex:
              shouldUseAnswerSlotIds
                ? answerIndex
                : null,

            number:
              questionNumber++,

            content:
              question.content,

            correctAnswer:
              answerSlot?.content ??
              question.answer_content,

            imageUrl:
              toStorageUrl(
                question.image_url ||
                  question.image ||
                  question.media?.image
              ),

            metadata:
              question.metadata
          })
        );
      });

      const {
        options,
        optionsWithContent,
        optionTitle
      } =
        getGroupAnswerOptions(
          group
        );

      const fallbackNumber =
        questions[0]?.number ||
        startQuestionNumber;

      groups.push({
        id: group.id,

        part: partNumber,

        type:
          group.question_type ||
          'multiple_choice',

        instructions:
          group.instructions,

        groupContent:
          normalizeHtmlMediaSources(
            group.content || ''
          ),

        imageUrl:
          toStorageUrl(
            group.image_url ||
              group.image ||
              group.media?.image
          ) ||
          questions.find(
            (question) =>
              question.imageUrl
          )?.imageUrl ||
          null,

        passage:
          normalizeHtmlMediaSources(
            section?.passage ||
              section?.content ||
              ''
          ),

        options,

        optionsWithContent,

        optionTitle,

        questions,

        startNumber:
          questions[0]?.number ||
          fallbackNumber,

        endNumber:
          questions[
            questions.length - 1
          ]?.number ||
          fallbackNumber
      });
    }
  );

  return {
    groups,

    nextQuestionNumber:
      questionNumber
  };
};

export const createReadingPartTitle = (
  partNumber,
  groups = [],
  fallbackStart = 1
) => {
  const firstNumber =
    groups[0]?.startNumber ||
    fallbackStart;

  const lastGroup =
    groups[groups.length - 1];

  const lastNumber =
    lastGroup?.endNumber ||
    Math.max(0, fallbackStart - 1);

  return `Passage ${partNumber} (${firstNumber}-${lastNumber})`;
};

export const getAnsweredCount = (
  answers = {}
) =>
  Object.values(answers).filter(
    (answer) =>
      String(answer ?? '').trim() !== ''
  ).length;
