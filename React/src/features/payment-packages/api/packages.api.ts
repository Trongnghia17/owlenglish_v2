import axios from '@/lib/axios';
const VITE_API_LARAVEL_SERVER = import.meta.env.VITE_API_BASE_URL;

export const getPaymentPackages = async () => {
  const response = await axios.get(
    `${VITE_API_LARAVEL_SERVER}/api/public/payment-packages`
  );
  return response.data;
};