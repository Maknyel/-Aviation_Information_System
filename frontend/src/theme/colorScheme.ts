import { API_URL } from '@/config/api';

/**
 * Single source of truth for the system color scheme.
 * Defaults are the official NAAP colors; the Admin can change them from the
 * Color Scheme page (/color-scheme) and the saved values apply to everyone.
 */
export interface ColorScheme {
  primary: string; // Deep Blue  - header, buttons, links
  accent: string;  // Golden Yellow - highlights, active markers
  surface: string; // White - page / card backgrounds
}

export const DEFAULT_COLOR_SCHEME: ColorScheme = {
  primary: '#002F6C',
  accent: '#FFCC00',
  surface: '#FFFFFF',
};

const CACHE_KEY = 'color_scheme';

export const isHexColor = (v: string) => /^#[0-9a-fA-F]{6}$/.test(v);

export const hexToRgb = (hex: string): [number, number, number] => {
  const n = parseInt(hex.slice(1), 16);
  return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
};

const mix = (hex: string, target: [number, number, number], amount: number) => {
  const [r, g, b] = hexToRgb(hex);
  const c = (from: number, to: number) => Math.round(from + (to - from) * amount);
  return `rgb(${c(r, target[0])}, ${c(g, target[1])}, ${c(b, target[2])})`;
};

/** Writes the scheme to CSS variables on :root (Tailwind and Ionic both read them). */
export const applyColorScheme = (scheme: ColorScheme) => {
  const root = document.documentElement.style;
  const set = (name: string, hex: string) => {
    root.setProperty(`--color-${name}`, hex);
    root.setProperty(`--color-${name}-rgb`, hexToRgb(hex).join(' '));
  };
  set('primary', scheme.primary);
  set('accent', scheme.accent);
  set('surface', scheme.surface);

  const pr = hexToRgb(scheme.primary);
  root.setProperty('--ion-color-primary', scheme.primary);
  root.setProperty('--ion-color-primary-rgb', pr.join(', '));
  root.setProperty('--ion-color-primary-contrast', '#ffffff');
  root.setProperty('--ion-color-primary-shade', mix(scheme.primary, [0, 0, 0], 0.12));
  root.setProperty('--ion-color-primary-tint', mix(scheme.primary, [255, 255, 255], 0.12));
  const ar = hexToRgb(scheme.accent);
  root.setProperty('--ion-color-secondary', scheme.accent);
  root.setProperty('--ion-color-secondary-rgb', ar.join(', '));
  root.setProperty('--ion-color-secondary-contrast', '#000000');
};

export const getCachedColorScheme = (): ColorScheme => {
  try {
    const raw = localStorage.getItem(CACHE_KEY);
    if (raw) {
      const parsed = JSON.parse(raw);
      if (isHexColor(parsed.primary) && isHexColor(parsed.accent) && isHexColor(parsed.surface)) {
        return parsed;
      }
    }
  } catch { /* fall through to defaults */ }
  return { ...DEFAULT_COLOR_SCHEME };
};

const cacheColorScheme = (scheme: ColorScheme) => {
  try { localStorage.setItem(CACHE_KEY, JSON.stringify(scheme)); } catch { /* ignore */ }
};

/** Apply the cached scheme immediately (no flash), then refresh from the server. */
export const initColorScheme = async () => {
  applyColorScheme(getCachedColorScheme());
  try {
    const res = await fetch(`${API_URL}/settings/color-scheme`, { headers: { Accept: 'application/json' } });
    const data = await res.json();
    if (data.success) {
      applyColorScheme(data.data);
      cacheColorScheme(data.data);
    }
  } catch { /* keep cached / default scheme when offline */ }
};

const authHeaders = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
  Accept: 'application/json',
  'Content-Type': 'application/json',
});

export const saveColorScheme = async (scheme: ColorScheme): Promise<ColorScheme> => {
  const res = await fetch(`${API_URL}/settings/color-scheme`, {
    method: 'PUT',
    headers: authHeaders(),
    body: JSON.stringify(scheme),
  });
  const data = await res.json();
  if (!res.ok) throw new Error(data.message || 'Failed to save color scheme');
  applyColorScheme(data.data);
  cacheColorScheme(data.data);
  return data.data;
};

export const resetColorScheme = async (): Promise<ColorScheme> => {
  const res = await fetch(`${API_URL}/settings/color-scheme/reset`, { method: 'POST', headers: authHeaders() });
  const data = await res.json();
  if (!res.ok) throw new Error(data.message || 'Failed to reset color scheme');
  applyColorScheme(data.data);
  cacheColorScheme(data.data);
  return data.data;
};

/** Chart colors derived from the active scheme: primary, accent, then tints of primary. */
export const getChartColors = (count = 7): string[] => {
  const { primary, accent } = getCachedColorScheme();
  const tints = [0.25, 0.4, 0.55, 0.7, 0.82].map(a => mix(primary, [255, 255, 255], a));
  return [primary, accent, ...tints].slice(0, Math.max(count, 1)).concat(
    Array.from({ length: Math.max(count - 7, 0) }, (_, i) => tints[i % tints.length])
  );
};
