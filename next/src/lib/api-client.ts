// src/lib/api-client.ts
import axios from 'axios';

const apiClient = axios.create({
  baseURL: process.env.GNUBOARD_URL,
  withCredentials: true, // 쿠키 공유를 위해 필요
  headers: {
    'Content-Type': 'application/json',
  },
});

// 요청 인터셉터
apiClient.interceptors.request.use((config) => {
  // API 키가 필요한 경우 추가
  if (process.env.GNUBOARD_API_KEY) {
    config.headers['X-API-KEY'] = process.env.GNUBOARD_API_KEY;
  }
  return config;
});

// 응답 인터셉터
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    console.error('API Error:', error);
    return Promise.reject(error);
  }
);

export default apiClient;