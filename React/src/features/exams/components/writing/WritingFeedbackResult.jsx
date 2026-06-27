import { useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import logo from '@/assets/images/logo.png';
import closeIcon from '@/assets/images/nutx.svg';
import clockIcon from '@/assets/images/clock.svg';
import { parseWritingFeedback } from '../../utils/writingFeedback';
import { normalizeHtmlMediaSources } from '../../utils/readingTest';
import '../TestLayout.css';
import './WritingFeedbackResult.css';

const SCORE_ITEMS = [
  { key: 'overall', label: 'Overall', color: '#2563eb', tone: 'primary' },
  { key: 'task_response', label: 'TA', color: '#06b6d4' },
  { key: 'coherence_cohesion', label: 'CC', color: '#9333ea' },
  { key: 'lexical_resource', label: 'LR', color: '#65c932' },
  { key: 'grammatical_range', label: 'GA', color: '#f06423' }
];

const CRITERIA_ITEMS = [
  { key: 'task_response', title: 'Task Response' },
  { key: 'coherence_cohesion', title: 'Coherence and Cohesion' },
  { key: 'lexical_resource', title: 'Lexical Resource' },
  { key: 'grammatical_range', title: 'Grammatical Range and Accuracy' }
];

const EMPTY_TASK = {
  id: 'empty',
  key: 'empty',
  label: 'Writing',
  prompt: '',
  promptHtml: '',
  text: 'Chưa có nội dung bài viết.',
  scores: {},
  teacherNote: '',
  criteria: [],
  details: []
};

const stripHtml = (value) => {
  if (Array.isArray(value)) {
    return value.map(stripHtml).filter(Boolean).join(', ');
  }

  return String(value ?? '')
    .replace(/<br\s*\/?>/gi, '\n')
    .replace(/<\/(p|div|li|h[1-6])>/gi, '\n')
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/[ \t]+\n/g, '\n')
    .replace(/\n{3,}/g, '\n\n')
    .trim();
};

const escapeHtml = (value) =>
  String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const hasHtmlTags = (value = '') => /<[a-z][\s\S]*>/i.test(value);

const textToHtml = (value = '') =>
  String(value || '')
    .split(/\n{2,}/)
    .map((paragraph) => paragraph.trim())
    .filter(Boolean)
    .map((paragraph) => `<p>${escapeHtml(paragraph).replace(/\n/g, '<br />')}</p>`)
    .join('');

const formatPromptHtml = (value) => {
  const source = String(value ?? '').trim();

  if (!source) return '';

  const html = hasHtmlTags(source) ? source : textToHtml(source);

  return normalizeHtmlMediaSources(html);
};

const getResultTitle = (result) =>
  result?.test?.name || result?.skill?.name || result?.section?.title || 'Writing Test';

const formatTime = (seconds = 0) => {
  const safeSeconds = Math.max(0, Number(seconds) || 0);
  const hours = Math.floor(safeSeconds / 3600);
  const minutes = Math.floor((safeSeconds % 3600) / 60);
  const remainingSeconds = safeSeconds % 60;

  return `${hours.toString().padStart(2, '0')}:${minutes
    .toString()
    .padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
};

const formatBand = (score) => {
  if (score === null || score === undefined || score === '') return '-';

  const numericScore = Number(score);

  return Number.isFinite(numericScore) ? numericScore.toFixed(1) : '-';
};

const getScoreDegrees = (score) => {
  if (score === null || score === undefined || score === '') return '0deg';

  const numericScore = Number(score);
  if (!Number.isFinite(numericScore)) return '0deg';

  return `${Math.max(0, Math.min(9, numericScore)) * 40}deg`;
};

const inferTaskNumber = (label = '', fallback = 1) => {
  if (/task\s*2|part\s*2|essay|250\s+words/i.test(label)) return 2;
  if (/task\s*1|part\s*1|chart|map|process|150\s+words/i.test(label)) return 1;

  return fallback >= 2 ? 2 : 1;
};

const formatTaskTitle = (label = '', taskNumber = 1) => {
  if (/task\s*[12]/i.test(label)) return label;

  return `Writing Task ${taskNumber}`;
};

const getWritingEntries = (result) => {
  const seen = new Set();

  return (result?.answers || [])
    .map((answer, index) => {
      const text = stripHtml(answer?.user_answer);
      const key = answer?.section_id
        ? `section:${answer.section_id}`
        : `question:${answer?.question_id || index}`;

      if (!text || seen.has(key)) {
        return null;
      }

      seen.add(key);
      const taskNumber = inferTaskNumber(answer?.part || '', index + 1);

      return {
        id: key,
        key,
        label: formatTaskTitle(answer?.part || '', taskNumber),
        taskNumber,
        prompt: stripHtml(answer?.prompt || answer?.question_prompt || ''),
        promptHtml: formatPromptHtml(
          answer?.prompt_html || answer?.prompt || answer?.question_prompt || ''
        ),
        text
      };
    })
    .filter(Boolean);
};

const normalizeCriteria = (task) => {
  const criteria = Array.isArray(task?.criteria) ? task.criteria : [];

  return CRITERIA_ITEMS.map((item) => ({
    ...item,
    ...(criteria.find((criterion) => criterion?.key === item.key) || {})
  })).filter(
    (item) =>
      String(item.strengths || '').trim() !== '' ||
      String(item.weaknesses || '').trim() !== '' ||
      (item.score !== null && item.score !== undefined)
  );
};

const normalizeDetails = (task) =>
  (Array.isArray(task?.details) ? task.details : [])
    .map((detail) => ({
      original: stripHtml(detail?.original),
      explanation: stripHtml(detail?.explanation),
      correction: stripHtml(detail?.correction)
    }))
    .filter((detail) => detail.original || detail.explanation || detail.correction);

const splitParagraphs = (text) =>
  String(text || '')
    .split(/\n{2,}/)
    .map((paragraph) => paragraph.trim())
    .filter(Boolean);

const getTaskPromptHtml = (task, entry = {}) => {
  const savedPromptHtml = task?.prompt_html || '';
  const savedPrompt = task?.prompt || '';

  if (savedPromptHtml) {
    return formatPromptHtml(savedPromptHtml);
  }

  if (hasHtmlTags(savedPrompt)) {
    return formatPromptHtml(savedPrompt);
  }

  return entry.promptHtml || formatPromptHtml(savedPrompt) || formatPromptHtml(entry.prompt);
};

const normalizeWritingTasks = (result, feedback) => {
  const entries = getWritingEntries(result);
  const savedTasks = Array.isArray(feedback?.tasks) ? feedback.tasks : [];

  if (savedTasks.length > 0) {
    return savedTasks.map((task, index) => {
      const entry = entries.find((item) => item.key === task?.key) || entries[index] || {};
      const taskNumber = Number(task?.task_number || entry.taskNumber || index + 1);

      return {
        key: task?.key || entry.key || `task:${index + 1}`,
        label: task?.title || entry.label || `Writing Task ${taskNumber}`,
        taskNumber,
        prompt: stripHtml(task?.prompt) || entry.prompt || '',
        promptHtml: getTaskPromptHtml(task, entry),
        text: stripHtml(task?.answer) || entry.text || '',
        scores: task?.scores || {},
        rawTaskScore: task?.raw_task_score ?? task?.scores?.raw_task_score ?? null,
        roundedTaskScore: task?.rounded_task_score ?? task?.scores?.rounded_task_score ?? null,
        teacherNote: task?.teacher_note || '',
        criteria: task?.criteria || [],
        details: task?.details || []
      };
    });
  }

  return entries.map((entry, index) => ({
    ...entry,
    promptHtml: entry.promptHtml || formatPromptHtml(entry.prompt),
    scores: index === 0 ? feedback?.scores || {} : {},
    teacherNote: index === 0 ? feedback?.teacher_note || '' : '',
    criteria: index === 0 ? feedback?.criteria || [] : [],
    details: index === 0 ? feedback?.details || [] : []
  }));
};

const renderHighlightedText = (text, details) => {
  const phrases = details
    .map((detail) => detail.original)
    .filter((phrase) => phrase.length > 8)
    .sort((left, right) => right.length - left.length);

  if (phrases.length === 0) {
    return text;
  }

  const nodes = [];
  const lowerText = text.toLowerCase();
  let cursor = 0;

  while (cursor < text.length) {
    let match = null;

    phrases.forEach((phrase) => {
      const index = lowerText.indexOf(phrase.toLowerCase(), cursor);
      if (index === -1) return;

      if (
        !match ||
        index < match.index ||
        (index === match.index && phrase.length > match.phrase.length)
      ) {
        match = { index, phrase };
      }
    });

    if (!match) {
      nodes.push(text.slice(cursor));
      break;
    }

    if (match.index > cursor) {
      nodes.push(text.slice(cursor, match.index));
    }

    nodes.push(
      <mark className="writing-feedback__essay-highlight" key={`${match.index}-${match.phrase}`}>
        {text.slice(match.index, match.index + match.phrase.length)}
      </mark>
    );
    cursor = match.index + match.phrase.length;
  }

  return nodes;
};

const getTaskOverallScore = (task) =>
  task?.roundedTaskScore ??
  task?.rounded_task_score ??
  task?.scores?.rounded_task_score ??
  task?.scores?.overall;

const ScoreCard = ({ item, score }) => (
  <div
    className={`writing-feedback__score-card ${
      item.tone === 'primary' ? 'writing-feedback__score-card--primary' : ''
    }`}
  >
    <div
      className="writing-feedback__score-ring"
      style={{
        '--score-color': item.color,
        '--score-degrees': getScoreDegrees(score)
      }}
      aria-hidden="true"
    >
      <span />
    </div>
    <div className="writing-feedback__score-copy">
      <span>{item.label}</span>
      <strong>{formatBand(score)}</strong>
    </div>
  </div>
);

const AccordionHeader = ({ index, title, open, onClick }) => (
  <button type="button" className="writing-feedback__accordion-header" onClick={onClick}>
    <span>{index}. {title}</span>
    <span
      className={`writing-feedback__accordion-icon ${
        open ? 'writing-feedback__accordion-icon--open' : ''
      }`}
      aria-hidden="true"
    />
  </button>
);

const WritingFeedbackResult = ({ result }) => {
  const navigate = useNavigate();
  const feedback = useMemo(() => parseWritingFeedback(result?.writing_feedback) || {}, [result]);
  const tasks = useMemo(() => normalizeWritingTasks(result, feedback), [result, feedback]);
  const [activeTaskIndex, setActiveTaskIndex] = useState(0);
  const [activeDetailIndex, setActiveDetailIndex] = useState(0);
  const [openSections, setOpenSections] = useState({ overall: true, detail: true });

  const activeTask = tasks[activeTaskIndex] || EMPTY_TASK;
  const details = useMemo(() => normalizeDetails(activeTask), [activeTask]);
  const criteria = useMemo(() => normalizeCriteria(activeTask), [activeTask]);
  const scoreNote =
    stripHtml(feedback?.teacher_note) || 'Highly appreciate your hard work and effort';
  const scores = {
    ...(activeTask?.scores || {}),
    overall:
      getTaskOverallScore(activeTask) ??
      feedback?.overall_score ??
      feedback?.scores?.overall ??
      result?.score
  };
  const highlightedDetails =
    activeDetailIndex === null || !details[activeDetailIndex]
      ? []
      : [details[activeDetailIndex]];

  const toggleSection = (section) => {
    setOpenSections((current) => ({
      ...current,
      [section]: !current[section]
    }));
  };

  return (
    <div className="writing-feedback">
      <header className="test-layout__header" style={{ '--font-size': 'normal' }}>
        <button className="test-layout__close" type="button" onClick={() => navigate(-1)}>
          <img src={closeIcon} alt="" />
        </button>
        <div className="test-layout__header-info">
          <img src={logo} alt="OWL IELTS" className="test-layout__logo" />
          <div className="test-layout__header-text">
            <div className="test-layout__header-label">Làm bài writing</div>
            <div className="test-layout__header-name">{getResultTitle(result)}</div>
          </div>
        </div>
        <div className="test-layout__header-right">
          <div className="test-layout__timer">
            <img src={clockIcon} alt="" />
            <span>{formatTime(result?.time_spent)}</span>
          </div>
        </div>
      </header>

      <main className="writing-feedback__main">
        <section className="writing-feedback__score-note">
          <p>Điểm từng kỹ năng sẽ được làm tròn xuống, đối với điểm overall mới được làm tròn lên</p>
          {scoreNote && <p>{scoreNote}</p>}
        </section>

        <section className="writing-feedback__scores" aria-label="Writing band scores">
          {SCORE_ITEMS.map((item) => (
            <ScoreCard key={item.key} item={item} score={scores[item.key]} />
          ))}
        </section>

        <section className="writing-feedback__body">
          <article className="writing-feedback__essay-panel">
            <div className="writing-feedback__essay">
              {activeTask.promptHtml && (
                <div
                  className="writing-feedback__prompt-card"
                  dangerouslySetInnerHTML={{ __html: activeTask.promptHtml }}
                />
              )}

              {splitParagraphs(activeTask.text).map((paragraph, index) => (
                <p key={`${activeTask.key}-${index}`}>
                  {renderHighlightedText(paragraph, highlightedDetails)}
                </p>
              ))}
            </div>
          </article>

          <aside className="writing-feedback__review-panel">
            <section className="writing-feedback__accordion">
              <AccordionHeader
                index={1}
                title="Overall"
                open={openSections.overall}
                onClick={() => toggleSection('overall')}
              />
              {openSections.overall && (
                <div className="writing-feedback__accordion-content">
                  {activeTask.teacherNote && (
                    <div className="writing-feedback__task-note">
                      {activeTask.teacherNote}
                    </div>
                  )}
                  {criteria.map((criterion) => (
                    <div className="writing-feedback__criterion-card" key={criterion.key}>
                      <h3>{criterion.title}</h3>
                      <ul>
                        {criterion.strengths && (
                          <li>
                            <strong className="writing-feedback__positive">Điểm mạnh: </strong>
                            {criterion.strengths}
                          </li>
                        )}
                        {criterion.weaknesses && (
                          <li>
                            <strong className="writing-feedback__negative">Điểm yếu: </strong>
                            {criterion.weaknesses}
                          </li>
                        )}
                      </ul>
                    </div>
                  ))}
                </div>
              )}
            </section>

            <section className="writing-feedback__accordion">
              <AccordionHeader
                index={2}
                title="Detail"
                open={openSections.detail}
                onClick={() => toggleSection('detail')}
              />
              {openSections.detail && (
                <div className="writing-feedback__detail-list">
                  {details.map((detail, index) => (
                    <button
                      type="button"
                      className={`writing-feedback__detail-card ${
                        index === activeDetailIndex ? 'is-active' : ''
                      }`}
                      key={`${detail.original}-${index}`}
                      onClick={() => setActiveDetailIndex(index)}
                    >
                      {detail.original && <span>{detail.original}</span>}
                      <ul>
                        {detail.explanation && (
                          <li>
                            <strong>Giải thích:</strong> {detail.explanation}
                          </li>
                        )}
                        {detail.correction && (
                          <li>
                            <strong>Cách sửa:</strong> {detail.correction}
                          </li>
                        )}
                      </ul>
                    </button>
                  ))}
                </div>
              )}
            </section>
          </aside>
        </section>
      </main>

      {tasks.length > 1 && (
        <footer className="test-layout__footer writing-feedback__pager">
          <div>
            <div className="test-layout__question-numbers writing-feedback__footer-tasks" aria-label="Writing tasks">
              {tasks.map((task, index) => (
                <button
                  type="button"
                  key={`${task.key}-task-footer`}
                  className={`test-layout__question-number-item ${
                    index === activeTaskIndex ? 'answered' : ''
                  }`}
                  title={task.label}
                  aria-label={task.label}
                  onClick={() => {
                    setActiveTaskIndex(index);
                    setActiveDetailIndex(0);
                  }}
                >
                  {task.taskNumber || index + 1}
                </button>
              ))}
            </div>
          </div>
        </footer>
      )}
    </div>
  );
};

export default WritingFeedbackResult;
