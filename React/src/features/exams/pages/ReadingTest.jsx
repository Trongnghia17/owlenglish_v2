import { useCallback, useEffect, useMemo, useState } from 'react';
import { useParams, useLocation, useNavigate } from 'react-router-dom';

import {
  getSkillById,
  getSectionById,
  submitTestResult
} from '../api/exams.api';

import TestLayout from '../components/TestLayout';

import ReadingPassagePanel from '../components/reading/ReadingPassagePanel';
import ReadingQuestionGroup from '../components/reading/ReadingQuestionGroup';

import ReadingNoteCompletionGroup from '../components/reading/ReadingNoteCompletionGroup';
import ReadingTableCompletionGroup from '../components/reading/ReadingTableCompletionGroup';
import ReadingFlowChartCompletionGroup from '../components/reading/ReadingFlowChartCompletionGroup';
import ReadingDiagramLabelCompletionGroup from '../components/reading/ReadingDiagramLabelCompletionGroup';

import {
  normalizeReadingSection,

  createReadingPartTitle,

  getAnsweredCount,

  usesReadingTwoColumnLayout,

  usesReadingNoteCompletionLayout,

  isReadingMultipleChoiceGroup,

  isReadingTrueFalseNotGivenGroup,

  isReadingYesNoNotGivenGroup,

  isReadingMatchingInformationGroup,

  isReadingMatchingHeadingsGroup,

  isReadingMatchingFeaturesGroup,

  isReadingMatchingSentenceEndingsGroup,

  isReadingSentenceCompletionGroup,

  isReadingSummaryCompletionGroup,

  isReadingNoteCompletionGroup,

  isReadingTableCompletionGroup,

  isReadingFlowChartCompletionGroup,

  isReadingDiagramLabelCompletionGroup,

  isReadingShortAnswerGroup
} from '../utils/readingTest';

import './ReadingTest.css';

const DEFAULT_TIME_LIMIT_SECONDS = 3600;

const getTimeLimitSeconds = (skill) => {
  const minutes = Number(skill?.time_limit);

  return Number.isFinite(minutes) && minutes > 0
    ? minutes * 60
    : DEFAULT_TIME_LIMIT_SECONDS;
};

const getSubmitQuestionId = (question) =>
  Number(question.sourceQuestionId ?? question.id);

const getSubmitAnswerIndex = (question) =>
  Number.isInteger(question.answerIndex)
    ? question.answerIndex
    : null;

const ReadingTest = () => {
  const { skillId, sectionId } = useParams();

  const location = useLocation();

  const navigate = useNavigate();

  const examData = location.state?.examData;

  const [answers, setAnswers] = useState({});

  const [timeRemaining, setTimeRemaining] = useState(
    DEFAULT_TIME_LIMIT_SECONDS
  );

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
        getSectionById(sectionId, {
          with_questions: true
        }),

        skillId
          ? getSkillById(skillId)
          : Promise.resolve(null)
      ]);

      if (skillResponse?.data?.success) {
        applySkillData(skillResponse.data.data);
      }

      if (!sectionResponse.data.success) return;

      const section = sectionResponse.data.data;

      const { groups } = normalizeReadingSection(
        section,
        1,
        1
      );

      setSectionData(section);

      setQuestionGroups(groups);

      setParts([
        {
          id: section.id,
          part: 1,
          title: createReadingPartTitle(
            1,
            groups,
            1
          )
        }
      ]);
    };

    const fetchFullSkillTest = async () => {
      const response = await getSkillById(skillId, {
        with_sections: true
      });

      if (!response.data.success) return;

      const skill = response.data.data;

      const allGroups = [];

      const allParts = [];

      let questionNumber = 1;

      applySkillData(skill);

      (skill.sections || []).forEach(
        (section, sectionIndex) => {
          const partNumber = sectionIndex + 1;

          const partStartNumber = questionNumber;

          const {
            groups,
            nextQuestionNumber
          } = normalizeReadingSection(
            section,
            partNumber,
            questionNumber
          );

          allGroups.push(...groups);

          allParts.push({
            id: section.id,
            part: partNumber,
            title: createReadingPartTitle(
              partNumber,
              groups,
              partStartNumber
            )
          });

          questionNumber = nextQuestionNumber;
        }
      );

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

        setTimeRemaining(
          DEFAULT_TIME_LIMIT_SECONDS
        );

        if (sectionId) {
          await fetchSectionTest();
        } else if (skillId) {
          await fetchFullSkillTest();
        }
      } catch (error) {
        console.error(
          'Error fetching reading exam:',
          error
        );

        alert(
          'Không thể tải dữ liệu bài đọc. Vui lòng thử lại.'
        );
      } finally {
        setLoading(false);
      }
    };

    fetchExamData();
  }, [skillId, sectionId]);

  const currentPartGroups = useMemo(
    () =>
      questionGroups.filter(
        (group) =>
          group.part === currentPartTab
      ),
    [questionGroups, currentPartTab]
  );

  const currentPassage = useMemo(() => {
    return (
      currentPartGroups[0]?.passage ||
      sectionData?.passage ||
      ''
    );
  }, [currentPartGroups, sectionData]);

  const isNoteCompletionLayout =
    usesReadingNoteCompletionLayout(
      currentPartGroups
    );

  const isTableCompletionLayout =
    !isNoteCompletionLayout &&
    currentPartGroups.some(
      isReadingTableCompletionGroup
    );

  const isDiagramLabelLayout =
    !isNoteCompletionLayout &&
    !isTableCompletionLayout &&
    currentPartGroups.some(
      isReadingDiagramLabelCompletionGroup
    );

  const isFlowChartLayout =
    !isNoteCompletionLayout &&
    !isTableCompletionLayout &&
    !isDiagramLabelLayout &&
    currentPartGroups.some(
      isReadingFlowChartCompletionGroup
    );

  const isMatchingLayout =
    currentPartGroups.some(
      isReadingMatchingInformationGroup
    ) ||
    currentPartGroups.some(
      isReadingMatchingHeadingsGroup
    ) ||
    currentPartGroups.some(
      isReadingMatchingFeaturesGroup
    ) ||
    currentPartGroups.some(
      isReadingMatchingSentenceEndingsGroup
    );

  const isMultipleChoiceLayout =
    currentPartGroups.some(
      isReadingMultipleChoiceGroup
    );

  const isTFNGLayout =
    currentPartGroups.some(
      isReadingTrueFalseNotGivenGroup
    );

  const isYNNGLayout =
    currentPartGroups.some(
      isReadingYesNoNotGivenGroup
    );

  const isSentenceCompletionLayout =
    currentPartGroups.some(
      isReadingSentenceCompletionGroup
    );

  const isSummaryCompletionLayout =
    currentPartGroups.some(
      isReadingSummaryCompletionGroup
    );

  const isShortAnswerLayout =
    currentPartGroups.some(
      isReadingShortAnswerGroup
    );

  const isTwoColumnLayout =
    usesReadingTwoColumnLayout(
      currentPartGroups
    );

  const handleAnswerSelect = useCallback(
    (questionId, answer) => {
      setAnswers((currentAnswers) => ({
        ...currentAnswers,
        [questionId]: answer
      }));
    },
    []
  );

  const handleSubmit = async () => {
    try {
      const allQuestionIds =
        questionGroups.flatMap((group) =>
          group.questions.map(
            getSubmitQuestionId
          )
        );

      const configuredTimeLimit =
        getTimeLimitSeconds(skillData);

      const answersArray =
        questionGroups.flatMap((group) =>
          group.questions.map((question) => {
            const answer =
              answers[question.id];

            return {
              question_id:
                getSubmitQuestionId(
                  question
                ),

              answer_index:
                getSubmitAnswerIndex(
                  question
                ),

              answer: String(
                answer ?? ''
              ).trim()
                ? answer
                : null
            };
          })
        );

      const submitData = {
        skill_id: skillId
          ? parseInt(skillId, 10)
          : null,

        section_id: sectionId
          ? parseInt(sectionId, 10)
          : null,

        test_id: examData?.id || null,

        answers: answersArray,

        all_question_ids:
          allQuestionIds,

        time_spent: Math.max(
          0,
          configuredTimeLimit -
          timeRemaining
        ),

        total_questions:
          allQuestionIds.length,

        answered_questions:
          getAnsweredCount(answers)
      };

      const response =
        await submitTestResult(
          submitData
        );

      if (response.data.success) {
        navigate(
          `/test-result/${response.data.data.id}`
        );

        return;
      }

      throw new Error(
        response.data.message ||
        'Không thể nộp bài'
      );
    } catch (error) {
      console.error(
        'Error submitting reading test:',
        error
      );

      alert(
        'Có lỗi xảy ra khi nộp bài.'
      );
    }
  };

  if (loading) {
    return (
      <div className="reading-test__loading">
        <div>
          Đang tải dữ liệu bài đọc...
        </div>
      </div>
    );
  }

  if (
    !questionGroups ||
    questionGroups.length === 0
  ) {
    return (
      <div className="reading-test__loading">
        <div
          style={{
            color: '#EF4444'
          }}
        >
          Không tìm thấy dữ liệu bài đọc
        </div>
      </div>
    );
  }

  return (
    <TestLayout
      examData={examData}
      skillData={skillData}
      sectionData={sectionData}
      timeRemaining={timeRemaining}
      setTimeRemaining={
        setTimeRemaining
      }
      parts={parts}
      currentPartTab={
        currentPartTab
      }
      setCurrentPartTab={
        setCurrentPartTab
      }
      questionGroups={
        questionGroups
      }
      answers={answers}
      onSubmit={handleSubmit}
      showQuestionNumbers={true}
      fontSize={fontSize}
      onFontSizeChange={
        setFontSize
      }
    >
      <div
        className={`
          reading-test__content

          ${fontSize !== 'normal'
            ? `reading-test__content--${fontSize}`
            : ''
          }

          ${isTwoColumnLayout
            ? 'reading-test__content--two-column'
            : ''
          }

          ${isMatchingLayout
            ? 'reading-test__content--matching'
            : ''
          }

          ${isMultipleChoiceLayout
            ? 'reading-test__content--multiple-choice'
            : ''
          }

          ${isTFNGLayout
            ? 'reading-test__content--tfng'
            : ''
          }

          ${isYNNGLayout
            ? 'reading-test__content--ynng'
            : ''
          }

          ${isSummaryCompletionLayout
            ? 'reading-test__content--summary-completion'
            : ''
          }

          ${isSentenceCompletionLayout
            ? 'reading-test__content--sentence-completion'
            : ''
          }

          ${isShortAnswerLayout
            ? 'reading-test__content--short-answer'
            : ''
          }
        `}
      >
        {isMultipleChoiceLayout && (
          <h1 className="reading-test__multiple-choice-title">
            Reading passage {currentPartTab}
          </h1>
        )}

        <ReadingPassagePanel
          currentPartTab={
            currentPartTab
          }
          passage={currentPassage}
          groups={currentPartGroups}
        />

        <div className="reading-test__right-column">
          {isNoteCompletionLayout ? (
            currentPartGroups.map(
              (group) =>
                isReadingNoteCompletionGroup(
                  group
                ) ? (
                  <ReadingNoteCompletionGroup
                    key={group.id}
                    group={group}
                    answers={
                      answers
                    }
                    onAnswerChange={
                      handleAnswerSelect
                    }
                  />
                ) : (
                  <ReadingQuestionGroup
                    key={group.id}
                    group={group}
                    answers={
                      answers
                    }
                    onAnswerChange={
                      handleAnswerSelect
                    }
                  />
                )
            )
          ) : isTableCompletionLayout ? (
            currentPartGroups.map(
              (group) =>
                isReadingTableCompletionGroup(
                  group
                ) ? (
                  <ReadingTableCompletionGroup
                    key={group.id}
                    group={group}
                    answers={
                      answers
                    }
                    onAnswerChange={
                      handleAnswerSelect
                    }
                  />
                ) : (
                  <ReadingQuestionGroup
                    key={group.id}
                    group={group}
                    answers={
                      answers
                    }
                    onAnswerChange={
                      handleAnswerSelect
                    }
                  />
                )
            )
          ) : isFlowChartLayout ? (
            currentPartGroups.map(
              (group) =>
                isReadingFlowChartCompletionGroup(
                  group
                ) ? (
                  <ReadingFlowChartCompletionGroup
                    key={group.id}
                    group={group}
                    answers={
                      answers
                    }
                    onAnswerChange={
                      handleAnswerSelect
                    }
                  />
                ) : (
                  <ReadingQuestionGroup
                    key={group.id}
                    group={group}
                    answers={
                      answers
                    }
                    onAnswerChange={
                      handleAnswerSelect
                    }
                  />
                )
            )
          ) : isDiagramLabelLayout ? (
            currentPartGroups.map(
              (group) =>
                isReadingDiagramLabelCompletionGroup(
                  group
                ) ? (
                  <ReadingDiagramLabelCompletionGroup
                    key={group.id}
                    group={group}
                    answers={
                      answers
                    }
                    onAnswerChange={
                      handleAnswerSelect
                    }
                  />
                ) : (
                  <ReadingQuestionGroup
                    key={group.id}
                    group={group}
                    answers={
                      answers
                    }
                    onAnswerChange={
                      handleAnswerSelect
                    }
                  />
                )
            )
          ) : (
            currentPartGroups.map(
              (group) => (
                <ReadingQuestionGroup
                  key={group.id}
                  group={group}
                  answers={
                    answers
                  }
                  onAnswerChange={
                    handleAnswerSelect
                  }
                />
              )
            )
          )}
        </div>
      </div>
    </TestLayout>
  );
};

export default ReadingTest;
