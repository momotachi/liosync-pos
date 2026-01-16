/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        "primary": "#059669", // Emerald 600
        "primary-hover": "#047857",
        "accent": "#f97316", // Orange 500
        "accent-hover": "#ea580c",
        "background-light": "#f9fafb",
        "background-dark": "#111827",
        "surface-light": "#ffffff",
        "surface-dark": "#1a2e26",
        "card-light": "#ffffff",
        "card-dark": "#1f2937",
        "input-light": "#f3f4f6",
        "input-dark": "#374151",
        "border-light": "#e5e7eb",
        "border-dark": "#4b5563",
      },
      fontFamily: {
        "display": ["Inter", "sans-serif"],
        "body": ["Inter", "sans-serif"],
      },
      boxShadow: {
        "soft": "0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03)",
      },
      borderRadius: {
        DEFAULT: "0.75rem",
      },
    },
  },
  plugins: [],
}
