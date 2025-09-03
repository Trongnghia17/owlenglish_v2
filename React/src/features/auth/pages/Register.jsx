import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import useAuth from '@/features/auth/store/auth.store';
import api from '@/lib/axios';

export default function Register() {
  const nav = useNavigate();
  const { setToken, setUser } = useAuth();

  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  const socialRedirect = (provider) => {
    const urls = {
      google: import.meta.env.VITE_OAUTH_GOOGLE_URL,
      facebook: import.meta.env.VITE_OAUTH_FACEBOOK_URL,
    };
    window.location.href = urls[provider];
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    try {
      // gọi API đăng ký tài khoản (backend trả { token, user })
      const res = await api.post('/register', { name, phone, password });
      setToken(res.data.token);
      setUser(res.data.user);
      nav('/'); // chuyển về trang chủ hoặc khu vực protected
    } catch (err) {
      setError(err?.response?.data?.message || 'Đăng ký thất bại');
    }
  };

  return (
    <div style={styles.wrapper}>
      <div style={styles.card}>
        <h2 style={{ marginBottom: 8 }}>Đăng ký OWL</h2>
        <p style={{ color: '#666', marginBottom: 16 }}>Chọn một phương thức bên dưới</p>

        <form onSubmit={handleSubmit} style={{ display: 'grid', gap: 8 }}>
          <input
            style={styles.input}
            type="text"
            placeholder="Tên của bạn"
            value={name}
            onChange={(e) => setName(e.target.value)}
            required
          />
          <input
            style={styles.input}
            type="text"
            placeholder="Số điện thoại"
            value={phone}
            onChange={(e) => setPhone(e.target.value)}
            required
          />
          <input
            style={styles.input}
            type="password"
            placeholder="Mật khẩu"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
          <button style={styles.primaryBtn} type="submit">Đăng ký</button>
        </form>
        {error && <div style={{ color: 'red', marginTop: 8 }}>{error}</div>}

        <div style={{ margin: '12px 0', textAlign: 'center', color: '#888' }}>— hoặc —</div>

        <div style={{ display: 'grid', gap: 8 }}>
          <button style={styles.socialBtn} onClick={() => socialRedirect('google')}>
            Đăng ký với Google
          </button>
          <button style={styles.socialBtn} onClick={() => socialRedirect('facebook')}>
            Đăng ký với Facebook
          </button>
        </div>

        <div style={{ marginTop: 16, fontSize: 14 }}>
          Đã có tài khoản? <Link to="/login">Đăng nhập</Link>
        </div>
      </div>
    </div>
  );
}

const styles = {
  wrapper: {
    minHeight: '70vh',
    display: 'grid',
    placeItems: 'center',
  },
  card: {
    width: 380,
    maxWidth: '92vw',
    border: '1px solid #eee',
    borderRadius: 12,
    padding: 20,
    boxShadow: '0 6px 20px rgba(0,0,0,0.06)',
    background: '#fff',
  },
  input: {
    height: 40,
    padding: '0 12px',
    borderRadius: 8,
    border: '1px solid #ddd',
    outline: 'none',
  },
  primaryBtn: {
    height: 42,
    border: 'none',
    borderRadius: 8,
    background: '#1677ff',
    color: '#fff',
    cursor: 'pointer',
    fontWeight: 600,
  },
  socialBtn: {
    height: 42,
    border: '1px solid #ddd',
    borderRadius: 8,
    background: '#fff',
    cursor: 'pointer',
    fontWeight: 600,
  },
};