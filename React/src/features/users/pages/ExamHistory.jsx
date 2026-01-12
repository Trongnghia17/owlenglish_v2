import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { getExamHistory } from '@/features/exams/api/exams.api';
import listening from '@/assets/images/writing.svg';
import listening_active from '@/assets/images/listening_active.svg';
import speaking from '@/assets/images/speaking.svg';
import speaking_active from '@/assets/images/icon_mic.svg';
import reading from '@/assets/images/speaking.svg';
import reading_active from '@/assets/images/icon_book.svg';
import writing from '@/assets/images/writing.svg';
import writing_active from '@/assets/images/icon_edit.svg';
import './ExamHistory.css';

const TABS = [
  { key: 'Listening', label: 'Listening', icon: listening, activeIcon: listening_active },
  { key: 'Speaking',  label: 'Speaking',  icon: speaking,  activeIcon: speaking_active },
  { key: 'Reading',   label: 'Reading',   icon: reading,   activeIcon: reading_active },
  { key: 'Writing',   label: 'Writing',   icon: writing,   activeIcon: writing_active },
];

export default function ExamHistory() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [exams, setExams] = useState([]);
  const [activeTab, setActiveTab] = useState('Listening');
  const [error, setError] = useState(null);

  useEffect(() => {
    let mounted = true;
    const fetchHistory = async () => {
      try {
        setLoading(true);
        setError(null);
        
        // Gọi API với skill type
        const skillTypeMap = {
          'Listening': 'listening',
          'Speaking': 'speaking',
          'Reading': 'reading',
          'Writing': 'writing',
        };
        
        const response = await getExamHistory(skillTypeMap[activeTab]);
        
        console.log('Exam history response:', response.data);
        
        if (!mounted) return;
        
        if (response.data.success) {
          setExams(response.data.data || []);
          console.log('Loaded exams:', response.data.data);
        } else {
          setError('Không thể tải lịch sử làm bài');
        }
      } catch (err) {
        if (!mounted) return;
        console.error('Error fetching exam history:', err);
        console.error('Error response:', err.response?.data);
        setError(err.response?.data?.message || 'Có lỗi xảy ra khi tải dữ liệu');
        setExams([]);
      } finally {
        if (mounted) setLoading(false);
      }
    };

    fetchHistory();

    return () => { mounted = false; };
  }, [activeTab]); // Reload when tab changes

  const rows = exams;

  return (
    <div className="exam-history">
      <h3 className="eh-title">Lịch sử làm bài</h3>

      <div className="eh-card">
        <div className="eh-tabs" role="tablist">
          {TABS.map((t) => {
            const isActive = t.key === activeTab;
            return (
              <button
                key={t.key}
                className={`eh-tab ${isActive ? 'active' : ''}`}
                onClick={() => setActiveTab(t.key)}
                role="tab"
                aria-selected={isActive}
              >
                <img
                  src={isActive ? (t.activeIcon || t.icon) : t.icon}
                  alt={t.label}
                  className={`eh-tab-icon ${isActive ? 'active' : ''}`}
                />
                <span className="eh-tab-label">{t.label}</span>
              </button>
            );
          })}
        </div>

        <div className="eh-table-wrap">
          {loading ? (
            <div className="eh-loading">Đang tải...</div>
          ) : (
            <>
              {error && <div className="eh-error">{error}</div>}
              <table className="eh-table">
                <thead>
                  <tr>
                    <th>Đề thi</th>
                    <th>Thời gian làm bài</th>
                    <th>Ngày làm bài</th>
                    <th>Câu đúng</th>
                    <th>Câu sai</th>
                    <th>Câu bỏ qua</th>
                    <th>Tỉ lệ đúng</th>
                  </tr>
                </thead>
                <tbody>
                  {rows.length === 0 ? (
                    <tr className="eh-empty">
                      <td colSpan="7">Chưa có lịch sử cho phần này.</td>
                    </tr>
                  ) : (
                    rows.map((r) => {
                      const total = (r.correct || 0) + (r.wrong || 0) + (r.skipped || 0) || 1;
                      const acc = Math.round(((r.correct || 0) / total) * 100);
                      return (
                        <tr key={r.id}>
                          <td className="eh-title-cell">
                            <a 
                              className="eh-link" 
                              href="#!" 
                              onClick={(e) => {
                                e.preventDefault();
                                navigate(`/test-result/${r.id}`);
                              }}
                            >
                              {r.title}
                            </a>
                          </td>
                          <td>{r.duration}</td>
                          <td>{new Date(r.date).toLocaleDateString('vi-VN')}</td>
                          <td>{r.correct ?? 0}</td>
                          <td>{r.wrong ?? 0}</td>
                          <td>{r.skipped ?? 0}</td>
                          <td>{acc}%</td>
                        </tr>
                      );
                    })
                  )}
                </tbody>
              </table>
            </>
          )}
        </div>
      </div>
    </div>
  );
}