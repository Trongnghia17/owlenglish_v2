// src/components/layout/Sidebar.jsx
import { Link } from 'react-router-dom';

export default function Sidebar() {
  return (
    <aside style={styles.sidebar}>
      <h3 style={styles.logo}>OWL</h3>
      <nav style={styles.nav}>
        <Link to="/">Trang chủ</Link>
        <Link to="/courses">Khóa học</Link>
        <Link to="/profile">Hồ sơ</Link>
      </nav>
    </aside>
  );
}

const styles = {
  sidebar: {
    width: 200,
    background: '#1677ff',
    color: '#fff',
    padding: 20,
  },
  logo: {
    marginBottom: 20,
  },
  nav: {
    display: 'grid',
    gap: 12,
  },
};
