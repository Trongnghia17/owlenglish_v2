import axios from '@/lib/axios';
const VITE_API_LARAVEL_SERVER = import.meta.env.VITE_API_BASE_URL;
export const getMe = () => axios.get('/api/me');

export const updateProfile = async (formData: FormData) => {
  const response = await axios.post(
    `${VITE_API_LARAVEL_SERVER}/api/user/profile`,
    formData,
    {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    }
  );

  return response.data;
};


