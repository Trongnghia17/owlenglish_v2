import { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import TestLayout from '../components/TestLayout';
import { getSkillById, getSectionById } from '../api/exams.api';
import './SpeakingTest.css';

const SpeakingTest = () => {
  const { skillId, sectionId } = useParams();
  const navigate = useNavigate();
  
  const [answers, setAnswers] = useState({});
  const [timeRemaining, setTimeRemaining] = useState(900); // 15 minutes default
  const [currentPartTab, setCurrentPartTab] = useState(1);
  const [loading, setLoading] = useState(true);
  const [skillData, setSkillData] = useState(null);
  const [sectionData, setSectionData] = useState(null);
  const [questionGroups, setQuestionGroups] = useState([]);
  const [parts, setParts] = useState([]);
  const [fontSize, setFontSize] = useState('normal');
  const [recordings, setRecordings] = useState({});
  const [isRecording, setIsRecording] = useState({});
  const mediaRecorderRef = useRef({});
  const audioChunksRef = useRef({});

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

  const startRecording = async (questionId) => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      const mediaRecorder = new MediaRecorder(stream);
      mediaRecorderRef.current[questionId] = mediaRecorder;
      audioChunksRef.current[questionId] = [];

      mediaRecorder.ondataavailable = (event) => {
        audioChunksRef.current[questionId].push(event.data);
      };

      mediaRecorder.onstop = () => {
        const audioBlob = new Blob(audioChunksRef.current[questionId], { type: 'audio/webm' });
        const audioUrl = URL.createObjectURL(audioBlob);
        setRecordings(prev => ({
          ...prev,
          [questionId]: audioUrl
        }));
        stream.getTracks().forEach(track => track.stop());
      };

      mediaRecorder.start();
      setIsRecording(prev => ({ ...prev, [questionId]: true }));
    } catch (error) {
      console.error('Error starting recording:', error);
      alert('Could not access microphone. Please check permissions.');
    }
  };

  const stopRecording = (questionId) => {
    if (mediaRecorderRef.current[questionId]) {
      mediaRecorderRef.current[questionId].stop();
      setIsRecording(prev => ({ ...prev, [questionId]: false }));
    }
  };

  const handleSubmit = () => {
    console.log('Submitting recordings:', recordings);
    // TODO: Submit to API
    navigate('/exams/result');
  };

  const currentPartGroups = questionGroups.filter(g => g.part === currentPartTab);

  if (loading) {
    return <div className="speaking-test__loading">Loading...</div>;
  }

  if (!skillData && !sectionData) {
    return (
      <div className="speaking-test__loading">
        <p>Không tìm thấy dữ liệu bài thi</p>
        <button onClick={() => navigate(-1)}>Quay lại</button>
      </div>
    );
  }

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
      <div className={`speaking-test__content ${fontSize !== 'normal' ? `speaking-test__content--${fontSize}` : ''}`}>
        {/* Part Header */}
        <div className="speaking-test__part-header">
          <h2 className="speaking-test__part-title">
            Speaking Part {currentPartTab}
          </h2>
          {currentPartTab === 1 && (
            <div className="speaking-test__part-description">
              The examiner will start by introducing him / herself and checking your identity. He or she will then ask you some questions about yourself.
            </div>
          )}
          {currentPartTab === 2 && (
            <div className="speaking-test__part-description">
              The examiner will give you a topic like the one below and some paper and a pencil.
            </div>
          )}
          {currentPartTab === 3 && (
            <div className="speaking-test__part-description">
              The examiner will ask some general questions which are connected to the topic in Part 2, for example:
            </div>
          )}
        </div>

        {/* Questions */}
        <div className="speaking-test__questions">
          {currentPartGroups.map((group, groupIndex) => (
            <div key={group.id} className="speaking-test__question-group">
              {group.questions?.map((question, qIndex) => (
                <div key={question.id} className="speaking-test__question-item">
                  <div className="speaking-test__question-header">
                    <span className="speaking-test__question-number">
                      {question.number}.
                    </span>
                    <div 
                      className="speaking-test__question-text"
                      dangerouslySetInnerHTML={{ __html: question.text }}
                    />
                  </div>

                  {/* Recording Controls */}
                  <div className="speaking-test__recording-controls">
                    {!recordings[question.id] ? (
                      <button
                        className={`speaking-test__record-button ${isRecording[question.id] ? 'recording' : ''}`}
                        onClick={() => isRecording[question.id] ? stopRecording(question.id) : startRecording(question.id)}
                      >
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                          <path d="M10 2C8.34 2 7 3.34 7 5V10C7 11.66 8.34 13 10 13C11.66 13 13 11.66 13 10V5C13 3.34 11.66 2 10 2Z" fill="currentColor"/>
                          <path d="M4 10C4 10.552 4.448 11 5 11C5.552 11 6 10.552 6 10C6 7.243 8.243 5 11 5C13.757 5 16 7.243 16 10C16 10.552 16.448 11 17 11C17.552 11 18 10.552 18 10C18 6.691 15.535 4 12.5 4V2C12.5 1.448 12.052 1 11.5 1C10.948 1 10.5 1.448 10.5 2V4C7.465 4 5 6.691 5 10C5 10.552 4.552 11 4 11Z" fill="currentColor"/>
                        </svg>
                        {isRecording[question.id] ? 'Thu âm' : 'Thu âm'}
                      </button>
                    ) : (
                      <div className="speaking-test__audio-player">
                        <audio controls src={recordings[question.id]} />
                        <button
                          className="speaking-test__upload-button"
                          onClick={() => console.log('Upload:', recordings[question.id])}
                        >
                          <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M13 10L10 7L7 10" stroke="currentColor" strokeWidth="2" strokeLinecap="round"/>
                            <path d="M10 7V14" stroke="currentColor" strokeWidth="2" strokeLinecap="round"/>
                            <path d="M16 14V16C16 16.552 15.552 17 15 17H5C4.448 17 4 16.552 4 16V14" stroke="currentColor" strokeWidth="2" strokeLinecap="round"/>
                          </svg>
                          Tải lên file ghi âm
                        </button>
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          ))}
        </div>
      </div>
    </TestLayout>
  );
};

export default SpeakingTest;
