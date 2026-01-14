import React, { useState, useEffect, useRef } from 'react';
import { useNavigate, useParams, useLocation } from 'react-router-dom';
import { getSkillById, getSectionById } from '../api/toeic.api';
import TestLayout from '@/features/exams/components/TestLayout';
import './Toeic.css';
import clock_speaking from '@/assets/images/clock.svg';

export default function SpeakingToeic() {
    const navigate = useNavigate();
    const { skillId, sectionId } = useParams();
    const location = useLocation();
    const examData = location.state?.examData;

    const audioRef = useRef(null);
    const [isPlaying, setIsPlaying] = useState(false);
    const [audioProgress, setAudioProgress] = useState(0);
    const [audioDuration, setAudioDuration] = useState(0);

    const [answers, setAnswers] = useState({});
    const [timeRemaining, setTimeRemaining] = useState(1800);
    const [currentPartTab, setCurrentPartTab] = useState(1);
    const [loading, setLoading] = useState(true);
    const [skillData, setSkillData] = useState(null);
    const [sectionData, setSectionData] = useState(null);
    const [questionGroups, setQuestionGroups] = useState([]);
    const [passages, setPassages] = useState([]);
    const [parts, setParts] = useState([]);

    const [fontSize, setFontSize] = useState("normal");
    const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);

    // Reset question index khi chuyển part
    useEffect(() => {
        setCurrentQuestionIndex(0);
    }, [currentPartTab]);

    useEffect(() => {
        const fetchExamData = async () => {
            try {
                setLoading(true);
                if (sectionId) {
                    const response = await getSectionById(sectionId, { with_questions: true });
                    if (response.data.success) {
                        const section = response.data.data;
                        setSectionData(section);
                        setPassages([{
                            id: section.id,
                            part: 1,
                            title: section.title || 'Speaking',
                            subtitle: '',
                            content: section.content || ''
                        }]);
                        setParts([{ id: section.id, part: 1, title: `Part 1` }]);

                        const allGroups = [];
                        let questionNumber = 1;
                        
                        // Speaking: questions nằm trực tiếp trong section.questions
                        if (section.questions && section.questions.length > 0) {
                            const questions = [];
                            section.questions.forEach((q) => {
                                questions.push({
                                    id: q.id,
                                    number: questionNumber++,
                                    content: q.content,
                                    correctAnswer: q.answer_content,
                                    metadata: q.metadata || null
                                });
                            });
                            
                            // Tạo 1 group ảo chứa tất cả questions
                            allGroups.push({
                                id: section.id,
                                part: 1,
                                type: 'speaking',
                                instructions: '',
                                groupContent: '',
                                options: [],
                                questions: questions,
                                startNumber: 1,
                                endNumber: questions.length
                            });
                        }
                        setQuestionGroups(allGroups);
                    }
                } else if (skillId) {
                    const response = await getSkillById(skillId, { with_sections: true });
                    if (response.data.success) {
                        const skill = response.data.data;
                        setSkillData(skill);

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
                                    title: section.title || `Speaking Part ${partNumber}`,
                                    subtitle: '',
                                    content: section.content || ''
                                });

                                // Speaking: questions nằm trực tiếp trong section.questions
                                if (section.questions && section.questions.length > 0) {
                                    const questions = [];
                                    section.questions.forEach((q) => {
                                        questions.push({
                                            id: q.id,
                                            number: questionNumber++,
                                            content: q.content,
                                            correctAnswer: q.answer_content,
                                            metadata: q.metadata || null
                                        });
                                    });
                                    
                                    // Tạo 1 group ảo chứa tất cả questions của section này
                                    allGroups.push({
                                        id: section.id,
                                        part: partNumber,
                                        type: 'speaking',
                                        instructions: '',
                                        groupContent: '',
                                        options: [],
                                        questions: questions,
                                        startNumber: questions[0]?.number || questionNumber,
                                        endNumber: questions[questions.length - 1]?.number || questionNumber
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

                        if (skill.time_limit) {
                            setTimeRemaining(skill.time_limit * 60);
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

    useEffect(() => {
        const audioUrl =
            sectionData?.audio_url ||
            sectionData?.media?.audio ||
            skillData?.audio_url ||
            skillData?.media?.audio ||
            skillData?.audio ||
            sectionData?.media_url ||
            null;

        if (audioUrl && audioRef.current) {
            audioRef.current.src = audioUrl;
            audioRef.current.load();
            const tryPlay = async () => {
                try {
                    await audioRef.current.play();
                    setIsPlaying(true);
                } catch (err) {
                    setIsPlaying(false);
                    console.info('Autoplay blocked or unavailable:', err);
                }
            };
            setTimeout(tryPlay, 300);
        }
    }, [sectionData, skillData]);

    useEffect(() => {
        const audio = audioRef.current;
        if (!audio) return;
        const onTime = () => setAudioProgress(audio.currentTime);
        const onLoaded = () => setAudioDuration(audio.duration || 0);
        audio.addEventListener('timeupdate', onTime);
        audio.addEventListener('loadedmetadata', onLoaded);
        return () => {
            audio.removeEventListener('timeupdate', onTime);
            audio.removeEventListener('loadedmetadata', onLoaded);
        };
    }, [audioRef.current]);

    const handleAudioToggle = async () => {
        const audio = audioRef.current;
        if (!audio) return;
        try {
            if (audio.paused) {
                await audio.play();
                setIsPlaying(true);
            } else {
                audio.pause();
                setIsPlaying(false);
            }
        } catch (err) {
            console.error('Audio play error', err);
        }
    };

    const handleAudioSeek = (e) => {
        const audio = audioRef.current;
        if (!audio) return;
        const pct = Number(e.target.value);
        const time = (audio.duration || audioDuration) * (pct / 100);
        audio.currentTime = time;
        setAudioProgress(time);
    };

    const formatTime = (seconds) => {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    };

    const handleAnswerSelect = (questionId, answer) => {
        setAnswers((prev) => ({ ...prev, [questionId]: answer }));
    };

    const handleSubmit = () => {
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

    // helper: render questions by group type (class names prefixed with lt-)
    const renderQuestionsByType = (group) => {
        // Xử lý options an toàn hơn
        let opts = [];
        if (Array.isArray(group.options)) {
            opts = group.options;
        } else if (group.options && typeof group.options === 'object') {
            // Nếu là object, lấy values
            opts = Object.values(group.options);
        } else if (group.options && typeof group.options === 'string') {
            try {
                const parsed = JSON.parse(group.options);
                opts = Array.isArray(parsed) ? parsed : [];
            } catch {
                opts = [];
            }
        }
        
        const type = (group.type || '').toLowerCase();

        const renderMCQ = (q) => (
            <div key={q.id} id={`question-${q.number}`} className="lt-question">
                <div className="lt-question__number">Câu {q.number}:</div>
                <div className="lt-question__content" dangerouslySetInnerHTML={{ __html: q.content || '' }} />
                <div className="lt-question__options">
                    {opts.map((option, idx) => {
                        const val = typeof option === 'object' ? (option.value ?? option.label ?? String(option)) : String(option);
                        const labelHtml = typeof option === 'object' ? (option.label ?? val) : option;
                        return (
                            <label key={idx} className="lt-option">
                                <input
                                    type="radio"
                                    name={`q-${q.id}`}
                                    value={val}
                                    checked={String(answers[q.id] ?? '') === String(val)}
                                    onChange={() => handleAnswerSelect(q.id, val)}
                                />
                                <span dangerouslySetInnerHTML={{ __html: labelHtml }} />
                            </label>
                        );
                    })}
                </div>
            </div>
        );

        const renderTF = (q) => (
            <div key={q.id} id={`question-${q.number}`} className="lt-question">
                <div className="lt-question__number">Câu {q.number}.</div>
                <div className="lt-question__content" dangerouslySetInnerHTML={{ __html: q.content || '' }} />
                <div className="lt-question__options">
                    {(opts.length ? opts : ['True', 'False', 'Not given']).map((o, i) => (
                        <label key={i} className="lt-option">
                            <input
                                type="radio"
                                name={`q-${q.id}`}
                                value={o}
                                checked={String(answers[q.id] ?? '') === String(o)}
                                onChange={() => handleAnswerSelect(q.id, o)}
                            />
                            <span>{o}</span>
                        </label>
                    ))}
                </div>
            </div>
        );

        const renderFill = (q) => (
            <div key={q.id} id={`question-${q.number}`} className="lt-question">
                <div className="lt-question__number">Câu {q.number}.</div>
                <div className="lt-question__content" dangerouslySetInnerHTML={{ __html: q.content || '' }} />
                <input
                    type="text"
                    className="lt-fill-input"
                    value={answers[q.id] ?? ''}
                    onChange={(e) => handleAnswerSelect(q.id, e.target.value)}
                    placeholder="Nhập đáp án"
                />
            </div>
        );

        const renderMatch = (q) => (
            <div key={q.id} id={`question-${q.number}`} className="lt-question">
                <div className="lt-question__number">Câu {q.number}.</div>
                <div className="lt-question__content" dangerouslySetInnerHTML={{ __html: q.content || '' }} />
                <select
                    value={answers[q.id] ?? ''}
                    onChange={(e) => handleAnswerSelect(q.id, e.target.value)}
                    className="lt-match-select"
                >
                    <option value="">Chọn đáp án</option>
                    {opts.map((o, idx) => {
                        const val = typeof o === 'object' ? (o.value ?? o.label ?? String(o)) : String(o);
                        const label = typeof o === 'object' ? (o.label ?? val) : o;
                        return <option key={idx} value={val}>{label}</option>;
                    })}
                </select>
            </div>
        );

        return (
            <div className="lt-group-questions">
                {group.questions.map((q) => {
                    if (type.includes('true') || type.includes('false') || type.includes('not_given')) return renderTF(q);
                    if (type.includes('fill') || type.includes('blank') || type.includes('gap')) return renderFill(q);
                    if (type.includes('match') || type.includes('matching')) return renderMatch(q);
                    return renderMCQ(q);
                })}
            </div>
        );
    };

    if (loading) {
        return (
            <div className="lt-loading">
                <div className="lt-loading__text">Đang tải...</div>
            </div>
        );
    }

    if (!passages || passages.length === 0) {
        return (
            <div className="lt-empty">
                <div className="lt-empty__text">Không tìm thấy dữ liệu bài thi</div>
            </div>
        );
    }

    const currentPassage = passages.find(p => p.part === currentPartTab) || passages[0];
    const currentPartGroups = questionGroups.filter(g => g.part === currentPartTab);
    const questionsByPart = questionGroups.reduce((acc, group) => {
        const part = group.part;
        if (!acc[part]) acc[part] = [];
        acc[part].push(...group.questions);
        return acc;
    }, {});

    // Lấy câu hỏi của part hiện tại
    const currentPartQuestions = questionsByPart[currentPartTab] || [];
    const currentQuestion = currentPartQuestions[currentQuestionIndex] || null;

    return (
        <div className="lt-page">
            <TestLayout
                examData={examData}
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
                fontSize={fontSize}
                onFontSizeChange={setFontSize}
                showQuestionNumbers={false}
                hideTimer={true}
            >
            <div className='lt-grid-main'>
                <div className="lt-grid">
                    <aside className="lt-col lt-col--left">
                    </aside>

                    <main className="lt-col lt-col--center wt-col-center">
                        {currentQuestion && (
                            <div className="speaking-question-container">
                                <h2 className="speaking-question-title">
                                    Speaking Question {currentQuestionIndex + 1}
                                </h2>
                                
                                {currentQuestion.content && (
                                    <div className="speaking-directions">
                                        <div className="lt-question__content" dangerouslySetInnerHTML={{ __html: currentQuestion.content }} />
                                    </div>
                                )}

                                <div className='clock_speaking'>
                                    <h4>Response Time</h4>
                                    <div className="lt-timer">
                                        <img src={clock_speaking} alt="" />
                                        <p>{formatTime(timeRemaining)}</p>
                                    </div>
                                </div>
                            </div>
                        )}
                    </main>

                    <aside className="lt-col lt-col--right">
                    </aside>
                </div>
            </div>

            <aside className="lt-footer">
                <div className='lt-footer-dflex'>
                    <div className="lt-part-tabs">
                        {parts.map((p) => (
                            <button
                                key={p.part}
                                className={`lt-part-tab ${currentPartTab === p.part ? 'active' : ''}`}
                                onClick={() => setCurrentPartTab(p.part)}
                            >
                                Part {p.part}
                            </button>
                        ))}
                    </div>
                </div>
            </aside>
        </TestLayout>
        </div>
    );
}