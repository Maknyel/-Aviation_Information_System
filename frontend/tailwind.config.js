/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'aviation-olive': '#6C9A6C',
        'aviation-white': '#F0F5F0',
      },
    },
  },
  plugins: [],
}
