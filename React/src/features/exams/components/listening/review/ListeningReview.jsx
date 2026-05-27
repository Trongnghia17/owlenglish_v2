import { useEffect, useMemo, useState } from 'react';
import TestLayout from '../../TestLayout';
import { getCurrentPartAudio } from '../../../utils/listeningTest';
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
  onSelectQuestion,
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
    if (currentPartQuestions.length === 0) return;

    const activeStillVisible = currentPartQuestions.some(
      (question) => String(question.id) === String(activeQuestionId)
    );

    if (activeStillVisible) return;

    const firstExpandedQuestion = currentPartQuestions.find(
      (question) => expandedExplanations[question.id]
    );
    setActiveQuestionId((firstExpandedQuestion || currentPartQuestions[0]).id);
  }, [activeQuestionId, currentPartQuestions, expandedExplanations]);

  const handleQuestionSelect = (question) => {
    setActiveQuestionId(question.id);
    onSelectQuestion(question.id);
  };

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
        <ListeningReviewContentPanel
          groups={currentPartGroups}
          currentPartTab={currentPartTab}
          audioUrl={currentPartAudio}
          userAnswers={userAnswers}
          activeQuestionId={activeQuestionId}
          onQuestionSelect={handleQuestionSelect}
        />
        <ListeningReviewAnswerPanel
          groups={currentPartGroups}
          userAnswers={userAnswers}
          expandedExplanations={expandedExplanations}
          activeQuestionId={activeQuestionId}
          onToggleExplanation={onToggleExplanation}
          onQuestionSelect={handleQuestionSelect}
          onQuestionFocus={handleQuestionFocus}
          onLocate={onLocate}
        />
      </div>
    </TestLayout>
  );
}
