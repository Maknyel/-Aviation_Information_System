import { ref } from 'vue';

export interface Toast {
  id: number;
  type: 'success' | 'error' | 'info';
  message: string;
}

const toasts = ref<Toast[]>([]);
let nextId = 1;

function push(type: Toast['type'], message: string, duration = 4000) {
  const id = nextId++;
  toasts.value.push({ id, type, message });
  setTimeout(() => remove(id), duration);
  return id;
}

function remove(id: number) {
  const index = toasts.value.findIndex((t) => t.id === id);
  if (index !== -1) toasts.value.splice(index, 1);
}

export function useToast() {
  return {
    toasts,
    success: (message: string, duration?: number) => push('success', message, duration),
    error: (message: string, duration?: number) => push('error', message, duration),
    info: (message: string, duration?: number) => push('info', message, duration),
    remove,
  };
}
