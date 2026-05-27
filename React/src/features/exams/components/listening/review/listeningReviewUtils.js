import {
  createPartTitle,
  normalizeHtmlMediaSources,
  normalizeListeningSection,
  parseMetadata
} from '../../../utils/listeningTest';

export const getResultAnswerKey = (answer) => {
  const answerIndex = answer?.answer_index;

  return answerIndex === null || answerIndex === undefined
    ? String(answer?.question_id ?? '')
    : `${answer.question_id}:${answerIndex}`;
};

export const isListeningReviewResult = (testResult, data) => {
  const skillType = (
    testResult?.skill?.skill_type ||
    data?.skill_type ||
    data?.exam_skill?.skill_type ||
    data?.skill?.skill_type ||
    ''
  ).toLowerCase();

  return skillType === 'listening';
};

export const buildListeningReviewData = (data, type) => {
  if (type === 'section') {
    const { groups } = normalizeListeningSection(data, 1, 1);
    const reviewContent = data.feedback || data.content || '';

    return {
      groups: groups.map((group) => ({ ...group, reviewContent })),
      parts: [
        {
          id: data.id,
          part: 1,
          title: createPartTitle(1, groups, 1)
        }
      ]
    };
  }

  const groups = [];
  const parts = [];
  let questionNumber = 1;

  (data.sections || []).forEach((section, sectionIndex) => {
    const partNumber = sectionIndex + 1;
    const partStartNumber = questionNumber;
    const normalized = normalizeListeningSection(section, partNumber, questionNumber);
    const reviewContent = section.feedback || section.content || '';

    groups.push(
      ...normalized.groups.map((group) => ({
        ...group,
        reviewContent
      }))
    );
    parts.push({
      id: section.id,
      part: partNumber,
      title: createPartTitle(partNumber, normalized.groups, partStartNumber)
    });

    questionNumber = normalized.nextQuestionNumber;
  });

  return { groups, parts };
};

export const stripHtmlToText = (value = '') =>
  String(value)
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

const escapeHtml = (value = '') =>
  String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

const escapeAttribute = escapeHtml;

const isCorrectAnswerOption = (answer) =>
  answer?.is_correct === '1' ||
  answer?.is_correct === 1 ||
  answer?.is_correct === true;

export const isCorrectResultValue = (value) =>
  value === '1' || value === 1 || value === true;

export const getReviewAnswerData = (userAnswers, question) => {
  const normalizedKey = String(question?.id ?? '');
  const sourceKey = getResultAnswerKey({
    question_id: question?.sourceQuestionId ?? question?.id,
    answer_index: question?.answerIndex
  });

  return userAnswers[normalizedKey] ||
    userAnswers[sourceKey] ||
    userAnswers[String(question?.sourceQuestionId ?? '')] ||
    {};
};

export const getQuestionExplanation = (question) => {
  const metadata = parseMetadata(question?.metadata);
  const answers = Array.isArray(metadata.answers) ? metadata.answers : [];
  const answerSlot = Number.isInteger(question?.answerIndex)
    ? answers[question.answerIndex]
    : null;
  const correctAnswer = answers.find(isCorrectAnswerOption);

  return answerSlot?.feedback ||
    answerSlot?.explanation ||
    correctAnswer?.feedback ||
    correctAnswer?.explanation ||
    metadata.explanation ||
    metadata.feedback ||
    question?.explanation ||
    '';
};

export const getQuestionLocateText = (question) => {
  const metadata = parseMetadata(question?.metadata);
  const answers = Array.isArray(metadata.answers) ? metadata.answers : [];
  const answerSlot = Number.isInteger(question?.answerIndex)
    ? answers[question.answerIndex]
    : null;

  return answerSlot?.locate ||
    answerSlot?.hint ||
    metadata.locate ||
    metadata.hint ||
    question?.locateText ||
    '';
};

export const getReviewCorrectAnswer = (answerData, question) =>
  stripHtmlToText(answerData.correctAnswer || question?.correctAnswer || '');

const getQuestionAnchorHtml = ({ question, fallbackText, answerText }) => {
  if (!question) return escapeHtml(fallbackText);

  const label = stripHtmlToText(answerText);

  if (!label) return '';

  return `<span class="listening-review__inline-answer" data-question-id="${escapeAttribute(question.id)}">${escapeHtml(label)}</span>`;
};

const replaceBoldAnswerMarkers = ({ content, questions }) => {
  const boldRegex = /<(strong|b)\b[^>]*>([\s\S]*?)<\/\1>|<span\b(?=[^>]*style=["'][^"']*font-weight\s*:\s*(?:bold|[6-9]00))[^>]*>([\s\S]*?)<\/span>/gi;
  let markerIndex = 0;

  return content.replace(boldRegex, (match, tagName, tagContent, spanContent) => {
    const question = questions[markerIndex];
    const answerText = stripHtmlToText(tagContent || spanContent || '');

    if (!question || !answerText) return match;

    markerIndex += 1;

    return getQuestionAnchorHtml({
      question,
      fallbackText: match,
      answerText
    });
  });
};

export const getListeningReviewContentHtml = (group, userAnswers) => {
  const content = group?.groupContent || '';
  const questions = group?.questions || [];

  if (!content) return '';

  let placeholderIndex = 0;

  const normalizedContent = normalizeHtmlMediaSources(content);

  if (/\{\{\s*[\w-]+\s*\}\}/.test(normalizedContent)) {
    return normalizedContent.replace(/\{\{\s*[\w-]+\s*\}\}/g, (match) => {
      const question = questions[placeholderIndex];
      placeholderIndex += 1;

      if (!question) return match;

      const answerData = getReviewAnswerData(userAnswers, question);
      const answerText = getReviewCorrectAnswer(answerData, question) ||
        stripHtmlToText(answerData.userAnswer || '');

      return getQuestionAnchorHtml({
        question,
        fallbackText: match,
        answerText
      });
    });
  }

  return replaceBoldAnswerMarkers({
    content: normalizedContent,
    questions
  });
};

export const getListeningReviewPartContentHtml = (groups = [], userAnswers) => {
  const reviewContent = groups.find((group) => group.reviewContent)?.reviewContent || '';

  if (!reviewContent) return '';

  return getListeningReviewContentHtml(
    {
      groupContent: reviewContent,
      questions: groups.flatMap((group) => group.questions || [])
    },
    userAnswers
  );
};

export const buildFooterAnswers = (userAnswers = {}) =>
  Object.fromEntries(
    Object.entries(userAnswers)
      .filter(([, answerData]) => String(answerData.userAnswer ?? '').trim() !== '')
      .map(([key, answerData]) => [key, answerData.userAnswer])
  );
