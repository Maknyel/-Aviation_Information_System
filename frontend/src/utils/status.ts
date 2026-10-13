/**
 * Request status presentation shared by every page.
 * Approved = green, Disapproved (stored as "rejected") = red.
 * The classes live in assets/tailwind.css so these two colors stay fixed
 * even when the theme colors change.
 */
export const statusLabel = (status: string): string => {
  if (status === 'rejected') return 'Disapproved';
  return (status || '').replace(/_/g, ' ').replace(/^\w/, c => c.toUpperCase());
};

export const statusBadgeClass = (status: string): string => {
  const map: Record<string, string> = {
    approved: 'status-badge status-approved',
    rejected: 'status-badge status-disapproved',
    pending: 'status-badge status-pending',
    canceled: 'status-badge status-canceled',
    in_progress: 'status-badge status-in-progress',
    completed: 'status-badge status-approved',
  };
  return map[status] || 'status-badge status-canceled';
};

export const statusDotClass = (status: string): string => {
  const map: Record<string, string> = {
    approved: 'status-dot-approved',
    completed: 'status-dot-approved',
    rejected: 'status-dot-disapproved',
    pending: 'bg-yellow-500',
    canceled: 'bg-gray-500',
    in_progress: 'bg-blue-500',
  };
  return map[status] || 'bg-gray-500';
};

export const STATUS_CHART_COLORS = { approved: '#16A34A', disapproved: '#DC2626' };

/** Solid fill for calendar events: green approved, yellow pending, red disapproved. */
export const statusEventClass = (status: string): string => {
  const map: Record<string, string> = {
    approved: 'event-approved',
    completed: 'event-approved',
    pending: 'event-pending',
    rejected: 'event-disapproved',
  };
  return map[status] || 'event-other';
};
