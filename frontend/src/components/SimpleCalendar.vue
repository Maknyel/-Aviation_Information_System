<template>
  <div class="calendar-widget">
    <!-- Year & Month Dropdowns -->
    <div class="flex items-center gap-2 mb-4">
      <!-- Month Dropdown -->
      <div class="relative flex-1">
        <button
          @click="showMonthDropdown = !showMonthDropdown; showYearDropdown = false"
          class="w-full flex items-center justify-between px-3 py-2 text-sm font-semibold text-gray-800 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors"
        >
          {{ monthName }}
          <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        <div
          v-if="showMonthDropdown"
          class="absolute left-0 mt-1 w-full bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50 max-h-48 overflow-y-auto"
        >
          <button
            v-for="(month, index) in monthNames"
            :key="index"
            @click="setMonth(index); showMonthDropdown = false"
            :disabled="!isMonthSelectable(index)"
            :class="[
              'w-full text-left px-3 py-1.5 text-sm transition-colors',
              currentDate.getMonth() === index ? 'bg-aviation-olive/10 text-aviation-olive font-medium' : 'text-gray-700 hover:bg-gray-100',
              !isMonthSelectable(index) ? 'opacity-30 cursor-not-allowed' : ''
            ]"
          >
            {{ month }}
          </button>
        </div>
      </div>

      <!-- Year Dropdown -->
      <div class="relative">
        <button
          @click="showYearDropdown = !showYearDropdown; showMonthDropdown = false"
          class="flex items-center justify-between px-3 py-2 text-sm font-semibold text-gray-800 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors gap-2"
        >
          {{ currentYear }}
          <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        <div
          v-if="showYearDropdown"
          class="absolute right-0 mt-1 w-24 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50 max-h-48 overflow-y-auto"
        >
          <button
            v-for="year in yearOptions"
            :key="year"
            @click="setYear(year); showYearDropdown = false"
            :class="[
              'w-full text-left px-3 py-1.5 text-sm transition-colors',
              currentYear === year ? 'bg-aviation-olive/10 text-aviation-olive font-medium' : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            {{ year }}
          </button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-7 gap-1 text-center text-sm">
      <div v-for="day in daysOfWeek" :key="day" class="font-semibold text-gray-600 py-2">
        {{ day }}
      </div>

      <div
        v-for="(day, index) in calendarDays"
        :key="index"
        @click="selectDay(day)"
        :class="[
          'p-2 rounded cursor-pointer transition-colors relative',
          day.isCurrentMonth ? 'text-gray-800' : 'text-gray-300',
          day.isToday ? 'bg-aviation-olive text-white font-bold' : 'hover:bg-gray-100',
          day.hasEvents ? 'font-semibold' : ''
        ]"
      >
        <div>{{ day.date }}</div>
        <div v-if="day.hasEvents && day.isCurrentMonth" class="absolute bottom-1 left-1/2 transform -translate-x-1/2 flex gap-0.5">
          <div
            v-for="(event, idx) in day.events.slice(0, 3)"
            :key="idx"
            :class="[
              'w-1 h-1 rounded-full',
              event.type === 'facility' ? 'bg-blue-500' : 'bg-orange-500'
            ]"
          ></div>
        </div>
      </div>
    </div>

    <!-- Events Modal -->
    <div
      v-if="selectedDay"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
      @click.self="selectedDay = null"
    >
      <div class="bg-white rounded-xl shadow-2xl max-w-md w-full max-h-[80vh] overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-800">
            Events on {{ formatDate(selectedDay.fullDate) }}
          </h3>
          <button @click="selectedDay = null" class="p-1 hover:bg-gray-100 rounded">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div class="p-4 overflow-y-auto max-h-[60vh]">
          <div v-if="selectedDay.events.length === 0" class="text-center text-gray-500 py-8">
            No events on this day
          </div>
          <div v-else class="space-y-3">
            <div
              v-for="event in selectedDay.events"
              :key="`${event.type}-${event.id}`"
              class="p-3 rounded-lg border"
              :class="event.type === 'facility' ? 'bg-blue-50 border-blue-200' : 'bg-orange-50 border-orange-200'"
            >
              <div class="flex items-start justify-between mb-2">
                <div class="flex items-center gap-2">
                  <div
                    class="w-2 h-2 rounded-full"
                    :class="event.type === 'facility' ? 'bg-blue-500' : 'bg-orange-500'"
                  ></div>
                  <h4 class="font-semibold text-gray-800">{{ event.title }}</h4>
                </div>
                <span
                  class="px-2 py-1 text-xs rounded capitalize"
                  :class="getStatusBadgeClass(event.status)"
                >
                  {{ event.status }}
                </span>
              </div>
              <p class="text-xs text-gray-600 mb-1">
                <span class="font-medium">Type:</span> {{ event.type === 'facility' ? 'Facility Request' : 'Work Order' }}
              </p>
              <p v-if="event.priority" class="text-xs text-gray-600 mb-2">
                <span class="font-medium">Priority:</span>
                <span :class="getPriorityClass(event.priority)" class="capitalize">{{ event.priority }}</span>
              </p>
              <button
                @click="openEditModal(event)"
                class="mt-2 w-full px-3 py-1.5 text-xs font-medium text-white rounded-lg transition-colors"
                :class="event.type === 'facility' ? 'bg-blue-500 hover:bg-blue-600' : 'bg-orange-500 hover:bg-orange-600'"
              >
                Change Date
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Date Modal -->
    <div
      v-if="editingEvent"
      class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black bg-opacity-50"
      @click.self="closeEditModal"
    >
      <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full">
        <div class="p-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800">Update Event Date</h3>
        </div>
        <div class="p-4 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Event</label>
            <p class="text-sm text-gray-600">{{ editingEvent.title }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Current Date</label>
            <p class="text-sm text-gray-600 mb-3">{{ formatEditDate(editingEvent.date) }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">New Date</label>
            <input
              v-model="newDate"
              type="date"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent bg-white text-black"
            />
          </div>
          <div v-if="updateError" class="p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-600">{{ updateError }}</p>
          </div>
          <div v-if="updateSuccess" class="p-3 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm text-green-600">{{ updateSuccess }}</p>
          </div>
        </div>
        <div class="p-4 border-t border-gray-200 flex justify-center gap-8">
          <button
            @click="closeEditModal"
            :disabled="updating"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50"
          >
            Cancel
          </button>
          <button
            @click="updateEventDate"
            :disabled="updating || !newDate"
            class="px-4 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 transition-colors disabled:opacity-50"
          >
            {{ updating ? 'Updating...' : 'Update Date' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { API_URL } from '@/config/api';

interface CalendarEvent {
  id: number;
  type: 'facility' | 'work_order';
  title: string;
  date: string;
  status: string;
  priority?: string;
}

interface Props {
  events?: CalendarEvent[];
}

const props = defineProps<Props>();
const emit = defineEmits(['monthChange', 'dateUpdated']);

const currentDate = ref(new Date());
const showMonthDropdown = ref(false);
const showYearDropdown = ref(false);
const selectedDay = ref<any>(null);
const editingEvent = ref<CalendarEvent | null>(null);
const newDate = ref('');
const updating = ref(false);
const updateError = ref('');
const updateSuccess = ref('');

const daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];


const getEventsForDate = (year: number, month: number, day: number) => {
  if (!props.events) return [];
  const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
  return props.events.filter(event => event.date === dateStr);
};

const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear();
  const month = currentDate.value.getMonth();

  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);

  const firstDayOfWeek = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;
  const daysInMonth = lastDay.getDate();

  const days = [];

  // Previous month days
  const prevMonthLastDay = new Date(year, month, 0).getDate();
  const prevMonth = month === 0 ? 11 : month - 1;
  const prevYear = month === 0 ? year - 1 : year;

  for (let i = firstDayOfWeek - 1; i >= 0; i--) {
    const day = prevMonthLastDay - i;
    const events = getEventsForDate(prevYear, prevMonth, day);
    days.push({
      date: day,
      fullDate: new Date(prevYear, prevMonth, day),
      isCurrentMonth: false,
      isToday: false,
      hasEvents: events.length > 0,
      events
    });
  }

  // Current month days
  const today = new Date();
  for (let i = 1; i <= daysInMonth; i++) {
    const events = getEventsForDate(year, month, i);
    days.push({
      date: i,
      fullDate: new Date(year, month, i),
      isCurrentMonth: true,
      isToday: year === today.getFullYear() && month === today.getMonth() && i === today.getDate(),
      hasEvents: events.length > 0,
      events
    });
  }

  // Next month days to fill the grid
  const remainingDays = 42 - days.length;
  const nextMonth = month === 11 ? 0 : month + 1;
  const nextYear = month === 11 ? year + 1 : year;

  for (let i = 1; i <= remainingDays; i++) {
    const events = getEventsForDate(nextYear, nextMonth, i);
    days.push({
      date: i,
      fullDate: new Date(nextYear, nextMonth, i),
      isCurrentMonth: false,
      isToday: false,
      hasEvents: events.length > 0,
      events
    });
  }

  return days;
});

const currentYear = computed(() => {
  return currentDate.value.getFullYear();
});

const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

const monthName = computed(() => {
  return monthNames[currentDate.value.getMonth()];
});

const yearOptions = computed(() => {
  const now = new Date();
  const thisYear = now.getFullYear();
  const years = [];
  for (let y = thisYear - 5; y <= thisYear; y++) {
    years.push(y);
  }
  return years;
});

const isMonthSelectable = (monthIndex: number) => {
  const now = new Date();
  if (currentDate.value.getFullYear() < now.getFullYear()) return true;
  return monthIndex <= now.getMonth();
};

const setMonth = (monthIndex: number) => {
  currentDate.value = new Date(currentDate.value.getFullYear(), monthIndex, 1);
};

const setYear = (year: number) => {
  const now = new Date();
  let month = currentDate.value.getMonth();
  // If selecting current year and current month is beyond now, clamp to current month
  if (year === now.getFullYear() && month > now.getMonth()) {
    month = now.getMonth();
  }
  currentDate.value = new Date(year, month, 1);
};



const selectDay = (day: any) => {
  if (day.isCurrentMonth) {
    selectedDay.value = day;
  }
};

const formatDate = (date: Date) => {
  return date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
};

const getStatusBadgeClass = (status: string) => {
  const classes: { [key: string]: string } = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    canceled: 'bg-gray-100 text-gray-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getPriorityClass = (priority: string) => {
  const classes: { [key: string]: string } = {
    urgent: 'text-red-600 font-bold',
    high: 'text-orange-600 font-semibold',
    medium: 'text-yellow-600',
    low: 'text-green-600',
  };
  return classes[priority] || '';
};

const formatEditDate = (dateStr: string) => {
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};

const openEditModal = (event: CalendarEvent) => {
  editingEvent.value = event;
  newDate.value = event.date;
  updateError.value = '';
  updateSuccess.value = '';
};

const closeEditModal = () => {
  editingEvent.value = null;
  newDate.value = '';
  updateError.value = '';
  updateSuccess.value = '';
};

const updateEventDate = async () => {
  if (!editingEvent.value || !newDate.value) return;

  updating.value = true;
  updateError.value = '';
  updateSuccess.value = '';

  try {
    const token = localStorage.getItem('token');
    const endpoint = editingEvent.value.type === 'facility'
      ? 'facility-requests'
      : 'work-orders';

    const dateField = editingEvent.value.type === 'facility'
      ? 'date_of_event'
      : 'date';

    const response = await fetch(`${API_URL}/${endpoint}/${editingEvent.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        [dateField]: newDate.value
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to update date');
    }

    updateSuccess.value = 'Date updated successfully!';

    // Emit event to refresh data
    emit('dateUpdated');

    // Close modal after delay
    setTimeout(() => {
      closeEditModal();
      selectedDay.value = null;
    }, 1500);
  } catch (err: any) {
    updateError.value = err.message || 'Failed to update date';
  } finally {
    updating.value = false;
  }
};

// Watch for month changes and emit event
watch(currentDate, () => {
  emit('monthChange', {
    month: currentDate.value.getMonth() + 1,
    year: currentDate.value.getFullYear()
  });
});
</script>
