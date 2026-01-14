import { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import logo from '@/assets/images/logo.png'
import './TestLayout.css';
import { getNotes, createNote, updateNote, deleteNote } from '../api/notes.api';

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
          } catch (e) {
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
  const handleSubmit = () => {
    const totalQuestions = questionGroups.reduce((total, group) => {
      return total + (group.questions?.length || 0);
    }, 0);

    const answeredCount = Object.keys(answers).filter(key => answers[key] && answers[key].trim() !== '').length;
    const isComplete = answeredCount === totalQuestions;
    setIsTestComplete(isComplete);
    setShowSubmitModal(true);
  };

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
          } catch (error) {
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
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </button>
        <div className="test-layout__header-info">
          <img src={logo} alt="OWL IELTS" className="test-layout__logo" />
          <div className="test-layout__header-text">
            <div className="test-layout__header-label">
              Làm bài {parts.length > 1 ? `passage ${currentPartTab}` : ''}
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
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="17" viewBox="0 0 16 17" fill="none">
              <path
                d="M4.16667 6.66667H5C5.22101 6.66667 5.43297 6.57887 5.58926 6.42259C5.74554 6.26631 5.83333 6.05435 5.83333 5.83333C5.83333 5.61232 5.74554 5.40036 5.58926 5.24408C5.43297 5.0878 5.22101 5 5 5H4.16667C3.94565 5 3.73369 5.0878 3.57741 5.24408C3.42113 5.40036 3.33333 5.61232 3.33333 5.83333C3.33333 6.05435 3.42113 6.26631 3.57741 6.42259C3.73369 6.57887 3.94565 6.66667 4.16667 6.66667ZM7.5 11.6667H4.16667C3.94565 11.6667 3.73369 11.7545 3.57741 11.9107C3.42113 12.067 3.33333 12.279 3.33333 12.5C3.33333 12.721 3.42113 12.933 3.57741 13.0893C3.73369 13.2455 3.94565 13.3333 4.16667 13.3333H7.5C7.72101 13.3333 7.93297 13.2455 8.08926 13.0893C8.24554 12.933 8.33333 12.721 8.33333 12.5C8.33333 12.279 8.24554 12.067 8.08926 11.9107C7.93297 11.7545 7.72101 11.6667 7.5 11.6667ZM7.5 8.33333H4.16667C3.94565 8.33333 3.73369 8.42113 3.57741 8.57741C3.42113 8.73369 3.33333 8.94565 3.33333 9.16667C3.33333 9.38768 3.42113 9.59964 3.57741 9.75592C3.73369 9.9122 3.94565 10 4.16667 10H7.5C7.72101 10 7.93297 9.9122 8.08926 9.75592C8.24554 9.59964 8.33333 9.38768 8.33333 9.16667C8.33333 8.94565 8.24554 8.73369 8.08926 8.57741C7.93297 8.42113 7.72101 8.33333 7.5 8.33333ZM13.2667 6.15C13.3305 5.99824 13.3479 5.83098 13.3168 5.66932C13.2856 5.50766 13.2073 5.35885 13.0917 5.24167L8.09167 0.241667C8.02278 0.176847 7.94402 0.123401 7.85833 0.0833333C7.83346 0.0798001 7.80821 0.0798001 7.78333 0.0833333L7.55 0H2.5C1.83696 0 1.20107 0.263392 0.732233 0.732233C0.263392 1.20107 0 1.83696 0 2.5V14.1667C0 14.8297 0.263392 15.4656 0.732233 15.9344C1.20107 16.4033 1.83696 16.6667 2.5 16.6667H7.5C7.72101 16.6667 7.93297 16.5789 8.08926 16.4226C8.24554 16.2663 8.33333 16.0543 8.33333 15.8333C8.33333 15.6123 8.24554 15.4004 8.08926 15.2441C7.93297 15.0878 7.72101 15 7.5 15H2.5C2.27899 15 2.06702 14.9122 1.91074 14.7559C1.75446 14.5996 1.66667 14.3877 1.66667 14.1667V2.5C1.66667 2.27899 1.75446 2.06702 1.91074 1.91074C2.06702 1.75446 2.27899 1.66667 2.5 1.66667H6.66667V4.16667C6.66667 4.82971 6.93006 5.46559 7.3989 5.93443C7.86774 6.40327 8.50362 6.66667 9.16667 6.66667H12.5C12.6645 6.66585 12.8251 6.61634 12.9616 6.5244C13.098 6.43245 13.2041 6.30218 13.2667 6.15ZM9.16667 5C8.94565 5 8.73369 4.9122 8.57741 4.75592C8.42113 4.59964 8.33333 4.38768 8.33333 4.16667V2.84167L10.4917 5H9.16667ZM15 8.33333H10.8333C10.6123 8.33333 10.4004 8.42113 10.2441 8.57741C10.0878 8.73369 10 8.94565 10 9.16667V15.8333C10.0004 15.9841 10.0417 16.1319 10.1195 16.261C10.1972 16.3902 10.3086 16.4958 10.4417 16.5667C10.572 16.6336 10.7176 16.665 10.8639 16.6576C11.0102 16.6503 11.152 16.6046 11.275 16.525L12.9167 15.4417L14.5833 16.525C14.7078 16.597 14.8488 16.6356 14.9926 16.6369C15.1364 16.6383 15.2782 16.6024 15.404 16.5328C15.5298 16.4631 15.6355 16.3621 15.7107 16.2396C15.786 16.117 15.8282 15.9771 15.8333 15.8333V9.16667C15.8333 8.94565 15.7455 8.73369 15.5893 8.57741C15.433 8.42113 15.221 8.33333 15 8.33333ZM14.1667 14.2667L13.3833 13.7417C13.2455 13.6485 13.083 13.5987 12.9167 13.5987C12.7503 13.5987 12.5878 13.6485 12.45 13.7417L11.6667 14.2667V10H14.1667V14.2667Z"
                fill="#4F4F4F" />
            </svg>

          </button>

          {/* Font Size Button */}
          <div className="test-layout__font-size-wrapper">
            <button
              className={`test-layout__font-size-button ${showFontSizeModal ? 'active' : ''}`}
              onClick={() => setShowFontSizeModal(!showFontSizeModal)}
              title="Cỡ chữ"
            >
              <span className="test-layout__font-size-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path
                    d="M7.74102 11.0753L3.33268 15.492V14.167C3.33268 13.946 3.24488 13.734 3.0886 13.5777C2.93232 13.4215 2.72036 13.3337 2.49935 13.3337C2.27834 13.3337 2.06637 13.4215 1.91009 13.5777C1.75381 13.734 1.66602 13.946 1.66602 14.167V17.5003C1.66733 17.6092 1.68998 17.7168 1.73268 17.817C1.81724 18.0206 1.97906 18.1824 2.18268 18.267C2.28287 18.3097 2.39045 18.3323 2.49935 18.3337H5.83268C6.0537 18.3337 6.26566 18.2459 6.42194 18.0896C6.57822 17.9333 6.66602 17.7213 6.66602 17.5003C6.66602 17.2793 6.57822 17.0673 6.42194 16.9111C6.26566 16.7548 6.0537 16.667 5.83268 16.667H4.50768L8.92435 12.2587C9.08127 12.1017 9.16943 11.8889 9.16943 11.667C9.16943 11.4451 9.08127 11.2322 8.92435 11.0753C8.76743 10.9184 8.5546 10.8302 8.33268 10.8302C8.11076 10.8302 7.89794 10.9184 7.74102 11.0753ZM4.50768 3.33366H5.83268C6.0537 3.33366 6.26566 3.24586 6.42194 3.08958C6.57822 2.9333 6.66602 2.72134 6.66602 2.50033C6.66602 2.27931 6.57822 2.06735 6.42194 1.91107C6.26566 1.75479 6.0537 1.66699 5.83268 1.66699H2.49935C2.39045 1.66831 2.28287 1.69096 2.18268 1.73366C1.97906 1.81822 1.81724 1.98003 1.73268 2.18366C1.68998 2.28384 1.66733 2.39143 1.66602 2.50033V5.83366C1.66602 6.05467 1.75381 6.26663 1.91009 6.42291C2.06637 6.57919 2.27834 6.66699 2.49935 6.66699C2.72036 6.66699 2.93232 6.57919 3.0886 6.42291C3.24488 6.26663 3.33268 6.05467 3.33268 5.83366V4.50866L7.74102 8.92533C7.81848 9.00343 7.91065 9.06543 8.0122 9.10773C8.11375 9.15004 8.22267 9.17182 8.33268 9.17182C8.44269 9.17182 8.55161 9.15004 8.65316 9.10773C8.75471 9.06543 8.84688 9.00343 8.92435 8.92533C9.00246 8.84786 9.06445 8.75569 9.10676 8.65414C9.14907 8.55259 9.17085 8.44367 9.17085 8.33366C9.17085 8.22365 9.14907 8.11473 9.10676 8.01318C9.06445 7.91163 9.00246 7.81946 8.92435 7.74199L4.50768 3.33366ZM17.4993 13.3337C17.2783 13.3337 17.0664 13.4215 16.9101 13.5777C16.7538 13.734 16.666 13.946 16.666 14.167V15.492L12.2577 11.0753C12.1008 10.9184 11.8879 10.8302 11.666 10.8302C11.4441 10.8302 11.2313 10.9184 11.0743 11.0753C10.9174 11.2322 10.8293 11.4451 10.8293 11.667C10.8293 11.8889 10.9174 12.1017 11.0743 12.2587L15.491 16.667H14.166C13.945 16.667 13.733 16.7548 13.5768 16.9111C13.4205 17.0673 13.3327 17.2793 13.3327 17.5003C13.3327 17.7213 13.4205 17.9333 13.5768 18.0896C13.733 18.2459 13.945 18.3337 14.166 18.3337H17.4993C17.6082 18.3323 17.7158 18.3097 17.816 18.267C18.0196 18.1824 18.1815 18.0206 18.266 17.817C18.3087 17.7168 18.3314 17.6092 18.3327 17.5003V14.167C18.3327 13.946 18.2449 13.734 18.0886 13.5777C17.9323 13.4215 17.7204 13.3337 17.4993 13.3337ZM18.266 2.18366C18.1815 1.98003 18.0196 1.81822 17.816 1.73366C17.7158 1.69096 17.6082 1.66831 17.4993 1.66699H14.166C13.945 1.66699 13.733 1.75479 13.5768 1.91107C13.4205 2.06735 13.3327 2.27931 13.3327 2.50033C13.3327 2.72134 13.4205 2.9333 13.5768 3.08958C13.733 3.24586 13.945 3.33366 14.166 3.33366H15.491L11.0743 7.74199C10.9962 7.81946 10.9342 7.91163 10.8919 8.01318C10.8496 8.11473 10.8279 8.22365 10.8279 8.33366C10.8279 8.44367 10.8496 8.55259 10.8919 8.65414C10.9342 8.75569 10.9962 8.84786 11.0743 8.92533C11.1518 9.00343 11.244 9.06543 11.3455 9.10773C11.4471 9.15004 11.556 9.17182 11.666 9.17182C11.776 9.17182 11.8849 9.15004 11.9865 9.10773C12.088 9.06543 12.1802 9.00343 12.2577 8.92533L16.666 4.50866V5.83366C16.666 6.05467 16.7538 6.26663 16.9101 6.42291C17.0664 6.57919 17.2783 6.66699 17.4993 6.66699C17.7204 6.66699 17.9323 6.57919 18.0886 6.42291C18.2449 6.26663 18.3327 6.05467 18.3327 5.83366V2.50033C18.3314 2.39143 18.3087 2.28384 18.266 2.18366Z" fill="#4F4F4F" />
                </svg>
              </span>
              <span className="test-layout__font-size-icon-active">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M7.74102 11.0753L3.33268 15.492V14.167C3.33268 13.946 3.24488 13.734 3.0886 13.5777C2.93232 13.4215 2.72036 13.3337 2.49935 13.3337C2.27834 13.3337 2.06637 13.4215 1.91009 13.5777C1.75381 13.734 1.66602 13.946 1.66602 14.167V17.5003C1.66733 17.6092 1.68998 17.7168 1.73268 17.817C1.81724 18.0206 1.97906 18.1824 2.18268 18.267C2.28287 18.3097 2.39045 18.3323 2.49935 18.3337H5.83268C6.0537 18.3337 6.26566 18.2459 6.42194 18.0896C6.57822 17.9333 6.66602 17.7213 6.66602 17.5003C6.66602 17.2793 6.57822 17.0673 6.42194 16.9111C6.26566 16.7548 6.0537 16.667 5.83268 16.667H4.50768L8.92435 12.2587C9.08127 12.1017 9.16943 11.8889 9.16943 11.667C9.16943 11.4451 9.08127 11.2322 8.92435 11.0753C8.76743 10.9184 8.5546 10.8302 8.33268 10.8302C8.11076 10.8302 7.89794 10.9184 7.74102 11.0753ZM4.50768 3.33366H5.83268C6.0537 3.33366 6.26566 3.24586 6.42194 3.08958C6.57822 2.9333 6.66602 2.72134 6.66602 2.50033C6.66602 2.27931 6.57822 2.06735 6.42194 1.91107C6.26566 1.75479 6.0537 1.66699 5.83268 1.66699H2.49935C2.39045 1.66831 2.28287 1.69096 2.18268 1.73366C1.97906 1.81822 1.81724 1.98003 1.73268 2.18366C1.68998 2.28384 1.66733 2.39143 1.66602 2.50033V5.83366C1.66602 6.05467 1.75381 6.26663 1.91009 6.42291C2.06637 6.57919 2.27834 6.66699 2.49935 6.66699C2.72036 6.66699 2.93232 6.57919 3.0886 6.42291C3.24488 6.26663 3.33268 6.05467 3.33268 5.83366V4.50866L7.74102 8.92533C7.81848 9.00343 7.91065 9.06543 8.0122 9.10773C8.11375 9.15004 8.22267 9.17182 8.33268 9.17182C8.44269 9.17182 8.55161 9.15004 8.65316 9.10773C8.75471 9.06543 8.84688 9.00343 8.92435 8.92533C9.00246 8.84786 9.06445 8.75569 9.10676 8.65414C9.14907 8.55259 9.17085 8.44367 9.17085 8.33366C9.17085 8.22365 9.14907 8.11473 9.10676 8.01318C9.06445 7.91163 9.00246 7.81946 8.92435 7.74199L4.50768 3.33366ZM17.4993 13.3337C17.2783 13.3337 17.0664 13.4215 16.9101 13.5777C16.7538 13.734 16.666 13.946 16.666 14.167V15.492L12.2577 11.0753C12.1008 10.9184 11.8879 10.8302 11.666 10.8302C11.4441 10.8302 11.2313 10.9184 11.0743 11.0753C10.9174 11.2322 10.8293 11.4451 10.8293 11.667C10.8293 11.8889 10.9174 12.1017 11.0743 12.2587L15.491 16.667H14.166C13.945 16.667 13.733 16.7548 13.5768 16.9111C13.4205 17.0673 13.3327 17.2793 13.3327 17.5003C13.3327 17.7213 13.4205 17.9333 13.5768 18.0896C13.733 18.2459 13.945 18.3337 14.166 18.3337H17.4993C17.6082 18.3323 17.7158 18.3097 17.816 18.267C18.0196 18.1824 18.1815 18.0206 18.266 17.817C18.3087 17.7168 18.3314 17.6092 18.3327 17.5003V14.167C18.3327 13.946 18.2449 13.734 18.0886 13.5777C17.9323 13.4215 17.7204 13.3337 17.4993 13.3337ZM18.266 2.18366C18.1815 1.98003 18.0196 1.81822 17.816 1.73366C17.7158 1.69096 17.6082 1.66831 17.4993 1.66699H14.166C13.945 1.66699 13.733 1.75479 13.5768 1.91107C13.4205 2.06735 13.3327 2.27931 13.3327 2.50033C13.3327 2.72134 13.4205 2.9333 13.5768 3.08958C13.733 3.24586 13.945 3.33366 14.166 3.33366H15.491L11.0743 7.74199C10.9962 7.81946 10.9342 7.91163 10.8919 8.01318C10.8496 8.11473 10.8279 8.22365 10.8279 8.33366C10.8279 8.44367 10.8496 8.55259 10.8919 8.65414C10.9342 8.75569 10.9962 8.84786 11.0743 8.92533C11.1518 9.00343 11.244 9.06543 11.3455 9.10773C11.4471 9.15004 11.556 9.17182 11.666 9.17182C11.776 9.17182 11.8849 9.15004 11.9865 9.10773C12.088 9.06543 12.1802 9.00343 12.2577 8.92533L16.666 4.50866V5.83366C16.666 6.05467 16.7538 6.26663 16.9101 6.42291C17.0664 6.57919 17.2783 6.66699 17.4993 6.66699C17.7204 6.66699 17.9323 6.57919 18.0886 6.42291C18.2449 6.26663 18.3327 6.05467 18.3327 5.83366V2.50033C18.3314 2.39143 18.3087 2.28384 18.266 2.18366Z" fill="#045CCE" />
                </svg>
              </span>
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

          <div className="test-layout__timer">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
              <path d="M10 0C8.35215 0 6.74099 0.488742 5.37058 1.40442C4.00017 2.3201 2.93206 3.62159 2.30133 5.1443C1.6706 6.66702 1.50558 8.34258 1.82712 9.95909C2.14866 11.5756 2.94234 13.0605 4.10777 14.2259C5.27321 15.3913 6.75807 16.185 8.37458 16.5065C9.99109 16.8281 11.6666 16.6631 13.1894 16.0323C14.7121 15.4016 16.0136 14.3335 16.9292 12.9631C17.8449 11.5927 18.3337 9.98151 18.3337 8.33333C18.3337 6.12632 17.456 4.00956 15.8929 2.44628C14.3296 0.883001 12.2128 0 10 0ZM10 15C8.68179 15 7.39286 14.609 6.29654 13.8765C5.20021 13.1439 4.34572 12.1027 3.84114 10.8846C3.33655 9.66638 3.20453 8.32594 3.46176 7.03273C3.719 5.73953 4.35393 4.55164 5.28628 3.61929C6.21863 2.68694 7.40652 2.052 8.69973 1.79477C9.99293 1.53753 11.3334 1.66956 12.5516 2.17414C13.7697 2.67872 14.8109 3.5332 15.5435 4.62953C16.276 5.72586 16.667 7.01479 16.667 8.33333C16.667 10.1014 15.9646 11.7971 14.7144 13.0474C13.4641 14.2976 11.7684 15 10 15Z" fill="#045CCE" />
              <path d="M10.8333 7.5H9.16667V4.16667C9.16667 3.94565 9.07887 3.73369 8.92259 3.57741C8.76631 3.42113 8.55435 3.33333 8.33334 3.33333C8.11232 3.33333 7.90036 3.42113 7.74408 3.57741C7.5878 3.73369 7.5 3.94565 7.5 4.16667V8.33333C7.5 8.55435 7.5878 8.76631 7.74408 8.92259C7.90036 9.07887 8.11232 9.16667 8.33334 9.16667H10.8333C11.0544 9.16667 11.2663 9.07887 11.4226 8.92259C11.5789 8.76631 11.6667 8.55435 11.6667 8.33333C11.6667 8.11232 11.5789 7.90036 11.4226 7.74408C11.2663 7.5878 11.0544 7.5 10.8333 7.5Z" fill="#045CCE" />
            </svg>
            <span>{formatTime(timeRemaining)}</span>
          </div>
          <button className="test-layout__submit-button-header" onClick={handleSubmit}>
            Nộp bài
          </button>
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
                  className={`test-layout__question-number-item ${answers[q.id] ? 'answered' : ''
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
                src={isTestComplete ? "/src/assets/images/cudaxong.png" : "/src/assets/images/cuchuaxong.png"}
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
