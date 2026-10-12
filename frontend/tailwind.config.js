/** @type {import('tailwindcss').Config} */
// Colors read CSS variables set in src/theme/variables.css and overridden at
// runtime by src/theme/colorScheme.ts, so changing the scheme re-skins everything.
// "aviation-olive" / "aviation-white" are legacy names kept so existing classes
// pick up the new scheme; they now resolve to the primary (Deep Blue) and surface (White).
const themeColor = (name) => `rgb(var(--color-${name}-rgb) / <alpha-value>)`;

export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'aviation-olive': themeColor('primary'),
        'aviation-white': themeColor('surface'),
        'aviation-gold': themeColor('accent'),
        'brand-primary': themeColor('primary'),
        'brand-accent': themeColor('accent'),
        'brand-surface': themeColor('surface'),
      },
    },
  },
  plugins: [],
}
