import { memo, useMemo } from 'react';
import ListeningAudioPlayer from './ListeningAudioPlayer';
import FormCompletionQuestions from './question-types/FormCompletionQuestions';

const PLACEHOLDER_PATTERN = /\{\{\s*([\w-]+)\s*\}\}/g;

const getRangeText = (group) => {
  const firstNumber = group.startNumber || group.questions?.[0]?.number || '';
  const lastQuestion = group.questions?.[group.questions.length - 1];
  const lastNumber = group.endNumber || lastQuestion?.number || firstNumber;

  return firstNumber && lastNumber
    ? `Questions ${firstNumber} - ${lastNumber}`
    : 'Questions';
};

const stripParagraphWrapper = (content = '') =>
  String(content).replace(/^<p[^>]*>/i, '').replace(/<\/p>$/i, '').trim();

const createPlaceholderHtml = (question) => `
  <span class="listening-test__form-placeholder">
    <span class="listening-test__form-placeholder-number">${question.number}</span>
    <span class="listening-test__form-placeholder-line">.......</span>
  </span>
`;

const createFallbackFormHtml = (questions = []) =>
  questions
    .map((question) => `<p>${stripParagraphWrapper(question.content || '')} ${createPlaceholderHtml(question)}</p>`)
    .join('');

const findQuestionForToken = (questions, token, usedQuestionIds) => {
  const normalizedToken = String(token ?? '').trim();

  if (!normalizedToken) return null;

  return questions.find((question) => {
    if (usedQuestionIds.has(question.id)) return false;

    return [
      question.id,
      question.sourceQuestionId,
      question.number
    ].some((value) => String(value ?? '') === normalizedToken);
  });
};

const createFormHtml = (content, questions = []) => {
  if (!content) {
    return createFallbackFormHtml(questions);
  }

  let questionIndex = 0;
  const usedQuestionIds = new Set();

  return content.replace(PLACEHOLDER_PATTERN, (match, token) => {
    const matchedQuestion = findQuestionForToken(questions, token, usedQuestionIds);
    const sequentialQuestion = questions
      .slice(questionIndex)
      .find((question) => !usedQuestionIds.has(question.id));
    const question = matchedQuestion || sequentialQuestion;

    questionIndex += 1;

    if (!question) return match;

    usedQuestionIds.add(question.id);
    return createPlaceholderHtml(question);
  });
};

const createDefaultInstructions = (group) => `
  <strong>${getRangeText(group)}</strong><br />
  Complete the notes below.<br />
  Write <strong>ONE WORD AND/OR A NUMBER</strong> for each answer.
`;

function ListeningFormCompletionGroup({
  group,
  currentPartTab,
  audioUrl,
  showPartTitle = true,
  showAudio = false,
  answers,
  onAnswerChange
}) {
  const formHtml = useMemo(
    () => createFormHtml(group.groupContent, group.questions),
    [group.groupContent, group.questions]
  );

  const instructionsHtml = group.instructions || createDefaultInstructions(group);

  return (
    <section
      id={`question-group-${group.id}`}
      className="listening-test__form-completion"
    >
      {showPartTitle && <h2>Listening Part {currentPartTab}</h2>}
      {showAudio && <ListeningAudioPlayer audioUrl={audioUrl} />}

      <div
        className="listening-test__form-instructions"
        dangerouslySetInnerHTML={{ __html: instructionsHtml }}
      />

      <div className="listening-test__form-workspace">
        <div className="listening-test__form-card">
          <div
            className="listening-test__form-content"
            dangerouslySetInnerHTML={{ __html: formHtml }}
          />
        </div>

        <FormCompletionQuestions
          group={group}
          answers={answers}
          onAnswerChange={onAnswerChange}
        />
      </div>
    </section>
  );
}

export default memo(ListeningFormCompletionGroup);
