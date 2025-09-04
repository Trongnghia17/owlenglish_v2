import React from 'react';
import { createBrowserRouter, Navigate } from 'react-router-dom';
import ProtectedRoute from '@/components/common/ProtectedRoute';
import GuestRoute from '@/components/common/GuestRoute';
import AuthLayout from '@/components/layout/AuthLayout';
import DashboardLayout from '@/components/layout/DashboardLayout';

import Login from '@/features/auth/pages/Login';
import Register from '@/features/auth/pages/Register';
import OAuthCallback from '@/features/auth/pages/OAuthCallback';
import Home from '@/features/home/pages/Home';

const PublicShell = (
  <DashboardLayout /> 
);

export const router = createBrowserRouter([

  { element: PublicShell, children: [
      { path: '/', element: <Home /> },           
    ]
  },

  {
    element: <GuestRoute><AuthLayout /></GuestRoute>,
    children: [
      { path: '/login', element: <Login /> },
      { path: '/register', element: <Register /> },
      { path: '/oauth/callback', element: <OAuthCallback /> },
    ],
  },

  // PROTECTED (ví dụ sau này: /courses, /attendance, ...)
  // {
  //   element: <ProtectedRoute><DashboardLayout /></ProtectedRoute>,
  //   children: [
  //     { path: '/courses', element: <CourseList /> },
  //     { path: '/attendance/me', element: <MyAttendance /> },
  //   ],
  // },

  // fallback
  { path: '*', element: <Navigate to="/" /> },
]);
