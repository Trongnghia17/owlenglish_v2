import api from '@/lib/axios';

export const requestOtp = (payload) =>
  api.post('/auth/otp/request', payload); // {channel,destination,purpose}

export const verifyOtp = (payload) =>
  api.post('/auth/otp/verify', payload);   // expect {token, user}

export const passwordLogin = (payload) =>
  api.post('/auth/login', payload);

export const socialRedirect = (provider) => { 
  const map = {
    google: import.meta.env.VITE_OAUTH_GOOGLE_URL,
    facebook: import.meta.env.VITE_OAUTH_FACEBOOK_URL,
  };
  window.location.href = map[provider];
};
