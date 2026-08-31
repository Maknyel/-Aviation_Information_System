import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { API_URL } from '@/config/api';

(window as any).Pusher = Pusher;

let echoInstance: Echo<'pusher'> | null = null;

// Base URL Laravel serves the broadcasting auth route from (not under /api).
const authEndpoint = `${API_URL.replace(/\/api\/?$/, '')}/broadcasting/auth`;

export function initEcho(): Echo<'pusher'> | null {
  const token = localStorage.getItem('token');
  if (!token) return null;

  if (echoInstance) return echoInstance;

  const key = import.meta.env.VITE_PUSHER_APP_KEY;
  const cluster = import.meta.env.VITE_PUSHER_APP_CLUSTER;
  if (!key || !cluster) {
    console.warn('Pusher not configured (VITE_PUSHER_APP_KEY/VITE_PUSHER_APP_CLUSTER missing) — skipping realtime setup.');
    return null;
  }

  try {
    echoInstance = new Echo({
      broadcaster: 'pusher',
      key,
      cluster,
      forceTLS: true,
      authEndpoint,
      auth: {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
      },
    });
  } catch (e) {
    console.error('Failed to initialize realtime connection:', e);
    echoInstance = null;
  }

  return echoInstance;
}

export function getEcho(): Echo<'pusher'> | null {
  return echoInstance ?? initEcho();
}

export function teardownEcho() {
  try {
    echoInstance?.disconnect();
  } catch (e) {
    console.error('Error disconnecting realtime connection:', e);
  } finally {
    echoInstance = null;
  }
}
