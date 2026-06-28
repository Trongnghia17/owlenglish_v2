import { useState, useEffect, useMemo, useCallback } from 'react';
import { useParams, useNavigate, useLocation } from 'react-router-dom';
import TestLayout from '../components/TestLayout';
import WritingTaskOne from '../components/writing/WritingTaskOne';
import WritingTaskTwo from '../components/writing/WritingTaskTwo';
import { getSkillById, getSectionById, submitTestResult } from '../api/exams.api';
import {
  DEFAULT_WRITING_TIME_LIMIT_SECONDS,
  getWritingTimeLimitSeconds,
  normalizeWritingSection
} from '../utils/writingTest';
import {
  createWritingLayoutAnswers,
  createWritingSubmissionAnswers,
  getWritingAnswerKey
} from '../utils/writingAnswers';
import './WritingTest.css';

const WritingTest = () => {
  const { skillId, sectionId } = useParams();
  const navigate = useNavigate();
  const location = useLocation();
  const examData = location.state?.examData || null;

  const [answers, setAnswers] = useState({});
  const [timeRemaining, setTimeRemaining] = useState(DEFAULT_WRITING_TIME_LIMIT_SECONDS);
  const [currentPartTab, setCurrentPartTab] = useState(1);
  const [loading, setLoading] = useState(true);
  const [skillData, setSkillData] = useState(null);
  const [sectionData, setSectionData] = useState(null);
  const [questionGroups, setQuestionGroups] = useState([]);
  const [fontSize, setFontSize] = useState('normal');

  useEffect(() => {
    let cancelled = false;

    const fetchExamData = async () => {
      try {
        setLoading(true);
        setAnswers({});
        setSkillData(null);
        setSectionData(null);
        setQuestionGroups([]);
        setCurrentPartTab(1);

        let allGroups = [];

        if (sectionId) {
          const [sectionResponse, skillResponse] = await Promise.all([
            getSectionById(sectionId, { with_questions: true }),
            skillId ? getSkillById(skillId) : Promise.resolve(null)
          ]);

          if (cancelled) return;

          const loadedSectionData = sectionResponse.data?.data || sectionResponse.data;
          const loadedSkillData = skillResponse?.data?.success
            ? skillResponse.data.data
            : null;
          const normalizedSection = normalizeWritingSection(loadedSectionData, 1);

          setSkillData(loadedSkillData);
          setSectionData(loadedSectionData);
          setTimeRemaining(
            getWritingTimeLimitSeconds(loadedSkillData || loadedSectionData)
          );
          allGroups = [normalizedSection];
        } else if (skillId) {
          const response = await getSkillById(skillId, { with_sections: true });

          if (cancelled) return;

          const loadedSkillData = response.data?.data || response.data;
          setSkillData(loadedSkillData);
          setTimeRemaining(getWritingTimeLimitSeconds(loadedSkillData));

          allGroups = (loadedSkillData?.sections || []).map((section, index) =>
            normalizeWritingSection(section, index + 1)
          );
        }

        setQuestionGroups(allGroups);
        setCurrentPartTab(allGroups[0]?.part || 1);
      } catch (error) {
        if (!cancelled) {
          console.error('Error fetching exam data:', error);
        }
      } finally {
        if (!cancelled) {
          setLoading(false);
        }
      }
    };

    fetchExamData();

    return () => {
      cancelled = true;
    };
  }, [skillId, sectionId]);

  const handleAnswerChange = useCallback((groupId, sectionKey, value) => {
    setAnswers(prev => ({
      ...prev,
      [getWritingAnswerKey(groupId, sectionKey)]: value
    }));
  }, []);

  const parts = useMemo(
    () => questionGroups.map((group) => ({
      id: group.id,
      part: group.part,
      headerLabel: group.taskLabel,
      footerLabel: group.taskLabel
    })),
    [questionGroups]
  );

  const layoutAnswers = useMemo(
    () => createWritingLayoutAnswers(answers, questionGroups),
    [answers, questionGroups]
  );

  const handleSubmit = async () => {
    try {
      const answersArray = createWritingSubmissionAnswers(answers, questionGroups);
      const allQuestionIds = answersArray.map((answer) => answer.question_id);

      if (allQuestionIds.length === 0) {
        alert('Bài writing này chưa có câu hỏi để nộp.');
        return;
      }

      const configuredTimeLimit = getWritingTimeLimitSeconds(skillData || sectionData);
      const answeredQuestions = answersArray.filter((answer) =>
        String(answer.answer ?? '').trim() !== ''
      ).length;

      const response = await submitTestResult({
        skill_id: skillId ? parseInt(skillId, 10) : null,
        section_id: sectionId ? parseInt(sectionId, 10) : null,
        test_id: examData?.id || null,
        answers: answersArray,
        all_question_ids: allQuestionIds,
        time_spent: Math.max(0, configuredTimeLimit - timeRemaining),
        total_questions: allQuestionIds.length,
        answered_questions: answeredQuestions
      });

      if (response.data.success) {
        navigate(`/test-result/${response.data.data.id}`);
        return;
      }

      throw new Error(response.data.message || 'Không thể nộp bài');
    } catch (error) {
      console.error('Error submitting writing test:', error);
      alert('Có lỗi xảy ra khi nộp bài. Vui lòng thử lại.');
    }
  };

  const currentPartGroups = questionGroups.filter(g => g.part === currentPartTab);

  if (loading) {
    return <div className="writing-test__loading">Loading...</div>;
  }

  if (!skillData && !sectionData) {
    return (
      <div className="writing-test__loading">
        <p>Không tìm thấy dữ liệu bài thi</p>
        <button onClick={() => navigate(-1)}>Quay lại</button>
      </div>
    );
  }

  return (
    <TestLayout
      testTypeExam="writing"
      examData={examData}
      skillData={skillData}
      sectionData={sectionData}
      timeRemaining={timeRemaining}
      setTimeRemaining={setTimeRemaining}
      parts={parts}
      currentPartTab={currentPartTab}
      setCurrentPartTab={setCurrentPartTab}
      questionGroups={questionGroups}
      answers={layoutAnswers}
      onSubmit={handleSubmit}
      fontSize={fontSize}
      onFontSizeChange={setFontSize}
    >
      <div className={`writing-test__content ${fontSize !== 'normal' ? `writing-test__content--${fontSize}` : ''}`}>
        {currentPartGroups.map((group) => {
          const TaskComponent = group.taskNumber === 2 ? WritingTaskTwo : WritingTaskOne;

          return (
            <TaskComponent
              key={group.id}
              group={group}
              answers={answers}
              onAnswerChange={handleAnswerChange}
            />
          );
        })}
        {currentPartGroups.length === 0 && (
          <div className="writing-test__empty">
            Không tìm thấy task writing cho phần này.
          </div>
        )}
      </div>
    </TestLayout>
  );
};

export default WritingTest;
