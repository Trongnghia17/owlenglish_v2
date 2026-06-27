export const parseWritingFeedback = (feedback) => {
  if (!feedback) return null;
  if (typeof feedback === 'string') {
    try {
      return JSON.parse(feedback);
    } catch {
      return null;
    }
  }

  return feedback;
};

export const hasWritingFeedback = (result) => {
  const feedback = parseWritingFeedback(result?.writing_feedback);
  const tasks = Array.isArray(feedback?.tasks) ? feedback.tasks : [];

  return Boolean(
    feedback &&
      (feedback.scores ||
        feedback.teacher_note ||
        tasks.some(
          (task) =>
            task?.scores ||
            task?.teacher_note ||
            (Array.isArray(task?.criteria) && task.criteria.length > 0) ||
            (Array.isArray(task?.details) && task.details.length > 0)
        ) ||
        (Array.isArray(feedback.criteria) && feedback.criteria.length > 0) ||
        (Array.isArray(feedback.details) && feedback.details.length > 0))
  );
};
