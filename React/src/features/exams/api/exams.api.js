import api from '@/lib/axios';

// Lấy danh sách exams
export const getExams = (params) => 
  api.get('/api/public/exams', { params });

// Lấy chi tiết exam
export const getExamById = (id, params) => 
  api.get(`/api/public/exams/${id}`, { params });

// Lấy chi tiết test
export const getTestById = (id, params) => 
  api.get(`/api/public/tests/${id}`, { params });

// Lấy danh sách skills
export const getSkills = (params) => 
  api.get('/api/public/skills', { params });

// Lấy chi tiết skill
export const getSkillById = (id, params) => 
  api.get(`/api/public/skills/${id}`, { params });
