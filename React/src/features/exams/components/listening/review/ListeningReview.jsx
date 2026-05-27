import { useEffect, useMemo, useState } from 'react';
import TestLayout from '../../TestLayout';
import { getCurrentPartAudio } from '../../../utils/listeningTest';
import ListeningAudioPlayer from '../ListeningAudioPlayer';
import ListeningReviewAnswerPanel from './ListeningReviewAnswerPanel';
import ListeningReviewContentPanel from './ListeningReviewContentPanel';
import { buildFooterAnswers } from './listeningReviewUtils';

export default function ListeningReview({
  skillData,
  sectionData,
  result,
  parts,
  currentPartTab,
  setCurrentPartTab,
  questionGroups,
  userAnswers,
  expandedExplanations,
  onToggleExplanation,
  onLocate,
  fontSize,
  onFontSizeChange
}) {
  const currentPartGroups = questionGroups.filter((group) => group.part === currentPartTab);
  const currentPartQuestions = useMemo(
    () => currentPartGroups.flatMap((group) => group.questions || []),
    [currentPartGroups]
  );
  const currentPartAudio = getCurrentPartAudio({ skillData, sectionData, currentPartGroups });
  const footerAnswers = buildFooterAnswers(userAnswers);
  const [activeQuestionId, setActiveQuestionId] = useState(null);

  useEffect(() => {
    if (!activeQuestionId) return;

    const activeStillVisible = currentPartQuestions.some(
      (question) => String(question.id) === String(activeQuestionId)
    );

    if (!activeStillVisible || !expandedExplanations[activeQuestionId]) {
      setActiveQuestionId(null);
    }
  }, [activeQuestionId, currentPartQuestions, expandedExplanations]);

  const handleQuestionFocus = (question) => {
    setActiveQuestionId(question.id);
  };

  return (
    <TestLayout
      skillData={skillData}
      sectionData={sectionData}
      timeRemaining={result?.time_spent || 0}
      setTimeRemaining={() => {}}
      parts={parts}
      currentPartTab={currentPartTab}
      setCurrentPartTab={setCurrentPartTab}
      questionGroups={questionGroups}
      answers={footerAnswers}
      onSubmit={null}
      showQuestionNumbers={true}
      fontSize={fontSize}
      onFontSizeChange={onFontSizeChange}
    >
      <div className={`listening-review listening-review--${fontSize}`}>
        <div className="listening-review__header">
          <h2 className="listening-review__title">Listening Part {currentPartTab}</h2>
          <ListeningAudioPlayer audioUrl={currentPartAudio} />
        </div>

        <div className="listening-review__body">
          <ListeningReviewContentPanel
            groups={currentPartGroups}
            userAnswers={userAnswers}
            activeQuestionId={activeQuestionId}
          />
          <ListeningReviewAnswerPanel
            groups={currentPartGroups}
            userAnswers={userAnswers}
            expandedExplanations={expandedExplanations}
            activeQuestionId={activeQuestionId}
            onToggleExplanation={onToggleExplanation}
            onQuestionFocus={handleQuestionFocus}
            onLocate={onLocate}
          />
        </div>
      </div>
    </TestLayout>
  );
}
