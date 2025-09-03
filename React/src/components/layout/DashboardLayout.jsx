import { Outlet, Link } from 'react-router-dom';
import useAuth from '@/features/auth/store/auth.store';

export default function DashboardLayout() {
  const { user, logout } = useAuth();

  return (
    <div style={{ display: 'grid', gridTemplateColumns: '240px 1fr', minHeight: '100vh' }}>
      <aside style={{ borderRight: '1px solid #eee', padding: 16 }}>
        <h3>OWL</h3>
        <nav style={{ display: 'grid', gap: 8 }}>
          <Link to="/courses">Courses</Link>
          <Link to="/attendance/me">My Attendance</Link>
        </nav>
      </aside>
      <main style={{ padding: 24 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 16 }}>
          <div>Xin chào, {user?.name || 'User'}</div>
          <button onClick={logout}>Logout</button>
        </div>
        <Outlet />
      </main>
    </div>
  );
}
