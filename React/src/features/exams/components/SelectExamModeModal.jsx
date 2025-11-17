import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { getSkillById } from '../api/exams.api';
import './SelectExamModeModal.css';
import speakingIcon from '../../../assets/images/Speaking.png';

export default function SelectExamModeModal({ isOpen, onClose, skill }) {
  const navigate = useNavigate();
  const [skillDetails, setSkillDetails] = useState(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (isOpen) {
      document.body.style.overflow = 'hidden';
      if (skill?.id) {
        fetchSkillDetails();
      }
    } else {
      document.body.style.overflow = 'unset';
    }
    return () => {
      document.body.style.overflow = 'unset';
    };
  }, [isOpen, skill?.id]);

  const fetchSkillDetails = async () => {
    try {
      setLoading(true);
      const response = await getSkillById(skill.id, { with_sections: true });
      if (response.data.success) {
        setSkillDetails(response.data.data);
      }
    } catch (error) {
      console.error('Error fetching skill details:', error);
    } finally {
      setLoading(false);
    }
  };

  if (!isOpen || !skill) return null;

  const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
      onClose();
    }
  };

  const handleFullTestClick = () => {
    // TODO: Navigate to full test
    console.log('Start full test for skill:', skill.id);
    // navigate(`/exam/full/${skill.id}`);
  };

  const handleSectionClick = (section) => {
    // TODO: Navigate to section test
    console.log('Start section test:', section.id, 'for skill:', skill.id);
    // navigate(`/exam/section/${skill.id}/${section.id}`);
  };

  // Lấy sections từ skillDetails hoặc fallback về array rỗng
  const sections = skillDetails?.sections || [];

  // Tính tổng số câu hỏi từ tất cả sections
  const getTotalQuestions = () => {
    if (!sections.length) return 0;
    return sections.reduce((total, section) => {
      // Đếm số câu hỏi từ question groups
      const groupQuestions = section.question_groups?.reduce((sum, group) => {
        return sum + (group.questions?.length || 0);
      }, 0) || 0;

      // Đếm số câu hỏi trực tiếp (cho speaking/writing)
      const directQuestions = section.questions?.length || 0;

      return total + groupQuestions + directQuestions;
    }, 0);
  };

  // Tính số câu hỏi cho một section
  const getSectionQuestionCount = (section) => {
    const groupQuestions = section.question_groups?.reduce((sum, group) => {
      return sum + (group.questions?.length || 0);
    }, 0) || 0;

    const directQuestions = section.questions?.length || 0;

    return groupQuestions + directQuestions;
  };

  return (
    <div className="select-exam-modal-overlay" onClick={handleOverlayClick}>
      <div className="select-exam-modal">
        <button className="select-exam-modal__close" onClick={onClose}>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </button>

        <div className="select-exam-modal__header">
          <div className="select-exam-modal__title-wrapper">
            <img src={speakingIcon} alt={skill.name} className="select-exam-modal__title-icon" />
            <div className="select-exam-modal__title-content">
              <h2 className="select-exam-modal__title">{skill.name}</h2>
              <p className="select-exam-modal__subtitle">Hãy chọn chế độ bạn muốn làm</p>
            </div>
          </div>
        </div>

        <div className="select-exam-modal__content">
          {loading ? (
            <div className="select-exam-modal__loading">Đang tải dữ liệu...</div>
          ) : (
            <>
              {/* Full Test Mode */}
              <div className="select-exam-modal__section">
                <div className="select-exam-modal__section-header">
                  <div>
                  <h3 className="select-exam-modal__section-title">Mô phỏng thi thật</h3>
                  <p className="select-exam-modal__section-description">
                  Bạn sẽ làm 1 lần toàn bộ bài thi
                </p>
                </div>
                  <button
                    className="select-exam-modal__button"
                    onClick={handleFullTestClick}
                  >
                    Thi ngay
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                      <path d="M3.33334 8H12.6667M12.6667 8L8.00001 3.33333M12.6667 8L8.00001 12.6667" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                  </button>
                </div>
                
              </div>

              {/* Practice Mode */}
              {sections.length > 0 && (
                <div className="select-exam-modal__section select-exam-modal__section--practice">
                  <h3 className="select-exam-modal__section-title">Luyện tập</h3>
                  <p className="select-exam-modal__section-description">Chọn phần bạn muốn làm</p>

                  <div className="select-exam-modal__sections-list">
                    {sections.map((section, index) => (
                      <div key={section.id} className="select-exam-modal__section-item">
                        <div className="select-exam-modal__section-info">
                          <span className="select-exam-modal__section-name">
                            {section.title || `Section ${index + 1}`}
                            {getSectionQuestionCount(section) > 0 &&
                              ` | ${getSectionQuestionCount(section)} câu làm thử`
                            }
                          </span>
                        </div>
                        <button
                          className="select-exam-modal__button"
                          onClick={() => handleSectionClick(section)}
                        >
                          Thi ngay
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M3.33334 8H12.6667M12.6667 8L8.00001 3.33333M12.6667 8L8.00001 12.6667" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                          </svg>
                        </button>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  );
}
