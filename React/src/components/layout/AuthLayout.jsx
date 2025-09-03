import { Outlet } from 'react-router-dom';

export default function AuthLayout() {
  return (
    <div style={{ maxWidth: 420, margin: '60px auto' }}>
      <Outlet />
    </div>
  );
}
