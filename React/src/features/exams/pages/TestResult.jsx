import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { getTestResult } from '../api/exams.api';
import logo from '@/assets/images/logo.png';
import './TestResult.css';

export default function TestResult() {
  const { resultId } = useParams();
  const navigate = useNavigate();
  const [result, setResult] = useState(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('all'); // 'all', 'correct', 'incorrect'

  useEffect(() => {
    const fetchResult = async () => {
      try {
        const response = await getTestResult(resultId);
        if (response.data.success) {
          setResult(response.data.data);
        }
      } catch (error) {
        console.error('Error fetching test result:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchResult();
  }, [resultId]);

  if (loading) {
    return (
      <div className="test-result__loading">
        <div>Đang tải kết quả...</div>
      </div>
    );
  }

  if (!result) {
    return (
      <div className="test-result__error">
        <div>Không tìm thấy kết quả bài thi</div>
        <button onClick={() => navigate(-1)}>Quay lại</button>
      </div>
    );
  }

  // Kiểm tra nếu là Speaking hoặc Writing
  const skillType = result.skill?.skill_type?.toLowerCase() || '';
  const isSpeakingOrWriting = skillType === 'speaking' || skillType === 'writing';

  // Nếu là Speaking hoặc Writing, hiển thị UI đặc biệt
  if (isSpeakingOrWriting) {
    return (
      <div className="test-result">
        <div className="test-result__header">
          <button className="test-result__close" onClick={() => navigate(-1)}>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </button>
          <div className="test-result__header-info">
            <img src={logo} alt="OWL English" className="test-result__logo" />
            <div className="test-result__header-text">
              <div className="test-result__header-label">Làm bài passage 1</div>
              <div className="test-result__header-name">
                {result.test?.name || result.skill?.name || result.section?.title || 'Test'}
              </div>
            </div>
          </div>
        </div>

        <div className="test-result__content test-result__content--centered">
          <div className="test-result__success-card">
            <div className="test-result__success-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="46" height="48" viewBox="0 0 46 48" fill="none">
  <path d="M44.639 28.5546C43.6562 27.0259 42.6735 25.5517 41.6907 24.023C41.4723 23.6954 41.4723 23.477 41.6907 23.1494C42.6735 21.6753 43.6016 20.2012 44.5844 18.727C45.7309 16.9799 45.1304 15.2874 43.1648 14.5776C41.5269 13.977 39.889 13.3218 38.251 12.7213C37.9235 12.6121 37.7597 12.3937 37.7597 12.0115C37.7051 10.2098 37.5959 8.40805 37.4867 6.66092C37.3775 4.75 35.9033 3.65805 34.047 4.14943C32.2999 4.58621 30.5528 5.07759 28.8602 5.56897C28.4781 5.67816 28.2597 5.56897 27.9867 5.29598C26.8947 3.87644 25.7482 2.5115 24.6562 1.14655C23.4551 -0.382184 21.5987 -0.382184 20.343 1.14655C19.251 2.5115 18.1045 3.87644 17.0671 5.24138C16.7941 5.62356 16.5212 5.67816 16.0844 5.56897C14.3918 5.07759 12.6993 4.64081 11.4982 4.31322C9.15047 3.76724 7.73094 4.6954 7.62174 6.66092C7.51254 8.46265 7.40335 10.2644 7.34875 12.1207C7.34875 12.5029 7.18495 12.6667 6.85737 12.8305C5.16484 13.4856 3.47232 14.1408 1.77978 14.796C0.0326588 15.5058 -0.513318 17.1983 0.524035 18.7816C1.50679 20.3104 2.48956 21.7845 3.47231 23.3132C3.69069 23.6408 3.69069 23.8592 3.47231 24.2414C2.43495 25.7701 1.45219 27.2989 0.469434 28.8822C-0.458725 30.3563 0.141844 32.1035 1.77977 32.7586C3.47231 33.4138 5.21943 34.069 6.91196 34.7242C7.29414 34.8334 7.40333 35.0517 7.40333 35.4339C7.45793 37.181 7.67632 38.8736 7.67632 40.6207C7.67632 42.3678 9.20506 43.9512 11.3344 43.296C13.0269 42.75 14.7194 42.3678 16.412 41.8764C16.7395 41.7672 16.9579 41.8218 17.1763 42.1494C18.3229 43.569 19.4148 44.9339 20.5614 46.3535C21.8171 47.8822 23.6188 47.8822 24.82 46.3535C25.9665 44.9339 27.0585 43.569 28.205 42.1494C28.4234 41.8764 28.5872 41.7672 28.9694 41.8764C30.7165 42.3678 32.4637 42.8046 34.2108 43.296C36.0125 43.7874 37.5413 42.6954 37.5958 40.8391C37.705 39.0374 37.8142 37.2356 37.8688 35.3793C37.8688 34.9425 38.0872 34.7787 38.4148 34.6696C40.0527 34.069 41.7453 33.4138 43.3832 32.7586C45.1304 31.8851 45.6763 30.1379 44.639 28.5546ZM31.5901 19.6006L20.6706 30.5201C20.3976 30.7931 20.0154 31.0115 19.6332 31.0661C19.524 31.0661 19.3602 31.1207 19.2511 31.1207C18.7597 31.1207 18.2137 30.9023 17.8315 30.5201L13.3545 26.0431C12.5901 25.2787 12.5901 24.023 13.3545 23.2586C14.1189 22.4943 15.3746 22.4943 16.139 23.2586L19.1965 26.3161L28.6965 16.8161C29.4608 16.0517 30.7166 16.0517 31.4809 16.8161C32.3545 17.5805 32.3545 18.8362 31.5901 19.6006Z" fill="#09B285"/>
</svg>
            </div>
            <h2 className="test-result__success-title">Chúc mừng bạn đã hoàn thành</h2>
            <p className="test-result__success-message">
              Bài làm của bạn đã được gửi đến giáo viên. Bạn sẽ nhận được thông báo khi bài được chấm xong.
            </p>
            <button 
              className="test-result__continue-btn"
              onClick={() => navigate('/exams')}
            >
              Tiếp tục luyện tập
            </button>
          </div>
        </div>
      </div>
    );
  }

  const correctAnswers = result.correct_answers || 0;
  const totalQuestions = result.total_questions || 0;
  const incorrectAnswers = result.answered_questions - correctAnswers; // Sửa: chỉ tính câu trả lời sai, không tính câu bỏ qua
  const unansweredCount = totalQuestions - result.answered_questions;

  // Group answers by part and section
  const answersByPart = {};
  if (result.answers && Array.isArray(result.answers)) {
    result.answers.forEach((answer) => {
      const part = answer.part || 'Part 1';
      
      if (!answersByPart[part]) {
        answersByPart[part] = [];
      }
      answersByPart[part].push(answer);
    });
  }

  const filteredAnswers = (answers) => {
    if (activeTab === 'correct') {
      return answers.filter(a => a.is_correct);
    }
    if (activeTab === 'incorrect') {
      return answers.filter(a => !a.is_correct);
    }
    return answers;
  };

  const formatTime = (seconds) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
  };

  return (
    <div className="test-result">
      {/* Header giống TestLayout */}
      <div className="test-result__header">
        <button className="test-result__close" onClick={() => navigate(-1)}>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </button>
        <div className="test-result__header-info">
          <img src={logo} alt="OWL IELTS" className="test-result__logo" />
          <div className="test-result__header-text">
            <div className="test-result__header-label">Làm bài passage 1</div>
            <div className="test-result__header-name">
              {result.exam?.name || result.skill?.name || 'IELTS - Reading Test 1'}
            </div>
          </div>
        </div>
      </div>

      <div className="test-result__content">
        {/* Score Section */}
        <div className="test-result__score-section">
          <div className="test-result__mascot-card">
             <div className="test-result__mascot-text">
              Hôi kho bạn nhỉ? Mời bạn ôn luyện tập với OWL nhé !
            </div>
            <div className="test-result__mascot">
              <img src="/src/assets/images/source_image-Photoroom.png" alt="Congratulations" />
            </div>
           
          </div>
          
          <div className="test-result__score-card">
            <div className="test-result__score-header">
              <h2>Kết quả làm bài</h2>
              <div className="test-result__time">
                Thời gian làm bài 
                <div className="test-result__time-value">  {formatTime(result.time_spent)}</div>
              </div>
            </div>

            <div className="test-result__score-chart">
              <div className="test-result__score-circle">
                <svg viewBox="0 0 100 100">
                  <circle
                    cx="50"
                    cy="50"
                    r="40"
                    fill="none"
                    stroke="#E5E7EB"
                    strokeWidth="8"
                  />
                  <circle
                    cx="50"
                    cy="50"
                    r="40"
                    fill="none"
                    stroke="#10B981"
                    strokeWidth="8"
                    strokeDasharray={`${(correctAnswers / totalQuestions) * 251.2} 251.2`}
                    strokeLinecap="round"
                    transform="rotate(-90 50 50)"
                  />
                </svg>
                <div className="test-result__score-text">
                  <div className="test-result__score-number">{correctAnswers}/{totalQuestions} </div>
                  <div className="test-result__score-label">câu đúng</div>
                </div>
              </div>

              <div className="test-result__score-stats">
                <div className="test-result__stat test-result__stat--correct">
                  <div className="test-result__stat-dot"></div>
                  <span>Đúng</span>
                </div>
                <div className="test-result__stat test-result__stat--incorrect">
                  <div className="test-result__stat-dot"></div>
                  <span>Sai</span>
                </div>
                <div className="test-result__stat test-result__stat--unanswered">
                  <div className="test-result__stat-dot"></div>
                  <span>Bỏ qua</span>
                </div>
              </div>
            </div>

            <button className="test-result__review-btn" onClick={() => {
              // Scroll to answers section
              document.querySelector('.test-result__answers-section')?.scrollIntoView({ 
                behavior: 'smooth' 
              });
            }}>
              Giải thích chi tiết
            </button>
          </div>
        </div>

        {/* Answers Section */}
        <div className="test-result__answers-section">
          {Object.entries(answersByPart).map(([partName, answers]) => (
            <div key={partName} className="test-result__part">
              <h3 className="test-result__part-title">{partName}</h3>
              <div className="test-result__answers-grid">
                {answers.map((answer) => {
                  // Sử dụng question_number từ backend (đã được đánh số theo part)
                  const questionNumber = answer.question_number;
                  const isUnanswered = !answer.user_answer || answer.user_answer.trim() === '';
                  
                  // Helper function to strip HTML tags and get text content
                  const stripHtml = (html) => {
                    if (!html) return '';
                    const tmp = document.createElement('div');
                    tmp.innerHTML = html;
                    return tmp.textContent || tmp.innerText || '';
                  };
                  
                  const userAnswerText = stripHtml(answer.user_answer);
                  const correctAnswerText = stripHtml(answer.correct_answer);
                  
                  return (
                    <div 
                      key={answer.question_id} 
                      className={`test-result__answer-item ${answer.is_correct ? 'correct' : isUnanswered ? 'unanswered' : 'incorrect'}`}
                    >
                      <div className="test-result__answer-number">{questionNumber}</div>
                      <div className="test-result__answer-content">
                        <div className="test-result__answer-label">
                          <span className={`test-result__user-answer ${answer.is_correct ? 'correct' : isUnanswered ? 'unanswered' : 'incorrect'}`}>
                            {userAnswerText || '-'}
                          </span> | Đáp án: <span className="test-result__answer-value">{correctAnswerText || 'N/A'}</span>
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
