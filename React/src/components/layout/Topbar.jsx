// src/components/layout/Topbar.jsx
import useAuth from '@/features/auth/store/auth.store';

export default function Topbar() {
  const { user } = useAuth();

  return (
    <header style={styles.header}>
      <h2>OWL English</h2>
      <div>
        {user && (
          <span>
            Xin chào 123, <b>{user.name}</b>
          </span>
        )}
      </div>
    </header>
  );
}

const styles = {
  header: {
    height: 60,
    background: '#fff',
    borderBottom: '1px solid #ddd',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: '0 20px',
  },
};
