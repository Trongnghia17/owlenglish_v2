import { memo } from 'react';

const stripHtml = (content) =>
  String(content ?? '')
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

const normalizePrompt = (content = '') =>
  stripHtml(content).toLowerCase();

const createQuestionBlocks = (
  questions = []
) =>
  questions.reduce((blocks, question) => {
    const sourceQuestionId =
      question.sourceQuestionId ??
      question.id;
    const promptKey = normalizePrompt(
      question.content
    );
    const previousBlock =
      blocks[blocks.length - 1];
    const shouldAppendToPrevious =
      previousBlock &&
      (previousBlock.sourceQuestionId ===
        sourceQuestionId ||
        !promptKey ||
        previousBlock.promptKey ===
          promptKey);

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

function ShortAnswerQuestions({
  group,
  answers,
  onAnswerChange
}) {
  return createQuestionBlocks(
    group.questions
  ).map((block) => (
    <div
      key={block.id}
      className="reading-test__short-answer-card"
    >
      <div className="reading-test__short-answer-card-header">
        <div
          className="reading-test__short-answer-question-text"
          dangerouslySetInnerHTML={{
            __html: block.content
          }}
        />
      </div>

      <div className="reading-test__short-answer-list">
        {block.questions.map((question) => {
          const value =
            answers[question.id] || '';

          return (
            <label
              key={question.id}
              className={`reading-test__short-answer-row ${
                value.trim() ? 'is-filled' : ''
              }`}
            >
              <span className="reading-test__short-answer-number">
                {question.number}
              </span>

              <input
                type="text"
                className="reading-test__short-answer-input"
                aria-label={`Answer ${question.number}`}
                placeholder="Type answer here"
                value={value}
                onChange={(event) =>
                  onAnswerChange(
                    question.id,
                    event.target.value
                  )
                }
                onFocus={(event) => {
                  event.currentTarget
                    .closest(
                      '.reading-test__short-answer-row'
                    )
                    ?.classList.add('is-focus');
                }}
                onBlur={(event) => {
                  event.currentTarget
                    .closest(
                      '.reading-test__short-answer-row'
                    )
                    ?.classList.remove(
                      'is-focus'
                    );
                }}
                maxLength={100}
                autoComplete="off"
              />
            </label>
          );
        })}
      </div>
    </div>
  ));
}

export default memo(ShortAnswerQuestions);
