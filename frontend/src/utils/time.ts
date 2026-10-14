const to12Hour = (time: string): string => {
  if (!time) return '';
  const [hours, minutes] = time.split(':').map(Number);
  if (isNaN(hours)) return time;
  const period = hours >= 12 ? 'PM' : 'AM';
  return `${hours % 12 || 12}:${String(minutes).padStart(2, '0')} ${period}`;
};

/** "8:00 AM - 10:00 AM" for a facility request; just the start time for older requests without an end time. */
export const formatTimeRange = (request: { start_time?: string; end_time?: string; time_of_event?: string }): string => {
  const start = request.start_time || request.time_of_event || '';
  return request.end_time ? `${to12Hour(start)} - ${to12Hour(request.end_time)}` : to12Hour(start);
};

/** "October 12, 2026" from "2026-10-12" or an ISO timestamp, without timezone shifting the day. */
export const formatDisplayDate = (date: string): string => {
  const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(date || '');
  if (!m) return date || '';
  return new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]))
    .toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};

/** Time range plus date, e.g. "7:00 AM - 9:00 AM, October 12, 2026". */
export const formatRequestSchedule = (request: { start_time?: string; end_time?: string; time_of_event?: string; date_of_event?: string }): string =>
  `${formatTimeRange(request)}, ${formatDisplayDate(request.date_of_event || '')}`;

/** Converts every 24-hour time inside a string to 12-hour AM/PM: "07:00 - 15:00" -> "7:00 AM - 3:00 PM". */
export const to12HourText = (text: string | null | undefined): string =>
  (text ?? '').replace(/\b([01]?\d|2[0-3]):([0-5]\d)(?::[0-5]\d)?\b/g, (_, h, m) => {
    const hours = Number(h);
    return `${hours % 12 || 12}:${m} ${hours >= 12 ? 'PM' : 'AM'}`;
  });
