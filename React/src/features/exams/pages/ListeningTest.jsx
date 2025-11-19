import { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import TestLayout from '../components/TestLayout';
import { getSkillById, getSectionById } from '../api/exams.api';
import './ListeningTest.css';

const ListeningTest = () => {
  const { skillId, sectionId } = useParams();
  const navigate = useNavigate();
  
  const [answers, setAnswers] = useState({});
  const [timeRemaining, setTimeRemaining] = useState(2400); // 40 minutes default
  const [currentPartTab, setCurrentPartTab] = useState(1);
  const [loading, setLoading] = useState(true);
  const [skillData, setSkillData] = useState(null);
  const [sectionData, setSectionData] = useState(null);
  const [questionGroups, setQuestionGroups] = useState([]);
  const [parts, setParts] = useState([]);
  const [fontSize, setFontSize] = useState('normal');
  const audioRef = useRef(null);

  useEffect(() => {
    const fetchExamData = async () => {
      try {
        setLoading(true);
        let data;
        
        if (sectionId) {
          data = await getSectionById(sectionId);
          setSectionData(data);
        } else {
          data = await getSkillById(skillId);
          setSkillData(data);
        }

        if (data?.questionGroups) {
          setQuestionGroups(data.questionGroups);
          
          const uniqueParts = [...new Set(data.questionGroups.map(g => g.part))]
            .sort((a, b) => a - b)
            .map(partNum => ({
              part: partNum,
              name: `Part ${partNum}`
            }));
          setParts(uniqueParts);
          setCurrentPartTab(uniqueParts[0]?.part || 1);
        }

        if (data?.duration) {
          setTimeRemaining(data.duration * 60);
        }
      } catch (error) {
        console.error('Error fetching exam data:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchExamData();
  }, [skillId, sectionId]);

  const handleAnswerChange = (questionId, value) => {
    setAnswers(prev => ({
      ...prev,
      [questionId]: value
    }));
  };

  const handleSubmit = () => {
    console.log('Submitting answers:', answers);
    // TODO: Submit to API
    navigate('/exams/result');
  };

  if (loading) {
    return <div className="listening-test__loading">Loading...</div>;
  }

  if (!skillData && !sectionData) {
    return (
      <div className="listening-test__loading">
        <p>Không tìm thấy dữ liệu bài thi</p>
        <button onClick={() => navigate(-1)}>Quay lại</button>
      </div>
    );
  }

  const currentPartGroups = questionGroups.filter(g => g.part === currentPartTab);
  const currentPartAudio = currentPartGroups[0]?.audioUrl;

  const renderQuestionsByType = (questions) => {
    return questions.map(question => {
      switch (question.type) {
        case 'MULTIPLE_CHOICE':
          return (
            <div key={question.id} className="listening-test__question-item">
              <div className="listening-test__question-row">
                <span className="listening-test__question-number">{question.number}.</span>
                <div className="listening-test__question-text" dangerouslySetInnerHTML={{ __html: question.text }} />
              </div>
              <div className="listening-test__options">
                {question.options?.map((option, index) => (
                  <label key={index} className={`listening-test__option ${answers[question.id] === option.value ? 'selected' : ''}`}>
                    <input
                      type="radio"
                      name={`question-${question.id}`}
                      value={option.value}
                      checked={answers[question.id] === option.value}
                      onChange={(e) => handleAnswerChange(question.id, e.target.value)}
                    />
                    <span className="listening-test__option-text">{option.text}</span>
                  </label>
                ))}
              </div>
            </div>
          );

        case 'TRUE_FALSE_NOT_GIVEN':
        case 'YES_NO_NOT_GIVEN':
          return (
            <div key={question.id} className="listening-test__question-item">
              <div className="listening-test__question-row">
                <span className="listening-test__question-number">{question.number}.</span>
                <div className="listening-test__question-text" dangerouslySetInnerHTML={{ __html: question.text }} />
              </div>
              <div className="listening-test__options listening-test__options--inline">
                {['True', 'False', 'Not given'].map((option) => (
                  <label key={option} className={`listening-test__option listening-test__option--box ${answers[question.id] === option ? 'selected' : ''}`}>
                    <input
                      type="radio"
                      name={`question-${question.id}`}
                      value={option}
                      checked={answers[question.id] === option}
                      onChange={(e) => handleAnswerChange(question.id, e.target.value)}
                    />
                    <span className="listening-test__option-text">{option}</span>
                  </label>
                ))}
              </div>
            </div>
          );

        case 'SHORT_TEXT':
        case 'FILL_IN_BLANK':
          return (
            <div key={question.id} className="listening-test__question-item">
              <div className="listening-test__question-row">
                <span className="listening-test__question-number">{question.number}.</span>
                <div className="listening-test__question-text" dangerouslySetInnerHTML={{ __html: question.text }} />
              </div>
              <input
                type="text"
                className="listening-test__text-input"
                placeholder="Type your answer here..."
                value={answers[question.id] || ''}
                onChange={(e) => handleAnswerChange(question.id, e.target.value)}
              />
            </div>
          );

        default:
          return null;
      }
    });
  };

  return (
    <TestLayout
      examData={skillData || sectionData}
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
      <div className={`listening-test__content ${fontSize !== 'normal' ? `listening-test__content--${fontSize}` : ''}`}>
        {/* Audio Player - Fixed at top */}
        {currentPartAudio && (
          <div className="listening-test__audio-section">
            <div className="listening-test__audio-label">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM9.5 16.5V7.5L16.5 12L9.5 16.5Z" fill="currentColor"/>
              </svg>
              Audio for Part {currentPartTab}
            </div>
            <audio
              ref={audioRef}
              controls
              src={currentPartAudio}
              className="listening-test__audio-player"
            />
          </div>
        )}

        {/* Task Info */}
        {currentPartGroups.map((group) => (
          <div key={group.id} className="listening-test__task-section">
            <div className="listening-test__task-header">
              <h3 className="listening-test__task-title">
                Listening Task {group.part}
              </h3>
              <div className="listening-test__task-info">
                You should spend 40 minutes on this task.
              </div>
            </div>

            {/* Task Instructions */}
            {group.instructions && (
              <div 
                className="listening-test__task-instructions"
                dangerouslySetInnerHTML={{ __html: group.instructions }}
              />
            )}

            {/* Task Content (image, map, etc.) */}
            {group.content && (
              <div 
                className="listening-test__task-content"
                dangerouslySetInnerHTML={{ __html: group.content }}
              />
            )}

            {/* Question Header */}
            {group.questionHeader && (
              <div className="listening-test__question-header-section">
                <div 
                  className="listening-test__question-header-text"
                  dangerouslySetInnerHTML={{ __html: group.questionHeader }}
                />
              </div>
            )}

            {/* Questions */}
            <div className="listening-test__questions-list">
              {renderQuestionsByType(group.questions || [])}
            </div>
          </div>
        ))}
      </div>
    </TestLayout>
  );
};

export default ListeningTest;
