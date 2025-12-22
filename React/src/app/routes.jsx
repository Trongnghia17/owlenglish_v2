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
import ExamPackage from '../features/exams/pages/ExamPackage';
import ExamPackageDetail from '../features/exams/pages/ExamPackageDetail';
import ExamInstructions from '../features/exams/pages/ExamInstructions';
import ReadingTest from '../features/exams/pages/ReadingTest';
import WritingTest from '../features/exams/pages/WritingTest';
import SpeakingTest from '../features/exams/pages/SpeakingTest';
import ListeningTest from '../features/exams/pages/ListeningTest';
import Profile from '../features/users/pages/Profile';
import ExamHistory from '../features/users/pages/ExamHistory';
import PaymentHistory from '../features/users/pages/PaymentHistory';
import LoginHistory from '../features/users/pages/LoginHistory';
import ListeningToeic from '../features/toeic/pages/Listening';
import WritingToeic from '../features/toeic/pages/Writing';
import SpeakingToeic from '../features/toeic/pages/Speaking';
import ReadingToeic from '../features/toeic/pages/Reading';
import PackageList from '../features/payment-packages/pages/PackageList';
const PublicShell = (
  <MainLayout />
);

export const router = createBrowserRouter([

  {
    // phục vụ cho việc chưa đăng nhập vẫn xem dc trang
    element: PublicShell, children: [
      { path: '/', element: <Home /> },
      { path: '/de-thi-online', element: <OnlineExamLibrary /> },
      { path: '/bo-de/:examType', element: <ExamPackage /> },
      { path: '/bo-de/:examType/:examId', element: <ExamPackageDetail /> },
      { path: '/goi-nap-tien', element: <PackageList /> },
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

  // Exam Test Pages - Trang làm bài
  // Reading
  {
    path: '/exam/section/:skillId/:sectionId/test/reading',
    element: <ReadingTest />,
  },
  {
    path: '/exam/full/:skillId/test/reading',
    element: <ReadingTest />,
  },
  
  // Writing
  {
    path: '/exam/section/:skillId/:sectionId/test/writing',
    element: <WritingTest />,
  },
  {
    path: '/exam/full/:skillId/test/writing',
    element: <WritingTest />,
  },
  
  // Speaking
  {
    path: '/exam/section/:skillId/:sectionId/test/speaking',
    element: <SpeakingTest />,
  },
  {
    path: '/exam/full/:skillId/test/speaking',
    element: <SpeakingTest />,
  },
  
  // Listening
  {
    path: '/exam/section/:skillId/:sectionId/test/listening',
    element: <ListeningTest />,
  },
  {
    path: '/exam/full/:skillId/test/listening',
    element: <ListeningTest />,
  },

  // phần thi TOEIC
  { path: '/toeic-listening/:skillId', element: <ListeningToeic /> },
  { path: '/toeic-listening/:skillId/:sectionId', element: <ListeningToeic /> },
  { path: '/toeic-writing/:skillId', element: <WritingToeic /> },
  { path: '/toeic-writing/:skillId/:sectionId', element: <WritingToeic /> },
  { path: '/toeic-speaking/:skillId', element: <SpeakingToeic /> },
  { path: '/toeic-speaking/:skillId/:sectionId', element: <SpeakingToeic /> },
  { path: '/toeic-reading/:skillId', element: <ReadingToeic /> },
  { path: '/toeic-reading/:skillId/:sectionId', element: <ReadingToeic /> },

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
