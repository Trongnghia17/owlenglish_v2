import { memo, useMemo } from 'react';

const DEFAULT_OPTION_LETTERS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

const normalizeLetter = (value) =>
  String(value ?? '').trim().toUpperCase().slice(0, 1);

const stripHtmlToText = (value = '') =>
  String(value)
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/&(ndash|mdash);/gi, '-')
    .replace(/\s+/g, ' ')
    .trim();

const getLetterRange = (text = '') => {
  const normalizedText = stripHtmlToText(text).toUpperCase();
  const rangeMatch = normalizedText.match(/\b([A-Z])\s*[-–—]\s*([A-Z])\b/);

  if (!rangeMatch) return [];

  const start = rangeMatch[1].charCodeAt(0);
  const end = rangeMatch[2].charCodeAt(0);

  if (end < start || end - start > 25) return [];

  return Array.from({ length: end - start + 1 }, (_, index) =>
    String.fromCharCode(start + index)
  );
};

const getOptionLetters = (group) => {
  const instructionLetters = getLetterRange(group.instructions);
  if (instructionLetters.length > 0) return instructionLetters;

  const optionLetters = [
    ...(group.options || []),
    ...(group.optionsWithContent || []).map((option) => option.letter)
  ]
    .map(normalizeLetter)
    .filter(Boolean);

  const uniqueLetters = [...new Set(optionLetters)];

  return uniqueLetters.length > 0 ? uniqueLetters : DEFAULT_OPTION_LETTERS;
};

function MatchingInformationQuestions({ group, answers, onAnswerChange }) {
  const optionLetters = useMemo(() => getOptionLetters(group), [group]);

  return (
    <div className="reading-test__matching-info">
      <div className="reading-test__matching-info-table-wrap">
        <table className="reading-test__matching-info-table">
          <colgroup>
            <col className="reading-test__matching-info-col-number" />
            <col className="reading-test__matching-info-col-question" />
            {optionLetters.map((letter) => (
              <col key={letter} className="reading-test__matching-info-col-choice" />
            ))}
          </colgroup>
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col" className="reading-test__matching-info-question-head">
                Câu hỏi
              </th>
              {optionLetters.map((letter) => (
                <th key={letter} scope="col" className="reading-test__matching-info-choice-head">
                  {letter}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {group.questions.map((question) => {
              const selectedLetter = normalizeLetter(answers[question.id]);

              return (
                <tr key={question.id}>
                  <td className="reading-test__matching-info-number">
                    {question.number}
                  </td>
                  <td className="reading-test__matching-info-question">
                    <div dangerouslySetInnerHTML={{ __html: question.content || '' }} />
                  </td>
                  {optionLetters.map((letter) => {
                    const isSelected = selectedLetter === letter;

                    return (
                      <td
                        key={letter}
                        className={`reading-test__matching-info-choice-cell ${isSelected ? 'is-selected' : ''}`}
                      >
                        <label className="reading-test__matching-info-radio">
                          <input
                            type="radio"
                            name={`matching-information-${question.id}`}
                            value={letter}
                            checked={isSelected}
                            aria-label={`Question ${question.number}, section ${letter}`}
                            onChange={() => onAnswerChange(question.id, letter)}
                          />
                          <span aria-hidden="true" />
                        </label>
                      </td>
                    );
                  })}
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </div>
  );
}

export default memo(MatchingInformationQuestions);
