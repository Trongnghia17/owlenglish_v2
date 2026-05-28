import { useCallback, useEffect, useMemo, useState } from 'react';
import { useParams, useLocation, useNavigate } from 'react-router-dom';
import { getSkillById, getSectionById, submitTestResult } from '../api/exams.api';
import ListeningContentPanel from '../components/listening/ListeningContentPanel';
import ListeningFormCompletionGroup from '../components/listening/ListeningFormCompletionGroup';
import ListeningNoteCompletionGroup from '../components/listening/ListeningNoteCompletionGroup';
import ListeningPlanMapDiagramLabellingGroup from '../components/listening/ListeningPlanMapDiagramLabellingGroup';
import ListeningQuestionGroup from '../components/listening/ListeningQuestionGroup';
import ListeningTableCompletionGroup from '../components/listening/ListeningTableCompletionGroup';
import TestLayout from '../components/TestLayout';
import {
  createPartTitle,
  getAnsweredCount,
  getCurrentPartAudio,
  isFlowChartCompletionGroup,
  isFormCompletionGroup,
  isMatchingGroup,
  isNoteCompletionGroup,
  isMultipleChoiceGroup,
  isPlanMapDiagramLabellingGroup,
  isSentenceCompletionGroup,
  isShortAnswerGroup,
  isSummaryCompletionGroup,
  isTableCompletionGroup,
  normalizeListeningSection,
  parseMetadata,
  usesNoteCompletionLayout,
  usesTwoColumnLayout
} from '../utils/listeningTest';
import './ListeningTest.css';

const DEFAULT_TIME_LIMIT_SECONDS = 2400;

const getTimeLimitSeconds = (skill) => {
  const minutes = Number(skill?.time_limit);
  return Number.isFinite(minutes) && minutes > 0
    ? minutes * 60
    : DEFAULT_TIME_LIMIT_SECONDS;
};

const getSubmitQuestionId = (question) =>
  Number(question.sourceQuestionId ?? question.id);

const getSubmitAnswerIndex = (question) =>
  Number.isInteger(question.answerIndex) ? question.answerIndex : null;

const stripHtmlToText = (value = '') =>
  String(value)
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

const getFlowChartTextAnswer = (answer, question, group) => {
  const normalizedAnswer = String(answer ?? '').trim();

  if (!/^[A-Z]$/i.test(normalizedAnswer)) {
    return normalizedAnswer;
  }

  const normalizedLetter = normalizedAnswer.toUpperCase();
  const groupOption = (group?.optionsWithContent || []).find(
    (option) => String(option.letter || '').trim().toUpperCase() === normalizedLetter
  );
  const groupOptionText = stripHtmlToText(groupOption?.content || '');

  if (groupOptionText) {
    return groupOptionText;
  }

  const metadata = parseMetadata(question?.metadata);
  const answers = Array.isArray(metadata.answers) ? metadata.answers : [];
  const answerIndex = normalizedLetter.charCodeAt(0) - 65;
  const answerText = stripHtmlToText(answers[answerIndex]?.content || '');

  return answerText || normalizedLetter;
};

const formatSubmitAnswer = (answer, question, group) => {
  if (Array.isArray(answer)) {
    const normalizedAnswers = answer
      .map((value) => String(value ?? '').trim())
      .filter(Boolean);

    return normalizedAnswers.length > 0 ? normalizedAnswers.join(',') : null;
  }

  const normalizedAnswer = String(answer ?? '').trim();

  if ((group?.type || '').toLowerCase() === 'flow_chart_completion') {
    const flowChartAnswer = getFlowChartTextAnswer(normalizedAnswer, question, group);

    return flowChartAnswer || null;
  }

  return normalizedAnswer ? normalizedAnswer : null;
};

const ListeningTest = () => {
  const { skillId, sectionId } = useParams();
  const location = useLocation();
  const navigate = useNavigate();
  const examData = location.state?.examData;

  const [answers, setAnswers] = useState({});
  const [timeRemaining, setTimeRemaining] = useState(DEFAULT_TIME_LIMIT_SECONDS);
  const [currentPartTab, setCurrentPartTab] = useState(1);
  const [loading, setLoading] = useState(true);
  const [skillData, setSkillData] = useState(null);
  const [sectionData, setSectionData] = useState(null);
  const [questionGroups, setQuestionGroups] = useState([]);
  const [parts, setParts] = useState([]);
  const [fontSize, setFontSize] = useState('normal');

  useEffect(() => {
    const applySkillData = (skill) => {
      setSkillData(skill);
      setTimeRemaining(getTimeLimitSeconds(skill));
    };

    const fetchSectionTest = async () => {
      const [sectionResponse, skillResponse] = await Promise.all([
        getSectionById(sectionId, { with_questions: true }),
        skillId ? getSkillById(skillId) : Promise.resolve(null)
      ]);

      if (skillResponse?.data?.success) {
        applySkillData(skillResponse.data.data);
      }

      if (!sectionResponse.data.success) return;

      const section = sectionResponse.data.data;
      const { groups } = normalizeListeningSection(section, 1, 1);

      setSectionData(section);
      setQuestionGroups(groups);
      setParts([
        {
          id: section.id,
          part: 1,
          title: createPartTitle(1, groups, 1)
        }
      ]);
    };

    const fetchFullSkillTest = async () => {
      const response = await getSkillById(skillId, { with_sections: true });
      if (!response.data.success) return;

      const skill = response.data.data;
      const allGroups = [];
      const allParts = [];
      let questionNumber = 1;

      applySkillData(skill);

      (skill.sections || []).forEach((section, sectionIndex) => {
        const partNumber = sectionIndex + 1;
        const partStartNumber = questionNumber;
        const { groups, nextQuestionNumber } = normalizeListeningSection(
          section,
          partNumber,
          questionNumber
        );

        allGroups.push(...groups);
        allParts.push({
          id: section.id,
          part: partNumber,
          title: createPartTitle(partNumber, groups, partStartNumber)
        });

        questionNumber = nextQuestionNumber;
      });

      setSectionData(null);
      setQuestionGroups(allGroups);
      setParts(allParts);
    };

    const fetchExamData = async () => {
      try {
        setLoading(true);
        setAnswers({});
        setCurrentPartTab(1);
        setQuestionGroups([]);
        setParts([]);
        setSkillData(null);
        setSectionData(null);
        setTimeRemaining(DEFAULT_TIME_LIMIT_SECONDS);

        if (sectionId) {
          await fetchSectionTest();
        } else if (skillId) {
          await fetchFullSkillTest();
        }
      } catch (error) {
        console.error('Error fetching exam data:', error);
        alert('Không thể tải dữ liệu bài thi. Vui lòng thử lại.');
      } finally {
        setLoading(false);
      }
    };

    fetchExamData();
  }, [skillId, sectionId]);

  const currentPartGroups = useMemo(
    () => questionGroups.filter((group) => group.part === currentPartTab),
    [questionGroups, currentPartTab]
  );

  const isNoteCompletionLayout = usesNoteCompletionLayout(currentPartGroups);
  const isFormCompletionLayout = !isNoteCompletionLayout && currentPartGroups.some(isFormCompletionGroup);
  const isTableCompletionLayout = !isNoteCompletionLayout && !isFormCompletionLayout && currentPartGroups.some(isTableCompletionGroup);
  const isPlanMapDiagramLabellingLayout = !isNoteCompletionLayout && !isFormCompletionLayout && !isTableCompletionLayout && currentPartGroups.some(isPlanMapDiagramLabellingGroup);
  const isTwoColumnLayout = !isNoteCompletionLayout && !isFormCompletionLayout && !isTableCompletionLayout && !isPlanMapDiagramLabellingLayout && usesTwoColumnLayout(currentPartGroups);
  const isMatchingLayout = !isNoteCompletionLayout && !isFormCompletionLayout && !isTableCompletionLayout && !isPlanMapDiagramLabellingLayout && !isTwoColumnLayout && currentPartGroups.some(isMatchingGroup);
  const isMultipleChoiceLayout = !isNoteCompletionLayout && !isFormCompletionLayout && !isTableCompletionLayout && !isPlanMapDiagramLabellingLayout && !isTwoColumnLayout && !isMatchingLayout && currentPartGroups.some(isMultipleChoiceGroup);
  const isFlowChartCompletionLayout = !isNoteCompletionLayout && !isFormCompletionLayout && !isTableCompletionLayout && !isPlanMapDiagramLabellingLayout && !isTwoColumnLayout && !isMatchingLayout && !isMultipleChoiceLayout && currentPartGroups.some(isFlowChartCompletionGroup);
  const isShortAnswerLayout = !isNoteCompletionLayout && !isFormCompletionLayout && !isTableCompletionLayout && !isPlanMapDiagramLabellingLayout && !isTwoColumnLayout && !isMultipleChoiceLayout && !isFlowChartCompletionLayout && currentPartGroups.some(isShortAnswerGroup);
  const isSummaryCompletionLayout = !isNoteCompletionLayout && !isFormCompletionLayout && !isTableCompletionLayout && !isPlanMapDiagramLabellingLayout && !isTwoColumnLayout && !isMultipleChoiceLayout && !isFlowChartCompletionLayout && !isShortAnswerLayout && currentPartGroups.some(isSummaryCompletionGroup);
  const isSentenceCompletionLayout = !isNoteCompletionLayout && !isFormCompletionLayout && !isTableCompletionLayout && !isPlanMapDiagramLabellingLayout && !isTwoColumnLayout && !isMultipleChoiceLayout && !isFlowChartCompletionLayout && !isShortAnswerLayout && !isSummaryCompletionLayout && currentPartGroups.some(isSentenceCompletionGroup);

  const currentPartAudio = useMemo(
    () => getCurrentPartAudio({ skillData, sectionData, currentPartGroups }),
    [skillData, sectionData, currentPartGroups]
  );

  const handleAnswerSelect = useCallback((questionId, answer) => {
    setAnswers((currentAnswers) => ({
      ...currentAnswers,
      [questionId]: answer
    }));
  }, []);

  const handleSubmit = async () => {
    try {
      const allQuestionIds = questionGroups.flatMap((group) =>
        group.questions.map(getSubmitQuestionId)
      );

      const configuredTimeLimit = getTimeLimitSeconds(skillData);
      const answersArray = questionGroups.flatMap((group) =>
        group.questions.map((question) => {
          const answer = answers[question.id];

          return {
            question_id: getSubmitQuestionId(question),
            answer_index: getSubmitAnswerIndex(question),
            answer: formatSubmitAnswer(answer, question, group)
          };
        })
      );

      const submitData = {
        skill_id: skillId ? parseInt(skillId, 10) : null,
        section_id: sectionId ? parseInt(sectionId, 10) : null,
        test_id: examData?.id || null,
        answers: answersArray,
        all_question_ids: allQuestionIds,
        time_spent: Math.max(0, configuredTimeLimit - timeRemaining),
        total_questions: allQuestionIds.length,
        answered_questions: getAnsweredCount(answers)
      };

      const response = await submitTestResult(submitData);

      if (response.data.success) {
        navigate(`/test-result/${response.data.data.id}`);
        return;
      }

      throw new Error(response.data.message || 'Không thể nộp bài');
    } catch (error) {
      console.error('Error submitting test:', error);
      alert('Có lỗi xảy ra khi nộp bài. Vui lòng thử lại.');
    }
  };

  if (loading) {
    return (
      <div className="listening-test__loading">
        <div>Đang tải dữ liệu bài thi...</div>
      </div>
    );
  }

  if (!questionGroups || questionGroups.length === 0) {
    return (
      <div className="listening-test__loading">
        <div style={{ color: '#EF4444' }}>Không tìm thấy dữ liệu bài thi</div>
      </div>
    );
  }

  return (
    <TestLayout
      examData={examData}
      skillData={skillData}
      sectionData={sectionData}
      timeRemaining={timeRemaining}
      setTimeRemaining={setTimeRemaining}
      parts={parts}
      currentPartTab={currentPartTab}
      setCurrentPartTab={setCurrentPartTab}
      questionGroups={questionGroups}
      answers={answers}
      onSubmit={handleSubmit}
      showQuestionNumbers={true}
      fontSize={fontSize}
      onFontSizeChange={setFontSize}
    >
      <div className={`listening-test__content ${fontSize !== 'normal' ? `listening-test__content--${fontSize}` : ''} ${isTwoColumnLayout ? 'listening-test__content--two-column' : ''} ${isNoteCompletionLayout ? 'listening-test__content--note-completion' : ''} ${isFormCompletionLayout ? 'listening-test__content--form-completion' : ''} ${isTableCompletionLayout ? 'listening-test__content--table-completion' : ''} ${isPlanMapDiagramLabellingLayout ? 'listening-test__content--map-labelling' : ''} ${isMatchingLayout ? 'listening-test__content--matching' : ''} ${isMultipleChoiceLayout ? 'listening-test__content--multiple-choice' : ''} ${isFlowChartCompletionLayout ? 'listening-test__content--flow-chart' : ''} ${isShortAnswerLayout ? 'listening-test__content--short-answer' : ''} ${isSummaryCompletionLayout ? 'listening-test__content--summary-completion' : ''} ${isSentenceCompletionLayout ? 'listening-test__content--sentence-completion' : ''}`}>
        {isNoteCompletionLayout ? (
          currentPartGroups.map((group, index) => (
            isNoteCompletionGroup(group) ? (
              <ListeningNoteCompletionGroup
                key={group.id}
                group={group}
                currentPartTab={currentPartTab}
                audioUrl={currentPartAudio}
                showAudio={index === 0}
                answers={answers}
                onAnswerChange={handleAnswerSelect}
              />
            ) : (
              <ListeningQuestionGroup
                key={group.id}
                group={group}
                currentPartTab={currentPartTab}
                audioUrl={currentPartAudio}
                showAudio={index === 0}
                answers={answers}
                onAnswerChange={handleAnswerSelect}
              />
            )
          ))
        ) : isFormCompletionLayout ? (
          currentPartGroups.map((group, index) => (
            isFormCompletionGroup(group) ? (
              <ListeningFormCompletionGroup
                key={group.id}
                group={group}
                currentPartTab={currentPartTab}
                audioUrl={currentPartAudio}
                showAudio={index === 0}
                answers={answers}
                onAnswerChange={handleAnswerSelect}
              />
            ) : (
              <ListeningQuestionGroup
                key={group.id}
                group={group}
                currentPartTab={currentPartTab}
                audioUrl={currentPartAudio}
                showAudio={index === 0}
                answers={answers}
                onAnswerChange={handleAnswerSelect}
              />
            )
          ))
        ) : isTableCompletionLayout ? (
          currentPartGroups.map((group, index) => (
            isTableCompletionGroup(group) ? (
              <ListeningTableCompletionGroup
                key={group.id}
                group={group}
                currentPartTab={currentPartTab}
                audioUrl={currentPartAudio}
                showAudio={index === 0}
                answers={answers}
                onAnswerChange={handleAnswerSelect}
              />
            ) : (
              <ListeningQuestionGroup
                key={group.id}
                group={group}
                currentPartTab={currentPartTab}
                audioUrl={currentPartAudio}
                showAudio={index === 0}
                answers={answers}
                onAnswerChange={handleAnswerSelect}
              />
            )
          ))
        ) : isPlanMapDiagramLabellingLayout ? (
          currentPartGroups.map((group, index) => (
            isPlanMapDiagramLabellingGroup(group) ? (
              <ListeningPlanMapDiagramLabellingGroup
                key={group.id}
                group={group}
                currentPartTab={currentPartTab}
                audioUrl={currentPartAudio}
                showAudio={index === 0}
                answers={answers}
                onAnswerChange={handleAnswerSelect}
              />
            ) : (
              <ListeningQuestionGroup
                key={group.id}
                group={group}
                currentPartTab={currentPartTab}
                audioUrl={currentPartAudio}
                showAudio={index === 0}
                answers={answers}
                onAnswerChange={handleAnswerSelect}
              />
            )
          ))
        ) : isTwoColumnLayout ? (
          <>
            <ListeningContentPanel
              groups={currentPartGroups}
              currentPartTab={currentPartTab}
              audioUrl={currentPartAudio}
            />

            <div className="listening-test__right-column">
              {currentPartGroups.map((group) => (
                <ListeningQuestionGroup
                  key={group.id}
                  group={group}
                  currentPartTab={currentPartTab}
                  answers={answers}
                  onAnswerChange={handleAnswerSelect}
                  showPartTitle={false}
                  showGroupContent={false}
                />
              ))}
            </div>
          </>
        ) : (
          currentPartGroups.map((group, index) => (
            <ListeningQuestionGroup
              key={group.id}
              group={group}
              currentPartTab={currentPartTab}
              audioUrl={currentPartAudio}
              showAudio={index === 0}
              answers={answers}
              onAnswerChange={handleAnswerSelect}
            />
          ))
        )}
      </div>
    </TestLayout>
  );
};

export default ListeningTest;
