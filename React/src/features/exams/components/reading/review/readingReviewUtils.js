import { normalizeReadingSection, parseMetadata, stripParagraphWrapper } from '../../../../utils/readingTest';

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

export const stripHtmlToText = (value = '') =>
  String(value).replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ').replace(/\s+/g, ' ').trim();

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
      return `<span class="reading-review__inline-answer ${cls}">${answerText || '…'}</span>`;
    });
  }
  return normalizedContent;
};

export const buildFooterAnswers = (userAnswers = {}) =>
  Object.fromEntries(
    Object.entries(userAnswers)
      .filter(([, v]) => String(v?.userAnswer ?? '').trim() !== '')
      .map(([k, v]) => [k, v.userAnswer])
  );
