import React from 'react';
import { createBrowserRouter, Navigate } from 'react-router-dom';
import ProtectedRoute from '@/components/common/ProtectedRoute';
import AuthLayout from '@/components/layout/AuthLayout';
import DashboardLayout from '@/components/layout/DashboardLayout';

// Auth pages
import Login from '@/features/auth/pages/Login';
import Register from '@/features/auth/pages/Register';
import OAuthCallback from '@/features/auth/pages/OAuthCallback';

// (tạm thời bỏ MyAttendance / SessionCheckin nếu bạn chưa cần)

export const router = createBrowserRouter([
  // điều hướng mọi đường dẫn gốc sang /login
  { path: '/', element: <Navigate to="/login" /> },

  {
    element: <AuthLayout />,
    children: [
      { path: '/login', element: <Login /> },
      { path: '/register', element: <Register /> },
      { path: '/oauth/callback', element: <OAuthCallback /> },
    ],
  },

  // (giữ khu vực protected để sau này dùng)
  {
    element: (
      <ProtectedRoute>
        <DashboardLayout />
      </ProtectedRoute>
    ),
    children: [
      // thêm các route sau này (ví dụ /dashboard, /courses, ...)
    ],
  },

  // // không khớp gì thì về /login
  // { path: '*', element: <Navigate to="/login" /> },
]);
