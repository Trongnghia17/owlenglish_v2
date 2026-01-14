import api from '@/lib/axios';

/**
 * Get all notes for a specific test
 * @param {string} testType - 'exam', 'skill', 'section', or 'test'
 * @param {number} testId - ID of the test
 */
export const getNotes = (testType, testId) =>
  api.get('/api/user-notes', {
    params: { test_type: testType, test_id: testId }
  });

/**
 * Create a new note
 * @param {Object} noteData
 * @param {string} noteData.test_type - 'exam', 'skill', 'section', or 'test'
 * @param {number} noteData.test_id - ID of the test
 * @param {string} noteData.title - Note title (optional)
 * @param {string} noteData.content - Note content
 * @param {string} noteData.selected_text - Selected text (optional)
 */
export const createNote = (noteData) =>
  api.post('/api/user-notes', noteData);

/**
 * Get a specific note
 * @param {number} id - Note ID
 */
export const getNote = (id) =>
  api.get(`/api/user-notes/${id}`);

/**
 * Update a note
 * @param {number} id - Note ID
 * @param {Object} noteData
 * @param {string} noteData.title - Note title (optional)
 * @param {string} noteData.content - Note content
 * @param {string} noteData.selected_text - Selected text (optional)
 */
export const updateNote = (id, noteData) =>
  api.put(`/api/user-notes/${id}`, noteData);

/**
 * Delete a note
 * @param {number} id - Note ID
 */
export const deleteNote = (id) =>
  api.delete(`/api/user-notes/${id}`);
