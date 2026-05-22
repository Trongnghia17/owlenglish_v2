import { useCallback, useEffect, useMemo, useState } from 'react';
import { useParams, useLocation, useNavigate } from 'react-router-dom';
import { getSkillById, getSectionById, submitTestResult } from '../api/exams.api';
import ListeningContentPanel from '../components/listening/ListeningContentPanel';
import ListeningQuestionGroup from '../components/listening/ListeningQuestionGroup';
import TestLayout from '../components/TestLayout';
import {
  createPartTitle,
  getAnsweredCount,
  getCurrentPartAudio,
  normalizeListeningSection,
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

  const isTwoColumnLayout = usesTwoColumnLayout(currentPartGroups);

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
        group.questions.map((question) => question.id)
      );

      const configuredTimeLimit = getTimeLimitSeconds(skillData);
      const answersArray = allQuestionIds.map((questionId) => ({
        question_id: questionId,
        answer: String(answers[questionId] ?? '').trim() ? answers[questionId] : null
      }));

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
      <div className={`listening-test__content ${fontSize !== 'normal' ? `listening-test__content--${fontSize}` : ''} ${isTwoColumnLayout ? 'listening-test__content--two-column' : ''}`}>
        {isTwoColumnLayout ? (
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
