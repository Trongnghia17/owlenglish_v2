import { useState, useEffect, useRef, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import logo from '@/assets/images/logo.png'
import closeIcon from '@/assets/images/nutx.svg'
import clockIcon from '@/assets/images/clock.svg'
import fileIcon from '@/assets/images/file.svg'
import fontIcon from '@/assets/images/aa.svg'
import fullscreenIcon from '@/assets/images/zoom.svg'
import doneImg from '@/assets/images/cudaxong.png'
import notDoneImg from '@/assets/images/cuchuaxong.png'
import './TestLayout.css';
import { getNotes, createNote, updateNote, deleteNote } from '../api/notes.api';

const hasAnswerValue = (answer) => {
  if (Array.isArray(answer)) {
    return answer.some((value) => String(value ?? '').trim() !== '');
  }

  return String(answer ?? '').trim() !== '';
};

export default function TestLayout({
  examData,
  skillData,
  sectionData,
  timeRemaining,
  setTimeRemaining,
  parts = [],
  currentPartTab,
  setCurrentPartTab,
  questionGroups = [],
  answers = {},
  onSubmit,
  children,
  showQuestionNumbers = true,
  fontSize: externalFontSize,
  onFontSizeChange
}) {
  const navigate = useNavigate();
  const [internalFontSize, setInternalFontSize] = useState('normal');
  const [showFontSizeModal, setShowFontSizeModal] = useState(false);
  const [showSubmitModal, setShowSubmitModal] = useState(false);
  const [isTestComplete, setIsTestComplete] = useState(false);
  const [showCongratulationModal, setShowCongratulationModal] = useState(false);

  // Notes state
  const [showNotesPanel, setShowNotesPanel] = useState(false);
  const [notes, setNotes] = useState([]);
  const [currentNote, setCurrentNote] = useState({ title: '', content: '' });
  const [editingNoteId, setEditingNoteId] = useState(null);
  const [showNotePopup, setShowNotePopup] = useState(false);
  const [selectedText, setSelectedText] = useState('');
  const [showNoteButton, setShowNoteButton] = useState(false);
  const [noteButtonPosition, setNoteButtonPosition] = useState({ x: 0, y: 0 });
  const [notesLoading, setNotesLoading] = useState(false);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
  const [noteToDelete, setNoteToDelete] = useState(null);

  // Use external fontSize if provided, otherwise use internal state
  const fontSize = externalFontSize !== undefined ? externalFontSize : internalFontSize;
  const setFontSize = onFontSizeChange || setInternalFontSize;

  // Determine test type and ID
  const testType = examData ? 'exam' : skillData ? 'skill' : sectionData ? 'section' : 'test';
  const testId = examData?.id || skillData?.id || sectionData?.id;

  // Load notes from API
  useEffect(() => {
    if (!testId) return;

    const loadNotes = async () => {
      setNotesLoading(true);
      try {
        const response = await getNotes(testType, testId);
        if (response.data.success) {
          setNotes(response.data.data);
        }
      } catch (error) {
        console.error('Error loading notes:', error);
        // Fallback to localStorage if API fails
        const savedNotes = localStorage.getItem(`test_notes_${testId}`);
        if (savedNotes) {
          try {
            setNotes(JSON.parse(savedNotes));
          } catch {
            setNotes([]);
          }
        }
      } finally {
        setNotesLoading(false);
      }
    };

    loadNotes();
  }, [testId, testType]);

  // Keep latest handleSubmit without forcing timer effect to re-run.
  const handleSubmitRef = useRef(null);

  // Format thời gian
  const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  // Xử lý nộp bài
  const handleSubmit = useCallback(() => {
    const totalQuestions = questionGroups.reduce((total, group) => {
      return total + (group.questions?.length || 0);
    }, 0);

    const answeredCount = Object.keys(answers).filter(key => hasAnswerValue(answers[key])).length;
    const isComplete = answeredCount === totalQuestions;
    setIsTestComplete(isComplete);
    setShowSubmitModal(true);
  }, [answers, questionGroups]);

  useEffect(() => {
    handleSubmitRef.current = handleSubmit;
  }, [handleSubmit]);

  // Timer countdown
  useEffect(() => {
    const timer = setInterval(() => {
      setTimeRemaining((prev) => {
        if (prev <= 0) {
          clearInterval(timer);
          handleSubmitRef.current?.();
          return 0;
        }
        return prev - 1;
      });
    }, 1000);

    return () => clearInterval(timer);
  }, [setTimeRemaining]);

  // Xác nhận nộp bài
  const confirmSubmit = () => {
    setShowSubmitModal(false);
    setShowCongratulationModal(true);
    if (onSubmit) {
      onSubmit();
    }
  };

  // Hủy nộp bài
  const cancelSubmit = () => {
    setShowSubmitModal(false);
  };

  const handleFullscreenToggle = () => {
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen?.();
      return;
    }

    document.exitFullscreen?.();
  };

  // Tiếp tục luyện tập
  const handleContinuePractice = () => {
    setShowCongratulationModal(false);
    navigate('/lich-su-lam-bai');
  };

  // Xử lý click câu hỏi
  const handleQuestionClick = (questionNumber) => {
    const group = questionGroups.find(g =>
      g.questions.some(q => q.number === questionNumber)
    );
    if (group) {
      if (group.part !== currentPartTab) {
        setCurrentPartTab(group.part);
      }
      setTimeout(() => {
        const groupElement = document.getElementById(`question-group-${group.id}`);
        if (groupElement) {
          groupElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }, 100);
    }
  };

  // Chuyển part
  const handlePartChange = (partNumber) => {
    setCurrentPartTab(partNumber);
  };

  // Get all questions in current part
  const currentPartGroups = questionGroups.filter(g => g.part === currentPartTab);
  const allQuestionsInPart = currentPartGroups.flatMap(g => g.questions);

  // Note handlers
  const handleEditNote = (note) => {
    setSelectedText(note.selected_text || '');
    setCurrentNote({ title: note.title || '', content: note.content || '' });
    setEditingNoteId(note.id);
    setShowNotePopup(true);
  };

  const handleDeleteClick = (note, event) => {
    event.stopPropagation();
    setNoteToDelete(note);
    setShowDeleteConfirm(true);
  };

  const confirmDelete = async () => {
    if (!noteToDelete) return;
    
    try {
      await deleteNote(noteToDelete.id);
      setNotes(notes.filter(note => note.id !== noteToDelete.id));
      setShowDeleteConfirm(false);
      setNoteToDelete(null);
    } catch (error) {
      console.error('Error deleting note:', error);
      alert('Không thể xóa ghi chú. Vui lòng thử lại.');
    }
  };

  const cancelDelete = () => {
    setShowDeleteConfirm(false);
    setNoteToDelete(null);
  };

  // Text selection handler
  useEffect(() => {
    const handleTextSelection = (e) => {
      setTimeout(() => {
        const selection = window.getSelection();

        // Bỏ qua nếu không có selection hoặc đang mở popup
        if (!selection || selection.isCollapsed || showNotePopup) {
          if (!e.target.closest('.test-layout__note-button-float')) {
            setShowNoteButton(false);
          }
          return;
        }

        const text = selection.toString().trim();

        // Kiểm tra text có trong content area
        const isInContent = e.target.closest('.test-layout__content');

        // CHO PHÉP tạo note ở passage và các phần khác trong content
        if (text.length > 0 && text.length <= 500 && isInContent) {
          try {
            const range = selection.getRangeAt(0);
            const rect = range.getBoundingClientRect();

            // Chỉ hiển thị nếu rect hợp lệ
            if (rect.width > 0 && rect.height > 0) {
              setSelectedText(text);
              setNoteButtonPosition({
                x: rect.left + rect.width / 2,
                y: rect.bottom + window.scrollY
              });
              setShowNoteButton(true);
            }
          } catch {
            setShowNoteButton(false);
          }
        } else if (!e.target.closest('.test-layout__note-button-float')) {
          setShowNoteButton(false);
        }
      }, 10);
    };

    const handleClickOutside = (e) => {
      if (!e.target.closest('.test-layout__note-button-float, .test-layout__note-popup')) {
        setShowNoteButton(false);
      }
    };

    document.addEventListener('mouseup', handleTextSelection);
    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      document.removeEventListener('mouseup', handleTextSelection);
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [showNotePopup]);

  const handleOpenNotePopup = () => {
    setShowNotePopup(true);
    setShowNoteButton(false);
  };

  const handleSaveFromPopup = async () => {
    if (!currentNote.content || !testId) return;

    try {
      if (editingNoteId) {
        // Cập nhật ghi chú đang edit
        const response = await updateNote(editingNoteId, {
          title: currentNote.title,
          content: currentNote.content,
          selected_text: selectedText,
        });

        if (response.data.success) {
          setNotes(notes.map(note =>
            note.id === editingNoteId
              ? response.data.data
              : note
          ));
        }
      } else {
        // Thêm ghi chú mới
        const response = await createNote({
          test_type: testType,
          test_id: testId,
          title: currentNote.title,
          content: currentNote.content,
          selected_text: selectedText,
        });

        if (response.data.success) {
          setNotes([response.data.data, ...notes]);
        }
      }
      handleClosePopup();
    } catch (error) {
      console.error('Error saving note:', error);
      alert('Không thể lưu ghi chú. Vui lòng thử lại.');
    }
  };

  const handleClosePopup = () => {
    setShowNotePopup(false);
    setCurrentNote({ title: '', content: '' });
    setSelectedText('');
    setEditingNoteId(null);
    window.getSelection().removeAllRanges();
  };

  return (
    <div className="test-layout" data-font-size={fontSize}>
      {/* Header */}
      <div className="test-layout__header" style={{ '--font-size': fontSize }}>
        <button className="test-layout__close" onClick={() => navigate(-1)}>
          <img src={closeIcon} alt="" />
        </button>
        <div className="test-layout__header-info">
          <img src={logo} alt="OWL IELTS" className="test-layout__logo" />
          <div className="test-layout__header-text">
            <div className="test-layout__header-label">
              Làm bài passage {currentPartTab || 1}
            </div>
            <div className="test-layout__header-name">
              {examData?.name || skillData?.name || sectionData?.title || 'Test'}
            </div>
          </div>
        </div>
        <div className="test-layout__header-right">
          {/* Nút Ghi chú */}
          <button
            className={`test-layout__note-toggle-btn ${showNotesPanel ? 'active' : ''}`}
            onClick={() => setShowNotesPanel(!showNotesPanel)}
            title="Danh sách ghi chú"
          >
            <img src={fileIcon} alt="" />
          </button>

          {/* Font Size Button */}
          <div className="test-layout__font-size-wrapper">
            <button
              className={`test-layout__font-size-button ${showFontSizeModal ? 'active' : ''}`}
              onClick={() => setShowFontSizeModal(!showFontSizeModal)}
              title="Cỡ chữ"
            >
              <img src={fontIcon} alt="" />
            </button>

            {/* Dropdown Menu */}
            {showFontSizeModal && (
              <div className="test-layout__font-dropdown">
                <h3 className="test-layout__font-dropdown-title">Cỡ chữ</h3>
                <p className="test-layout__font-dropdown-subtitle">Chọn cỡ chữ phù hợp cho việc đọc</p>

                <div className="test-layout__font-options">
                  <button
                    className={`test-layout__font-option ${fontSize === 'normal' ? 'active' : ''}`}
                    onClick={() => {
                      setFontSize('normal');
                      setShowFontSizeModal(false);
                    }}
                  >
                    Bình thường
                    {fontSize === 'normal' && (
                      <svg className="test-layout__check-icon" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M13.3337 4L6.00033 11.3333L2.66699 8" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                      </svg>
                    )}
                  </button>

                  <button
                    className={`test-layout__font-option ${fontSize === 'large' ? 'active' : ''}`}
                    onClick={() => {
                      setFontSize('large');
                      setShowFontSizeModal(false);
                    }}
                  >
                    Lớn
                    {fontSize === 'large' && (
                      <svg className="test-layout__check-icon" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M13.3337 4L6.00033 11.3333L2.66699 8" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                      </svg>
                    )}
                  </button>

                  <button
                    className={`test-layout__font-option ${fontSize === 'extra-large' ? 'active' : ''}`}
                    onClick={() => {
                      setFontSize('extra-large');
                      setShowFontSizeModal(false);
                    }}
                  >
                    Rất lớn
                    {fontSize === 'extra-large' && (
                      <svg className="test-layout__check-icon" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M13.3337 4L6.00033 11.3333L2.66699 8" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                      </svg>
                    )}
                  </button>
                </div>

                <button
                  className="test-layout__font-dropdown-close"
                  onClick={() => setShowFontSizeModal(false)}
                >
                  Đóng
                </button>
              </div>
            )}
          </div>

          <button
            className="test-layout__fullscreen-button"
            onClick={handleFullscreenToggle}
            title="Toàn màn hình"
          >
            <img src={fullscreenIcon} alt="" />
          </button>

          <div className="test-layout__timer">
            <img src={clockIcon} alt="" />
            <span>{formatTime(timeRemaining)}</span>
          </div>
          {onSubmit && (
            <button className="test-layout__submit-button-header" onClick={handleSubmit}>
              Nộp bài
            </button>
          )}
        </div>
      </div>

      {/* Main Content - children will be rendered here */}
      <div className={`test-layout__content test-layout__content--${fontSize}`}>
        {children}
      </div>

      {/* Footer - Part Tabs và Question Numbers */}
      {showQuestionNumbers && (
        <div className="test-layout__footer">
          <div>
            {/* Part Tabs */}
            {parts.length > 1 && (
              <div className="test-layout__part-tabs">
                {parts.map((part) => {
                  const partGroups = questionGroups.filter(g => g.part === part.part);
                  const partQuestions = partGroups.flatMap(g => g.questions || []);
                  const questionCount = partQuestions.length;

                  return (
                    <button
                      key={part.part}
                      className={`test-layout__part-tab ${currentPartTab === part.part ? 'active' : ''}`}
                      onClick={() => handlePartChange(part.part)}
                    >
                      Part {part.part} - 1/{questionCount}
                    </button>
                  );
                })}
              </div>
            )}

            {/* Question Numbers Grid */}
            <div className="test-layout__question-numbers">
              {allQuestionsInPart.map((q, index) => (
                <button
                  key={q.id}
                  className={`test-layout__question-number-item ${hasAnswerValue(answers[q.id]) ? 'answered' : ''
                    }`}
                  onClick={() => handleQuestionClick(q.number || index + 1)}
                >
                  {q.number || index + 1}
                </button>
              ))}
            </div>
          </div>
        </div>
      )}

      {/* Submit Modal */}
      {showSubmitModal && (
        <div className="test-layout__modal-overlay" onClick={cancelSubmit}>
          <div className="test-layout__modal-content test-layout__submit-modal" onClick={(e) => e.stopPropagation()}>
            <div className="test-layout__submit-modal-icon">
              <img
                src={isTestComplete ? doneImg : notDoneImg}
                alt="Owl"
                className="test-layout__submit-modal-owl"
              />
            </div>

            <div className="test-layout__submit-modal-body">
              {isTestComplete ? (
                <>
                  <h3 className="test-layout__submit-modal-title">Bạn đã hoàn thành bài thi.</h3>
                  <p className="test-layout__submit-modal-text">Bạn có cần kiểm tra lại không?</p>
                </>
              ) : (
                <>
                  <h3 className="test-layout__submit-modal-title">Bạn vẫn chưa hoàn thành</h3>
                  <p className="test-layout__submit-modal-text">Bạn có chắc muốn nộp bài không?</p>
                </>
              )}
            </div>

            <div className="test-layout__submit-modal-actions">
              {isTestComplete ? (
                <>
                  <button
                    className="test-layout__submit-modal-button test-layout__submit-modal-button--primary"
                    onClick={confirmSubmit}
                  >
                    Nộp bài
                  </button>
                  <button
                    className="test-layout__submit-modal-button test-layout__submit-modal-button--secondary"
                    onClick={cancelSubmit}
                  >
                    Kiểm tra lại
                  </button>
                </>
              ) : (
                <>
                  <button
                    className="test-layout__submit-modal-button test-layout__submit-modal-button--primary"
                    onClick={cancelSubmit}
                  >
                    Tiếp tục làm bài
                  </button>
                  <button
                    className="test-layout__submit-modal-button test-layout__submit-modal-button--secondary"
                    onClick={confirmSubmit}
                  >
                    Nộp bài
                  </button>
                </>
              )}
            </div>
          </div>
        </div>
      )}

      {/* Congratulation Modal */}
      {showCongratulationModal && (
        <div className="test-layout__modal-overlay">
          <div className="test-layout__modal-content test-layout__submit-modal" onClick={(e) => e.stopPropagation()}>
            <div className="test-layout__submit-modal-icon">
              <img
                src="/src/assets/images/cuchucmung.png"
                alt="Owl"
                className="test-layout__submit-modal-owl"
              />
            </div>

            <div className="test-layout__submit-modal-body">
              <h3 className="test-layout__submit-modal-title">Chúc mừng bạn đã hoàn thành</h3>
            </div>

            <div className="test-layout__submit-modal-actions">
              <button
                className="test-layout__submit-modal-button test-layout__submit-modal-button--primary"
                onClick={handleContinuePractice}
              >
                Tiếp tục luyện tập
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Notes Sidebar */}
      {showNotesPanel && (
        <div className="test-layout__notes-sidebar">
          <div className="test-layout__notes-sidebar-header">
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="17" viewBox="0 0 16 17" fill="none">
                <path d="M4.16667 6.66667H5C5.22101 6.66667 5.43297 6.57887 5.58926 6.42259C5.74554 6.26631 5.83333 6.05435 5.83333 5.83333C5.83333 5.61232 5.74554 5.40036 5.58926 5.24408C5.43297 5.0878 5.22101 5 5 5H4.16667C3.94565 5 3.73369 5.0878 3.57741 5.24408C3.42113 5.40036 3.33333 5.61232 3.33333 5.83333C3.33333 6.05435 3.42113 6.26631 3.57741 6.42259C3.73369 6.57887 3.94565 6.66667 4.16667 6.66667ZM7.5 11.6667H4.16667C3.94565 11.6667 3.73369 11.7545 3.57741 11.9107C3.42113 12.067 3.33333 12.279 3.33333 12.5C3.33333 12.721 3.42113 12.933 3.57741 13.0893C3.73369 13.2455 3.94565 13.3333 4.16667 13.3333H7.5C7.72101 13.3333 7.93297 13.2455 8.08926 13.0893C8.24554 12.933 8.33333 12.721 8.33333 12.5C8.33333 12.279 8.24554 12.067 8.08926 11.9107C7.93297 11.7545 7.72101 11.6667 7.5 11.6667ZM7.5 8.33333H4.16667C3.94565 8.33333 3.73369 8.42113 3.57741 8.57741C3.42113 8.73369 3.33333 8.94565 3.33333 9.16667C3.33333 9.38768 3.42113 9.59964 3.57741 9.75592C3.73369 9.9122 3.94565 10 4.16667 10H7.5C7.72101 10 7.93297 9.9122 8.08926 9.75592C8.24554 9.59964 8.33333 9.38768 8.33333 9.16667C8.33333 8.94565 8.24554 8.73369 8.08926 8.57741C7.93297 8.42113 7.72101 8.33333 7.5 8.33333ZM13.2667 6.15C13.3305 5.99824 13.3479 5.83098 13.3168 5.66932C13.2856 5.50766 13.2073 5.35885 13.0917 5.24167L8.09167 0.241667C8.02278 0.176847 7.94402 0.123401 7.85833 0.0833333C7.83346 0.0798001 7.80821 0.0798001 7.78333 0.0833333L7.55 0H2.5C1.83696 0 1.20107 0.263392 0.732233 0.732233C0.263392 1.20107 0 1.83696 0 2.5V14.1667C0 14.8297 0.263392 15.4656 0.732233 15.9344C1.20107 16.4033 1.83696 16.6667 2.5 16.6667H7.5C7.72101 16.6667 7.93297 16.5789 8.08926 16.4226C8.24554 16.2663 8.33333 16.0543 8.33333 15.8333C8.33333 15.6123 8.24554 15.4004 8.08926 15.2441C7.93297 15.0878 7.72101 15 7.5 15H2.5C2.27899 15 2.06702 14.9122 1.91074 14.7559C1.75446 14.5996 1.66667 14.3877 1.66667 14.1667V2.5C1.66667 2.27899 1.75446 2.06702 1.91074 1.91074C2.06702 1.75446 2.27899 1.66667 2.5 1.66667H6.66667V4.16667C6.66667 4.82971 6.93006 5.46559 7.3989 5.93443C7.86774 6.40327 8.50362 6.66667 9.16667 6.66667H12.5C12.6645 6.66585 12.8251 6.61634 12.9616 6.5244C13.098 6.43245 13.2041 6.30218 13.2667 6.15ZM9.16667 5C8.94565 5 8.73369 4.9122 8.57741 4.75592C8.42113 4.59964 8.33333 4.38768 8.33333 4.16667V2.84167L10.4917 5H9.16667ZM15 8.33333H10.8333C10.6123 8.33333 10.4004 8.42113 10.2441 8.57741C10.0878 8.73369 10 8.94565 10 9.16667V15.8333C10.0004 15.9841 10.0417 16.1319 10.1195 16.261C10.1972 16.3902 10.3086 16.4958 10.4417 16.5667C10.572 16.6336 10.7176 16.665 10.8639 16.6576C11.0102 16.6503 11.152 16.6046 11.275 16.525L12.9167 15.4417L14.5833 16.525C14.7078 16.597 14.8488 16.6356 14.9926 16.6369C15.1364 16.6383 15.2782 16.6024 15.404 16.5328C15.5298 16.4631 15.6355 16.3621 15.7107 16.2396C15.786 16.117 15.8282 15.9771 15.8333 15.8333V9.16667C15.8333 8.94565 15.7455 8.73369 15.5893 8.57741C15.433 8.42113 15.221 8.33333 15 8.33333ZM14.1667 14.2667L13.3833 13.7417C13.2455 13.6485 13.083 13.5987 12.9167 13.5987C12.7503 13.5987 12.5878 13.6485 12.45 13.7417L11.6667 14.2667V10H14.1667V14.2667Z" fill="#045CCE" />
              </svg>
              <h3>Danh sách ghi chú</h3>
            </div>
            <button
              className="test-layout__notes-sidebar-close"
              onClick={() => setShowNotesPanel(false)}
            >
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
              </svg>
            </button>
          </div>

          <div className="test-layout__notes-sidebar-body">
            {notesLoading ? (
              <div className="test-layout__notes-sidebar-empty">
                <p>Đang tải ghi chú...</p>
              </div>
            ) : notes.length === 0 ? (
              <div className="test-layout__notes-sidebar-empty">
                <p>Chưa có ghi chú nào. Nhấn vào văn bản để thêm ghi chú.</p>
              </div>
            ) : (
              <div className="test-layout__notes-sidebar-list">
                {notes.map((note, index) => (
                  <div
                    key={note.id}
                    className="test-layout__notes-sidebar-item"
                    onClick={() => handleEditNote(note)}
                    style={{ cursor: 'pointer' }}
                  >
                    <div className="test-layout__notes-item-header">
                      <div className="test-layout__notes-item-info" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', width: '100%' }}>
                        <h4>Note {index + 1}</h4>
                        <button
                          className="test-layout__notes-item-delete-icon"
                          onClick={(e) => handleDeleteClick(note, e)}
                          title="Xóa ghi chú"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M8.33333 15.0003C8.55435 15.0003 8.76631 14.9125 8.92259 14.7562C9.07887 14.6 9.16667 14.388 9.16667 14.167V9.16699C9.16667 8.94598 9.07887 8.73402 8.92259 8.57774C8.76631 8.42146 8.55435 8.33366 8.33333 8.33366C8.11232 8.33366 7.90036 8.42146 7.74408 8.57774C7.5878 8.73402 7.5 8.94598 7.5 9.16699V14.167C7.5 14.388 7.5878 14.6 7.74408 14.7562C7.90036 14.9125 8.11232 15.0003 8.33333 15.0003ZM16.6667 5.00033H13.3333V4.16699C13.3333 3.50395 13.0699 2.86807 12.6011 2.39923C12.1323 1.93038 11.4964 1.66699 10.8333 1.66699H9.16667C8.50363 1.66699 7.86774 1.93038 7.3989 2.39923C6.93006 2.86807 6.66667 3.50395 6.66667 4.16699V5.00033H3.33333C3.11232 5.00033 2.90036 5.08812 2.74408 5.2444C2.5878 5.40068 2.5 5.61264 2.5 5.83366C2.5 6.05467 2.5878 6.26663 2.74408 6.42291C2.90036 6.57919 3.11232 6.66699 3.33333 6.66699H4.16667V15.8337C4.16667 16.4967 4.43006 17.1326 4.8989 17.6014C5.36774 18.0703 6.00363 18.3337 6.66667 18.3337H13.3333C13.9964 18.3337 14.6323 18.0703 15.1011 17.6014C15.5699 17.1326 15.8333 16.4967 15.8333 15.8337V6.66699H16.6667C16.8877 6.66699 17.0996 6.57919 17.2559 6.42291C17.4122 6.26663 17.5 6.05467 17.5 5.83366C17.5 5.61264 17.4122 5.40068 17.2559 5.2444C17.0996 5.08812 16.8877 5.00033 16.6667 5.00033ZM8.33333 4.16699C8.33333 3.94598 8.42113 3.73402 8.57741 3.57774C8.73369 3.42146 8.94565 3.33366 9.16667 3.33366H10.8333C11.0543 3.33366 11.2663 3.42146 11.4226 3.57774C11.5789 3.73402 11.6667 3.94598 11.6667 4.16699V5.00033H8.33333V4.16699ZM14.1667 15.8337C14.1667 16.0547 14.0789 16.2666 13.9226 16.4229C13.7663 16.5792 13.5543 16.667 13.3333 16.667H6.66667C6.44565 16.667 6.23369 16.5792 6.07741 16.4229C5.92113 16.2666 5.83333 16.0547 5.83333 15.8337V6.66699H14.1667V15.8337ZM11.6667 15.0003C11.8877 15.0003 12.0996 14.9125 12.2559 14.7562C12.4122 14.6 12.5 14.388 12.5 14.167V9.16699C12.5 8.94598 12.4122 8.73402 12.2559 8.57774C12.0996 8.42146 11.8877 8.33366 11.6667 8.33366C11.4457 8.33366 11.2337 8.42146 11.0774 8.57774C10.9211 8.73402 10.8333 8.94598 10.8333 9.16699V14.167C10.8333 14.388 10.9211 14.6 11.0774 14.7562C11.2337 14.9125 11.4457 15.0003 11.6667 15.0003Z" fill="#888888" />
                          </svg>
                        </button>
                      </div>
                    </div>
                    <div className="test-layout__notes-item-body">
                      {note.selected_text && (
                        <h5 className="test-layout__notes-item-title">{note.selected_text}</h5>
                      )}
                      <p className="test-layout__notes-item-category">{note.content}</p>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      )}

      {/* Note Button Float - Hiển thị khi select text */}
      {showNoteButton && (
        <button
          className="test-layout__note-button-float"
          style={{
            position: 'absolute',
            left: `${noteButtonPosition.x}px`,
            top: `${noteButtonPosition.y + 5}px`,
            transform: 'translateX(-50%)'
          }}
          onClick={handleOpenNotePopup}
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="17" viewBox="0 0 16 17" fill="none">
            <path d="M4.16667 6.66667H5C5.22101 6.66667 5.43297 6.57887 5.58926 6.42259C5.74554 6.26631 5.83333 6.05435 5.83333 5.83333C5.83333 5.61232 5.74554 5.40036 5.58926 5.24408C5.43297 5.0878 5.22101 5 5 5H4.16667C3.94565 5 3.73369 5.0878 3.57741 5.24408C3.42113 5.40036 3.33333 5.61232 3.33333 5.83333C3.33333 6.05435 3.42113 6.26631 3.57741 6.42259C3.73369 6.57887 3.94565 6.66667 4.16667 6.66667ZM7.5 11.6667H4.16667C3.94565 11.6667 3.73369 11.7545 3.57741 11.9107C3.42113 12.067 3.33333 12.279 3.33333 12.5C3.33333 12.721 3.42113 12.933 3.57741 13.0893C3.73369 13.2455 3.94565 13.3333 4.16667 13.3333H7.5C7.72101 13.3333 7.93297 13.2455 8.08926 13.0893C8.24554 12.933 8.33333 12.721 8.33333 12.5C8.33333 12.279 8.24554 12.067 8.08926 11.9107C7.93297 11.7545 7.72101 11.6667 7.5 11.6667ZM7.5 8.33333H4.16667C3.94565 8.33333 3.73369 8.42113 3.57741 8.57741C3.42113 8.73369 3.33333 8.94565 3.33333 9.16667C3.33333 9.38768 3.42113 9.59964 3.57741 9.75592C3.73369 9.9122 3.94565 10 4.16667 10H7.5C7.72101 10 7.93297 9.9122 8.08926 9.75592C8.24554 9.59964 8.33333 9.38768 8.33333 9.16667C8.33333 8.94565 8.24554 8.73369 8.08926 8.57741C7.93297 8.42113 7.72101 8.33333 7.5 8.33333ZM13.2667 6.15C13.3305 5.99824 13.3479 5.83098 13.3168 5.66932C13.2856 5.50766 13.2073 5.35885 13.0917 5.24167L8.09167 0.241667C8.02278 0.176847 7.94402 0.123401 7.85833 0.0833333C7.83346 0.0798001 7.80821 0.0798001 7.78333 0.0833333L7.55 0H2.5C1.83696 0 1.20107 0.263392 0.732233 0.732233C0.263392 1.20107 0 1.83696 0 2.5V14.1667C0 14.8297 0.263392 15.4656 0.732233 15.9344C1.20107 16.4033 1.83696 16.6667 2.5 16.6667H7.5C7.72101 16.6667 7.93297 16.5789 8.08926 16.4226C8.24554 16.2663 8.33333 16.0543 8.33333 15.8333C8.33333 15.6123 8.24554 15.4004 8.08926 15.2441C7.93297 15.0878 7.72101 15 7.5 15H2.5C2.27899 15 2.06702 14.9122 1.91074 14.7559C1.75446 14.5996 1.66667 14.3877 1.66667 14.1667V2.5C1.66667 2.27899 1.75446 2.06702 1.91074 1.91074C2.06702 1.75446 2.27899 1.66667 2.5 1.66667H6.66667V4.16667C6.66667 4.82971 6.93006 5.46559 7.3989 5.93443C7.86774 6.40327 8.50362 6.66667 9.16667 6.66667H12.5C12.6645 6.66585 12.8251 6.61634 12.9616 6.5244C13.098 6.43245 13.2041 6.30218 13.2667 6.15ZM9.16667 5C8.94565 5 8.73369 4.9122 8.57741 4.75592C8.42113 4.59964 8.33333 4.38768 8.33333 4.16667V2.84167L10.4917 5H9.16667ZM15 8.33333H10.8333C10.6123 8.33333 10.4004 8.42113 10.2441 8.57741C10.0878 8.73369 10 8.94565 10 9.16667V15.8333C10.0004 15.9841 10.0417 16.1319 10.1195 16.261C10.1972 16.3902 10.3086 16.4958 10.4417 16.5667C10.572 16.6336 10.7176 16.665 10.8639 16.6576C11.0102 16.6503 11.152 16.6046 11.275 16.525L12.9167 15.4417L14.5833 16.525C14.7078 16.597 14.8488 16.6356 14.9926 16.6369C15.1364 16.6383 15.2782 16.6024 15.404 16.5328C15.5298 16.4631 15.6355 16.3621 15.7107 16.2396C15.786 16.117 15.8282 15.9771 15.8333 15.8333V9.16667C15.8333 8.94565 15.7455 8.73369 15.5893 8.57741C15.433 8.42113 15.221 8.33333 15 8.33333ZM14.1667 14.2667L13.3833 13.7417C13.2455 13.6485 13.083 13.5987 12.9167 13.5987C12.7503 13.5987 12.5878 13.6485 12.45 13.7417L11.6667 14.2667V10H14.1667V14.2667Z" fill="#045CCE" />
          </svg>
          Note
        </button>
      )}

      {/* Note Popup - Hiển thị ở giữa màn hình */}
      {showNotePopup && (
        <>
          <div className="test-layout__note-popup-overlay" onClick={handleClosePopup}></div>
          <div className="test-layout__note-popup">
            <div className="test-layout__note-popup-header">
              <h4>{editingNoteId ? 'Sửa ghi chú' : 'Thêm ghi chú'}</h4>

            </div>

            <div className="test-layout__note-popup-body">
              <div className="test-layout__note-popup-field">
                <label>Từ chọn</label>
                <div className="test-layout__note-popup-selected-text">{selectedText}</div>
              </div>

              <div className="test-layout__note-popup-field">

                <textarea
                  className="test-layout__note-popup-textarea"
                  placeholder="Nhập nội dung ghi chú..."
                  rows="4"
                  value={currentNote.content}
                  onChange={(e) => setCurrentNote({ ...currentNote, content: e.target.value })}
                  autoFocus
                />
              </div>
            </div>

            <div className="test-layout__note-popup-actions">
              <button
                className="test-layout__note-popup-btn test-layout__note-popup-btn--secondary"
                onClick={handleClosePopup}
              >
                Đóng
              </button>
              <button
                className="test-layout__note-popup-btn test-layout__note-popup-btn--primary"
                onClick={handleSaveFromPopup}
              >
                {editingNoteId ? 'Cập nhật' : 'Lưu'}
              </button>
            </div>
          </div>
        </>
      )}

      {/* Delete Confirmation Modal */}
      {showDeleteConfirm && (
        <div className="test-layout__modal-overlay" onClick={cancelDelete}>
          <div 
            className="test-layout__modal-content test-layout__submit-modal" 
            onClick={(e) => e.stopPropagation()}
            style={{
              display: 'flex',
              padding: '24px',
              flexDirection: 'column',
              alignItems: 'center',
              borderRadius: '5px',
              border: '1px solid #E7E7E7',
              background: '#FFF',
              boxShadow: '0 4px 20px 0 rgba(0, 0, 0, 0.10)'
            }}
          >
            <div className="test-layout__submit-modal-body">
              <h3 className="test-layout__submit-modal-title">Xóa note</h3>
              <p className="test-layout__submit-modal-text">
                Bạn có chắc chắn muốn xóa Note {notes.findIndex(n => n.id === noteToDelete?.id) + 1}
              </p>
            </div>

            <div className="test-layout__submit-modal-actions" style={{ flexDirection: 'row', gap: '12px' }}>
              <button
                className="test-layout__submit-modal-button test-layout__submit-modal-button--secondary"
                onClick={cancelDelete}
                style={{ flex: 1 }}
              >
                Hủy
              </button>
              <button
                className="test-layout__submit-modal-button test-layout__submit-modal-button--primary"
                onClick={confirmDelete}
                style={{ flex: 1, backgroundColor: '#EF4444' }}
              >
                Xóa
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
