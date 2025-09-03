import { create } from 'zustand';
import { persist } from 'zustand/middleware';

const useAuth = create()(
  persist(
    (set) => ({
      token: null,
      user: null,
      setToken: (t) => set({ token: t }),
      setUser: (u) => set({ user: u }),
      logout: () => set({ token: null, user: null }),
    }),
    { name: 'owl-auth' }
  )
);

export default useAuth;
