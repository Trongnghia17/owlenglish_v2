import React from 'react';
import { createBrowserRouter, Navigate } from 'react-router-dom';
import ProtectedRoute from '@/components/common/ProtectedRoute';
import GuestRoute from '@/components/common/GuestRoute';
import AuthLayout from '@/components/layout/AuthLayout';
import MainLayout from '@/components/layout/MainLayout';
import Login from '@/features/auth/pages/Login';
import Register from '@/features/auth/pages/Register';
import VerifyOtp from '@/features/auth/pages/VerifyOtp';
import OAuthCallback from '@/features/auth/pages/OAuthCallback';
import Home from '../features/home/pages/Home';
import OnlineExamLibrary from '../features/exams/pages/OnlineExamLibrary';
import ExamInstructions from '../features/exams/pages/ExamInstructions';
import Profile from '../features/users/pages/Profile';
import ExamHistory from '../features/users/pages/ExamHistory';
import PaymentHistory from '../features/users/pages/PaymentHistory';
import LoginHistory from '../features/users/pages/LoginHistory';
const PublicShell = (
  <MainLayout />
);

export const router = createBrowserRouter([

  {
    // phục vụ cho việc chưa đăng nhập vẫn xem dc trang
    element: PublicShell, children: [
      { path: '/', element: <Home /> },
      { path: '/de-thi-online', element: <OnlineExamLibrary /> },
    ]
  },

  // Exam Instructions - Trang riêng không dùng layout
  {
    path: '/exam/instructions/:skillId',
    element: <ExamInstructions />,
  },
  {
    path: '/exam/instructions/:skillId/:sectionId',
    element: <ExamInstructions />,
  },

  {
    element: <GuestRoute><AuthLayout /></GuestRoute>,
    children: [
      { path: '/login', element: <Login /> },
      { path: '/register', element: <Register /> },
      { path: '/verify-otp', element: <VerifyOtp /> },
      { path: '/oauth/callback', element: <OAuthCallback /> },
    ],
  },

  // PROTECTED (ví dụ sau này: /courses, /attendance, ...)
  // phục vụ cho việc đăng nhập mới xem dc trang
  {
    element: <ProtectedRoute><MainLayout /></ProtectedRoute>,
    children: [
      // trang cá nhân
      { path: '/trang-ca-nhan', element: <Profile /> },
      { path: '/lich-su-lam-bai', element: <ExamHistory /> },
      { path: '/lich-su-thanh-toan', element: <PaymentHistory /> },
      { path: '/lich-su-giao-dich-owl', element: <ExamHistory /> },
      { path: '/lich-su-dang-nhap', element: <LoginHistory /> },
    ],
  },

  // fallback
  { path: '*', element: <Navigate to="/" /> },
]);
