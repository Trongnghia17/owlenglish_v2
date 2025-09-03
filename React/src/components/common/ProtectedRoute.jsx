import { Navigate } from 'react-router-dom';
import useAuth from '@/features/auth/store/auth.store';

export default function ProtectedRoute({ children }) {
  const { token, user } = useAuth.getState(); // đọc trực tiếp để tránh re-render thừa
  if (!token || !user) return <Navigate to="/login" replace />;
  return children;
}
