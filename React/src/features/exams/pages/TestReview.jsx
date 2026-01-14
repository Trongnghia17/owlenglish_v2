import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { getTestResult } from '../api/exams.api';
import ReadingTest from './ReadingTest';

export default function TestReview() {
  const { resultId } = useParams();
  const navigate = useNavigate();
  const [result, setResult] = useState(null);
  const [loading, setLoading] = useState(true);

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
      <div style={{ 
        display: 'flex', 
        justifyContent: 'center', 
        alignItems: 'center', 
        height: '100vh' 
      }}>
        <div>Đang tải kết quả...</div>
      </div>
    );
  }

  if (!result) {
    return (
      <div style={{ 
        display: 'flex', 
        flexDirection: 'column',
        justifyContent: 'center', 
        alignItems: 'center', 
        height: '100vh',
        gap: '1rem'
      }}>
        <div>Không tìm thấy kết quả bài thi</div>
        <button onClick={() => navigate(-1)}>Quay lại</button>
      </div>
    );
  }

  // Chuyển đổi result data để tương thích với ReadingTest
  const skillId = result.exam_skill_id;
  const sectionId = result.exam_section_id;

  return (
    <ReadingTest 
      reviewMode={true}
      reviewData={result}
      preloadedSkillId={skillId}
      preloadedSectionId={sectionId}
    />
  );
}
