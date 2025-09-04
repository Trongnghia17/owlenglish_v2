import { Outlet, Link, useLocation } from 'react-router-dom';
import useAuth from '@/features/auth/store/auth.store';

export default function DashboardLayout() {
  const { user, logout } = useAuth();
  const { pathname } = useLocation();

  return (
    <div className="app-shell">
      {/* Header */}
      <header className="app-header">
        <div className="brand">
          <span className="logo">🦉</span>
          <span>OWL English</span>
        </div>
        <div className="spacer" />
        <div className="me">
          <span>{user?.name || 'User'}</span>
          <button className="btn" onClick={logout}>Logout</button>
        </div>
      </header>

      <div className="app-body">
        {/* Sidebar */}
        <aside className="app-sidebar">
          <nav>
            <Link className={navCls(pathname === '/')} to="/">Trang chủ</Link>
            <Link className={navCls(pathname.startsWith('/courses'))} to="/courses">Khoá học</Link>
            <Link className={navCls(pathname.startsWith('/attendance'))} to="/attendance/me">Điểm danh của tôi</Link>
          </nav>
        </aside>

        {/* Content */}
        <main className="app-content">
          <Outlet />
        </main>
      </div>

      {/* Footer */}
      <footer className="app-footer">
        <span>© {new Date().getFullYear()} OWL English</span>
      </footer>
    </div>
  );
}

function navCls(active) {
  return `nav-link ${active ? 'active' : ''}`;
}
