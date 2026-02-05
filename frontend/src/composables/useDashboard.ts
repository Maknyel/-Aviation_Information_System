import { ref } from 'vue';
import { API_URL } from '@/config/api';

export interface Statistics {
  facility_requests_today: number;
  facility_pending_requests: number;
  facility_approved_requests: number;
  work_orders_today: number;
  pending_maintenance: number;
  urgent_repairs: number;
}

export interface UpcomingRequest {
  id: number;
  type: 'facility' | 'work_order';
  title: string;
  subtitle: string;
  date: string;
  time: string;
  status: string;
  priority?: string;
  user: string;
}

export interface ChartData {
  labels: string[];
  values: number[];
}

export interface CalendarEvent {
  id: number;
  type: 'facility' | 'work_order';
  title: string;
  date: string;
  status: string;
  priority?: string;
}

export function useDashboard() {
  const statistics = ref<Statistics>({
    facility_requests_today: 0,
    facility_pending_requests: 0,
    facility_approved_requests: 0,
    work_orders_today: 0,
    pending_maintenance: 0,
    urgent_repairs: 0,
  });

  const upcomingRequests = ref<UpcomingRequest[]>([]);
  const venueUsageData = ref<ChartData>({ labels: [], values: [] });
  const maintenanceData = ref<ChartData>({ labels: [], values: [] });
  const calendarEvents = ref<CalendarEvent[]>([]);
  const loading = ref(false);
  const error = ref('');

  const getAuthHeaders = () => {
    const token = localStorage.getItem('token');
    return {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
    };
  };

  const fetchStatistics = async () => {
    try {
      const response = await fetch(`${API_URL}/dashboard/statistics`, {
        headers: getAuthHeaders(),
      });

      const data = await response.json();
      if (data.success) {
        statistics.value = data.data;
      }
    } catch (err) {
      console.error('Error fetching statistics:', err);
      error.value = 'Failed to load statistics';
    }
  };

  const fetchUpcomingRequests = async () => {
    try {
      const response = await fetch(`${API_URL}/dashboard/upcoming-requests`, {
        headers: getAuthHeaders(),
      });

      const data = await response.json();
      if (data.success) {
        upcomingRequests.value = data.data;
      }
    } catch (err) {
      console.error('Error fetching upcoming requests:', err);
      error.value = 'Failed to load upcoming requests';
    }
  };

  const fetchVenueUsage = async () => {
    try {
      const response = await fetch(`${API_URL}/dashboard/venue-usage`, {
        headers: getAuthHeaders(),
      });

      const data = await response.json();
      if (data.success) {
        venueUsageData.value = data.data;
      }
    } catch (err) {
      console.error('Error fetching venue usage:', err);
      error.value = 'Failed to load venue usage data';
    }
  };

  const fetchMaintenanceData = async () => {
    try {
      const response = await fetch(`${API_URL}/dashboard/maintenance-data`, {
        headers: getAuthHeaders(),
      });

      const data = await response.json();
      if (data.success) {
        maintenanceData.value = data.data;
      }
    } catch (err) {
      console.error('Error fetching maintenance data:', err);
      error.value = 'Failed to load maintenance data';
    }
  };

  const fetchCalendarEvents = async (month?: number, year?: number) => {
    try {
      const params = new URLSearchParams();
      if (month) params.append('month', month.toString());
      if (year) params.append('year', year.toString());

      const response = await fetch(`${API_URL}/dashboard/calendar-events?${params}`, {
        headers: getAuthHeaders(),
      });

      const data = await response.json();
      if (data.success) {
        calendarEvents.value = data.data;
      }
    } catch (err) {
      console.error('Error fetching calendar events:', err);
      error.value = 'Failed to load calendar events';
    }
  };

  const loadAllDashboardData = async () => {
    loading.value = true;
    error.value = '';

    await Promise.all([
      fetchStatistics(),
      fetchUpcomingRequests(),
      fetchVenueUsage(),
      fetchMaintenanceData(),
      fetchCalendarEvents(),
    ]);

    loading.value = false;
  };

  return {
    statistics,
    upcomingRequests,
    venueUsageData,
    maintenanceData,
    calendarEvents,
    loading,
    error,
    fetchStatistics,
    fetchUpcomingRequests,
    fetchVenueUsage,
    fetchMaintenanceData,
    fetchCalendarEvents,
    loadAllDashboardData,
  };
}
