<template>
  <AppLayout>
    <div class="max-w-full mx-auto">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Calendar</h1>
        <p class="text-gray-600">View and manage your schedule</p>
      </div>

      <!-- 3 Column Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Column 1: Mini Calendar (2 columns wide) -->
        <div class="lg:col-span-3">
          <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-4">
            <div class="flex items-center gap-2 mb-4">
              <!-- Mini Month Dropdown -->
              <div class="relative flex-1">
                <button
                  @click="showMiniMonthDropdown = !showMiniMonthDropdown; showMiniYearDropdown = false"
                  class="w-full flex items-center justify-between px-2 py-1.5 text-xs font-semibold text-gray-800 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  {{ miniMonthName }}
                  <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
                <div
                  v-if="showMiniMonthDropdown"
                  class="absolute left-0 mt-1 w-full bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50 max-h-48 overflow-y-auto"
                >
                  <button
                    v-for="(month, index) in monthNames"
                    :key="index"
                    @click="setMiniMonth(index); showMiniMonthDropdown = false"
                    :class="[
                      'w-full text-left px-2 py-1.5 text-xs transition-colors',
                      miniCurrentDate.getMonth() === index ? 'bg-aviation-olive/10 text-aviation-olive font-medium' : 'text-gray-700 hover:bg-gray-100'
                    ]"
                  >
                    {{ month }}
                  </button>
                </div>
              </div>
              <!-- Mini Year Dropdown -->
              <div class="relative">
                <button
                  @click="showMiniYearDropdown = !showMiniYearDropdown; showMiniMonthDropdown = false"
                  class="flex items-center justify-between px-2 py-1.5 text-xs font-semibold text-gray-800 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors gap-1"
                >
                  {{ miniCurrentDate.getFullYear() }}
                  <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
                <div
                  v-if="showMiniYearDropdown"
                  class="absolute right-0 mt-1 w-20 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50 max-h-48 overflow-y-auto"
                >
                  <button
                    v-for="year in yearOptions"
                    :key="year"
                    @click="setMiniYear(year); showMiniYearDropdown = false"
                    :class="[
                      'w-full text-left px-2 py-1.5 text-xs transition-colors',
                      miniCurrentDate.getFullYear() === year ? 'bg-aviation-olive/10 text-aviation-olive font-medium' : 'text-gray-700 hover:bg-gray-100'
                    ]"
                  >
                    {{ year }}
                  </button>
                </div>
              </div>
            </div>
            <div class="grid grid-cols-7 gap-1 text-center text-xs">
              <div v-for="day in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" :key="day" class="text-gray-600 font-medium py-1">
                {{ day }}
              </div>
              <div
                v-for="date in miniCalendarDates"
                :key="date.key"
                @click="selectDate(date)"
                :class="[
                  'py-1 cursor-pointer rounded',
                  date.isCurrentMonth ? 'text-gray-900' : 'text-gray-400',
                  date.isToday ? 'bg-aviation-olive text-white' : 'hover:bg-gray-200',
                  date.isSelected && !date.isToday ? 'bg-blue-100 text-blue-900' : ''
                ]"
              >
                {{ date.date }}
              </div>
            </div>

            <!-- People Section -->
            <div class="mt-6 pt-6 border-t border-gray-200">
              <h3 class="text-sm font-semibold text-gray-800 mb-3">People</h3>
              <div class="space-y-2">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold">
                    SSC
                  </div>
                  <span class="text-sm text-gray-700">Supreme Student Council</span>
                </div>
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                    AMT
                  </div>
                  <span class="text-sm text-gray-700">AMT Organization</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Column 2 & 3: Main Calendar (10 columns wide) -->
        <div class="lg:col-span-9">
          <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 relative">
            <!-- Calendar Header -->
            <div class="flex items-center gap-3 mb-6">
              <!-- Main Month Dropdown -->
              <div class="relative">
                <button
                  @click="showMainMonthDropdown = !showMainMonthDropdown; showMainYearDropdown = false"
                  class="flex items-center gap-2 px-4 py-2 text-2xl font-bold text-gray-800 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  {{ mainMonthName }}
                  <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
                <div
                  v-if="showMainMonthDropdown"
                  class="absolute left-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50 max-h-48 overflow-y-auto"
                >
                  <button
                    v-for="(month, index) in monthNames"
                    :key="index"
                    @click="setMainMonth(index); showMainMonthDropdown = false"
                    :class="[
                      'w-full text-left px-3 py-1.5 text-sm transition-colors',
                      mainCurrentDate.getMonth() === index ? 'bg-aviation-olive/10 text-aviation-olive font-medium' : 'text-gray-700 hover:bg-gray-100'
                    ]"
                  >
                    {{ month }}
                  </button>
                </div>
              </div>
              <!-- Main Year Dropdown -->
              <div class="relative">
                <button
                  @click="showMainYearDropdown = !showMainYearDropdown; showMainMonthDropdown = false"
                  class="flex items-center gap-2 px-4 py-2 text-2xl font-bold text-gray-800 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  {{ mainCurrentDate.getFullYear() }}
                  <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
                <div
                  v-if="showMainYearDropdown"
                  class="absolute left-0 mt-1 w-28 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50 max-h-48 overflow-y-auto"
                >
                  <button
                    v-for="year in yearOptions"
                    :key="year"
                    @click="setMainYear(year); showMainYearDropdown = false"
                    :class="[
                      'w-full text-left px-3 py-1.5 text-sm transition-colors',
                      mainCurrentDate.getFullYear() === year ? 'bg-aviation-olive/10 text-aviation-olive font-medium' : 'text-gray-700 hover:bg-gray-100'
                    ]"
                  >
                    {{ year }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Loading Overlay -->
            <div v-if="loading" class="absolute inset-0 bg-white bg-opacity-70 flex items-center justify-center z-10 rounded-xl">
              <div class="text-gray-500 font-medium">Loading events...</div>
            </div>

            <!-- Calendar Grid -->
            <div class="grid grid-cols-7 gap-px bg-gray-200 border border-gray-200 rounded-lg overflow-hidden">
              <!-- Day Headers -->
              <div
                v-for="day in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']"
                :key="day"
                class="bg-gray-50 p-3 text-center text-sm font-semibold text-gray-700"
              >
                {{ day }}
              </div>

              <!-- Calendar Dates -->
              <div
                v-for="date in mainCalendarDates"
                :key="date.key"
                :class="[
                  'bg-white p-2 min-h-[100px] relative',
                  date.isCurrentMonth ? '' : 'bg-gray-50'
                ]"
              >
                <div
                  :class="[
                    'text-sm font-medium mb-1',
                    date.isCurrentMonth ? 'text-gray-900' : 'text-gray-400',
                    date.isToday ? 'w-6 h-6 bg-aviation-olive text-white rounded-full flex items-center justify-center' : ''
                  ]"
                >
                  {{ date.date }}
                </div>

                <!-- Events for this date -->
                <div class="space-y-1">
                  <div
                    v-for="event in date.events"
                    :key="event.id"
                    @click="viewEventDetails(event)"
                    :class="[
                      'text-xs p-2 rounded cursor-pointer shadow-sm',
                      event.type === 'facility' ? 'bg-blue-500 text-white' : 'bg-orange-400 text-white'
                    ]"
                  >
                    <div class="font-semibold mb-0.5">{{ event.organization || 'Organization' }}</div>
                    <div class="font-medium">{{ event.title }}</div>
                    <div class="text-xs opacity-90 mt-1">{{ event.time }}</div>
                    <div class="text-xs opacity-90 capitalize">{{ event.status }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Event Details Modal -->
    <FacilityRequestDetailsModal
      v-model="showFacilityModal"
      :request="selectedFacilityRequest"
      @statusUpdated="handleStatusUpdated"
    />
    <WorkOrderDetailsModal
      v-model="showWorkOrderModal"
      :order="selectedWorkOrder"
      @statusUpdated="handleStatusUpdated"
    />
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import FacilityRequestDetailsModal from '@/components/FacilityRequestDetailsModal.vue';
import WorkOrderDetailsModal from '@/components/WorkOrderDetailsModal.vue';
import { API_URL } from '@/config/api';

const miniCurrentDate = ref(new Date());
const mainCurrentDate = ref(new Date());
const selectedDate = ref<Date | null>(null);
const events = ref<any[]>([]);
const loading = ref(false);
const showFacilityModal = ref(false);
const showWorkOrderModal = ref(false);
const selectedFacilityRequest = ref<any>(null);
const selectedWorkOrder = ref<any>(null);
const showMiniMonthDropdown = ref(false);
const showMiniYearDropdown = ref(false);
const showMainMonthDropdown = ref(false);
const showMainYearDropdown = ref(false);

const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

const miniMonthName = computed(() => monthNames[miniCurrentDate.value.getMonth()]);
const mainMonthName = computed(() => monthNames[mainCurrentDate.value.getMonth()]);

const yearOptions = computed(() => {
  const thisYear = new Date().getFullYear();
  const years = [];
  for (let y = thisYear - 5; y <= thisYear + 1; y++) {
    years.push(y);
  }
  return years;
});

function setMiniMonth(index: number) {
  miniCurrentDate.value = new Date(miniCurrentDate.value.getFullYear(), index, 1);
  mainCurrentDate.value = new Date(miniCurrentDate.value);
}

function setMiniYear(year: number) {
  miniCurrentDate.value = new Date(year, miniCurrentDate.value.getMonth(), 1);
  mainCurrentDate.value = new Date(miniCurrentDate.value);
}

function setMainMonth(index: number) {
  mainCurrentDate.value = new Date(mainCurrentDate.value.getFullYear(), index, 1);
  miniCurrentDate.value = new Date(mainCurrentDate.value);
}

function setMainYear(year: number) {
  mainCurrentDate.value = new Date(year, mainCurrentDate.value.getMonth(), 1);
  miniCurrentDate.value = new Date(mainCurrentDate.value);
}


const miniCalendarDates = computed(() => {
  return generateCalendarDates(miniCurrentDate.value, false);
});

const mainCalendarDates = computed(() => {
  return generateCalendarDates(mainCurrentDate.value, true);
});

function generateCalendarDates(currentDate: Date, includeEvents: boolean) {
  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();

  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);

  // Get day of week (0 = Sunday, 1 = Monday, etc.)
  // Adjust so Monday = 0
  let startDayOfWeek = firstDay.getDay() - 1;
  if (startDayOfWeek === -1) startDayOfWeek = 6;

  const dates = [];
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  // Previous month days
  const prevMonthLastDay = new Date(year, month, 0).getDate();
  for (let i = startDayOfWeek - 1; i >= 0; i--) {
    const date = prevMonthLastDay - i;
    const dateObj = new Date(year, month - 1, date);
    dates.push({
      date,
      dateObj,
      isCurrentMonth: false,
      isToday: false,
      isSelected: selectedDate.value?.toDateString() === dateObj.toDateString(),
      key: `prev-${date}`,
      events: []
    });
  }

  // Current month days
  for (let date = 1; date <= lastDay.getDate(); date++) {
    const dateObj = new Date(year, month, date);
    const isToday = dateObj.toDateString() === today.toDateString();
    const dayEvents = includeEvents ? getEventsForDate(dateObj) : [];

    dates.push({
      date,
      dateObj,
      isCurrentMonth: true,
      isToday,
      isSelected: selectedDate.value?.toDateString() === dateObj.toDateString(),
      key: `current-${date}`,
      events: dayEvents
    });
  }

  // Next month days
  const remainingDays = 42 - dates.length; // 6 rows * 7 days
  for (let date = 1; date <= remainingDays; date++) {
    const dateObj = new Date(year, month + 1, date);
    dates.push({
      date,
      dateObj,
      isCurrentMonth: false,
      isToday: false,
      isSelected: selectedDate.value?.toDateString() === dateObj.toDateString(),
      key: `next-${date}`,
      events: []
    });
  }

  return dates;
}

function getEventsForDate(date: Date) {
  return events.value.filter(event => {
    const eventDate = new Date(event.date);
    eventDate.setHours(0, 0, 0, 0);
    date.setHours(0, 0, 0, 0);
    return eventDate.toDateString() === date.toDateString();
  });
}


function selectDate(dateInfo: any) {
  selectedDate.value = dateInfo.dateObj;
  mainCurrentDate.value = new Date(dateInfo.dateObj.getFullYear(), dateInfo.dateObj.getMonth(), 1);
}

async function fetchEvents() {
  loading.value = true;
  try {
    const token = localStorage.getItem('token');
    const month = mainCurrentDate.value.getMonth() + 1;
    const year = mainCurrentDate.value.getFullYear();
    const headers = {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    };

    // Fetch calendar events and full request details in parallel
    const [calendarRes, facilityRes, workOrderRes] = await Promise.all([
      fetch(`${API_URL}/dashboard/calendar-events?month=${month}&year=${year}`, { headers }),
      fetch(`${API_URL}/facility-requests`, { headers }),
      fetch(`${API_URL}/work-orders`, { headers }),
    ]);

    const [calendarData, facilityData, workOrderData] = await Promise.all([
      calendarRes.json(),
      facilityRes.json(),
      workOrderRes.json(),
    ]);

    // Build lookup maps for full request data (needed for detail modals)
    const facilityMap = new Map<number, any>();
    (facilityData.data || []).forEach((r: any) => facilityMap.set(r.id, r));

    const workOrderMap = new Map<number, any>();
    (workOrderData.data || []).forEach((o: any) => workOrderMap.set(o.id, o));

    // Transform calendar events with full data attached
    events.value = (calendarData.data || []).map((event: any) => {
      if (event.type === 'facility') {
        const request = facilityMap.get(event.id);
        return {
          id: `facility-${event.id}`,
          type: 'facility',
          organization: request?.user?.name || 'Organization',
          title: event.title || 'Facility Request',
          subtitle: request?.title_of_event || '',
          date: event.date,
          time: request?.time_of_event || '',
          status: event.status,
          data: request || event
        };
      } else {
        const order = workOrderMap.get(event.id);
        return {
          id: `workorder-${event.id}`,
          type: 'workorder',
          organization: order?.user?.name || order?.requisitioner || 'Organization',
          title: event.title || 'Work Order',
          subtitle: order?.description_of_problem?.substring(0, 30) || '',
          date: event.date,
          time: order?.time || '',
          status: event.status,
          priority: event.priority,
          data: order || event
        };
      }
    });
  } catch (error) {
    console.error('Error fetching events:', error);
  } finally {
    loading.value = false;
  }
}

function viewEventDetails(event: any) {
  if (event.type === 'facility') {
    selectedFacilityRequest.value = event.data;
    showFacilityModal.value = true;
  } else if (event.type === 'workorder') {
    selectedWorkOrder.value = event.data;
    showWorkOrderModal.value = true;
  }
}

function handleStatusUpdated() {
  fetchEvents();
}

// Re-fetch events when main calendar month/year changes
watch(mainCurrentDate, () => {
  fetchEvents();
});

onMounted(() => {
  fetchEvents();
});
</script>
