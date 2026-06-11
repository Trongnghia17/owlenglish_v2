import { useState, useEffect, useRef, memo } from 'react';
import { useParams, useLocation, useNavigate } from 'react-router-dom';
import { getTestResult, getSkillById, getSectionById } from '../api/exams.api';
import ListeningReview from '../components/listening/review/ListeningReview';
import {
  buildListeningReviewData,
  getResultAnswerKey,
  isListeningReviewResult
} from '../components/listening/review/listeningReviewUtils';
import TestLayout from '../components/TestLayout';
import { getReadingReviewRenderer } from '../components/reading/review/question-types';
import {
  buildFooterAnswers,
  buildReadingReviewData,
  getReadingReviewPartContentHtml,
  isReadingReviewResult
} from '../components/reading/review/readingReviewUtils';
import './ReadingTest.css'; // Sử dụng cùng CSS với ReadingTest
import './ListeningTest.css';
import './TestResultReview.css'; // CSS riêng cho review mode

// Component hiển thị group content với inline inputs ở chế độ review (readonly)
const GroupContentWithInlineInputsReview = ({ content, questions = [], userAnswers = {} }) => {
  const containerRef = useRef(null);

  useEffect(() => {
    const container = containerRef.current;
    if (!container) return;

    if (!content) {
      container.innerHTML = '';
      return;
    }

    const placeholderRegex = /\{\{\s*([a-zA-Z0-9]+)\s*\}\}/g;
    let questionIndex = 0;

    const processedContent = content.replace(placeholderRegex, (match) => {
      const question = questions[questionIndex];
      questionIndex += 1;

      if (!question) {
        return match;
      }

      const answerData = userAnswers[question.id] || {};
      const isCorrect = answerData.isCorrect;
      const userAnswer = answerData.userAnswer || '';
      const correctAnswer = answerData.correctAnswer || question.correctAnswer || '';

      const placeholderId = `inline-placeholder-review-${question.id}`;
      
      return `<span class="reading-test__inline-placeholder" data-placeholder-id="${placeholderId}" data-is-correct="${isCorrect}" data-user-answer="${userAnswer}" data-correct-answer="${correctAnswer}"></span>`;
    });

    container.innerHTML = processedContent;

    // Thay thế placeholders bằng inputs readonly
    questions.forEach((question) => {
      const placeholderId = `inline-placeholder-review-${question.id}`;
      const placeholderElement = container.querySelector(`[data-placeholder-id="${placeholderId}"]`);
      if (!placeholderElement) return;

      const answerData = userAnswers[question.id] || {};
      const isCorrect = answerData.isCorrect;
      const userAnswer = answerData.userAnswer || '';
      const correctAnswer = answerData.correctAnswer || question.correctAnswer || '';

      const input = document.createElement('input');
      input.type = 'text';
      input.className = `reading-test__inline-input review-inline-input ${isCorrect ? 'correct' : 'incorrect'}`;
      input.value = userAnswer || '';
      input.disabled = true;
      input.readOnly = true;
      input.title = isCorrect ? `Đúng: ${userAnswer}` : `Sai - Đáp án đúng: ${correctAnswer}`;

      const wrapper = document.createElement('span');
      wrapper.className = 'reading-test__inline-input-wrapper';
      wrapper.appendChild(input);

      // Thêm đáp án đúng nếu sai
      if (!isCorrect && correctAnswer) {
        const correctAnswerSpan = document.createElement('span');
        correctAnswerSpan.className = 'review-inline-correct-answer';
        correctAnswerSpan.textContent = `(Đáp án: ${correctAnswer})`;
        wrapper.appendChild(correctAnswerSpan);
      }

      placeholderElement.replaceWith(wrapper);
    });
  }, [content, questions, userAnswers]);

  return <div ref={containerRef} className="reading-test__group-content-with-inputs" />;
};

// Component hiển thị passage content
const PassageContent = memo(function PassageContent({ fontSize, content }) {
  return (
    <div
      className={`reading-test__passage-content reading-test__passage-content--${fontSize}`}
      dangerouslySetInnerHTML={{ __html: content }}
    />
  );
});

const ReadingReviewPassageContent = memo(function ReadingReviewPassageContent({
  fontSize,
  content,
  groups,
  userAnswers,
  activeQuestionId
}) {
  const contentRef = useRef(null);
  const contentHtml = getReadingReviewPartContentHtml(groups, userAnswers) || content;

  useEffect(() => {
    const contentElement = contentRef.current;
    if (!contentElement) return;

    const anchors = contentElement.querySelectorAll('[data-question-id]');
    anchors.forEach((anchor) => {
      anchor.classList.toggle(
        'is-active',
        String(anchor.dataset.questionId) === String(activeQuestionId ?? '')
      );
    });
  }, [activeQuestionId, contentHtml]);

  return (
    <div
      ref={contentRef}
      className={`reading-test__passage-content reading-test__passage-content--${fontSize}`}
      dangerouslySetInnerHTML={{ __html: contentHtml }}
    />
  );
});

export default function TestResultReview() {
  const { resultId } = useParams();
  const location = useLocation();
  const navigate = useNavigate();
  const resultFromState = location.state?.result;

  const [loading, setLoading] = useState(true);
  const [result, setResult] = useState(resultFromState || null);
  const [skillData, setSkillData] = useState(null);
  const [sectionData, setSectionData] = useState(null);
  const [questionGroups, setQuestionGroups] = useState([]);
  const [passages, setPassages] = useState([]);
  const [parts, setParts] = useState([]);
  const [currentPartTab, setCurrentPartTab] = useState(1);
  const [fontSize, setFontSize] = useState('normal');
  const [timeRemaining] = useState(0); // No timer in review mode
  const [expandedExplanations, setExpandedExplanations] = useState({});
  const [activeQuestionId, setActiveQuestionId] = useState(null);
  const [userAnswers, setUserAnswers] = useState({}); // Store user's answers from result
  const [isListeningReview, setIsListeningReview] = useState(false);
  const [isReadingReview, setIsReadingReview] = useState(false);

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        setExpandedExplanations({});
        setActiveQuestionId(null);
        setIsListeningReview(false);
        setIsReadingReview(false);

        // Fetch test result if not in state
        let testResult = result;
        if (!testResult) {
          const response = await getTestResult(resultId);
          if (response.data.success) {
            testResult = response.data.data;
            setResult(testResult);
          }
        }

        if (!testResult) {
          throw new Error('No test result found');
        }

        // Build user answers map from result
        const answersMap = {};
        if (testResult.answers && Array.isArray(testResult.answers)) {
          testResult.answers.forEach(ans => {
            answersMap[getResultAnswerKey(ans)] = {
              userAnswer: ans.user_answer,
              correctAnswer: ans.correct_answer,
              isCorrect: ans.is_correct
            };
          });
        }
        setUserAnswers(answersMap);

        // Section results also include exam_skill_id, so prefer the section scope first.
        if (testResult.exam_section_id) {
          const response = await getSectionById(testResult.exam_section_id, { with_questions: true });
          if (response.data.success) {
            const section = response.data.data;
            const shouldUseListeningReview = isListeningReviewResult(testResult, section);
            const shouldUseReadingReview = isReadingReviewResult(testResult, section);
            setSkillData(null);
            setSectionData(section);
            setIsListeningReview(shouldUseListeningReview);
            setIsReadingReview(shouldUseReadingReview);
            if (shouldUseListeningReview) {
              processListeningExamData(section, 'section');
            } else if (shouldUseReadingReview) {
              processReadingExamData(section, 'section');
            } else {
              processExamData(section, testResult, 'section');
            }
          }
        } else if (testResult.exam_skill_id) {
          const response = await getSkillById(testResult.exam_skill_id, { with_sections: true });
          if (response.data.success) {
            const skill = response.data.data;
            const shouldUseListeningReview = isListeningReviewResult(testResult, skill);
            const shouldUseReadingReview = isReadingReviewResult(testResult, skill);
            setSkillData(skill);
            setSectionData(null);
            setIsListeningReview(shouldUseListeningReview);
            setIsReadingReview(shouldUseReadingReview);
            if (shouldUseListeningReview) {
              processListeningExamData(skill, 'skill');
            } else if (shouldUseReadingReview) {
              processReadingExamData(skill, 'skill');
            } else {
              processExamData(skill, testResult, 'skill');
            }
          }
        }
      } catch (error) {
        console.error('Error fetching data:', error);
        alert('Không thể tải dữ liệu. Vui lòng thử lại.');
        navigate(-1);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [resultId]);

  const processListeningExamData = (data, type) => {
    const listeningReviewData = buildListeningReviewData(data, type);

    setCurrentPartTab(1);
    setPassages([]);
    setQuestionGroups(listeningReviewData.groups);
    setParts(listeningReviewData.parts);
  };

const processReadingExamData = (data, type) => {
  const readingReviewData = buildReadingReviewData(data, type);
  setCurrentPartTab(1);
  if (type === 'section') {
    setPassages([{ id: data.id, part: 1, title: data.title || 'Reading Passage', subtitle: '', content: data.content || '' }]);
  } else {
    setPassages(
      (data.sections || []).map((section, idx) => ({
        id: section.id,
        part: idx + 1,
        title: section.title || `Reading Passage ${idx + 1}`,
        subtitle: '',
        content: section.content || ''
      }))
    );
  }
  setQuestionGroups(readingReviewData.groups);
  setParts(readingReviewData.parts);
};
  const processExamData = (data, testResult, type) => {
    if (type === 'section') {
      // Process single section
      const section = data;
      
      setPassages([{
        id: section.id,
        part: 1,
        title: section.title || 'Reading Passage',
        subtitle: '',
        content: section.content || ''
      }]);
      
      setParts([{ 
        id: section.id, 
        part: 1, 
        title: `Part 1 (1-${section.question_groups?.reduce((sum, g) => sum + (g.questions?.length || 0), 0) || 0})` 
      }]);
      
      const allGroups = [];
      let questionNumber = 1;
      
      if (section.question_groups) {
        section.question_groups.forEach(group => {
          const questions = [];
          if (group.questions) {
            group.questions.forEach((q) => {
              // Parse metadata to get explanation from answers[].feedback
              let explanation = '';
              let locateText = '';
              if (q.metadata) {
                const metadata = typeof q.metadata === 'string' ? JSON.parse(q.metadata) : q.metadata;
                
                // Get feedback from metadata.answers array
                if (metadata.answers && Array.isArray(metadata.answers)) {
                  const correctAnswer = metadata.answers.find(a => a.is_correct === "1" || a.is_correct === 1 || a.is_correct === true);
                  if (correctAnswer && correctAnswer.feedback) {
                    explanation = correctAnswer.feedback;
                  }
                }
                
                // Fallback to other fields
                explanation = explanation || metadata.explanation || metadata.feedback || q.feedback || '';
                locateText = metadata.locate || metadata.hint || q.hint || '';
                
                // Debug log
                console.log('Question', q.id, '- Explanation:', explanation);
              }

              questions.push({
                id: q.id,
                number: questionNumber++,
                content: q.content,
                correctAnswer: q.answer_content,
                metadata: q.metadata,
                explanation: explanation,
                locateText: locateText
              });
            });
          }
          
          let options = [];
          let optionsWithContent = null;
          const questionType = (group.question_type || '').toLowerCase();
          
          switch (questionType) {
            case 'multiple_choice':
            case 'matching':
            case 'matching_information':
            case 'matching_headings':
            case 'matching_features':
            case 'matching_sentence_endings':
              if (group.questions && group.questions.length > 0) {
                const firstQuestion = group.questions[0];
                if (firstQuestion.metadata) {
                  const metadata = typeof firstQuestion.metadata === 'string' 
                    ? JSON.parse(firstQuestion.metadata) 
                    : firstQuestion.metadata;
                  
                  if (metadata.answers && Array.isArray(metadata.answers)) {
                    options = metadata.answers.map((answer, index) => answer.letter || answer.label || String.fromCharCode(65 + index));
                    optionsWithContent = metadata.answers.map((answer, index) => {
                      let content = answer.content || '';
                      content = content.replace(/^<p[^>]*>|<\/p>$/gi, '').trim();
                      return {
                        letter: answer.letter || answer.label || String.fromCharCode(65 + index),
                        content: content
                      };
                    });
                  }
                }
              }
              break;
              
            case 'yes_no_not_given':
              options = (group.options && group.options.length > 0) 
                ? group.options 
                : ['Yes', 'No', 'Not Given'];
              break;
              
            case 'true_false_not_given':
              options = (group.options && group.options.length > 0) 
                ? group.options 
                : ['True', 'False', 'Not Given'];
              break;
              
            case 'short_text':
            case 'note_completion':
            case 'form_completion':
            case 'table_completion':
            case 'flow_chart_completion':
            case 'diagram_label_completion':
            case 'summary_completion':
            case 'sentence_completion':
            case 'short_answer_questions':
            case 'plan_map_diagram_labelling':
              options = [];
              break;
              
            default:
              options = (group.options && group.options.length > 0) ? group.options : [];
              break;
          }
          
          allGroups.push({
            id: group.id,
            part: 1,
            type: group.question_type || 'TRUE_FALSE_NOT_GIVEN',
            instructions: group.instructions,
            groupContent: group.content,
            options: options,
            optionsWithContent: optionsWithContent,
            questions: questions,
            startNumber: questions[0]?.number || 1,
            endNumber: questions[questions.length - 1]?.number || 1
          });
        });
      }
      setQuestionGroups(allGroups);
      
    } else if (type === 'skill') {
      // Process full skill (multiple sections)
      const skill = data;
      const allGroups = [];
      const allPassages = [];
      const allParts = [];
      let questionNumber = 1;
      
      if (skill.sections && skill.sections.length > 0) {
        skill.sections.forEach((section, sectionIndex) => {
          const partNumber = sectionIndex + 1;
          const groupStartIndex = allGroups.length;
          
          allPassages.push({
            id: section.id,
            part: partNumber,
            title: section.title || `Reading Passage ${partNumber}`,
            subtitle: '',
            content: section.content || ''
          });
          
          if (section.question_groups) {
            section.question_groups.forEach(group => {
              const questions = [];
              if (group.questions) {
                group.questions.forEach((q) => {
                  // Parse metadata to get explanation from answers[].feedback
                  let explanation = '';
                  let locateText = '';
                  if (q.metadata) {
                    const metadata = typeof q.metadata === 'string' ? JSON.parse(q.metadata) : q.metadata;
                    
                    // Get feedback from metadata.answers array
                    if (metadata.answers && Array.isArray(metadata.answers)) {
                      const correctAnswer = metadata.answers.find(a => a.is_correct === "1" || a.is_correct === 1 || a.is_correct === true);
                      if (correctAnswer && correctAnswer.feedback) {
                        explanation = correctAnswer.feedback;
                      }
                    }
                    
                    // Fallback to other fields
                    explanation = explanation || metadata.explanation || metadata.feedback || q.feedback || '';
                    locateText = metadata.locate || metadata.hint || q.hint || '';
                  }

                  questions.push({
                    id: q.id,
                    number: questionNumber++,
                    content: q.content,
                    correctAnswer: q.answer_content,
                    metadata: q.metadata,
                    explanation: explanation,
                    locateText: locateText
                  });
                });
              }
              
              let options = [];
              let optionsWithContent = null;
              const questionType = (group.question_type || '').toLowerCase();
              
              switch (questionType) {
                case 'multiple_choice':
                case 'matching':
                case 'matching_information':
                case 'matching_headings':
                case 'matching_features':
                case 'matching_sentence_endings':
                  if (group.questions && group.questions.length > 0) {
                    const firstQuestion = group.questions[0];
                    if (firstQuestion.metadata) {
                      const metadata = typeof firstQuestion.metadata === 'string' 
                        ? JSON.parse(firstQuestion.metadata) 
                        : firstQuestion.metadata;
                      
                      if (metadata.answers && Array.isArray(metadata.answers)) {
                        options = metadata.answers.map((answer, index) => answer.letter || answer.label || String.fromCharCode(65 + index));
                        optionsWithContent = metadata.answers.map((answer, index) => {
                          let content = answer.content || '';
                          content = content.replace(/^<p[^>]*>|<\/p>$/gi, '').trim();
                          return {
                            letter: answer.letter || answer.label || String.fromCharCode(65 + index),
                            content: content
                          };
                        });
                      }
                    }
                  }
                  break;
                  
                case 'yes_no_not_given':
                  options = (group.options && group.options.length > 0) 
                    ? group.options 
                    : ['Yes', 'No', 'Not Given'];
                  break;
                  
                case 'true_false_not_given':
                  options = (group.options && group.options.length > 0) 
                    ? group.options 
                    : ['True', 'False', 'Not Given'];
                  break;
                  
                case 'short_text':
                case 'note_completion':
                case 'form_completion':
                case 'table_completion':
                case 'flow_chart_completion':
                case 'diagram_label_completion':
                case 'summary_completion':
                case 'sentence_completion':
                case 'short_answer_questions':
                case 'plan_map_diagram_labelling':
                  options = [];
                  break;
                  
                default:
                  options = (group.options && group.options.length > 0) ? group.options : [];
                  break;
              }
              
              allGroups.push({
                id: group.id,
                part: partNumber,
                type: group.question_type || 'TRUE_FALSE_NOT_GIVEN',
                instructions: group.instructions,
                groupContent: group.content,
                options: options,
                optionsWithContent: optionsWithContent,
                questions: questions,
                startNumber: questions[0]?.number || questionNumber,
                endNumber: questions[questions.length - 1]?.number || questionNumber
              });
            });
          }
          
          const firstQuestionNum = allGroups[groupStartIndex]?.startNumber || questionNumber;
          const lastQuestionNum = allGroups[allGroups.length - 1]?.endNumber || questionNumber;
          allParts.push({
            id: section.id,
            part: partNumber,
            title: `Part ${partNumber} (${firstQuestionNum}-${lastQuestionNum})`
          });
        });
        
        setPassages(allPassages);
        setParts(allParts);
      }
      
      setQuestionGroups(allGroups);
    }
  };

  const toggleExplanation = (questionId) => {
    const nextExpanded = !expandedExplanations[questionId];
    setActiveQuestionId(nextExpanded ? questionId : null);
    setExpandedExplanations(prev => ({
      ...prev,
      [questionId]: !prev[questionId]
    }));
  };

  const toggleListeningExplanation = (questionId) => {
    setExpandedExplanations(prev => (
      prev[questionId] ? {} : { [questionId]: true }
    ));
  };

  const handleLocate = (locateText) => {
    if (!locateText) return;
    console.log('Locate:', locateText);
  };

  if (loading) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh' }}>
        <div style={{ textAlign: 'center' }}>
          <div style={{ fontSize: '18px', color: '#6B7280', marginBottom: '16px' }}>
            Đang tải dữ liệu...
          </div>
        </div>
      </div>
    );
  }

  if ((!isListeningReview && !isReadingReview && (!passages || passages.length === 0)) || questionGroups.length === 0) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh' }}>
        <div style={{ textAlign: 'center' }}>
          <div style={{ fontSize: '18px', color: '#EF4444', marginBottom: '16px' }}>
            Không tìm thấy dữ liệu
          </div>
        </div>
      </div>
    );
  }

  const currentPart = parts.find((part) => String(part.part) === String(currentPartTab));
  const currentPartGroups = questionGroups.filter((group) => String(group.part) === String(currentPartTab));
  const currentReviewType = (currentPartGroups[0]?.type || '').toLowerCase();
  const isTrueFalseNotGivenReview = currentReviewType === 'true_false_not_given';
  const isMatchingHeadingsReview = currentReviewType === 'matching_headings';
  const isMatchingFeaturesReview = currentReviewType === 'matching_features';
  const isMatchingSentenceEndingsReview = currentReviewType === 'matching_sentence_endings';
  const isSummaryCompletionReview = currentReviewType === 'summary_completion';
  const isSentenceCompletionReview = currentReviewType === 'sentence_completion';
  const isNoteCompletionReview = currentReviewType === 'note_completion';
  const isTableCompletionReview = currentReviewType === 'table_completion';
  const isFlowChartCompletionReview = currentReviewType === 'flow_chart_completion';
  const isDiagramLabelCompletionReview = currentReviewType === 'diagram_label_completion';
  const isShortAnswerReview = currentReviewType === 'short_answer_questions';
  const currentPassage = currentPart?.id
    ? passages.find((passage) => String(passage.id) === String(currentPart.id))
    : passages.find((passage) => String(passage.part) === String(currentPartTab));

  if (isListeningReview) {
    return (
      <ListeningReview
        skillData={skillData}
        sectionData={sectionData}
        result={result}
        parts={parts}
        currentPartTab={currentPartTab}
        setCurrentPartTab={setCurrentPartTab}
        questionGroups={questionGroups}
        userAnswers={userAnswers}
        expandedExplanations={expandedExplanations}
        onToggleExplanation={toggleListeningExplanation}
        onLocate={handleLocate}
        fontSize={fontSize}
        onFontSizeChange={setFontSize}
      />
    );
  }

  if (!currentPassage) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh' }}>
        <div style={{ textAlign: 'center' }}>
          <div style={{ fontSize: '18px', color: '#EF4444', marginBottom: '16px' }}>
            Không tìm thấy passage cho section hiện tại
          </div>
        </div>
      </div>
    );
  }

  // Render câu hỏi ở chế độ review (readonly with colors)
  const renderReviewQuestions = (group) => {
    const questionType = (group.type || '').toLowerCase();
    const ReviewComponent = getReadingReviewRenderer(questionType);
    return (
      <ReviewComponent
        group={group}
        userAnswers={userAnswers}
        expandedExplanations={expandedExplanations}
        activeQuestionId={activeQuestionId}
        onToggleExplanation={toggleExplanation}
        onQuestionFocus={(question) => setActiveQuestionId(question?.id ?? null)}
        onLocate={handleLocate}
      />
    );
  };

  return (
    <TestLayout
      skillData={skillData}
      sectionData={sectionData}
      timeRemaining={timeRemaining}
      setTimeRemaining={() => {}}
      parts={parts}
      currentPartTab={currentPartTab}
      setCurrentPartTab={setCurrentPartTab}
      questionGroups={questionGroups}
      answers={buildFooterAnswers(userAnswers)}
      onSubmit={null}
      showQuestionNumbers={true}
      fontSize={fontSize}
      onFontSizeChange={setFontSize}
    >
      <div className={isReadingReview ? 'reading-review__content-shell' : 'reading-test__content-wrapper'}>
        {isReadingReview && (
          <h1 className="reading-review__passage-title">
            {isTrueFalseNotGivenReview ||
              isMatchingHeadingsReview ||
              isMatchingFeaturesReview ||
              isMatchingSentenceEndingsReview ||
              isSummaryCompletionReview ||
              isSentenceCompletionReview ||
              isNoteCompletionReview ||
              isTableCompletionReview ||
              isFlowChartCompletionReview ||
              isDiagramLabelCompletionReview ||
              isShortAnswerReview
              ? 'Reading'
              : /reading\s+passage/i.test(currentPassage.title || '')
              ? currentPassage.title
              : `Reading passage ${currentPartTab}`}
          </h1>
        )}
        <div className={[
          isReadingReview ? 'reading-test__content-wrapper reading-review__content-wrapper' : 'reading-test__content-wrapper',
          isTrueFalseNotGivenReview ? 'reading-review__content-wrapper--tfng' : '',
          isMatchingHeadingsReview ? 'reading-review__content-wrapper--matching-headings' : '',
          isMatchingFeaturesReview ? 'reading-review__content-wrapper--matching-features' : '',
          isMatchingSentenceEndingsReview ? 'reading-review__content-wrapper--matching-sentence-endings' : '',
          isSummaryCompletionReview ? 'reading-review__content-wrapper--summary-completion' : '',
          isSentenceCompletionReview ? 'reading-review__content-wrapper--sentence-completion' : '',
          isNoteCompletionReview ? 'reading-review__content-wrapper--note-completion' : '',
          isTableCompletionReview ? 'reading-review__content-wrapper--table-completion' : '',
          isFlowChartCompletionReview ? 'reading-review__content-wrapper--flow-chart' : '',
          isDiagramLabelCompletionReview ? 'reading-review__content-wrapper--diagram' : '',
          isShortAnswerReview ? 'reading-review__content-wrapper--short-answer' : ''
        ].filter(Boolean).join(' ')}>
          <div className={isReadingReview ? 'reading-test__passage reading-review__passage' : 'reading-test__passage'}>
            {!isReadingReview && (
              <div className="reading-test__passage-header">
                <h2 className="reading-test__passage-title">{currentPassage.title}</h2>
                {currentPassage.subtitle && (
                  <p className="reading-test__passage-subtitle">{currentPassage.subtitle}</p>
                )}
              </div>
            )}
            {isReadingReview ? (
              <ReadingReviewPassageContent
                fontSize={fontSize}
                content={currentPassage.content}
                groups={currentPartGroups}
                userAnswers={userAnswers}
                activeQuestionId={activeQuestionId}
              />
            ) : (
              <PassageContent fontSize={fontSize} content={currentPassage.content} />
            )}
          </div>

          <div className={`reading-test__questions reading-test__questions--${fontSize} ${isReadingReview ? 'reading-review__questions' : ''}`}>
            {currentPartGroups.map((group) => (
              <div key={group.id} id={`question-group-${group.id}`} className="reading-test__question-group">
                {renderReviewQuestions(group)}
              </div>
            ))}
          </div>
        </div>
      </div>
    </TestLayout>
  );
}
