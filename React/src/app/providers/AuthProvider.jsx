import { useEffect } from 'react';
import useAuth from '@/features/auth/store/auth.store';
import { getMe } from '@/features/users/api/users.api';

export default function AuthProvider({ children }) {
  const { setUser, token } = useAuth();

  useEffect(() => {
    (async () => {
      if (!token) { setUser(null); return; }
      try {
        const res = await getMe();
        setUser(res.data);
      } catch {
        setUser(null);
      }
    })();
  }, [token, setUser]);

  return children;
}
