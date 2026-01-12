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
                  
                  return (
                    <div 
                      key={answer.question_id} 
                      className={`test-result__answer-item ${answer.is_correct ? 'correct' : isUnanswered ? 'unanswered' : 'incorrect'}`}
                    >
                      <div className="test-result__answer-number">{questionNumber}</div>
                      <div className="test-result__answer-content">
                        <div className="test-result__answer-label">
                          <span className={`test-result__user-answer ${answer.is_correct ? 'correct' : isUnanswered ? 'unanswered' : 'incorrect'}`}>
                            {answer.user_answer || '-'}
                          </span> | Đáp án: <span className="test-result__answer-value">{answer.correct_answer || 'N/A'}</span>
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
