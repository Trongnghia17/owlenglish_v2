import { useState, useEffect, useRef } from 'react';
import { useNavigate, useParams, useLocation } from 'react-router-dom';
import { getSkillById, getSectionById } from '../api/exams.api';
import './ReadingTest.css';

export default function ReadingTest() {
  const navigate = useNavigate();
  const { skillId, sectionId } = useParams();
  const location = useLocation();
  const examData = location.state?.examData;

  // State để quản lý câu hỏi hiện tại và câu trả lời
  const [answers, setAnswers] = useState({});
  const [timeRemaining, setTimeRemaining] = useState(1800); // 30 phút = 1800 giây
  const [currentPartTab, setCurrentPartTab] = useState(1); // Tab hiện tại
  const [loading, setLoading] = useState(true);
  const [skillData, setSkillData] = useState(null);
  const [sectionData, setSectionData] = useState(null);
  const [questionGroups, setQuestionGroups] = useState([]); // Lưu theo groups
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
            
            // Lấy question groups
            const allGroups = [];
            let questionNumber = 1;
            if (section.question_groups) {
              section.question_groups.forEach(group => {
                console.log('Question Group:', group); // Debug
                const questions = [];
                if (group.questions) {
                  group.questions.forEach((q) => {
                    questions.push({
                      id: q.id,
                      number: questionNumber++,
                      content: q.content,
                      correctAnswer: q.answer_content
                    });
                  });
                }
                
                allGroups.push({
                  id: group.id,
                  part: 1,
                  type: group.question_type || 'TRUE_FALSE_NOT_GIVEN',
                  instructions: group.instructions,
                  groupContent: group.content, // Thêm group content
                  options: Array.isArray(group.options) 
                    ? group.options 
                    : (group.options ? (typeof group.options === 'string' ? JSON.parse(group.options) : ['True', 'False', 'Not given']) : ['True', 'False', 'Not given']),
                  questions: questions,
                  startNumber: questions[0]?.number || 1,
                  endNumber: questions[questions.length - 1]?.number || 1
                });
              });
            }
            console.log('All Question Groups (section):', allGroups); // Debug
            setQuestionGroups(allGroups);
          }
        } else if (skillId) {
          // Lấy data cho full test (nhiều parts/sections)
          const response = await getSkillById(skillId, { with_sections: true });
          if (response.data.success) {
            const skill = response.data.data;
            setSkillData(skill);
            
            // Lấy tất cả question groups từ tất cả sections
            const allGroups = [];
            const allPassages = [];
            const allParts = [];
            let questionNumber = 1;
            
            if (skill.sections && skill.sections.length > 0) {
              skill.sections.forEach((section, sectionIndex) => {
                const partNumber = sectionIndex + 1;
                const groupStartIndex = allGroups.length;
                
                // Lưu passage cho mỗi part
                allPassages.push({
                  id: section.id,
                  part: partNumber,
                  title: section.title || `Reading Passage ${partNumber}`,
                  subtitle: '',
                  content: section.content || ''
                });
                
                // Lấy question groups
                if (section.question_groups) {
                  section.question_groups.forEach(group => {
                    console.log('Question Group (full test):', group); // Debug
                    const questions = [];
                    if (group.questions) {
                      group.questions.forEach((q) => {
                        questions.push({
                          id: q.id,
                          number: questionNumber++,
                          content: q.content,
                          correctAnswer: q.answer_content
                        });
                      });
                    }
                    
                    allGroups.push({
                      id: group.id,
                      part: partNumber,
                      type: group.question_type || 'TRUE_FALSE_NOT_GIVEN',
                      instructions: group.instructions,
                      groupContent: group.content, // Thêm group content
                      options: Array.isArray(group.options) 
                        ? group.options 
                        : (group.options ? (typeof group.options === 'string' ? JSON.parse(group.options) : ['True', 'False', 'Not given']) : ['True', 'False', 'Not given']),
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
              
              console.log('All Question Groups (full test):', allGroups); // Debug
              setPassages(allPassages);
              setParts(allParts);
            }
            
            setQuestionGroups(allGroups);
            
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

  const handleQuestionClick = (questionNumber) => {
    // Scroll to the question group containing this question
    const group = questionGroups.find(g => 
      g.questions.some(q => q.number === questionNumber)
    );
    if (group) {
      if (group.part !== currentPartTab) {
        setCurrentPartTab(group.part);
      }
      // Scroll to the question group element
      setTimeout(() => {
        const groupElement = document.getElementById(`question-group-${group.id}`);
        if (groupElement) {
          groupElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }, 100);
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
  if (!passages || passages.length === 0 || questionGroups.length === 0) {
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

  const currentPassage = passages.find(p => p.part === currentPartTab) || passages[0];
  
  // Get all questions in current part for question numbers display
  const currentPartGroups = questionGroups.filter(g => g.part === currentPartTab);
  const allQuestionsInPart = currentPartGroups.flatMap(g => g.questions);
  
  // Chuyển part
  const handlePartChange = (partNumber) => {
    setCurrentPartTab(partNumber);
  };

  // Parse group content có chứa {{ a }} thành input fields
  const parseGroupContentWithInputs = (content, questions, answers, handleAnswerSelect) => {
    if (!content) return null;

    // Kiểm tra xem content có chứa {{ }} không
    const hasPlaceholders = /\{\{\s*[a-zA-Z0-9]+\s*\}\}/g.test(content);
    if (!hasPlaceholders) {
      return <div dangerouslySetInnerHTML={{ __html: content }} />;
    }

    // Tạo các input elements với unique IDs
    let questionIndex = 0;
    const inputsMap = new Map();
    
    const processedContent = content.replace(/\{\{\s*([a-zA-Z0-9]+)\s*\}\}/g, (match) => {
      const question = questions[questionIndex];
      if (question) {
        const inputId = `input-placeholder-${question.id}`;
        inputsMap.set(inputId, question);
        questionIndex++;
        return `<span class="reading-test__input-placeholder" data-input-id="${inputId}" data-question-id="${question.id}"></span>`;
      }
      return match;
    });

    // Render content với placeholders
    return (
      <div 
        className="reading-test__group-content-parsed"
        dangerouslySetInnerHTML={{ __html: processedContent }}
        ref={(element) => {
          if (!element) return;
          
          // Replace placeholders với actual input elements
          const placeholders = element.querySelectorAll('.reading-test__input-placeholder');
          placeholders.forEach((placeholder) => {
            const inputId = placeholder.getAttribute('data-input-id');
            const question = inputsMap.get(inputId);
            
            if (!question) return;
            
            // Kiểm tra xem input đã tồn tại chưa
            let input = placeholder.querySelector('input');
            
            if (!input) {
              // Tạo input mới
              input = document.createElement('input');
              input.type = 'text';
              input.className = 'reading-test__inline-input';
              input.placeholder = question.number.toString();
              input.maxLength = 50;
              input.dataset.questionId = question.id;
              
              // Sử dụng oninput để tránh duplicate listeners
              input.oninput = (e) => {
                handleAnswerSelect(question.id, e.target.value);
              };
              
              // Ngăn re-render khi focus vào input
              input.onfocus = (e) => {
                e.stopPropagation();
              };
              
              input.onclick = (e) => {
                e.stopPropagation();
              };
              
              placeholder.appendChild(input);
            }
            
            // Luôn update giá trị từ state (nhưng không thay đổi focus)
            const currentValue = answers[question.id] || '';
            if (input.value !== currentValue && document.activeElement !== input) {
              input.value = currentValue;
            }
          });
        }}
      />
    );
  };

  // Render câu hỏi dựa trên loại question type
  const renderQuestionsByType = (group, answers, handleAnswerSelect) => {
    let questionType = group.type;

    // Auto-detect question type từ instructions nếu backend trả sai
    if (questionType === 'TRUE_FALSE_NOT_GIVEN') {
      const instructions = (group.instructions || '').toLowerCase();
      const options = group.options || [];
      
      console.log('Auto-detecting question type:', {
        instructions,
        options,
        groupType: group.type
      });
      
      // Detect MATCHING - chọn đoạn văn A, B, C...
      if (instructions.includes('which paragraph') || 
          instructions.includes('correct letter') ||
          (options.length > 0 && /^[A-Z]$/.test(options[0]))) {
        questionType = 'MATCHING_HEADINGS';
        console.log('✅ Detected as MATCHING_HEADINGS');
      }
      // Detect MULTIPLE_CHOICE - có nhiều options không phải True/False
      else if (options.length > 3 && 
               !options.includes('True') && 
               !options.includes('False')) {
        questionType = 'MULTIPLE_CHOICE';
        console.log('✅ Detected as MULTIPLE_CHOICE');
      }
      // Detect FILL_IN_THE_BLANK
      else if (instructions.includes('complete') || 
               instructions.includes('no more than') ||
               instructions.includes('write your answer')) {
        questionType = 'FILL_IN_THE_BLANK';
        console.log('✅ Detected as FILL_IN_THE_BLANK');
      }
    }

   

    // 1. Dạng điền vào chỗ trống - Nếu groupContent có {{ a }} thì chỉ render groupContent với inputs
    const hasPlaceholders = group.groupContent && /\{\{\s*[a-zA-Z0-9]+\s*\}\}/g.test(group.groupContent);
    
    if (hasPlaceholders) {
      // Render groupContent với inline inputs thay cho placeholders
      return (
        <div className="reading-test__question-group-with-inputs">
          {parseGroupContentWithInputs(group.groupContent, group.questions, answers, handleAnswerSelect)}
        </div>
      );
    }

    // 2. Dạng text input riêng lẻ (không có placeholders trong content)
    if (questionType === 'FILL_IN_THE_BLANK' || questionType === 'SHORT_ANSWER' || questionType === 'COMPLETE_NOTES') {
      return group.questions.map((question) => (
        <div key={question.id} className="reading-test__question-item reading-test__question-item--input">
          <div className="reading-test__question-row">
            <div className="reading-test__question-number">
              {question.number}
            </div>
            <div
              className="reading-test__question-text"
              dangerouslySetInnerHTML={{ __html: question.content }}
            />
          </div>
          <div className="reading-test__answer-input-wrapper">
            <input
              type="text"
              className="reading-test__answer-input"
              placeholder="Type your answer here..."
              value={answers[question.id] || ''}
              onChange={(e) => handleAnswerSelect(question.id, e.target.value)}
              maxLength={100}
            />
          </div>
        </div>
      ));
    }

    // 3. Dạng Multiple Choice (chọn từ dropdown hoặc list)
    if (questionType === 'MULTIPLE_CHOICE' || questionType === 'CHOOSE_FROM_LIST') {
      return group.questions.map((question) => (
        <div key={question.id} className="reading-test__question-item">
          <div className="reading-test__question-row">
            <div className="reading-test__question-number">
              {question.number}
            </div>
            <div
              className="reading-test__question-text"
              dangerouslySetInnerHTML={{ __html: question.content }}
            />
          </div>
          <div className="reading-test__options reading-test__options--grid">
            {group.options.map((option) => (
              <label
                key={option}
                className={`reading-test__option reading-test__option--box ${
                  answers[question.id] === option ? 'selected' : ''
                }`}
              >
                <input
                  type="radio"
                  name={`question-${question.id}`}
                  value={option}
                  checked={answers[question.id] === option}
                  onChange={() => handleAnswerSelect(question.id, option)}
                />
                <span className="reading-test__option-text">{option}</span>
              </label>
            ))}
          </div>
        </div>
      ));
    }

    // 4. Dạng MATCHING - Chọn đoạn văn A, B, C... (giống Multiple Choice nhưng style khác)
    if (questionType === 'MATCHING_HEADINGS' || questionType === 'MATCHING_INFORMATION' || questionType === 'MATCHING') {
      return group.questions.map((question) => (
        <div key={question.id} className="reading-test__question-item reading-test__question-item--matching">
          <div className="reading-test__question-row">
            <div className="reading-test__question-number">
              {question.number}
            </div>
            <div
              className="reading-test__question-text"
              dangerouslySetInnerHTML={{ __html: question.content }}
            />
          </div>
          <div className="reading-test__matching-select">
            <select
              className="reading-test__select"
              value={answers[question.id] || ''}
              onChange={(e) => handleAnswerSelect(question.id, e.target.value)}
            >
              <option value="">Select...</option>
              {group.options.map((option) => (
                <option key={option} value={option}>
                  {option}
                </option>
              ))}
            </select>
          </div>
        </div>
      ));
    }

    // 5. Dạng TRUE/FALSE/NOT GIVEN (mặc định)
    return group.questions.map((question) => (
      <div key={question.id} className="reading-test__question-item">
        <div className="reading-test__question-row">
          <div className="reading-test__question-number">
            {question.number}
          </div>
          <div
            className="reading-test__question-text"
            dangerouslySetInnerHTML={{ __html: question.content }}
          />
        </div>
        <div className="reading-test__options">
          {group.options.map((option) => (
            <label
              key={option}
              className={`reading-test__option ${
                answers[question.id] === option ? 'selected' : ''
              }`}
            >
              <input
                type="radio"
                name={`question-${question.id}`}
                value={option}
                checked={answers[question.id] === option}
                onChange={() => handleAnswerSelect(question.id, option)}
              />
              <span className="reading-test__option-text">{option}</span>
            </label>
          ))}
        </div>
      </div>
    ));
  };
  
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
          {/* Render all question groups for current part */}
          {currentPartGroups.map((group) => (
            <div key={group.id} id={`question-group-${group.id}`} className="reading-test__question-group">
              {/* Group Header */}
              <div className="reading-test__group-header">
                <h3>Questions {group.startNumber} - {group.endNumber}</h3>
                {group.instructions && (
                  <div 
                    className="reading-test__group-instructions"
                    dangerouslySetInnerHTML={{ __html: group.instructions }}
                  />
                )}
              </div>

              {/* Group Content - Hiển thị nội dung đầy đủ của question group */}
              {group.groupContent && !(/\{\{\s*[a-zA-Z0-9]+\s*\}\}/g.test(group.groupContent)) && (
                <div
                  className="reading-test__group-content"
                  dangerouslySetInnerHTML={{ __html: group.groupContent }}
                />
              )}

              {/* All Questions in Group */}
              <div className="reading-test__questions-list">
                {renderQuestionsByType(group, answers, handleAnswerSelect)}
              </div>
            </div>
          ))}
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
          {allQuestionsInPart.map((q) => (
            <button
              key={q.id}
              className={`reading-test__question-number-item ${
                answers[q.id] ? 'answered' : ''
              }`}
              onClick={() => handleQuestionClick(q.number)}
            >
              {q.number}
            </button>
          ))}
        </div>
      </div>
    </div>
  );
}
