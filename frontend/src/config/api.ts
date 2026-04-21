const isLocal = typeof window !== 'undefined' &&
  (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1');

export const API_URL = isLocal
  ? (import.meta.env.VITE_API_URL_LOCAL || 'http://localhost:8000/api')
  : (import.meta.env.VITE_API_URL || 'http://localhost:8000/api');

export default {
  API_URL,
};
