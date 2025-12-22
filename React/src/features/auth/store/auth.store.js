import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { deviceAction } from '../../users/api/users.api';

const useAuth = create()(
  persist(
    (set, get) => ({
      token: null,
      user: null,
      deviceId: null,
      initialized: false,
      setToken: (t) => set({ token: t }),
      setUser: (u) => set({ user: u }),
      setDeviceId: (id) => set({ deviceId: id }),
      logout: async () => {
        const { token, deviceId } = get();
        if (token && deviceId) {
          try {
            await deviceAction({ device_id: deviceId, action: 'logout' });
          } catch (err) {
            console.error('Không thể logout thiết bị:', err);
          }
        }
        set({ token: null, user: null, deviceId: null });
      },
      setInitialized: (v) => set({ initialized: v }),
    }),
    {
      name: 'owl-auth',
      onRehydrateStorage: () => (state) => {
        // khi state load xong từ localStorage
        state?.setInitialized(true);
      },
    }
  )
);

export default useAuth;
