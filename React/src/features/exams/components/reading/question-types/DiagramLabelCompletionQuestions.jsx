import { memo, useMemo } from 'react';
import { normalizeHtmlMediaSources } from '../../../utils/readingTest';

const IMAGE_SRC_PATTERN = /<img\b[^>]*\bsrc=(["'])(.*?)\1/i;

const getTextAnswerValue = (answer) => {
  if (Array.isArray(answer)) {
    return answer.filter(Boolean).join(', ');
  }

  return String(answer ?? '');
};

const getNestedImageUrl = (value) =>
  value?.imageUrl ||
  value?.image_url ||
  value?.diagramUrl ||
  value?.diagram_url ||
  value?.image ||
  value?.media?.image ||
  '';

const getDiagramImageUrl = (group) => {
  const directImage = getNestedImageUrl(group);
  if (directImage) return directImage;

  const questionImage = group.questions?.map(getNestedImageUrl).find(Boolean);
  if (questionImage) return questionImage;

  const normalizedContent = normalizeHtmlMediaSources(group.groupContent || '');
  const imageMatch = normalizedContent.match(IMAGE_SRC_PATTERN);
  return imageMatch?.[2] || '';
};

function DiagramLabelCompletionQuestions({ group, answers, onAnswerChange }) {
  const diagramImageUrl = useMemo(() => getDiagramImageUrl(group), [group]);
  const fallbackHtml = useMemo(
    () => normalizeHtmlMediaSources(group.groupContent || ''),
    [group.groupContent]
  );

  return (
    <div className="reading-test__diagram-layout">
      <div className="reading-test__diagram-figure">
        {diagramImageUrl ? (
          <img src={diagramImageUrl} alt="Diagram label completion" />
        ) : (
          <div
            className="reading-test__diagram-figure-html"
            dangerouslySetInnerHTML={{ __html: fallbackHtml }}
          />
        )}
      </div>

      <aside className="reading-test__diagram-answer-card" aria-label="Diagram label answers">
        <div className="reading-test__diagram-answer-title">
          Your answer
        </div>

        <div className="reading-test__diagram-answer-list">
          {group.questions.map((question) => {
            const answer = getTextAnswerValue(answers?.[question.id]);
            const isFilled = String(answer).trim() !== '';
            const inputId = `reading-diagram-answer-${question.id}`;

            return (
              <div
                key={question.id}
                id={`reading-diagram-question-${question.id}`}
                className={`reading-test__diagram-answer-row ${isFilled ? 'is-filled' : ''}`}
              >
                <label
                  className="reading-test__diagram-answer-number"
                  htmlFor={inputId}
                >
                  {question.number}
                </label>

                <div className="reading-test__diagram-answer-dropzone">
                  <input
                    id={inputId}
                    type="text"
                    className="reading-test__diagram-answer-input"
                    placeholder="Type answer here"
                    value={answer}
                    onChange={(event) =>
                      onAnswerChange(question.id, event.target.value)
                    }
                    maxLength={100}
                    autoComplete="off"
                  />

                  {isFilled && (
                    <button
                      type="button"
                      className="reading-test__diagram-answer-clear"
                      aria-label={`Clear answer ${question.number}`}
                      onClick={() => onAnswerChange(question.id, '')}
                    />
                  )}
                </div>
              </div>
            );
          })}
        </div>
      </aside>
    </div>
  );
}

export default memo(DiagramLabelCompletionQuestions);
