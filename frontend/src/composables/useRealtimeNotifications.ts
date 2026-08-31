import { onMounted, onBeforeUnmount } from 'vue';
import { getEcho } from '@/echo';
import { getStoredUser } from '@/utils/auth';

/**
 * Subscribes to the current user's private notifications channel and invokes
 * `onNotification` whenever a new notification is pushed in realtime (e.g. a new
 * request submitted, or a status change) — used to auto-refresh page data instead
 * of waiting for a manual reload or the fallback poll.
 */
export function useRealtimeNotifications(onNotification: (payload: any) => void) {
  let channelName: string | null = null;

  onMounted(() => {
    const user = getStoredUser();
    if (!user?.id) return;

    const echo = getEcho();
    if (!echo) return;

    channelName = `notifications.${user.id}`;
    echo.private(channelName).listen('.notification.created', onNotification);
  });

  onBeforeUnmount(() => {
    if (channelName) {
      getEcho()?.leave(channelName);
    }
  });
}
