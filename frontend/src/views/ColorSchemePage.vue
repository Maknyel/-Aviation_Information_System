<template>
  <AppLayout>
    <div class="max-w-4xl mx-auto">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Color Scheme</h1>
        <p class="text-gray-600">Change the system colors in one place. Saved colors apply to every page and every user.</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Editor -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">Theme Colors</h2>

          <div v-for="field in fields" :key="field.key" class="mb-5">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ field.label }}</label>
            <p class="text-xs text-gray-500 mb-2">{{ field.hint }}</p>
            <div class="flex items-center gap-3">
              <input
                type="color"
                :value="form[field.key]"
                @input="form[field.key] = ($event.target as HTMLInputElement).value.toUpperCase()"
                class="w-12 h-10 p-0 border border-gray-300 rounded cursor-pointer bg-white"
              />
              <input
                v-model="form[field.key]"
                type="text"
                maxlength="7"
                placeholder="#000000"
                class="w-32 px-3 py-2 border rounded-lg text-sm uppercase bg-white text-black focus:ring-2 focus:ring-aviation-olive"
                :class="isHexColor(form[field.key]) ? 'border-gray-300' : 'border-red-400'"
              />
              <span class="text-xs text-gray-400">Default {{ DEFAULT_COLOR_SCHEME[field.key] }}</span>
            </div>
          </div>

          <div class="flex flex-wrap gap-3 pt-2">
            <button
              @click="save"
              :disabled="saving || !valid"
              class="px-4 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 disabled:opacity-50"
            >
              {{ saving ? 'Saving...' : 'Save Color Scheme' }}
            </button>
            <button
              @click="reset"
              :disabled="saving"
              class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            >
              Reset to NAAP Defaults
            </button>
          </div>
        </div>

        <!-- Live preview (uses the colors being edited, not the saved ones) -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">Preview</h2>
          <div class="rounded-lg overflow-hidden border border-gray-200" :style="{ backgroundColor: preview.surface }">
            <div class="px-4 py-3 text-white font-semibold" :style="{ backgroundColor: preview.primary, borderBottom: `4px solid ${preview.accent}` }">
              General Service Department
            </div>
            <div class="flex">
              <div class="w-32 p-3 space-y-2 border-r border-gray-200">
                <div class="px-3 py-2 rounded text-white text-sm" :style="{ backgroundColor: preview.primary, borderLeft: `4px solid ${preview.accent}` }">Dashboard</div>
                <div class="px-3 py-2 rounded text-gray-700 text-sm">Requests</div>
              </div>
              <div class="flex-1 p-4 space-y-3">
                <p class="font-semibold" :style="{ color: preview.primary }">Facility Request</p>
                <button class="px-3 py-1.5 rounded text-white text-sm" :style="{ backgroundColor: preview.primary }">Submit</button>
                <div class="flex gap-2">
                  <span class="status-badge status-approved px-2 py-0.5 text-xs rounded-full">Approved</span>
                  <span class="status-badge status-disapproved px-2 py-0.5 text-xs rounded-full">Disapproved</span>
                </div>
              </div>
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-3">
            Approved (green) and Disapproved (red) status colors are fixed and are not affected by the theme.
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { reactive, computed, onMounted, ref } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import { useToast } from '@/composables/useToast';
import {
  ColorScheme,
  DEFAULT_COLOR_SCHEME,
  getCachedColorScheme,
  isHexColor,
  saveColorScheme,
  resetColorScheme,
} from '@/theme/colorScheme';

const toast = useToast();
const saving = ref(false);
const form = reactive<ColorScheme>({ ...getCachedColorScheme() });

const fields: { key: keyof ColorScheme; label: string; hint: string }[] = [
  { key: 'primary', label: 'Deep Blue (Primary)', hint: 'Header, sidebar highlight, buttons, links and headings.' },
  { key: 'accent', label: 'Golden Yellow (Accent)', hint: 'Header underline and active-menu marker.' },
  { key: 'surface', label: 'White (Background)', hint: 'Page background.' },
];

const valid = computed(() => fields.every(f => isHexColor(form[f.key])));

const preview = computed<ColorScheme>(() => ({
  primary: isHexColor(form.primary) ? form.primary : DEFAULT_COLOR_SCHEME.primary,
  accent: isHexColor(form.accent) ? form.accent : DEFAULT_COLOR_SCHEME.accent,
  surface: isHexColor(form.surface) ? form.surface : DEFAULT_COLOR_SCHEME.surface,
}));

const save = async () => {
  saving.value = true;
  try {
    Object.assign(form, await saveColorScheme({ ...form }));
    toast.success('Color scheme saved');
  } catch (e: any) {
    toast.error(e.message || 'Failed to save color scheme');
  } finally {
    saving.value = false;
  }
};

const reset = async () => {
  saving.value = true;
  try {
    Object.assign(form, await resetColorScheme());
    toast.success('Color scheme reset to NAAP defaults');
  } catch (e: any) {
    toast.error(e.message || 'Failed to reset color scheme');
  } finally {
    saving.value = false;
  }
};

onMounted(() => Object.assign(form, getCachedColorScheme()));
</script>
