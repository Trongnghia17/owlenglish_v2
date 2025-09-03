import { useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import useAuth from '@/features/auth/store/auth.store';
import { getMe } from '@/features/users/api/users.api';

export default function OAuthCallback() {
  const [sp] = useSearchParams();
  const nav = useNavigate();
  const { setToken, setUser } = useAuth();

  useEffect(() => {
    (async () => {
      const token = sp.get('token');
      const provider = sp.get('provider') || 'google';
      if (!token) {
        nav('/login?error=missing_token', { replace: true });
        return;
      }
      try {
        setToken(token);
        const me = await getMe(); // gọi /me để lấy thông tin người dùng
        setUser(me.data);
        // chuyển vào trang chính (sau này bạn đổi thành /dashboard hoặc /courses)
        nav('/', { replace: true });
      } catch (e) {
        setToken(null);
        setUser(null);
        nav('/login?error=bad_token', { replace: true });
      }
    })();
  }, [sp, nav, setToken, setUser]);

  return <div style={{ textAlign:'center', padding:40 }}>Đang đăng nhập…</div>;
}
