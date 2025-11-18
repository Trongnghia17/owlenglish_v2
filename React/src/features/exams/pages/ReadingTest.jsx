import { useState, useEffect } from 'react';
import { useNavigate, useParams, useLocation } from 'react-router-dom';
import { getSkillById, getSectionById } from '../api/exams.api';
import './ReadingTest.css';

export default function ReadingTest() {
  const navigate = useNavigate();
  const { skillId, sectionId } = useParams();
  const location = useLocation();
  const examData = location.state?.examData;

  // State để quản lý câu hỏi hiện tại và câu trả lời
  const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);
  const [answers, setAnswers] = useState({});
  const [timeRemaining, setTimeRemaining] = useState(1800); // 30 phút = 1800 giây
  const [currentPartTab, setCurrentPartTab] = useState(1); // Tab hiện tại
  const [loading, setLoading] = useState(true);
  const [skillData, setSkillData] = useState(null);
  const [sectionData, setSectionData] = useState(null);
  const [questions, setQuestions] = useState([]);
  const [passages, setPassages] = useState([]); // Nhiều passages theo part
  const [parts, setParts] = useState([]); // Danh sách các parts

  // Lấy dữ liệu từ API
  useEffect(() => {
    const fetchExamData = async () => {
      try {
        setLoading(true);
        
        if (sectionId) {
          // Lấy data cho section cụ thể (1 part)
          const response = await getSectionById(sectionId, { with_questions: true });
          if (response.data.success) {
            const section = response.data.data;
            setSectionData(section);
            
            // Lấy passage content từ section
            setPassages([{
              id: section.id,
              part: 1,
              title: section.title || 'Reading Passage',
              subtitle: '',
              content: section.content || ''
            }]);
            
            setParts([{ id: section.id, part: 1, title: `Part 1 (1-${section.question_groups?.reduce((sum, g) => sum + (g.questions?.length || 0), 0) || 0})` }]);
            
            // Lấy questions từ question groups
            const allQuestions = [];
            if (section.question_groups) {
              section.question_groups.forEach(group => {
                console.log('Question Group:', group); // Debug
                if (group.questions) {
                  group.questions.forEach((q, qIndex) => {
                    allQuestions.push({
                      id: q.id,
                      number: allQuestions.length + 1,
                      part: 1,
                      type: group.question_type || 'TRUE_FALSE_NOT_GIVEN',
                      question: q.content,
                      options: Array.isArray(group.options) 
                        ? group.options 
                        : (group.options ? (typeof group.options === 'string' ? JSON.parse(group.options) : ['True', 'False', 'Not given']) : ['True', 'False', 'Not given']),
                      correctAnswer: q.answer_content,
                      groupId: group.id,
                      groupInstructions: group.instructions
                    });
                  });
                }
              });
            }
            console.log('All Questions (section):', allQuestions); // Debug
            setQuestions(allQuestions);
          }
        } else if (skillId) {
          // Lấy data cho full test (nhiều parts/sections)
          const response = await getSkillById(skillId, { with_sections: true });
          if (response.data.success) {
            const skill = response.data.data;
            setSkillData(skill);
            
            // Lấy tất cả questions từ tất cả sections
            const allQuestions = [];
            const allPassages = [];
            const allParts = [];
            
            if (skill.sections && skill.sections.length > 0) {
              skill.sections.forEach((section, sectionIndex) => {
                const partNumber = sectionIndex + 1;
                const questionStartIndex = allQuestions.length + 1;
                
                // Lưu passage cho mỗi part
                allPassages.push({
                  id: section.id,
                  part: partNumber,
                  title: section.title || `Reading Passage ${partNumber}`,
                  subtitle: '',
                  content: section.content || ''
                });
                
                // Lấy questions
                if (section.question_groups) {
                  section.question_groups.forEach(group => {
                    console.log('Question Group (full test):', group); // Debug
                    if (group.questions) {
                      group.questions.forEach((q, qIndex) => {
                        allQuestions.push({
                          id: q.id,
                          number: allQuestions.length + 1,
                          part: partNumber,
                          type: group.question_type || 'TRUE_FALSE_NOT_GIVEN',
                          question: q.content,
                          options: Array.isArray(group.options) 
                            ? group.options 
                            : (group.options ? (typeof group.options === 'string' ? JSON.parse(group.options) : ['True', 'False', 'Not given']) : ['True', 'False', 'Not given']),
                          correctAnswer: q.answer_content,
                          groupId: group.id,
                          groupInstructions: group.instructions
                        });
                      });
                    }
                  });
                }
                
                const questionEndIndex = allQuestions.length;
                allParts.push({
                  id: section.id,
                  part: partNumber,
                  title: `Part ${partNumber} (${questionStartIndex}-${questionEndIndex})`
                });
              });
              
              console.log('All Questions (full test):', allQuestions); // Debug
              setPassages(allPassages);
              setParts(allParts);
            }
            
            setQuestions(allQuestions);
            
            // Set thời gian từ skill
            if (skill.time_limit) {
              setTimeRemaining(skill.time_limit * 60); // Convert minutes to seconds
            }
          }
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

  // Timer countdown
  useEffect(() => {
    const timer = setInterval(() => {
      setTimeRemaining((prev) => {
        if (prev <= 0) {
          clearInterval(timer);
          handleSubmit();
          return 0;
        }
        return prev - 1;
      });
    }, 1000);

    return () => clearInterval(timer);
  }, []);

  // Format thời gian
  const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  // Xử lý chọn đáp án
  const handleAnswerSelect = (questionId, answer) => {
    setAnswers({
      ...answers,
      [questionId]: answer
    });
  };

  // Chuyển câu hỏi
  const handleNextQuestion = () => {
    if (currentQuestionIndex < questions.length - 1) {
      const nextQuestion = questions[currentQuestionIndex + 1];
      setCurrentQuestionIndex(currentQuestionIndex + 1);
      // Nếu câu tiếp theo thuộc part khác, chuyển tab part
      if (nextQuestion.part !== currentPartTab) {
        setCurrentPartTab(nextQuestion.part);
      }
    }
  };

  const handlePreviousQuestion = () => {
    if (currentQuestionIndex > 0) {
      const prevQuestion = questions[currentQuestionIndex - 1];
      setCurrentQuestionIndex(currentQuestionIndex - 1);
      // Nếu câu trước đó thuộc part khác, chuyển tab part
      if (prevQuestion.part !== currentPartTab) {
        setCurrentPartTab(prevQuestion.part);
      }
    }
  };

  const handleQuestionClick = (index) => {
    setCurrentQuestionIndex(index);
    const question = questions[index];
    if (question && question.part !== currentPartTab) {
      setCurrentPartTab(question.part);
    }
  };

  // Xử lý nộp bài
  const handleSubmit = () => {
    // TODO: Gửi kết quả về server
    const result = {
      skillId,
      sectionId,
      answers,
      timeSpent: (skillData?.time_limit * 60 || 1800) - timeRemaining
    };
    console.log('Submit result:', result);
    alert('Nộp bài thành công!');
    navigate('/lich-su-lam-bai');
  };

  // Loading state
  if (loading) {
    return (
      <div className="reading-test">
        <div className="reading-test__header">
          <div className="reading-test__header-info">
            <img src="/src/assets/images/logo.png" alt="OWL IELTS" className="reading-test__logo" />
            <div className="reading-test__header-text">
              <div className="reading-test__header-label">Đang tải...</div>
            </div>
          </div>
        </div>
        <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: 'calc(100vh - 73px)' }}>
          <div style={{ textAlign: 'center' }}>
            <div style={{ fontSize: '18px', color: '#6B7280', marginBottom: '16px' }}>
              Đang tải dữ liệu bài thi...
            </div>
          </div>
        </div>
      </div>
    );
  }

  // Error state
  if (!passages || passages.length === 0 || questions.length === 0) {
    return (
      <div className="reading-test">
        <div className="reading-test__header">
          <button className="reading-test__close" onClick={() => navigate(-1)}>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </button>
          <div className="reading-test__header-info">
            <img src="/src/assets/images/logo.png" alt="OWL IELTS" className="reading-test__logo" />
            <div className="reading-test__header-text">
              <div className="reading-test__header-label">Lỗi</div>
            </div>
          </div>
        </div>
        <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: 'calc(100vh - 73px)' }}>
          <div style={{ textAlign: 'center' }}>
            <div style={{ fontSize: '18px', color: '#EF4444', marginBottom: '16px' }}>
              Không tìm thấy dữ liệu bài thi
            </div>
            <button 
              onClick={() => navigate(-1)}
              style={{ 
                padding: '12px 24px', 
                background: '#045CCE', 
                color: 'white', 
                border: 'none', 
                borderRadius: '8px',
                cursor: 'pointer',
                fontSize: '15px',
                fontWeight: '600'
              }}
            >
              Quay lại
            </button>
          </div>
        </div>
      </div>
    );
  }

  const currentQuestion = questions[currentQuestionIndex];
  const currentPassage = passages.find(p => p.part === currentPartTab) || passages[0];
  const currentPartQuestions = questions.filter(q => q.part === currentPartTab);
  
  // Tìm index của câu hỏi đầu tiên trong part hiện tại
  const firstQuestionIndexInPart = questions.findIndex(q => q.part === currentPartTab);
  
  // Chuyển câu hỏi khi đổi part
  const handlePartChange = (partNumber) => {
    setCurrentPartTab(partNumber);
    const firstIndex = questions.findIndex(q => q.part === partNumber);
    if (firstIndex !== -1) {
      setCurrentQuestionIndex(firstIndex);
    }
  };
  
  // Kiểm tra nếu không có currentQuestion
  if (!currentQuestion) {
    return (
      <div className="reading-test">
        <div className="reading-test__header">
          <button className="reading-test__close" onClick={() => navigate(-1)}>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </button>
          <div className="reading-test__header-info">
            <img src="/src/assets/images/logo.png" alt="OWL IELTS" className="reading-test__logo" />
            <div className="reading-test__header-text">
              <div className="reading-test__header-label">Đang tải...</div>
            </div>
          </div>
        </div>
        <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: 'calc(100vh - 73px)' }}>
          <div style={{ textAlign: 'center' }}>
            <div style={{ fontSize: '18px', color: '#6B7280' }}>
              Đang tải câu hỏi...
            </div>
          </div>
        </div>
      </div>
    );
  }
  
  // Kiểm tra xem question content có chứa placeholder {{ a }}, {{ b }}, etc không
  const hasInlineInputs = (content) => {
    return content && /\{\{\s*[a-z]\s*\}\}/i.test(content);
  };

  // Parse và render group instructions với inline input fields
  const renderGroupInstructionsWithInlineInputs = () => {
    let content = currentQuestion.groupInstructions || '';
    
    // Split content theo placeholder pattern
    const parts = content.split(/(\{\{\s*[a-z]\s*\}\})/gi);
    let inputIndex = 0;
    
    return (
      <div className="reading-test__group-instructions">
        {parts.map((part, index) => {
          // Nếu là placeholder
          if (/\{\{\s*[a-z]\s*\}\}/i.test(part)) {
            const inputId = `${currentQuestion.groupId}_${currentQuestion.number}_input_${inputIndex}`;
            inputIndex++;
            
            return (
              <input
                key={`input-${index}`}
                type="text"
                className="reading-test__inline-input"
                id={inputId}
                placeholder="..."
                value={answers[inputId] || ''}
                onChange={(e) => handleAnswerSelect(inputId, e.target.value)}
              />
            );
          }
          
          // Nếu là text HTML thông thường
          return (
            <span 
              key={`text-${index}`}
              dangerouslySetInnerHTML={{ __html: part }}
            />
          );
        })}
      </div>
    );
  };

  // Parse và render question content với inline input fields
  const renderQuestionWithInlineInputs = () => {
    let content = currentQuestion.question || '';
    
    // Split content theo placeholder pattern
    const parts = content.split(/(\{\{\s*[a-z]\s*\}\})/gi);
    let inputIndex = 0;
    
    return (
      <div className="reading-test__question-text">
        {parts.map((part, index) => {
          // Nếu là placeholder
          if (/\{\{\s*[a-z]\s*\}\}/i.test(part)) {
            const inputId = `${currentQuestion.id}_input_${inputIndex}`;
            const currentInputIndex = inputIndex;
            inputIndex++;
            
            return (
              <input
                key={`input-${index}`}
                type="text"
                className="reading-test__inline-input"
                id={inputId}
                placeholder="..."
                value={answers[inputId] || ''}
                onChange={(e) => handleAnswerSelect(inputId, e.target.value)}
              />
            );
          }
          
          // Nếu là text HTML thông thường
          return (
            <span 
              key={`text-${index}`}
              dangerouslySetInnerHTML={{ __html: part }}
            />
          );
        })}
      </div>
    );
  };

  // Render câu hỏi theo loại
  const renderQuestionInput = () => {
    const questionType = currentQuestion.type;

    // Nếu content có {{ a }}, {{ b }} thì dùng inline inputs
    if (hasInlineInputs(currentQuestion.question)) {
      return null; // Không render options riêng, đã có input trong content
    }

    // TRUE/FALSE/NOT GIVEN hoặc YES/NO/NOT GIVEN
    if (questionType === 'TRUE_FALSE_NOT_GIVEN' || questionType === 'YES_NO_NOT_GIVEN') {
      return (
        <div className="reading-test__options">
          {questionOptions.map((option) => (
            <label
              key={option}
              className={`reading-test__option ${
                answers[currentQuestion.id] === option ? 'selected' : ''
              }`}
            >
              <input
                type="radio"
                name={`question-${currentQuestion.id}`}
                value={option}
                checked={answers[currentQuestion.id] === option}
                onChange={() => handleAnswerSelect(currentQuestion.id, option)}
              />
              <span className="reading-test__option-text">{option}</span>
            </label>
          ))}
        </div>
      );
    }

    // MULTIPLE CHOICE - Radio buttons với A, B, C, D...
    if (questionType === 'MULTIPLE_CHOICE' || questionType === 'SINGLE_CHOICE') {
      return (
        <div className="reading-test__options">
          {questionOptions.map((option, index) => (
            <label
              key={index}
              className={`reading-test__option ${
                answers[currentQuestion.id] === option ? 'selected' : ''
              }`}
            >
              <input
                type="radio"
                name={`question-${currentQuestion.id}`}
                value={option}
                checked={answers[currentQuestion.id] === option}
                onChange={() => handleAnswerSelect(currentQuestion.id, option)}
              />
              <span className="reading-test__option-label">{String.fromCharCode(65 + index)}</span>
              <span className="reading-test__option-text">{option}</span>
            </label>
          ))}
        </div>
      );
    }

    // FILL IN THE BLANK - Text input
    if (questionType === 'FILL_IN_BLANK' || questionType === 'SHORT_ANSWER' || questionType === 'COMPLETION') {
      return (
        <div className="reading-test__input-group">
          <input
            type="text"
            className="reading-test__text-input"
            placeholder="Type your answer here..."
            value={answers[currentQuestion.id] || ''}
            onChange={(e) => handleAnswerSelect(currentQuestion.id, e.target.value)}
          />
          {currentQuestion.groupInstructions && (
            <div className="reading-test__word-limit">
              {currentQuestion.groupInstructions}
            </div>
          )}
        </div>
      );
    }

    // MATCHING - Dropdown hoặc select
    if (questionType === 'MATCHING' || questionType === 'MATCHING_HEADINGS') {
      return (
        <div className="reading-test__dropdown-group">
          <select
            className="reading-test__dropdown"
            value={answers[currentQuestion.id] || ''}
            onChange={(e) => handleAnswerSelect(currentQuestion.id, e.target.value)}
          >
            <option value="">-- Select an option --</option>
            {questionOptions.map((option, index) => (
              <option key={index} value={option}>
                {option}
              </option>
            ))}
          </select>
        </div>
      );
    }

    // DRAG AND DROP / LIST SELECTION - Clickable list items
    if (questionType === 'LIST_SELECTION' || questionType === 'DRAG_DROP') {
      return (
        <div className="reading-test__list-options">
          <div className="reading-test__list-title">LIST OF OPTIONS</div>
          <div className="reading-test__list-items">
            {questionOptions.map((option, index) => (
              <button
                key={index}
                className={`reading-test__list-item ${
                  answers[currentQuestion.id] === option ? 'selected' : ''
                }`}
                onClick={() => handleAnswerSelect(currentQuestion.id, option)}
              >
                <span className="reading-test__list-icon">⋮⋮</span>
                <span className="reading-test__list-text">{option}</span>
              </button>
            ))}
          </div>
        </div>
      );
    }

    // Default fallback - Radio buttons
    return (
      <div className="reading-test__options">
        {questionOptions.map((option) => (
          <label
            key={option}
            className={`reading-test__option ${
              answers[currentQuestion.id] === option ? 'selected' : ''
            }`}
          >
            <input
              type="radio"
              name={`question-${currentQuestion.id}`}
              value={option}
              checked={answers[currentQuestion.id] === option}
              onChange={() => handleAnswerSelect(currentQuestion.id, option)}
            />
            <span className="reading-test__option-text">{option}</span>
          </label>
        ))}
      </div>
    );
  };

  // Đảm bảo options luôn là array
  const questionOptions = currentQuestion?.options 
    ? (Array.isArray(currentQuestion.options) 
        ? currentQuestion.options 
        : (typeof currentQuestion.options === 'string' 
            ? JSON.parse(currentQuestion.options) 
            : ['True', 'False', 'Not given']))
    : ['True', 'False', 'Not given'];

  return (
    <div className="reading-test">
      {/* Header */}
      <div className="reading-test__header">
        <button className="reading-test__close" onClick={() => navigate(-1)}>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </button>
        <div className="reading-test__header-info">
          <img src="/src/assets/images/logo.png" alt="OWL IELTS" className="reading-test__logo" />
          <div className="reading-test__header-text">
            <div className="reading-test__header-label">Làm bài passage {currentPartTab}</div>
            <div className="reading-test__header-name">
              {examData?.name || skillData?.name || sectionData?.title || 'Reading Test'}
            </div>
          </div>
        </div>
        <div className="reading-test__header-right">
          <div className="reading-test__timer">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
              <path d="M10 0C8.35215 0 6.74099 0.488742 5.37058 1.40442C4.00017 2.3201 2.93206 3.62159 2.30133 5.1443C1.6706 6.66702 1.50558 8.34258 1.82712 9.95909C2.14866 11.5756 2.94234 13.0605 4.10777 14.2259C5.27321 15.3913 6.75807 16.185 8.37458 16.5065C9.99109 16.8281 11.6666 16.6631 13.1894 16.0323C14.7121 15.4016 16.0136 14.3335 16.9292 12.9631C17.8449 11.5927 18.3337 9.98151 18.3337 8.33333C18.3337 6.12632 17.456 4.00956 15.8929 2.44628C14.3296 0.883001 12.2128 0 10 0ZM10 15C8.68179 15 7.39286 14.609 6.29654 13.8765C5.20021 13.1439 4.34572 12.1027 3.84114 10.8846C3.33655 9.66638 3.20453 8.32594 3.46176 7.03273C3.719 5.73953 4.35393 4.55164 5.28628 3.61929C6.21863 2.68694 7.40652 2.052 8.69973 1.79477C9.99293 1.53753 11.3334 1.66956 12.5516 2.17414C13.7697 2.67872 14.8109 3.5332 15.5435 4.62953C16.276 5.72586 16.667 7.01479 16.667 8.33333C16.667 10.1014 15.9646 11.7971 14.7144 13.0474C13.4641 14.2976 11.7684 15 10 15Z" fill="#045CCE"/>
              <path d="M10.8333 7.5H9.16667V4.16667C9.16667 3.94565 9.07887 3.73369 8.92259 3.57741C8.76631 3.42113 8.55435 3.33333 8.33334 3.33333C8.11232 3.33333 7.90036 3.42113 7.74408 3.57741C7.5878 3.73369 7.5 3.94565 7.5 4.16667V8.33333C7.5 8.55435 7.5878 8.76631 7.74408 8.92259C7.90036 9.07887 8.11232 9.16667 8.33334 9.16667H10.8333C11.0544 9.16667 11.2663 9.07887 11.4226 8.92259C11.5789 8.76631 11.6667 8.55435 11.6667 8.33333C11.6667 8.11232 11.5789 7.90036 11.4226 7.74408C11.2663 7.5878 11.0544 7.5 10.8333 7.5Z" fill="#045CCE"/>
            </svg>
            <span>{formatTime(timeRemaining)}</span>
          </div>
          <button className="reading-test__submit-button-header" onClick={handleSubmit}>
            Nộp bài
          </button>
        </div>
      </div>

      {/* Main Content */}
      <div className="reading-test__content">
        {/* Passage Panel */}
        <div className="reading-test__passage">
          <div className="reading-test__passage-header">
            <h2 className="reading-test__passage-title">{currentPassage.title}</h2>
            {currentPassage.subtitle && (
              <p className="reading-test__passage-subtitle">{currentPassage.subtitle}</p>
            )}
          </div>
          <div 
            className="reading-test__passage-content"
            dangerouslySetInnerHTML={{ __html: currentPassage.content }}
          />
        </div>

          {/* Questions Panel */}
        <div className="reading-test__questions">
          <div className="reading-test__questions-header">
            <h3>Question {currentQuestionIndex + 1} - {questions.length}</h3>
            <span className="reading-test__question-type">
              {currentQuestion?.type?.replace(/_/g, ' ') || 'Choose an answer'}
            </span>
          </div>          {/* Group Instructions - nếu có và có inline inputs */}
          {currentQuestion.groupInstructions && hasInlineInputs(currentQuestion.groupInstructions) && (
            renderGroupInstructionsWithInlineInputs()
          )}

          {/* Group Instructions - nếu có và KHÔNG có inline inputs */}
          {currentQuestion.groupInstructions && !hasInlineInputs(currentQuestion.groupInstructions) && (
            <div 
              className="reading-test__group-instructions"
              dangerouslySetInnerHTML={{ __html: currentQuestion.groupInstructions }}
            />
          )}

          {/* Current Question */}
          <div className="reading-test__current-question">
            <div className="reading-test__question-number">
              {currentQuestion.number}
            </div>
            {hasInlineInputs(currentQuestion.question) ? (
              renderQuestionWithInlineInputs()
            ) : (
              <div 
                className="reading-test__question-text"
                dangerouslySetInnerHTML={{ __html: currentQuestion.question }}
              />
            )}
          </div>

          {/* Answer Options */}
          {renderQuestionInput()}

          {/* Navigation Buttons */}
          <div className="reading-test__navigation">
            <button
              className="reading-test__nav-button"
              onClick={handlePreviousQuestion}
              disabled={currentQuestionIndex === 0}
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
              Previous
            </button>
            <button
              className="reading-test__nav-button reading-test__nav-button--next"
              onClick={handleNextQuestion}
              disabled={currentQuestionIndex === questions.length - 1}
            >
              Next
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
      
      {/* Part Tabs và Question Numbers - Fixed ở dưới cùng */}
      <div className="reading-test__footer">
        {/* Part Tabs */}
        {parts.length > 1 && (
          <div className="reading-test__part-tabs">
            {parts.map((part) => (
              <button
                key={part.part}
                className={`reading-test__part-tab ${currentPartTab === part.part ? 'active' : ''}`}
                onClick={() => handlePartChange(part.part)}
              >
                {part.title}
              </button>
            ))}
          </div>
        )}
        
        {/* Question Numbers Grid - chỉ hiển thị câu của part hiện tại */}
        <div className="reading-test__question-numbers">
          {currentPartQuestions.map((q, index) => {
            const globalIndex = questions.findIndex(qu => qu.id === q.id);
            return (
              <button
                key={q.id}
                className={`reading-test__question-number-item ${
                  globalIndex === currentQuestionIndex ? 'active' : ''
                } ${answers[q.id] ? 'answered' : ''}`}
                onClick={() => handleQuestionClick(globalIndex)}
              >
                {q.number}
              </button>
            );
          })}
        </div>
      </div>
    </div>
  );
}
