export const getWritingAnswerKey = (groupId, sectionKey) =>
  `${groupId}:${sectionKey}`;

export const getWritingAnswerText = (answers, group) =>
  (group.answerSections || [])
    .map((section) => answers[getWritingAnswerKey(group.id, section.key)] || '')
    .map((value) => value.trim())
    .filter(Boolean)
    .join('\n\n');

export const countWritingWords = (text = '') =>
  text.trim().split(/\s+/).filter(Boolean).length;

export const createWritingLayoutAnswers = (answers, groups) =>
  groups.reduce((layoutAnswers, group) => {
    const combinedAnswer = getWritingAnswerText(answers, group);

    (group.questions || []).forEach((question) => {
      layoutAnswers[question.id] = combinedAnswer;
    });

    return layoutAnswers;
  }, {});

export const createWritingSubmissionAnswers = (answers, groups) =>
  groups.flatMap((group) => {
    const combinedAnswer = getWritingAnswerText(answers, group);

    return (group.questions || []).map((question) => ({
      question_id: Number(question.sourceQuestionId ?? question.id),
      answer_index: null,
      answer: combinedAnswer || null
    }));
  });
