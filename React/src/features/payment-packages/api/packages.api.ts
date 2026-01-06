import axios from '@/lib/axios';
const VITE_API_LARAVEL_SERVER = import.meta.env.VITE_API_BASE_URL;

export const getPaymentPackages = async () => {
  const response = await axios.get(
    `${VITE_API_LARAVEL_SERVER}/api/public/payment-packages`
  );
  return response.data;
};

export const createPayment = async (packageId) => {
  const response = await axios.post(
    `${VITE_API_LARAVEL_SERVER}/api/payments/create`,
    { package_id: packageId }
  );
  return response.data;
};

export const cancelPayment = (orderCode) => {
    return axios.get(`${VITE_API_LARAVEL_SERVER}/api/payment/cancel/${orderCode}`);
};

export const successPayment = (orderCode) => {
    return axios.get(
        `${VITE_API_LARAVEL_SERVER}/api/payment/success/${orderCode}`
    );
};



