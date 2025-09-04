// src/components/layout/MainLayout.jsx
import Sidebar from './Sidebar';
import Topbar from './Topbar';
import './MainLayout.css';

export default function MainLayout({ children }) {
  return (
    <div className="main-layout">
      {/* Header */}
      <Topbar />

      <div className="main-body">
        {/* Sidebar */}
        <Sidebar />

        {/* Nội dung chính */}
        <div className="main-content">
          {children}
        </div>
      </div>

      {/* Footer */}
      <footer className="main-footer">
        © {new Date().getFullYear()} OWL English - All rights reserved.
      </footer>
    </div>
  );
}
