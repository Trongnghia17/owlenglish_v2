import { normalizeHtmlMediaSources } from './readingTest';

export const DEFAULT_WRITING_TIME_LIMIT_SECONDS = 3600;
const TASK_TWO_SUPPORTING_INSTRUCTION =
  'Give reasons for your answer and include any relevant examples from your own knowledge or experience.';

const TASK_TWO_ANSWER_SECTIONS = [
  { key: 'introduction', label: 'Introduction', minHeight: 120 },
  { key: 'body_1', label: 'Body 1', minHeight: 180 },
  { key: 'body_2', label: 'Body 2', minHeight: 180 },
  { key: 'conclusion', label: 'Conclusion', minHeight: 180 }
];

const TASK_ONE_ANSWER_SECTIONS = [
  TASK_TWO_ANSWER_SECTIONS[0],
  { key: 'overview', label: 'Overview', minHeight: 180 },
  ...TASK_TWO_ANSWER_SECTIONS.slice(1)
];

const WRITING_TASK_CONFIGS = {
  1: {
    taskLabel: 'Writing Task 1',
    timeInstruction: 'You should spend about 20 minutes on this task.',
    promptIntro: '',
    supportingInstruction: '',
    wordTarget: 150,
    answerSections: TASK_ONE_ANSWER_SECTIONS
  },
  2: {
    taskLabel: 'Writing Task 2',
    timeInstruction: 'You should spend about 40 minutes on this task.',
    promptIntro: 'Write about the following topic:',
    supportingInstruction: TASK_TWO_SUPPORTING_INSTRUCTION,
    wordTarget: 250,
    answerSections: TASK_TWO_ANSWER_SECTIONS
  }
};

const getSectionGroups = (section) =>
  section?.question_groups || section?.questionGroups || [];

const hasHtmlTags = (content = '') => /<[a-z][\s\S]*>/i.test(content);

const stripHtml = (content = '') =>
  String(content || '')
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

const formatContentHtml = (content = '') => {
  const normalized = normalizeHtmlMediaSources(String(content || '').trim());

  if (!normalized) return '';
  if (hasHtmlTags(normalized)) return normalized;

  return normalized
    .split(/\n{2,}/)
    .map((paragraph) => `<p>${paragraph.replace(/\n/g, '<br />')}</p>`)
    .join('');
};

const htmlToTextLines = (content = '') =>
  String(content || '')
    .replace(/<br\s*\/?>/gi, '\n')
    .replace(/<\/(p|div|li|h[1-6])>/gi, '\n')
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .split(/\n+/)
    .map((line) => line.replace(/\s+/g, ' ').trim())
    .filter(Boolean);

const getSectionFilterText = (section) => {
  if (!Array.isArray(section?.filters)) return '';

  return section.filters
    .map((filter) => `${filter?.name || ''} ${filter?.slug || ''}`)
    .join(' ');
};

const inferWritingTaskNumber = (section, partNumber, questions) => {
  const searchableText = stripHtml([
    section?.title,
    section?.content,
    section?.feedback,
    getSectionFilterText(section),
    ...questions.flatMap((question) => [
      question?.content,
      question?.feedback,
      question?.question_feedback,
      question?.prompt
    ])
  ].filter(Boolean).join(' ')).toLowerCase();

  if (/(writing\s*)?task\s*2|task-2|task_2|essay|write about the following topic|write at least\s+250\s+words|40\s+minutes/.test(searchableText)) {
    return 2;
  }

  if (/(writing\s*)?task\s*1|task-1|task_1|line graph|bar chart|pie chart|table|map|process|diagram|overview/.test(searchableText)) {
    return 1;
  }

  return partNumber >= 2 ? 2 : 1;
};

const isTaskTwoIntroLine = (line) =>
  /^write about the following topic:?$/i.test(line.trim());

const isTaskTwoSupportingLine = (line) =>
  /^give reasons for your answer and include any relevant examples/i.test(line.trim());

const isWordTargetLine = (line) =>
  /^write at least\s+\d+\s+words\.?$/i.test(line.trim());

const isTimeInstructionLine = (line) =>
  /^you should spend about\s+\d+\s+minutes/i.test(line.trim());

const isTaskTwoTopicLine = (line) =>
  !isTaskTwoIntroLine(line) &&
  !isTaskTwoSupportingLine(line) &&
  !isWordTargetLine(line) &&
  !isTimeInstructionLine(line);

const hasTaskTwoTopicLine = (content = '') =>
  htmlToTextLines(content).some(isTaskTwoTopicLine);

const hasTaskTwoPromptSignal = (content = '') =>
  /write about the following topic|write at least\s+250\s+words|discuss both|give reasons for your answer/i.test(
    stripHtml(content).toLowerCase()
  );

const normalizeTaskTwoPrompt = (promptSource, taskConfig) => {
  const lines = htmlToTextLines(promptSource);
  const topicLines = lines.filter(isTaskTwoTopicLine);

  return {
    promptIntro: lines.find(isTaskTwoIntroLine) || taskConfig.promptIntro,
    topicHtml: topicLines.length > 0 ? formatContentHtml(topicLines.join('\n')) : '',
    supportingInstruction:
      lines.find(isTaskTwoSupportingLine) || taskConfig.supportingInstruction,
    wordTargetInstruction:
      lines.find(isWordTargetLine) || `Write at least ${taskConfig.wordTarget} words.`
  };
};

const getTimeInstructionHtml = (section, primaryGroup, taskConfig) => {
  const candidate = section?.content || primaryGroup.instructions || '';
  const timeLine = htmlToTextLines(candidate).find(isTimeInstructionLine);

  return formatContentHtml(timeLine || candidate || taskConfig.timeInstruction);
};

const getQuestionPromptContents = (question = {}) => [
  question.content,
  question.question_content,
  question.prompt,
  question.feedback,
  question.question_feedback,
  question.title,
  question.hint
].filter((content) => String(content || '').trim() !== '');

const getPromptCandidates = (section, primaryGroup, questions) => [
  ...questions.flatMap(getQuestionPromptContents),
  primaryGroup.content,
  primaryGroup.instructions,
  section?.content,
  section?.feedback
].filter((content) => String(content || '').trim() !== '');

const selectPromptSource = (section, primaryGroup, questions, taskNumber) => {
  const candidates = getPromptCandidates(section, primaryGroup, questions);

  if (taskNumber === 2) {
    return candidates.find(hasTaskTwoPromptSignal) ||
      candidates.find(hasTaskTwoTopicLine) ||
      '';
  }

  return candidates[0] || '';
};

export const getWritingTimeLimitSeconds = (data) => {
  const minutes = Number(data?.time_limit ?? data?.duration);

  return Number.isFinite(minutes) && minutes > 0
    ? minutes * 60
    : DEFAULT_WRITING_TIME_LIMIT_SECONDS;
};

export const normalizeWritingSection = (section, partNumber = 1) => {
  const groups = getSectionGroups(section);
  const primaryGroup = groups[0] || {};
  const directQuestions = Array.isArray(section?.questions) ? section.questions : [];
  const groupedQuestions = groups.flatMap((group) =>
    Array.isArray(group.questions) ? group.questions : []
  );
  const questions = directQuestions.length > 0 ? directQuestions : groupedQuestions;
  const taskNumber = inferWritingTaskNumber(section, partNumber, questions);
  const taskConfig = WRITING_TASK_CONFIGS[taskNumber] || WRITING_TASK_CONFIGS[1];
  const promptSource = selectPromptSource(section, primaryGroup, questions, taskNumber);
  const taskTwoPrompt = taskNumber === 2
    ? normalizeTaskTwoPrompt(promptSource, taskConfig)
    : null;

  return {
    id: section.id,
    part: partNumber,
    title: section.title || taskConfig.taskLabel,
    taskNumber,
    taskLabel: taskConfig.taskLabel,
    timeInstructionHtml: getTimeInstructionHtml(section, primaryGroup, taskConfig),
    promptHtml: taskNumber === 1 ? formatContentHtml(promptSource) : '',
    topicHtml: taskTwoPrompt?.topicHtml || '',
    promptIntro: taskTwoPrompt?.promptIntro || '',
    supportingInstruction: taskTwoPrompt?.supportingInstruction || '',
    wordTarget: taskConfig.wordTarget,
    wordTargetInstruction:
      taskTwoPrompt?.wordTargetInstruction || `Write at least ${taskConfig.wordTarget} words.`,
    questions: questions.map((question, index) => ({
      ...question,
      sourceQuestionId: question.sourceQuestionId ?? question.id,
      number: index + 1
    })),
    answerSections: taskConfig.answerSections
  };
};
