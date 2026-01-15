import React, { useState, useEffect, useRef } from 'react';
import { useNavigate, useParams, useLocation } from 'react-router-dom';
import { getSkillById, getSectionById } from '../api/toeic.api';
import { submitTestResult } from '@/features/exams/api/exams.api';
import TestLayout from '@/features/exams/components/TestLayout';
import './Toeic.css';

export default function WritingToeic() {
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

    // Lưu câu trả lời cho từng part
    const [writingAnswers, setWritingAnswers] = useState({});

    const handleWritingChange = (partNumber, text) => {
        setWritingAnswers(prev => ({
            ...prev,
            [partNumber]: text
        }));
    };

    // Tính số từ cho part hiện tại
    const getCurrentWordCount = () => {
        const text = writingAnswers[currentPartTab] || "";
        return text.trim() === "" ? 0 : text.trim().split(/\s+/).length;
    };

    const goNextPart = () => {
        if (currentPartTab < parts.length) {
            setCurrentPartTab(currentPartTab + 1);
            window.scrollTo({ top: 0, behavior: "smooth" });
        }
    };

    const goPrevPart = () => {
        if (currentPartTab > 1) {
            setCurrentPartTab(currentPartTab - 1);
            window.scrollTo({ top: 0, behavior: "smooth" });
        }
    };


    const renderNavigationButtons = () => {
        const isFirst = currentPartTab === 1;
        const isLast = currentPartTab === parts.length;

        // Câu cuối → chỉ hiện nút "Hoàn thành"
        if (isLast) {
            return (
                <div className="wt-nav-btns wt-nav-btns-center">
                    <button className="btn-complete" onClick={handleSubmit}>
                        Hoàn thành
                    </button>
                </div>
            );
        }

        // Câu đầu → chỉ hiện "Câu tiếp"
        if (isFirst) {
            return (
                <div className="wt-nav-btns wt-nav-btns-right">
                    <button className="btn-next" onClick={goNextPart}>
                        Câu tiếp
                    </button>
                </div>
            );
        }

        // Câu giữa → hiện cả "Câu trước" + "Câu tiếp"
        return (
            <div className="wt-nav-btns wt-nav-btns-two">
                <button className="btn-prev" onClick={goPrevPart}>
                    Câu trước
                </button>
                <button className="btn-next" onClick={goNextPart}>
                    Câu tiếp
                </button>
            </div>
        );
    };

    useEffect(() => {
        const fetchExamData = async () => {
            try {
                setLoading(true);
                if (sectionId) {
                    const response = await getSectionById(sectionId, { with_questions: true });
                    if (response.data.success) {
                        const section = response.data.data;
                        setSectionData(section);
                        
                        const allGroups = [];
                        const allPassages = [];
                        const allParts = [];
                        let questionNumber = 1;

                        // Xử lý khi có question_groups
                        if (section.question_groups && section.question_groups.length > 0) {
                            setPassages([{
                                id: section.id,
                                part: 1,
                                title: section.title || 'Writing Question',
                                subtitle: '',
                                content: section.content || ''
                            }]);
                            setParts([{ id: section.id, part: 1, title: `Part 1` }]);

                            section.question_groups.forEach(group => {
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
                                    type: group.question_type || 'MCQ',
                                    instructions: group.instructions,
                                    groupContent: group.content,
                                    options: Array.isArray(group.options)
                                        ? group.options
                                        : (group.options ? (typeof group.options === 'string' ? JSON.parse(group.options) : ['A', 'B', 'C', 'D']) : ['A', 'B', 'C', 'D']),
                                    questions: questions,
                                    startNumber: questions[0]?.number || 1,
                                    endNumber: questions[questions.length - 1]?.number || 1
                                });
                            });
                        }
                        // Xử lý khi chỉ có questions trực tiếp (không có question_groups)
                        else if (section.questions && section.questions.length > 0) {
                            // Mỗi question là một màn hình riêng (nhưng không gọi là Part)
                            section.questions.forEach((question, index) => {
                                const questionNumber = index + 1;
                                
                                allPassages.push({
                                    id: question.id,
                                    part: questionNumber,
                                    title: `Writing Question ${questionNumber}`,
                                    subtitle: '',
                                    content: question.content || '' // Hiển thị directions
                                });

                                allParts.push({
                                    id: question.id,
                                    part: questionNumber,
                                    title: `Question ${questionNumber}` // Hiển thị "Question 1", "Question 2" thay vì "Part"
                                });

                                // Tạo group cho question để lưu answer
                                allGroups.push({
                                    id: question.id,
                                    part: questionNumber,
                                    type: 'ESSAY',
                                    instructions: '',
                                    groupContent: '',
                                    options: [],
                                    questions: [{
                                        id: question.id,
                                        number: questionNumber,
                                        content: '',
                                        correctAnswer: question.answer_content
                                    }],
                                    startNumber: questionNumber,
                                    endNumber: questionNumber
                                });
                            });

                            setPassages(allPassages);
                            setParts(allParts);
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
                                // Xử lý nếu section có question_groups
                                if (section.question_groups && section.question_groups.length > 0) {
                                    const partNumber = sectionIndex + 1;
                                    const groupStartIndex = allGroups.length;

                                    allPassages.push({
                                        id: section.id,
                                        part: partNumber,
                                        title: section.title || `Writing Part ${partNumber}`,
                                        subtitle: '',
                                        content: section.content || ''
                                    });

                                    section.question_groups.forEach(group => {
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
                                            type: group.question_type || 'MCQ',
                                            instructions: group.instructions,
                                            groupContent: group.content,
                                            options: Array.isArray(group.options)
                                                ? group.options
                                                : (group.options ? (typeof group.options === 'string' ? JSON.parse(group.options) : ['A', 'B', 'C', 'D']) : ['A', 'B', 'C', 'D']),
                                            questions: questions,
                                            startNumber: questions[0]?.number || questionNumber,
                                            endNumber: questions[questions.length - 1]?.number || questionNumber
                                        });
                                    });

                                    const firstQuestionNum = allGroups[groupStartIndex]?.startNumber || questionNumber;
                                    const lastQuestionNum = allGroups[allGroups.length - 1]?.endNumber || questionNumber;
                                    allParts.push({
                                        id: section.id,
                                        part: partNumber,
                                        title: `Part ${partNumber} (${firstQuestionNum}-${lastQuestionNum})`
                                    });
                                }
                                // Xử lý nếu section chỉ có questions (không có question_groups)
                                else if (section.questions && section.questions.length > 0) {
                                    section.questions.forEach((question, qIndex) => {
                                        const partNumber = questionNumber;

                                        allPassages.push({
                                            id: question.id,
                                            part: partNumber,
                                            title: `Writing Question ${partNumber}`,
                                            subtitle: '',
                                            content: question.content || ''
                                        });

                                        allParts.push({
                                            id: question.id,
                                            part: partNumber,
                                            title: `Question ${partNumber}`
                                        });

                                        allGroups.push({
                                            id: question.id,
                                            part: partNumber,
                                            type: 'ESSAY',
                                            instructions: '',
                                            groupContent: '',
                                            options: [],
                                            questions: [{
                                                id: question.id,
                                                number: questionNumber,
                                                content: '',
                                                correctAnswer: question.answer_content
                                            }],
                                            startNumber: questionNumber,
                                            endNumber: questionNumber
                                        });

                                        questionNumber++;
                                    });
                                }
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

    const handleAnswerSelect = (questionId, answer) => {
        setAnswers((prev) => ({ ...prev, [questionId]: answer }));
    };

    const handleSubmit = async () => {
        try {
            // Tính thời gian đã làm bài
            const timeSpent = (skillData?.time_limit * 60 || sectionData?.time_limit * 60 || 1800) - timeRemaining;
            
            // Thu thập tất cả ID câu hỏi
            const allQuestionIds = [];
            questionGroups.forEach(group => {
                if (group.questions) {
                    group.questions.forEach(q => {
                        allQuestionIds.push(q.id);
                    });
                }
            });

            // Chuẩn bị dữ liệu câu trả lời
            const answersArray = allQuestionIds.map(questionId => {
                // Tìm part number của question này
                let answerText = answers[questionId] || null;
                
                // Nếu là ESSAY question, lấy từ writingAnswers
                questionGroups.forEach(group => {
                    if (group.type === 'ESSAY' && group.questions) {
                        const question = group.questions.find(q => q.id === questionId);
                        if (question && writingAnswers[group.part]) {
                            answerText = writingAnswers[group.part];
                        }
                    }
                });

                return {
                    question_id: questionId,
                    answer: answerText
                };
            });

            const submitData = {
                skill_id: skillId ? parseInt(skillId) : null,
                section_id: sectionId ? parseInt(sectionId) : null,
                test_id: examData?.id || null,
                answers: answersArray,
                all_question_ids: allQuestionIds,
                time_spent: timeSpent,
                total_questions: allQuestionIds.length,
                answered_questions: answersArray.filter(a => a.answer && a.answer.trim() !== '').length
            };

            console.log('Submitting:', submitData);

            // Gửi kết quả lên server
            const response = await submitTestResult(submitData);

            if (response.data.success) {
                // Chuyển đến trang kết quả
                navigate(`/test-result/${response.data.data.id}`);
            } else {
                throw new Error(response.data.message || 'Không thể nộp bài');
            }
        } catch (error) {
            console.error('Error submitting test:', error);
            alert('Có lỗi xảy ra khi nộp bài. Vui lòng thử lại.');
        }
    };

    // helper: render questions by group type (class names prefixed with lt-)
    const renderQuestionsByType = (group) => {
        const opts = Array.isArray(group.options) ? group.options : (group.options || []);
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
            >
            <div className='lt-grid-main'>
                <div className="lt-grid">
                    <main className="lt-col lt-col--left">
                        <div className="lt-left-box">
                            <div className="lt-passage">
                                <div className="lt-passage__header">
                                    <h2 className="lt-passage__title">{currentPassage.title}</h2>
                                    {currentPassage.subtitle && <p className="lt-passage__subtitle">{currentPassage.subtitle}</p>}
                                </div>
                                <div className="lt-passage__content" dangerouslySetInnerHTML={{ __html: currentPassage.content }} />
                            </div>

                           
                        </div>
                    </main>

                    <aside className="lt-col lt-col--right">
                        <div className="lt-writing-box-answer">
                            <h4>Your answer</h4>

                            <textarea
                                className=''
                                placeholder="Your answer"
                                value={writingAnswers[currentPartTab] || ""}
                                onChange={(e) => handleWritingChange(currentPartTab, e.target.value)}
                            ></textarea>

                        <div className="wt-answer-footer">
                            <div className="lt-word-count">
                                Số từ: <span>{getCurrentWordCount()}</span>/150
                            </div>
                              </div>
                            {renderNavigationButtons()}
                        </div>
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
                                {p.part}
                            </button>
                        ))}
                    </div>
                </div>
            </aside>
        </TestLayout>
        </div>
    );
}