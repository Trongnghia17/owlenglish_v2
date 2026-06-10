import { normalizeReadingSection, parseMetadata, stripParagraphWrapper } from '../../../utils/readingTest';

const escapeHtml = (value = '') =>
  String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const escapeAttribute = (value = '') => escapeHtml(value).replace(/`/g, '&#096;');

const normalizeLabel = (value = '') => String(value ?? '').trim().toLowerCase();

export const getResultAnswerKey = (answer) => {
  const answerIndex = answer?.answer_index;
  return answerIndex === null || answerIndex === undefined
    ? String(answer?.question_id ?? '')
    : `${answer.question_id}:${answerIndex}`;
};

export const isReadingReviewResult = (testResult, data) => {
  const skillType = (testResult?.skill?.skill_type || data?.skill_type || data?.exam_skill?.skill_type || data?.skill?.skill_type || '').toLowerCase();
  return skillType === 'reading';
};

export const buildReadingReviewData = (data, type) => {
  if (type === 'section') {
    const { groups } = normalizeReadingSection(data, 1, 1);
    const reviewContent = data.feedback || data.content || '';
    return {
      groups: groups.map((group) => ({ ...group, reviewContent })),
      parts: [{ id: data.id, part: 1, title: `Passage 1 (1-${groups[0]?.endNumber || 0})` }],
    };
  }

  const groups = [];
  const allParts = [];
  let questionNumber = 1;

  (data.sections || []).forEach((section, sectionIndex) => {
    const partNumber = sectionIndex + 1;
    const partStartNumber = questionNumber;
    const { groups: sectionGroups, nextQuestionNumber } = normalizeReadingSection(section, partNumber, questionNumber);
    const reviewContent = section.feedback || section.content || '';

    groups.push(
      ...sectionGroups.map((group) => ({ ...group, reviewContent }))
    );

    const firstNum = sectionGroups[0]?.startNumber || partStartNumber;
    const lastNum = sectionGroups[sectionGroups.length - 1]?.endNumber || questionNumber;
    allParts.push({ id: section.id, part: partNumber, title: `Passage ${partNumber} (${firstNum}-${lastNum})` });

    questionNumber = nextQuestionNumber;
  });

  return { groups, parts: allParts };
};

export const stripHtmlToText = (value = '') => {
  if (Array.isArray(value)) {
    return value.map(stripHtmlToText).filter(Boolean).join(', ');
  }

  return String(value).replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ').replace(/\s+/g, ' ').trim();
};

export const isCorrectResultValue = (value) => value === '1' || value === 1 || value === true;

export const getReviewAnswerData = (userAnswers, question) => {
  const normalizedKey = String(question?.id ?? '');
  const sourceKey = getResultAnswerKey({ question_id: question?.sourceQuestionId ?? question?.id, answer_index: question?.answerIndex });
  return userAnswers[normalizedKey] || userAnswers[sourceKey] || userAnswers[String(question?.sourceQuestionId ?? '')] || {};
};

export const getReviewCorrectAnswer = (answerData, question) =>
  stripHtmlToText(answerData.correctAnswer || question?.correctAnswer || '');

export const getQuestionExplanation = (question) => {
  const metadata = parseMetadata(question?.metadata);
  const answers = Array.isArray(metadata?.answers) ? metadata.answers : [];
  const answerSlot = Number.isInteger(question?.answerIndex) ? answers[question.answerIndex] : null;
  const correctAnswer = answers.find((a) => a?.is_correct === '1' || a?.is_correct === 1 || a?.is_correct === true);
  return answerSlot?.feedback || answerSlot?.explanation || correctAnswer?.feedback || correctAnswer?.explanation || metadata.explanation || metadata.feedback || question?.explanation || '';
};

export const getQuestionLocateText = (question) => {
  const metadata = parseMetadata(question?.metadata);
  const answers = Array.isArray(metadata?.answers) ? metadata.answers : [];
  const answerSlot = Number.isInteger(question?.answerIndex) ? answers[question.answerIndex] : null;
  return answerSlot?.locate || answerSlot?.hint || metadata.locate || metadata.hint || question?.locateText || '';
};

const getQuestionSectionLabel = (question, index) => {
  const text = stripHtmlToText(question?.content || '');
  const explicitMatch = text.match(/\b(?:section|paragraph|para)\s+([A-Z])\b/i);
  if (explicitMatch) return explicitMatch[1].toUpperCase();

  const simpleMatch = text.match(/^([A-Z])(?:\b|[\s.:)-])/i);
  return simpleMatch ? simpleMatch[1].toUpperCase() : String.fromCharCode(65 + index);
};

const getMatchingHeadingOption = (group, question, userAnswers) => {
  const answerData = getReviewAnswerData(userAnswers, question);
  const correctAnswer = getReviewCorrectAnswer(answerData, question);
  const options = group?.optionsWithContent || [];
  return (
    options.find((option) => normalizeLabel(option.letter) === normalizeLabel(correctAnswer)) ||
    options.find((option) => normalizeLabel(stripHtmlToText(option.content)) === normalizeLabel(correctAnswer)) ||
    null
  );
};

const getMatchingHeadingsReviewContentHtml = (content, groups, userAnswers) => {
  const normalizedContent = stripParagraphWrapper(content || '');
  const matchingGroups = groups.filter((group) => (group?.type || '').toLowerCase() === 'matching_headings');
  if (!normalizedContent || matchingGroups.length === 0) return '';

  const targetsByLabel = new Map();
  matchingGroups.forEach((group) => {
    (group.questions || []).forEach((question, index) => {
      const headingOption = getMatchingHeadingOption(group, question, userAnswers);
      const headingText = stripHtmlToText(headingOption?.content || '');
      if (!headingText) return;

      targetsByLabel.set(getQuestionSectionLabel(question, index), {
        questionId: question.id,
        headingText,
      });
    });
  });

  if (targetsByLabel.size === 0) return getReadingReviewContentHtml({ groupContent: normalizedContent, questions: [] }, userAnswers);

  let replaced = false;
  const paragraphRegex = /<p\b[^>]*>[\s\S]*?<\/p>/gi;
  const enhanced = normalizedContent.replace(paragraphRegex, (paragraphHtml) => {
    const plainText = stripHtmlToText(paragraphHtml);
    const singleLetterMatch = plainText.match(/^([A-Z])$/i);
    const headingLineMatch = plainText.match(/^([A-Z])\s*[-–]\s*(.+)$/i);
    const label = (singleLetterMatch?.[1] || headingLineMatch?.[1] || '').toUpperCase();
    const target = label ? targetsByLabel.get(label) : null;

    if (!target) return paragraphHtml;

    replaced = true;
    const headingText = headingLineMatch?.[2] || target.headingText;
    return `<p class="reading-review__mh-passage-heading" data-question-id="${escapeAttribute(target.questionId)}">${escapeHtml(label)}- ${escapeHtml(headingText)}</p>`;
  });

  if (!replaced) return getReadingReviewContentHtml({ groupContent: normalizedContent, questions: [] }, userAnswers);
  return enhanced;
};

export const getReadingReviewContentHtml = (group, userAnswers) => {
  const content = group?.reviewContent || group?.groupContent || '';
  const questions = group?.questions || [];
  if (!content) return '';

  let placeholderIndex = 0;
  const normalizedContent = stripParagraphWrapper(content);

  if (/\{\{\s*[\w-]+\s*\}\}/.test(normalizedContent)) {
    return normalizedContent.replace(/\{\{\s*[\w-]+\s*\}\}/g, () => {
      const question = questions[placeholderIndex];
      placeholderIndex += 1;
      if (!question) return '';
      const answerData = getReviewAnswerData(userAnswers, question);
      const answerText = getReviewCorrectAnswer(answerData, question) || stripHtmlToText(answerData.userAnswer || '');
      const isCorrect = isCorrectResultValue(answerData.isCorrect);
      const cls = isCorrect ? 'correct' : 'incorrect';
      return `<span class="reading-review__inline-answer ${cls}" data-question-id="${escapeAttribute(question.id)}">${escapeHtml(answerText || '…')}</span>`;
    });
  }

  const boldRegex = /<(strong|b)\b[^>]*>([\s\S]*?)<\/\1>|<span\b(?=[^>]*style=["'][^"']*font-weight\s*:\s*(?:bold|[6-9]00))[^>]*>([\s\S]*?)<\/span>/gi;
  let markerIndex = 0;

  return normalizedContent.replace(boldRegex, (match, tagName, tagContent, spanContent) => {
    const question = questions[markerIndex];
    const markerText = stripHtmlToText(tagContent || spanContent || '');

    if (!question || !markerText) return match;

    markerIndex += 1;

    return `<span class="reading-review__inline-answer" data-question-id="${escapeAttribute(question.id)}">${escapeHtml(markerText)}</span>`;
  });
};

export const getReadingReviewPartContentHtml = (groups = [], userAnswers) => {
  const reviewContent = groups.find((group) => group.reviewContent)?.reviewContent || '';
  if (!reviewContent) return '';

  if (groups.some((group) => (group?.type || '').toLowerCase() === 'matching_headings')) {
    return getMatchingHeadingsReviewContentHtml(reviewContent, groups, userAnswers);
  }

  return getReadingReviewContentHtml(
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
      .filter(([, v]) => stripHtmlToText(v?.userAnswer ?? '') !== '')
      .map(([k, v]) => [k, v.userAnswer])
  );
